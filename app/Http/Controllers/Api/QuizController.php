<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\QuizQuestion;
use App\Models\QuizOption;
use App\Models\QuizAttempt;
use App\Models\UserQuizAnswer;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class QuizController extends Controller
{
    public function getQuestions()
    {
        // Ambil soal beserta pilihan jawabannya
        $questions = QuizQuestion::with('options')->get();
        return response()->json($questions);
    }

    public function submitQuiz(Request $request)
    {
        $request->validate([
            'answers' => 'required|array', // Format: [['question_id' => 1, 'option_id' => 2], ...]
        ]);

        $user = $request->user();
        
        // Inisiasi skor
        $scores = [
            'prestige' => 0,
            'peaceful_calm' => 0,
            'rebel_brave' => 0,
            'sweet_shy' => 0,
        ];

        DB::beginTransaction();
        try {
            // Hitung skor dari setiap jawaban
            foreach ($request->answers as $answer) {
                $option = QuizOption::find($answer['option_id']);
                if ($option) {
                    $scores['prestige'] += $option->prestige_score;
                    $scores['peaceful_calm'] += $option->peaceful_calm_score;
                    $scores['rebel_brave'] += $option->rebel_brave_score;
                    $scores['sweet_shy'] += $option->sweet_shy_score;
                }
            }

            // Cari kepribadian dominan
            $dominantPersonality = array_keys($scores, max($scores))[0];

            // Cari rekomendasi produk berdasarkan kepribadian dominan
            $recommendedProduct = Product::where('personality_type', $dominantPersonality)
                                         ->where('stock_status', '!=', 'habis')
                                         ->inRandomOrder()
                                         ->first();

            // Simpan Quiz Attempt
            $attempt = QuizAttempt::create([
                'user_id' => $user->id,
                'total_prestige' => $scores['prestige'],
                'total_peaceful_calm' => $scores['peaceful_calm'],
                'total_rebel_brave' => $scores['rebel_brave'],
                'total_sweet_shy' => $scores['sweet_shy'],
                'dominant_personality' => $dominantPersonality,
                'product_id' => $recommendedProduct ? $recommendedProduct->id : null,
            ]);

            // Simpan detail jawaban user
            foreach ($request->answers as $answer) {
                UserQuizAnswer::create([
                    'quiz_attempt_id' => $attempt->id,
                    'quiz_question_id' => $answer['question_id'],
                    'quiz_option_id' => $answer['option_id'],
                ]);
            }

            DB::commit();

            // Return hasil beserta produk yang direkomendasikan
            return response()->json([
                'message' => 'Quiz berhasil disubmit',
                'result' => $attempt->load('recommendedProduct'),
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Gagal memproses kuis', 'error' => $e->getMessage()], 500);
        }
    }
}
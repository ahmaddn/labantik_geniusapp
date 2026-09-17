<?php

namespace Database\Seeders;

use App\Models\Quizzes;
use Illuminate\Database\Seeder;

class QuizRetakeConfigSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $quizzes = Quizzes::all();

        if ($quizzes->isEmpty()) {
            return;
        }

        // 1. Set Pretest quiz to 1x Kerjakan (Tidak Bisa Diulang)
        $pretest = Quizzes::where('category', 'pretest')->first();
        if ($pretest) {
            $pretest->update([
                'allow_retake' => false,
                'max_retakes' => 0,
            ]);
        }

        // 2. Set Mission quiz to Max 2x Ulang
        $missionQuiz = Quizzes::where('type', '!=', 'materials')->where('category', '!=', 'pretest')->first();
        if ($missionQuiz) {
            $missionQuiz->update([
                'allow_retake' => true,
                'max_retakes' => 2,
            ]);
        }

        // 3. Set Posttest quiz to Tanpa Batas (∞)
        $posttest = Quizzes::where('category', 'posttest')->first();
        if ($posttest) {
            $posttest->update([
                'allow_retake' => true,
                'max_retakes' => 0,
            ]);
        }
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Log and delete orphaned quizzes (quizzes with non-existent mission_id)
        $orphaned = DB::table('quizzes')
            ->whereNotNull('mission_id')
            ->whereNotExists(function($q) {
                $q->select(DB::raw(1))
                    ->from('missions')
                    ->whereColumn('missions.id', 'quizzes.mission_id');
            })
            ->get();

        if ($orphaned->isNotEmpty()) {
            \Log::warning('Deleting orphaned quizzes', [
                'count' => $orphaned->count(),
                'ids' => $orphaned->pluck('id')->toArray(),
            ]);
            
            foreach ($orphaned as $quiz) {
                DB::table('quizzes')->where('id', $quiz->id)->delete();
            }
        }

        \Log::info('Orphaned quizzes cleanup completed', [
            'deleted_count' => $orphaned->count(),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Rollback only logs the action, data deletion is permanent
        \Log::info('Orphaned quizzes cleanup migration rolled back');
    }
};

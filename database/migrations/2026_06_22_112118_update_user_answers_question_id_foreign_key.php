<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('user_answers')) {
            try {
                Schema::table('user_answers', function (Blueprint $table) {
                    try { $table->dropForeign(['question_id']); } catch (\Exception $e) {}
                    try { $table->dropForeign(['attempt_id']); } catch (\Exception $e) {}
                    
                    $table->foreign('question_id')
                        ->references('id')
                        ->on('questions')
                        ->onDelete('cascade');
                        
                    $table->foreign('attempt_id')
                        ->references('id')
                        ->on('quiz_attempts')
                        ->onDelete('cascade');
                });
            } catch (\Exception $e) {
                // Ignore if foreign keys already modified
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_answers', function (Blueprint $table) {
            $table->dropForeign(['question_id']);
            $table->dropForeign(['attempt_id']);
            
            $table->foreign('question_id')
                ->references('id')
                ->on('questions');
                
            $table->foreign('attempt_id')
                ->references('id')
                ->on('quiz_attempts');
        });
    }
};

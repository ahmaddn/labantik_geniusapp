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
        Schema::table('user_answers', function (Blueprint $table) {
            $table->dropForeign(['question_id']);
            $table->dropForeign(['attempt_id']);
            
            $table->foreign('question_id')
                ->references('id')
                ->on('questions')
                ->onDelete('cascade');
                
            $table->foreign('attempt_id')
                ->references('id')
                ->on('quiz_attempts')
                ->onDelete('cascade');
        });
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

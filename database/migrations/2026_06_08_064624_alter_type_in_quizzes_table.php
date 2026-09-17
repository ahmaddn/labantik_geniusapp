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
        \Illuminate\Support\Facades\DB::statement("ALTER TABLE quizzes MODIFY COLUMN type ENUM('multiple_choices','drag_drop','true_false','case_study','short_answer') NOT NULL DEFAULT 'multiple_choices'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        \Illuminate\Support\Facades\DB::statement("ALTER TABLE quizzes MODIFY COLUMN type ENUM('multiple_choices','drag_drop','true_false','case_study') NOT NULL");
    }
};

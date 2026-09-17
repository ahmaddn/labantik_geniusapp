<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quiz_attempts', function (Blueprint $table) {
            $table->integer('score_multiple_choice')->nullable()->default(0)->after('score');
            $table->integer('score_true_false')->nullable()->default(0)->after('score_multiple_choice');
            $table->integer('score_case_study')->nullable()->default(0)->after('score_true_false');
            $table->integer('score_drag_drop')->nullable()->default(0)->after('score_case_study');
        });
    }

    public function down(): void
    {
        Schema::table('quiz_attempts', function (Blueprint $table) {
            $table->dropColumn([
                'score_multiple_choice',
                'score_true_false',
                'score_case_study',
                'score_drag_drop',
            ]);
        });
    }
};

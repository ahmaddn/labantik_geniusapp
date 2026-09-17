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
            // Safe drop columns
            if (\Illuminate\Support\Facades\Schema::hasColumn($table->getTable(), '
                'score_multiple_choice')) { $table->dropColumn('
                'score_multiple_choice'); }
            if (\Illuminate\Support\Facades\Schema::hasColumn($table->getTable(), '
                'score_true_false')) { $table->dropColumn('
                'score_true_false'); }
            if (\Illuminate\Support\Facades\Schema::hasColumn($table->getTable(), '
                'score_case_study')) { $table->dropColumn('
                'score_case_study'); }
            if (\Illuminate\Support\Facades\Schema::hasColumn($table->getTable(), '
                'score_drag_drop')) { $table->dropColumn('
                'score_drag_drop'); }
            if (\Illuminate\Support\Facades\Schema::hasColumn($table->getTable(), '
')) { if (\Illuminate\Support\Facades\Schema::hasColumn($table->getTable(), '
')) { $table->dropColumn('
'); } }
        });
    }
};

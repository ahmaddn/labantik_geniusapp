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
        Schema::table('simulation_sliders', function (Blueprint $table) {
            $table->dropColumn([
                'case_study_scenario',
                'case_study_options',
                'case_study_answer',
                'case_study_feedback'
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('simulation_sliders', function (Blueprint $table) {
            $table->text('case_study_scenario')->nullable();
            $table->json('case_study_options')->nullable();
            $table->string('case_study_answer')->nullable();
            $table->text('case_study_feedback')->nullable();
        });
    }
};

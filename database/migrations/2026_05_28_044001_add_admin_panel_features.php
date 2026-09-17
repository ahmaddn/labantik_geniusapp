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
        Schema::table('quizzes', function (Blueprint $table) {
            $table->integer('duration_minutes')->nullable();
        });

        Schema::table('questions', function (Blueprint $table) {
            $table->string('type')->default('multiple_choice');
            $table->text('expected_keywords')->nullable();
        });

        Schema::table('missions', function (Blueprint $table) {
            $table->text('objective')->nullable();
            $table->longText('content')->nullable();
            $table->string('image')->nullable();
            $table->string('youtube_link')->nullable();
            $table->boolean('is_active')->default(true);
        });

        Schema::create('simulation_sliders', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('mission_id')->index();
            $table->string('x_axis_label')->nullable();
            $table->text('conclusion_text')->nullable();
            $table->text('case_study_scenario')->nullable();
            $table->json('case_study_options')->nullable(); // JSON array [A, B, C, D]
            $table->string('case_study_answer')->nullable();
            $table->text('case_study_feedback')->nullable();
            $table->timestamps();

            $table->foreign('mission_id')->references('id')->on('missions')->onDelete('cascade');
        });

        Schema::create('simulation_slider_levels', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('simulation_slider_id')->index();
            $table->string('level_name');
            $table->text('narration')->nullable();
            $table->string('water_debit')->nullable();
            $table->string('image')->nullable();
            $table->timestamps();

            $table->foreign('simulation_slider_id')->references('id')->on('simulation_sliders')->onDelete('cascade');
        });

        Schema::create('simulation_comparisons', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('mission_id')->index();
            $table->string('left_label')->nullable();
            $table->string('right_label')->nullable();
            $table->text('left_narration')->nullable();
            $table->text('right_narration')->nullable();
            $table->string('left_image')->nullable();
            $table->string('right_image')->nullable();
            $table->text('explanation')->nullable();
            $table->timestamps();

            $table->foreign('mission_id')->references('id')->on('missions')->onDelete('cascade');
        });

        Schema::create('simulation_clickable_objects', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('mission_id')->index();
            $table->string('name');
            $table->string('image')->nullable();
            $table->string('pos_x')->nullable();
            $table->string('pos_y')->nullable();
            $table->text('impact_text')->nullable();
            $table->boolean('is_positive')->default(false);
            $table->timestamps();

            $table->foreign('mission_id')->references('id')->on('missions')->onDelete('cascade');
        });

        Schema::create('simulation_scenarios', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('mission_id')->index();
            $table->text('context');
            $table->string('image')->nullable();
            $table->string('correct_option')->nullable(); // e.g. A, B, C, D
            $table->timestamps();

            $table->foreign('mission_id')->references('id')->on('missions')->onDelete('cascade');
        });

        Schema::create('simulation_scenario_options', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('simulation_scenario_id')->index();
            $table->string('label'); // A, B, C, D
            $table->text('text');
            $table->text('feedback')->nullable();
            $table->timestamps();

            $table->foreign('simulation_scenario_id')->references('id')->on('simulation_scenarios')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('simulation_scenario_options');
        Schema::dropIfExists('simulation_scenarios');
        Schema::dropIfExists('simulation_clickable_objects');
        Schema::dropIfExists('simulation_comparisons');
        Schema::dropIfExists('simulation_slider_levels');
        Schema::dropIfExists('simulation_sliders');

        Schema::table('missions', function (Blueprint $table) {
            $table->dropColumn(['objective', 'content', 'image', 'youtube_link', 'is_active']);
        });

        Schema::table('questions', function (Blueprint $table) {
            $table->dropColumn(['type', 'expected_keywords']);
        });

        Schema::table('quizzes', function (Blueprint $table) {
            $table->dropColumn('duration_minutes');
        });
    }
};

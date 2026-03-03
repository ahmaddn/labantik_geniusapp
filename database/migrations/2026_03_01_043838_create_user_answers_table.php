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
        Schema::create('user_answers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('attempt_id')->index();
            $table->uuid('question_id')->index();
            $table->uuid('selected_option_id')->index();
            $table->uuid('selected_group_id')->index();
            $table->string('response')->nullable();
            $table->timestamps();

            $table->foreign('question_id')->references('id')->on('questions');
            $table->foreign('attempt_id')->references('id')->on('quiz_attempts');
            $table->foreign('selected_option_id')->references('id')->on('question_options');
            $table->foreign('selected_group_id')->references('id')->on('drag_drop_groups');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_answers');
    }
};

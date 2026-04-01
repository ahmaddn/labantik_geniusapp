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
        Schema::create('quizzes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('title');
            $table->text('description')->nullable();
            $table->integer('time_limit');
            $table->string('image');
            $table->enum('category', ['pretest', 'mission', 'posttest']);
            $table->enum('type', ['multiple_choices', 'drag_drop', 'true_false', 'case_study']);
            $table->uuid('created_by')->index();
            $table->uuid('module_id')->index();
            $table->uuid('mission_id')->index();
            $table->timestamps();

            $table->foreign('created_by')->references('id')->on('users');
            $table->foreign('module_id')->references('id')->on('learning_modules');
            $table->foreign('mission_id')->references('id')->on('missions');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quizzes');
    }
};

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
        Schema::create('reflection_questions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('reflection_id')->constrained('scientific_reflections')->cascadeOnDelete();
            $table->text('question_text');
            $table->integer('order_number')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reflection_questions');
    }
};

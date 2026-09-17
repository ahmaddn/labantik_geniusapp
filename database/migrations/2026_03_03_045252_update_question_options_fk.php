<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('question_options', function (Blueprint $table) {

            // Drop foreign key lama
            $table->dropForeign(['question_id']);

            // Pastikan kolom nullable
            $table->uuid('question_id')->nullable()->change();

            // Tambahkan FK baru tanpa cascade
            $table->foreign('question_id')
                ->references('id')
                ->on('questions')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('question_options', function (Blueprint $table) {

            $table->dropForeign(['question_id']);

            $table->uuid('question_id')->nullable(false)->change();

            $table->foreign('question_id')
                ->references('id')
                ->on('questions');
        });
    }
};

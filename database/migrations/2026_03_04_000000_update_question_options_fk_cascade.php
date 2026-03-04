<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('question_options', function (Blueprint $table) {
            // Drop existing foreign key (if any)
            $table->dropForeign(['question_id']);

            // Ensure column is NOT nullable (we want strict FK)
            $table->uuid('question_id')->nullable(false)->change();

            // Recreate FK with cascade on delete
            $table->foreign('question_id')
                ->references('id')
                ->on('questions')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('question_options', function (Blueprint $table) {
            $table->dropForeign(['question_id']);

            // Revert to previous nullable + nullOnDelete behaviour
            $table->uuid('question_id')->nullable()->change();

            $table->foreign('question_id')
                ->references('id')
                ->on('questions')
                ->nullOnDelete();
        });
    }
};

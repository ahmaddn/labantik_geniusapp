<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Clean up any invalid data first
        DB::table('question_options')->whereNull('question_id')->delete();

        // The foreign key constraint handling is already in the schema
        // Just ensure the column exists and is properly structured
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

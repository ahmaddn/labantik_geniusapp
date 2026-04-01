<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            // drop existing foreign key
            $table->dropForeign(['mascot_id']);

            // ensure column nullable (already nullable in original migration)
            $table->uuid('mascot_id')->nullable()->change();

            // add fk with nullOnDelete
            $table->foreign('mascot_id')
                ->references('id')
                ->on('mascots')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            $table->dropForeign(['mascot_id']);

            // revert to no nullOnDelete
            $table->uuid('mascot_id')->nullable()->change();

            $table->foreign('mascot_id')
                ->references('id')
                ->on('mascots');
        });
    }
};

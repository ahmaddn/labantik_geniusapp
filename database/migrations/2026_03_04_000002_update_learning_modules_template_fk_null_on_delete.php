<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('learning_modules', function (Blueprint $table) {
            // drop existing foreign key
            $table->dropForeign(['template_id']);

            // ensure column nullable
            $table->uuid('template_id')->nullable()->change();

            // add fk with nullOnDelete
            $table->foreign('template_id')
                ->references('id')
                ->on('templates')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('learning_modules', function (Blueprint $table) {
            $table->dropForeign(['template_id']);

            // revert to not nullable if original was not nullable (assume nullable)
            $table->uuid('template_id')->nullable()->change();

            $table->foreign('template_id')
                ->references('id')
                ->on('templates');
        });
    }
};

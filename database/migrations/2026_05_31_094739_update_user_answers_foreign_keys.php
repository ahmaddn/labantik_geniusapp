<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('user_answers', function (Blueprint $table) {
            // Drop existing foreign keys
            $table->dropForeign(['selected_option_id']);
            $table->dropForeign(['selected_group_id']);
            
            // Recreate with cascade delete
            $table->foreign('selected_option_id')
                ->references('id')
                ->on('question_options')
                ->onDelete('cascade');
            
            $table->foreign('selected_group_id')
                ->references('id')
                ->on('drag_drop_groups')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_answers', function (Blueprint $table) {
            // Revert to original without cascade delete (defensive)
            $table->dropForeign(['selected_option_id']);
            $table->dropForeign(['selected_group_id']);
            
            $table->foreign('selected_option_id')
                ->references('id')
                ->on('question_options');
            
            $table->foreign('selected_group_id')
                ->references('id')
                ->on('drag_drop_groups');
        });
    }
};

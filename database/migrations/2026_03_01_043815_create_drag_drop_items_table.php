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
        Schema::create('drag_drop_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('question_id')->index();
            $table->uuid('drag_drop_group_id')->index();
            $table->text('item_text');
            $table->string('item_image');
            $table->timestamps();

            $table->foreign('question_id')->references('id')->on('questions');
            $table->foreign('drag_drop_group_id')->references('id')->on('drag_drop_groups');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('drag_drop_items');
    }
};

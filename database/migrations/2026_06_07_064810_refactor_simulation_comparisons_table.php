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
        Schema::table('simulation_comparisons', function (Blueprint $table) {
            $table->dropColumn(['left_label', 'right_label', 'left_narration', 'right_narration', 'left_image', 'right_image']);
            $table->json('items')->nullable()->after('title');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('simulation_comparisons', function (Blueprint $table) {
            $table->dropColumn('items');
            $table->string('left_label')->nullable();
            $table->string('right_label')->nullable();
            $table->text('left_narration')->nullable();
            $table->text('right_narration')->nullable();
            $table->string('left_image')->nullable();
            $table->string('right_image')->nullable();
        });
    }
};

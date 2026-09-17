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
        Schema::table('simulation_slider_levels', function (Blueprint $table) {
            $table->string('image_transition')->nullable()->default('none');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('simulation_slider_levels', function (Blueprint $table) {
            $table->dropColumn('image_transition');
        });
    }
};

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
        Schema::table('scientific_reflections', function (Blueprint $table) {
            $table->integer('order_number')->default(0)->after('mission_id');
        });

        Schema::table('simulation_decisions', function (Blueprint $table) {
            $table->integer('order_number')->default(0)->after('mission_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('scientific_reflections', function (Blueprint $table) {
            $table->dropColumn('order_number');
        });

        Schema::table('simulation_decisions', function (Blueprint $table) {
            $table->dropColumn('order_number');
        });
    }
};

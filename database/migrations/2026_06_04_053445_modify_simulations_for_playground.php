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
        // 1. Drop pos_x and pos_y from simulation_clickable_objects
        Schema::table('simulation_clickable_objects', function (Blueprint $table) {
            $table->dropColumn(['pos_x', 'pos_y']);
        });

        // 2. Add title column to all simulation tables
        $tables = [
            'simulation_sliders',
            'simulation_comparisons',
            'simulation_clickable_objects',
            'simulation_scenarios',
        ];

        foreach ($tables as $table) {
            Schema::table($table, function (Blueprint $t) use ($table) {
                if (!Schema::hasColumn($table, 'title')) {
                    $t->string('title')->nullable()->after('mission_id');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('simulation_clickable_objects', function (Blueprint $table) {
            $table->string('pos_x', 50)->nullable();
            $table->string('pos_y', 50)->nullable();
        });

        $tables = [
            'simulation_sliders',
            'simulation_comparisons',
            'simulation_clickable_objects',
            'simulation_scenarios',
        ];

        foreach ($tables as $table) {
            Schema::table($table, function (Blueprint $t) use ($table) {
                if (Schema::hasColumn($table, 'title')) {
                    $t->dropColumn('title');
                }
            });
        }
    }
};

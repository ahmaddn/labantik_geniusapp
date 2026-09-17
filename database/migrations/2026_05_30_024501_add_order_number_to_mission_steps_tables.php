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
        $tables = [
            'materials',
            'quizzes',
            'simulation_sliders',
            'simulation_comparisons',
            'simulation_clickable_objects',
            'simulation_scenarios',
        ];

        foreach ($tables as $table) {
            Schema::table($table, function (Blueprint $t) use ($table) {
                if (!Schema::hasColumn($table, 'order_number')) {
                    $t->integer('order_number')->default(0)->after('mission_id');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tables = [
            'materials',
            'quizzes',
            'simulation_sliders',
            'simulation_comparisons',
            'simulation_clickable_objects',
            'simulation_scenarios',
        ];

        foreach ($tables as $table) {
            Schema::table($table, function (Blueprint $t) {
                $t->dropColumn('order_number');
            });
        }
    }
};

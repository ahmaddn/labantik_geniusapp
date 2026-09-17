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
        Schema::table('simulation_sliders', function (Blueprint $table) {
            // Safe drop columns
            if (\Illuminate\Support\Facades\Schema::hasColumn($table->getTable(), '
                'slider1_name')) { $table->dropColumn('
                'slider1_name'); }
            if (\Illuminate\Support\Facades\Schema::hasColumn($table->getTable(), '
                'slider1_min_label')) { $table->dropColumn('
                'slider1_min_label'); }
            if (\Illuminate\Support\Facades\Schema::hasColumn($table->getTable(), '
                'slider1_max_label')) { $table->dropColumn('
                'slider1_max_label'); }
            if (\Illuminate\Support\Facades\Schema::hasColumn($table->getTable(), '
                'slider2_name')) { $table->dropColumn('
                'slider2_name'); }
            if (\Illuminate\Support\Facades\Schema::hasColumn($table->getTable(), '
                'slider2_min_label')) { $table->dropColumn('
                'slider2_min_label'); }
            if (\Illuminate\Support\Facades\Schema::hasColumn($table->getTable(), '
                'slider2_max_label'
')) { $table->dropColumn('
                'slider2_max_label'
'); }
            $table->json('variables')->nullable();
        });

        Schema::table('simulation_slider_levels', function (Blueprint $table) {
            $table->renameColumn('water_debit', 'metric_value');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('simulation_sliders', function (Blueprint $table) {
            if (\Illuminate\Support\Facades\Schema::hasColumn($table->getTable(), 'variables')) { $table->dropColumn('variables'); }
            $table->string('slider1_name')->nullable();
            $table->string('slider1_min_label')->nullable();
            $table->string('slider1_max_label')->nullable();
            $table->string('slider2_name')->nullable();
            $table->string('slider2_min_label')->nullable();
            $table->string('slider2_max_label')->nullable();
        });

        Schema::table('simulation_slider_levels', function (Blueprint $table) {
            $table->renameColumn('metric_value', 'water_debit');
        });
    }
};

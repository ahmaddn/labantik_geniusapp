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
        if (Schema::hasTable('simulation_slider_levels') && ! Schema::hasColumn('simulation_slider_levels', 'status')) {
            Schema::table('simulation_slider_levels', function (Blueprint $table) {
                $table->string('status')->nullable()->after('level_name');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('simulation_slider_levels', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};

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
        Schema::table('simulation_scenarios', function (Blueprint $table) {
            $table->uuid('mission_id')->nullable()->change();
            $table->uuid('module_id')->nullable()->index()->after('id');
            $table->foreign('module_id')->references('id')->on('learning_modules')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('simulation_scenarios', function (Blueprint $table) {
            $table->dropForeign(['module_id']);
            $table->dropColumn('module_id');
            $table->uuid('mission_id')->nullable(false)->change();
        });
    }
};

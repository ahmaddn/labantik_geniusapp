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
        Schema::table('settings', function (Blueprint $table) {
            $table->string('platform_mascot')->nullable();
            $table->string('platform_mascot_pose')->nullable();
            $table->json('platform_mascot_dialog')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn(['platform_mascot', 'platform_mascot_pose', 'platform_mascot_dialog']);
        });
    }
};

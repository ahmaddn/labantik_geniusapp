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
            // Safe drop columns
            if (\Illuminate\Support\Facades\Schema::hasColumn($table->getTable(), 'platform_mascot')) { if (\Illuminate\Support\Facades\Schema::hasColumn($table->getTable(), 'platform_mascot')) { $table->dropColumn('platform_mascot'); } }
            if (\Illuminate\Support\Facades\Schema::hasColumn($table->getTable(), 'platform_mascot_pose')) { if (\Illuminate\Support\Facades\Schema::hasColumn($table->getTable(), 'platform_mascot_pose')) { $table->dropColumn('platform_mascot_pose'); } }
            if (\Illuminate\Support\Facades\Schema::hasColumn($table->getTable(), 'platform_mascot_dialog')) { if (\Illuminate\Support\Facades\Schema::hasColumn($table->getTable(), 'platform_mascot_dialog')) { $table->dropColumn('platform_mascot_dialog'); } }
        });
    }
};

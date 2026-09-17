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
        Schema::table('missions', function (Blueprint $table) {
            $table->text('conclusion_speech')->nullable()->after('is_active');
            $table->longText('conclusion_body')->nullable()->after('conclusion_speech');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('missions', function (Blueprint $table) {
            // Safe drop columns
            if (\Illuminate\Support\Facades\Schema::hasColumn($table->getTable(), 'conclusion_speech')) { if (\Illuminate\Support\Facades\Schema::hasColumn($table->getTable(), 'conclusion_speech')) { $table->dropColumn('conclusion_speech'); } }
            if (\Illuminate\Support\Facades\Schema::hasColumn($table->getTable(), 'conclusion_body')) { if (\Illuminate\Support\Facades\Schema::hasColumn($table->getTable(), 'conclusion_body')) { $table->dropColumn('conclusion_body'); } }
        });
    }
};

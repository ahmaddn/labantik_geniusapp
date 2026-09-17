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
        Schema::table('materials', function (Blueprint $table) {
            $table->string('youtube_link')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('materials', function (Blueprint $table) {
            if (\Illuminate\Support\Facades\Schema::hasColumn($table->getTable(), 'youtube_link')) { $table->dropColumn('youtube_link'); }
        });
    }
};

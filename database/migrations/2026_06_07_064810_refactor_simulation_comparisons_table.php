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
        Schema::table('simulation_comparisons', function (Blueprint $table) {
            // Safe drop columns
            if (\Illuminate\Support\Facades\Schema::hasColumn($table->getTable(), 'left_label')) { if (\Illuminate\Support\Facades\Schema::hasColumn($table->getTable(), 'left_label')) { $table->dropColumn('left_label'); } }
            if (\Illuminate\Support\Facades\Schema::hasColumn($table->getTable(), 'right_label')) { if (\Illuminate\Support\Facades\Schema::hasColumn($table->getTable(), 'right_label')) { $table->dropColumn('right_label'); } }
            if (\Illuminate\Support\Facades\Schema::hasColumn($table->getTable(), 'left_narration')) { if (\Illuminate\Support\Facades\Schema::hasColumn($table->getTable(), 'left_narration')) { $table->dropColumn('left_narration'); } }
            if (\Illuminate\Support\Facades\Schema::hasColumn($table->getTable(), 'right_narration')) { if (\Illuminate\Support\Facades\Schema::hasColumn($table->getTable(), 'right_narration')) { $table->dropColumn('right_narration'); } }
            if (\Illuminate\Support\Facades\Schema::hasColumn($table->getTable(), 'left_image')) { if (\Illuminate\Support\Facades\Schema::hasColumn($table->getTable(), 'left_image')) { $table->dropColumn('left_image'); } }
            if (\Illuminate\Support\Facades\Schema::hasColumn($table->getTable(), 'right_image')) { if (\Illuminate\Support\Facades\Schema::hasColumn($table->getTable(), 'right_image')) { $table->dropColumn('right_image'); } }
            $table->json('items')->nullable()->after('title');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('simulation_comparisons', function (Blueprint $table) {
            if (\Illuminate\Support\Facades\Schema::hasColumn($table->getTable(), 'items')) { $table->dropColumn('items'); }
            $table->string('left_label')->nullable();
            $table->string('right_label')->nullable();
            $table->text('left_narration')->nullable();
            $table->text('right_narration')->nullable();
            $table->string('left_image')->nullable();
            $table->string('right_image')->nullable();
        });
    }
};

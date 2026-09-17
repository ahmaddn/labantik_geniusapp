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
        Schema::table('learning_modules', function (Blueprint $table) {
            if (Schema::hasColumn('learning_modules', 'grade_level')) {
                $table->dropColumn('grade_level');
            }
            if (Schema::hasColumn('learning_modules', 'sub_class')) {
                $table->dropColumn('sub_class');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('learning_modules', function (Blueprint $table) {
            if (!Schema::hasColumn('learning_modules', 'grade_level')) {
                $table->integer('grade_level')->nullable()->after('thumbnail');
            }
            if (!Schema::hasColumn('learning_modules', 'sub_class')) {
                $table->string('sub_class', 10)->nullable()->after('grade_level');
            }
        });
    }
};

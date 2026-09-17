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
        if (Schema::hasTable('quizzes') && ! Schema::hasColumn('quizzes', 'is_randomized')) {
            Schema::table('quizzes', function (Blueprint $table) {
                $table->boolean('is_randomized')->default(false)->after('type');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quizzes', function (Blueprint $table) {
            $table->dropColumn('is_randomized');
        });
    }
};

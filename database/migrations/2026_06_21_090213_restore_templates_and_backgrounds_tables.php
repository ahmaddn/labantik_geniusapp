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
        // 1. Create templates table
        if (!Schema::hasTable('templates')) {
            Schema::create('templates', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->string('name');
                $table->string('backsound')->nullable();
                $table->uuid('created_by')->index();
                $table->timestamps();

                $table->foreign('created_by')->references('id')->on('users');
            });
        }

        // 2. Create backgrounds table
        if (!Schema::hasTable('backgrounds')) {
            Schema::create('backgrounds', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->string('name');
                $table->string('image');
                $table->uuid('template_id')->index()->nullable();
                $table->timestamps();

                $table->foreign('template_id')->references('id')->on('templates')->onDelete('cascade');
            });
        }

        // 3. Add template_id to mascots
        if (Schema::hasTable('mascots') && !Schema::hasColumn('mascots', 'template_id')) {
            Schema::table('mascots', function (Blueprint $table) {
                $table->uuid('template_id')->index()->nullable()->after('image');
                $table->foreign('template_id')->references('id')->on('templates')->onDelete('cascade');
            });
        }

        // 4. Add template_id to learning_modules
        if (Schema::hasTable('learning_modules') && !Schema::hasColumn('learning_modules', 'template_id')) {
            Schema::table('learning_modules', function (Blueprint $table) {
                $table->uuid('template_id')->index()->nullable()->after('id');
                $table->foreign('template_id')->references('id')->on('templates')->nullOnDelete();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('learning_modules') && Schema::hasColumn('learning_modules', 'template_id')) {
            Schema::table('learning_modules', function (Blueprint $table) {
                $table->dropForeign(['template_id']);
                $table->dropColumn('template_id');
            });
        }

        if (Schema::hasTable('mascots') && Schema::hasColumn('mascots', 'template_id')) {
            Schema::table('mascots', function (Blueprint $table) {
                $table->dropForeign(['template_id']);
                $table->dropColumn('template_id');
            });
        }

        Schema::dropIfExists('backgrounds');
        Schema::dropIfExists('templates');
    }
};

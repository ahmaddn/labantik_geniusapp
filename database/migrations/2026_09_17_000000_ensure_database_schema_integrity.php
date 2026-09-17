<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations with full schema validation to prevent aborts on existing databases.
     */
    public function up(): void
    {
        // 1. Quizzes table check
        if (Schema::hasTable('quizzes')) {
            Schema::table('quizzes', function (Blueprint $table) {
                if (!Schema::hasColumn('quizzes', 'allow_retake')) {
                    $table->boolean('allow_retake')->default(true);
                }
                if (!Schema::hasColumn('quizzes', 'is_randomized')) {
                    $table->boolean('is_randomized')->default(false);
                }
                if (!Schema::hasColumn('quizzes', 'custom_dialogues')) {
                    $table->text('custom_dialogues')->nullable();
                }
            });
        }

        // 2. Missions table check
        if (Schema::hasTable('missions')) {
            Schema::table('missions', function (Blueprint $table) {
                if (!Schema::hasColumn('missions', 'voiceover_url')) {
                    $table->string('voiceover_url')->nullable();
                }
                if (!Schema::hasColumn('missions', 'conclusion')) {
                    $table->text('conclusion')->nullable();
                }
            });
        }

        // 3. Materials table check
        if (Schema::hasTable('materials')) {
            Schema::table('materials', function (Blueprint $table) {
                if (!Schema::hasColumn('materials', 'custom_dialogues')) {
                    $table->text('custom_dialogues')->nullable();
                }
                if (!Schema::hasColumn('materials', 'youtube_link')) {
                    $table->string('youtube_link')->nullable();
                }
            });
        }

        // 4. Learning Modules table check
        if (Schema::hasTable('learning_modules')) {
            Schema::table('learning_modules', function (Blueprint $table) {
                if (!Schema::hasColumn('learning_modules', 'closing_text')) {
                    $table->text('closing_text')->nullable();
                }
            });
        }

        // 5. Pivot table: learning_module_classes
        if (!Schema::hasTable('learning_module_classes')) {
            Schema::create('learning_module_classes', function (Blueprint $table) {
                $table->uuid('learning_module_id');
                $table->uuid('class_id');
                
                $table->foreign('learning_module_id')->references('id')->on('learning_modules')->onDelete('cascade');
                $table->foreign('class_id')->references('id')->on('classes')->onDelete('cascade');
                
                $table->primary(['learning_module_id', 'class_id']);
            });
        }
    }

    /**
     * Reverse the migrations (Safe no-op to preserve server data).
     */
    public function down(): void
    {
        // Intentionally kept safe to avoid deleting server data on rollback
    }
};

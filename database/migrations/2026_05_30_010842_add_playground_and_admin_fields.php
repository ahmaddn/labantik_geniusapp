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
        Schema::create('settings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('platform_name')->nullable();
            $table->string('platform_subtitle')->nullable();
            $table->string('platform_logo')->nullable();
            $table->string('bgm_file')->nullable();
            $table->boolean('bgm_enabled')->default(true);
            $table->timestamps();
        });

        Schema::table('backgrounds', function (Blueprint $table) {
            $table->string('context_key')->nullable();
        });

        Schema::table('learning_modules', function (Blueprint $table) {
            $table->string('adventure_title')->nullable();
            $table->text('adventure_objective')->nullable();
            $table->text('intro_speech_bubble')->nullable();
            $table->string('intro_cta_text')->nullable();
            $table->string('closing_title')->nullable();
            $table->string('closing_subtitle')->nullable();
            $table->text('closing_speech_bubble')->nullable();
            $table->string('closing_tagline')->nullable();
            $table->decimal('map_mascot_x_1', 5, 2)->nullable();
            $table->decimal('map_mascot_y_1', 5, 2)->nullable();
            $table->decimal('map_mascot_x_2', 5, 2)->nullable();
            $table->decimal('map_mascot_y_2', 5, 2)->nullable();
        });

        Schema::table('missions', function (Blueprint $table) {
            $table->decimal('pos_x', 5, 2)->nullable();
            $table->decimal('pos_y', 5, 2)->nullable();
            $table->string('icon')->nullable();
        });

        Schema::table('materials', function (Blueprint $table) {
            $table->string('layout_type')->nullable();
            $table->string('emoji_icon')->nullable();
            $table->string('secondary_image')->nullable();
            $table->text('speech_bubble')->nullable();
            $table->string('button_text')->nullable();
            $table->integer('order_number')->default(0);
        });

        Schema::table('questions', function (Blueprint $table) {
            $table->string('badge_label')->nullable();
            $table->string('mascot_pose')->nullable();
            $table->text('feedback_correct')->nullable();
            $table->text('feedback_incorrect')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            // Safe drop columns
            if (\Illuminate\Support\Facades\Schema::hasColumn($table->getTable(), 'badge_label')) { if (\Illuminate\Support\Facades\Schema::hasColumn($table->getTable(), 'badge_label')) { $table->dropColumn('badge_label'); } }
            if (\Illuminate\Support\Facades\Schema::hasColumn($table->getTable(), 'mascot_pose')) { if (\Illuminate\Support\Facades\Schema::hasColumn($table->getTable(), 'mascot_pose')) { $table->dropColumn('mascot_pose'); } }
            if (\Illuminate\Support\Facades\Schema::hasColumn($table->getTable(), 'feedback_correct')) { if (\Illuminate\Support\Facades\Schema::hasColumn($table->getTable(), 'feedback_correct')) { $table->dropColumn('feedback_correct'); } }
            if (\Illuminate\Support\Facades\Schema::hasColumn($table->getTable(), 'feedback_incorrect')) { if (\Illuminate\Support\Facades\Schema::hasColumn($table->getTable(), 'feedback_incorrect')) { $table->dropColumn('feedback_incorrect'); } }
        });

        Schema::table('materials', function (Blueprint $table) {
            // Safe drop columns
            if (\Illuminate\Support\Facades\Schema::hasColumn($table->getTable(), 'layout_type')) { if (\Illuminate\Support\Facades\Schema::hasColumn($table->getTable(), 'layout_type')) { $table->dropColumn('layout_type'); } }
            if (\Illuminate\Support\Facades\Schema::hasColumn($table->getTable(), 'emoji_icon')) { if (\Illuminate\Support\Facades\Schema::hasColumn($table->getTable(), 'emoji_icon')) { $table->dropColumn('emoji_icon'); } }
            if (\Illuminate\Support\Facades\Schema::hasColumn($table->getTable(), 'secondary_image')) { if (\Illuminate\Support\Facades\Schema::hasColumn($table->getTable(), 'secondary_image')) { $table->dropColumn('secondary_image'); } }
            if (\Illuminate\Support\Facades\Schema::hasColumn($table->getTable(), 'speech_bubble')) { if (\Illuminate\Support\Facades\Schema::hasColumn($table->getTable(), 'speech_bubble')) { $table->dropColumn('speech_bubble'); } }
            if (\Illuminate\Support\Facades\Schema::hasColumn($table->getTable(), 'button_text')) { if (\Illuminate\Support\Facades\Schema::hasColumn($table->getTable(), 'button_text')) { $table->dropColumn('button_text'); } }
            if (\Illuminate\Support\Facades\Schema::hasColumn($table->getTable(), 'order_number')) { if (\Illuminate\Support\Facades\Schema::hasColumn($table->getTable(), 'order_number')) { $table->dropColumn('order_number'); } }
        });

        Schema::table('missions', function (Blueprint $table) {
            // Safe drop columns
            if (\Illuminate\Support\Facades\Schema::hasColumn($table->getTable(), 'pos_x')) { if (\Illuminate\Support\Facades\Schema::hasColumn($table->getTable(), 'pos_x')) { $table->dropColumn('pos_x'); } }
            if (\Illuminate\Support\Facades\Schema::hasColumn($table->getTable(), 'pos_y')) { if (\Illuminate\Support\Facades\Schema::hasColumn($table->getTable(), 'pos_y')) { $table->dropColumn('pos_y'); } }
            if (\Illuminate\Support\Facades\Schema::hasColumn($table->getTable(), 'icon')) { if (\Illuminate\Support\Facades\Schema::hasColumn($table->getTable(), 'icon')) { $table->dropColumn('icon'); } }
        });

        Schema::table('learning_modules', function (Blueprint $table) {
            // Safe drop columns
            if (\Illuminate\Support\Facades\Schema::hasColumn($table->getTable(), '
                'adventure_title')) { $table->dropColumn('
                'adventure_title'); }
            if (\Illuminate\Support\Facades\Schema::hasColumn($table->getTable(), 'adventure_objective')) { if (\Illuminate\Support\Facades\Schema::hasColumn($table->getTable(), 'adventure_objective')) { $table->dropColumn('adventure_objective'); } }
            if (\Illuminate\Support\Facades\Schema::hasColumn($table->getTable(), 'intro_speech_bubble')) { if (\Illuminate\Support\Facades\Schema::hasColumn($table->getTable(), 'intro_speech_bubble')) { $table->dropColumn('intro_speech_bubble'); } }
            if (\Illuminate\Support\Facades\Schema::hasColumn($table->getTable(), 'intro_cta_text')) { if (\Illuminate\Support\Facades\Schema::hasColumn($table->getTable(), 'intro_cta_text')) { $table->dropColumn('intro_cta_text'); } }
            if (\Illuminate\Support\Facades\Schema::hasColumn($table->getTable(), '
                'closing_title')) { $table->dropColumn('
                'closing_title'); }
            if (\Illuminate\Support\Facades\Schema::hasColumn($table->getTable(), 'closing_subtitle')) { if (\Illuminate\Support\Facades\Schema::hasColumn($table->getTable(), 'closing_subtitle')) { $table->dropColumn('closing_subtitle'); } }
            if (\Illuminate\Support\Facades\Schema::hasColumn($table->getTable(), 'closing_speech_bubble')) { if (\Illuminate\Support\Facades\Schema::hasColumn($table->getTable(), 'closing_speech_bubble')) { $table->dropColumn('closing_speech_bubble'); } }
            if (\Illuminate\Support\Facades\Schema::hasColumn($table->getTable(), 'closing_tagline')) { if (\Illuminate\Support\Facades\Schema::hasColumn($table->getTable(), 'closing_tagline')) { $table->dropColumn('closing_tagline'); } }
            if (\Illuminate\Support\Facades\Schema::hasColumn($table->getTable(), '
                'map_mascot_x_1')) { $table->dropColumn('
                'map_mascot_x_1'); }
            if (\Illuminate\Support\Facades\Schema::hasColumn($table->getTable(), 'map_mascot_y_1')) { if (\Illuminate\Support\Facades\Schema::hasColumn($table->getTable(), 'map_mascot_y_1')) { $table->dropColumn('map_mascot_y_1'); } }
            if (\Illuminate\Support\Facades\Schema::hasColumn($table->getTable(), 'map_mascot_x_2')) { if (\Illuminate\Support\Facades\Schema::hasColumn($table->getTable(), 'map_mascot_x_2')) { $table->dropColumn('map_mascot_x_2'); } }
            if (\Illuminate\Support\Facades\Schema::hasColumn($table->getTable(), 'map_mascot_y_2'
')) { $table->dropColumn('map_mascot_y_2'
'); }
        });

        Schema::table('backgrounds', function (Blueprint $table) {
            if (\Illuminate\Support\Facades\Schema::hasColumn($table->getTable(), 'context_key')) { $table->dropColumn('context_key'); }
        });

        Schema::dropIfExists('settings');
    }
};

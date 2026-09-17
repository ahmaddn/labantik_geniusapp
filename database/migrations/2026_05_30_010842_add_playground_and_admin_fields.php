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
            $table->dropColumn(['badge_label', 'mascot_pose', 'feedback_correct', 'feedback_incorrect']);
        });

        Schema::table('materials', function (Blueprint $table) {
            $table->dropColumn(['layout_type', 'emoji_icon', 'secondary_image', 'speech_bubble', 'button_text', 'order_number']);
        });

        Schema::table('missions', function (Blueprint $table) {
            $table->dropColumn(['pos_x', 'pos_y', 'icon']);
        });

        Schema::table('learning_modules', function (Blueprint $table) {
            $table->dropColumn([
                'adventure_title', 'adventure_objective', 'intro_speech_bubble', 'intro_cta_text',
                'closing_title', 'closing_subtitle', 'closing_speech_bubble', 'closing_tagline',
                'map_mascot_x_1', 'map_mascot_y_1', 'map_mascot_x_2', 'map_mascot_y_2'
            ]);
        });

        Schema::table('backgrounds', function (Blueprint $table) {
            $table->dropColumn('context_key');
        });

        Schema::dropIfExists('settings');
    }
};

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
        Schema::create('simulation_decisions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('mission_id');
            $table->string('title')->nullable();
            $table->string('initial_state_title')->nullable();
            $table->string('initial_state_image')->nullable();
            $table->string('future_state_title')->nullable();
            $table->string('character_image')->nullable();
            $table->timestamps();

            $table->foreign('mission_id')
                  ->references('id')
                  ->on('missions')
                  ->onDelete('cascade');
        });

        Schema::create('simulation_decision_options', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('simulation_decision_id');
            $table->string('button_label')->nullable();
            $table->string('button_color')->nullable(); // green, yellow, red, blue, dst.
            $table->string('future_state_image')->nullable();
            $table->text('feedback_message')->nullable();
            $table->timestamps();

            $table->foreign('simulation_decision_id')
                  ->references('id')
                  ->on('simulation_decisions')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('simulation_decision_options');
        Schema::dropIfExists('simulation_decisions');
    }
};

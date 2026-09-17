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
        Schema::create('materials', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('title');
            $table->text('description');
            $table->longText('content');
            $table->string('image')->nullable();
            $table->uuid('created_by')->index();
            $table->uuid('mascot_id')->index()->nullable();
            $table->uuid('module_id')->index();
            $table->uuid('mission_id')->index();
            $table->timestamps();

            $table->foreign('created_by')->references('id')->on('users');
            $table->foreign('mascot_id')->references('id')->on('mascots');
            $table->foreign('module_id')->references('id')->on('learning_modules');
            $table->foreign('mission_id')->references('id')->on('missions');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('materials');
    }
};

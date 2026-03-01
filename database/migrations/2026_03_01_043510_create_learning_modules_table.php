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
        Schema::create('learning_modules', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->text('description');
            $table->text('content');
            $table->string('quotes');
            $table->boolean('is_active');
            $table->string('thumbnail')->nullable();
            $table->uuid('template_id')->index();
            $table->uuid('created_by')->index();
            $table->timestamps();

            $table->foreign('template_id')->references('id')->on('templates');
            $table->foreign('created_by')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('learning_modules');
    }
};

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
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('learning_module_classes');
    }
};

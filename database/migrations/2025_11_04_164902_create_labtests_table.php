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
        Schema::create('labtests', function (Blueprint $table) {
            $table->id();
            $table->enum('lab_types', ['cbc', 'microscopy', 'bloodchem', 'ultrasound', 'xray']);
            $table->json('photo_result')->nullable(); // Store multiple photo paths as JSON
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('labtests');
    }
};

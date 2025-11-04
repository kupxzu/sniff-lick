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
        Schema::create('consultations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pet_id')->constrained()->onDelete('cascade');
            $table->dateTime('consultation_date');
            $table->decimal('weight', 5, 2)->nullable(); // Weight in kg (e.g., 25.50)
            $table->decimal('temperature', 4, 2)->nullable(); // Temperature in Celsius (e.g., 38.50)
            $table->text('complaint')->nullable(); // Pet owner's complaint
            $table->text('diagnosis')->nullable();
            $table->foreignId('labtest_id')->nullable()->constrained('labtests')->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('consultations');
    }
};

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
        Schema::create('vac_treatments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vac_id')->constrained('vaccinations')->onDelete('cascade');
            $table->string('treatment');
            $table->string('dose');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vac_treatments');
    }
};

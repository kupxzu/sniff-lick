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
        Schema::table('labtests', function (Blueprint $table) {
            // Drop existing foreign key constraint
            $table->dropForeign(['pet_id']);
            
            // Rename column
            $table->renameColumn('pet_id', 'consultation_id');
            
            // Add new foreign key constraint
            $table->foreign('consultation_id')->references('id')->on('consultations')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('labtests', function (Blueprint $table) {
            // Drop new foreign key constraint
            $table->dropForeign(['consultation_id']);
            
            // Rename column back
            $table->renameColumn('consultation_id', 'pet_id');
            
            // Add back original foreign key constraint
            $table->foreign('pet_id')->references('id')->on('pets')->onDelete('cascade');
        });
    }
};

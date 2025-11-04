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
            // Add pet_id foreign key
            $table->foreignId('pet_id')->after('id')->constrained()->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('labtests', function (Blueprint $table) {
            // Drop foreign key constraint and column
            $table->dropForeign(['pet_id']);
            $table->dropColumn('pet_id');
        });
    }
};

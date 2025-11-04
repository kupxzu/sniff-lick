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
        Schema::table('consultations', function (Blueprint $table) {
            // Drop foreign key constraint first
            $table->dropForeign(['labtest_id']);
            // Remove the column
            $table->dropColumn('labtest_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('consultations', function (Blueprint $table) {
            // Add back the column
            $table->foreignId('labtest_id')->nullable()->constrained('labtests')->onDelete('set null');
        });
    }
};

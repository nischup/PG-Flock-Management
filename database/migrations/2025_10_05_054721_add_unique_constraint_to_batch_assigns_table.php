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
        Schema::table('batch_assigns', function (Blueprint $table) {
            // Add unique constraint to prevent duplicate batch assignments
            // Same company + project + flock + level + batch_no combination
            $table->unique(
                ['company_id', 'project_id', 'flock_id', 'level', 'batch_no'],
                'unique_batch_assignment'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('batch_assigns', function (Blueprint $table) {
            $table->dropUnique('unique_batch_assignment');
        });
    }
};
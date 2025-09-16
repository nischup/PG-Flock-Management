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
        Schema::table('flocks', function (Blueprint $table) {
            // Drop the unique constraint first
            $table->dropUnique(['name']);

            // Change the column from string to integer
            $table->integer('name')->unique()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('flocks', function (Blueprint $table) {
            // Drop the unique constraint
            $table->dropUnique(['name']);

            // Change back to string
            $table->string('name')->unique()->change();
        });
    }
};

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
        Schema::create('daily_operations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('batchassign_id')
                  ->constrained('batch_assigns')
                  ->cascadeOnDelete();
            $table->date('operation_date');
            $table->foreignId('created_by')->constrained('users');
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedTinyInteger('status')->default(1);
            $table->timestamps();

            // Prevent duplicate entry for same batch and date
            $table->unique(['batchassign_id', 'operation_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daily_operations');
    }
};

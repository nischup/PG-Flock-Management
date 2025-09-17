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
        Schema::create('batch_configurations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('batch_assign_id')
                  ->constrained('batch_assigns')
                  ->onDelete('cascade');
            $table->decimal('area_sqft', 8, 2); // square feet of batch
            $table->integer('num_workers')->default(0); // assigned workers
            $table->decimal('density_per_sqft', 8, 2)->nullable();
            $table->integer('feeders')->nullable();
            $table->integer('drinkers')->nullable();
            $table->decimal('temperature_target', 5, 2)->nullable();
            $table->decimal('humidity_target', 5, 2)->nullable();
            $table->text('note')->nullable(); // added note field
            $table->date('effective_from')->default(now());
            $table->date('effective_to')->nullable();
            $table->timestamps();
            $table->unique(['batch_assign_id', 'effective_from']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('batch_configurations');
    }
};

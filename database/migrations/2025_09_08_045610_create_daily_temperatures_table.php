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
        Schema::create('daily_temperatures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('daily_operation_id')->constrained()->cascadeOnDelete();
            $table->decimal('inside_temp', 5, 2)->nullable();
            $table->decimal('std_inside_temp', 5, 2)->nullable();
            $table->decimal('outside_temp', 5, 2)->nullable();
            $table->decimal('std_outside_temp', 5, 2)->nullable();
            $table->text('note')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daily_temperatures');
    }
};

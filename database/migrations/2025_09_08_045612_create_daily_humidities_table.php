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
        Schema::create('daily_humidities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('daily_operation_id')->constrained()->cascadeOnDelete();
            $table->decimal('today_humidity', 5, 2)->nullable();
            $table->decimal('std_humidity', 5, 2)->nullable();
            $table->text('note')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daily_humidities');
    }
};

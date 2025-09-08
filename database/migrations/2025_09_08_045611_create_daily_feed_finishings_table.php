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
        Schema::create('daily_feed_finishings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('daily_operation_id')->constrained()->cascadeOnDelete();
            $table->decimal('female_finishing_time', 8, 2)->nullable();
            $table->decimal('male_finishing_time', 8, 2)->nullable();
            $table->text('note')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daily_feed_finishings');
    }
};

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
        Schema::create('daily_mortalities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('daily_operation_id')->constrained()->cascadeOnDelete();
            $table->integer('female_qty')->default(0);
            $table->integer('male_qty')->default(0);
            $table->string('female_mortality_reason')->nullable();
            $table->string('male_mortality_reason')->nullable();
            $table->text('note')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daily_mortalities');
    }
};

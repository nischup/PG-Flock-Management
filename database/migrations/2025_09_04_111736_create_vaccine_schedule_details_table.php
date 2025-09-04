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
        Schema::create('vaccine_schedules_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('vaccine_schedule_id');
            $table->unsignedBigInteger('disease_id')->nullable();
            $table->unsignedBigInteger('vaccine_id');
            $table->string('age', 50);
            $table->date('vaccination_date');
            $table->date('next_vaccination_date')->nullable();
            $table->enum('status', ['pending', 'completed', 'cancelled'])->default('pending');
            $table->text('notes')->nullable();
            $table->string('administered_by', 100)->nullable();
            $table->tinyInteger('is_active')->default(1);
            $table->timestamps();

            // Foreign key constraints
            $table->foreign('vaccine_schedule_id')->references('id')->on('vaccine_schedules')->onDelete('cascade');
            $table->foreign('disease_id')->references('id')->on('diseases')->onDelete('set null');
            $table->foreign('vaccine_id')->references('id')->on('vaccines')->onDelete('cascade');

            // Indexes for better performance
            $table->index(['vaccine_schedule_id', 'status']);
            $table->index('vaccination_date');
            $table->index('next_vaccination_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vaccine_schedules_details');
    }
};

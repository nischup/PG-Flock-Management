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
     Schema::create('vaccine_schedules', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->string('job_no', 50);
            $table->unsignedBigInteger('project_id');
            $table->string('flock_no', 50);
            $table->unsignedBigInteger('shed_id');
            $table->string('batch_no', 50);
            $table->unsignedBigInteger('breed_type_id');
            $table->tinyInteger('status')->default(1);
            $table->timestamps();

            // Foreign key constraints
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
            $table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');
            $table->foreign('shed_id')->references('id')->on('sheds')->onDelete('cascade');
            $table->foreign('breed_type_id')->references('id')->on('breed_types')->onDelete('cascade');

            // Indexes for better performance
            $table->index(['company_id', 'project_id']);
            $table->index(['flock_no', 'batch_no']);
            $table->index('job_no');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vaccine_schedules');
        
        // Recreate the original vaccine_schedules table
        Schema::create('vaccine_schedules', function (Blueprint $table) {
            $table->id();
            $table->string('project', 100);
            $table->string('flock_no', 50);
            $table->string('batch_no', 50);
            $table->string('breed_type', 50);
            $table->string('disease', 100);
            $table->string('vaccine', 100);
            $table->string('age', 50);
            $table->date('last_vaccination');
            $table->date('next_vaccination');
            $table->tinyInteger('status')->default(1);
            $table->timestamps();
        });
    }
};

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
        Schema::create('htc_hatchery_receives', function (Blueprint $table) {
            $table->id();

            $table->date('receive_date');
            $table->unsignedBigInteger('store_receive_id'); // FK to store_receive
            // Link to hatchery and store receive
            $table->unsignedBigInteger('hatchery_id'); // FK to master hatchery
            $table->unsignedBigInteger('egg_classification_grade_id'); 
            $table->unsignedBigInteger('flock_id'); 
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('project_id')->nullable();
            $table->string('shade_id')->nullable();
            $table->string('batch_no')->nullable();
            $table->string('job_no')->nullable();
            $table->string('transaction_no')->nullable();
            
            $table->unsignedInteger('quantity')->default(0);

            // Status flags
            $table->tinyInteger('status')->default(1)
                  ->comment('1 = Active, 0 = Inactive');
            $table->tinyInteger('receive_status')->default(1)
                  ->comment('1 = Hatchery Transfer, 2 = Received');

            // Foreign keys
            $table->foreign('hatchery_id')->references('id')->on('hatcheries')->cascadeOnDelete();
            $table->foreign('store_receive_id')->references('id')->on('store_receives')->cascadeOnDelete();
            $table->foreign('egg_classification_grade_id')->references('id')->on('egg_classification_grades')->cascadeOnDelete();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('htc_hatchery_receives');
    }
};

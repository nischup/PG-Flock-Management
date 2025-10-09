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
        Schema::create('store_receives', function (Blueprint $table) {
            $table->id();

            // Transfer info
            $table->date('receive_date');  
            $table->unsignedBigInteger('egg_classification_grade_id');        
            $table->unsignedBigInteger('flock_id'); 
            $table->unsignedBigInteger('company_id'); 
            $table->unsignedBigInteger('project_id')->nullable(); // floor/level
            $table->string('shade_id')->nullable();
            $table->string('batch_no')->nullable();
            $table->string('job_no')->nullable();
            $table->string('transaction_no')->nullable();
            
            $table->unsignedInteger('quantity')->default(0);
            // FK to store
            $table->foreignId('store_id')->constrained('stores')->cascadeOnDelete();
            $table->tinyInteger('status')->default(1)
                  ->comment('1 = Active, 0 = Inactive');
            $table->tinyInteger('transfer_status')->default(1)
                  ->comment('1 = Firm Transfer, 2 = Store Receive, 3 = Store Transfer');
            // FK to egg classification grade
            $table->foreign('egg_classification_grade_id')
                  ->references('id')
                  ->on('egg_classification_grades')
                  ->onDelete('cascade');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('store_receives');
    }
};

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
        Schema::create('firm_lab_tests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('batch_assign_id')
                  ->constrained('batch_assigns')
                  ->onDelete('cascade');

            $table->tinyInteger('firm_lab_type')->default(2); // Provita Lab
            
            $table->integer('firm_lab_send_female_qty')->default(0);
            $table->integer('firm_lab_send_male_qty')->default(0);
            $table->integer('firm_lab_send_total_qty')->default(0);
             $table->integer('firm_lab_receive_female_qty')->default(0);
            $table->integer('firm_lab_receive_male_qty')->default(0);
            $table->integer('firm_lab_receive_total_qty')->default(0);

            $table->string('note')->nullable();
            $table->string('remarks')->nullable();
            $table->tinyInteger('status')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('firm_lab_tests');
    }
};

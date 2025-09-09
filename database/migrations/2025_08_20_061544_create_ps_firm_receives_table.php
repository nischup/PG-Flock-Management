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
Schema::create('ps_firm_receives', function (Blueprint $table) {
    
            $table->id();
            $table->foreignId('ps_receive_id')
                ->constrained('ps_receives')
                ->onDelete('cascade');

            $table->string('job_no')->nullable();
            $table->string('transaction_no')->nullable();
            $table->unsignedTinyInteger('receiving_company_id')->default(0);
            $table->unsignedInteger('firm_female_box_qty')->default(0);
            $table->unsignedInteger('firm_male_box_qty')->default(0);
            $table->unsignedInteger('firm_total_box_qty')->default(0);
            $table->string('remarks')->nullable();
            $table->unsignedTinyInteger('created_by')->nullable();
            $table->unsignedTinyInteger('updated_by')->nullable();
            $table->unsignedTinyInteger('status')->default(1);
            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ps_firm_receives');
    }
};

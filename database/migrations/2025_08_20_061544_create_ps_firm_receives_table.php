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
            $table->unsignedTinyInteger('receiving_company_id')->nullable();
            $table->unsignedInteger('female_box_qty')->nullable();
            $table->unsignedInteger('male_box_qty')->nullable();
            $table->unsignedInteger('total_box_qty')->nullable();
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

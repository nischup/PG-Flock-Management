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
        Schema::create('bird_transfers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('batch_assign_id')->constrained('batch_assigns')->onDelete('cascade');

            $table->string('job_no')->nullable();
            $table->string('transaction_no')->nullable();
            $table->unsignedInteger('flock_no')->default(0);
            $table->foreignId('flock_id')->constrained('flocks');
            $table->foreignId('from_company_id')->constrained('companies');
            $table->foreignId('to_company_id')->constrained('companies');
            $table->foreignId('from_shed_id')->constrained('sheds');
            $table->unsignedInteger('to_shed_id')->nullable();
            $table->date('transfer_date');
            $table->integer('transfer_female_qty')->default(0);
            $table->integer('transfer_male_qty')->default(0);
            $table->integer('transfer_total_qty')->default(0);
            $table->integer('medical_female_qty')->default(0);
            $table->integer('medical_male_qty')->default(0);
            $table->integer('medical_total_qty')->default(0);
            $table->integer('deviation_female_qty')->default(0);
            $table->integer('deviation_male_qty')->default(0);
            $table->integer('deviation_total_qty')->default(0);
            $table->tinyInteger('shipment_type_id');
            $table->string('lc_no')->nullable();
            $table->json('breed_type')->nullable();
            $table->tinyInteger('country_of_origin')->default(0);
            $table->tinyInteger('transport_type')->default(0);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedTinyInteger('status')->default(1);
            $table->timestamps();


        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bird_transfers');
    }
};

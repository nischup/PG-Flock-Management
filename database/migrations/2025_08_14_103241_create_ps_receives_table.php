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
        Schema::create('ps_receives', function (Blueprint $table) {
            $table->id();
            $table->tinyInteger('shipment_type_id');
            $table->string('pi_no')->nullable();
            $table->date('pi_date')->nullable();
            $table->string('order_no')->nullable();
            $table->date('order_date')->nullable();
            $table->string('lc_no')->nullable();
            $table->date('lc_date')->nullable();
            $table->tinyInteger('supplier_id')->default(0);
            $table->tinyInteger('breed_type')->default(0);
            $table->tinyInteger('country_of_origin')->default(0);
            $table->tinyInteger('transport_type')->default(0);
            $table->unsignedTinyInteger('company_id')->default(0);
            $table->string('remarks')->nullable();
            $table->tinyInteger('created_by')->nullable();
            $table->tinyInteger('updated_by')->nullable();
            $table->tinyInteger('status')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ps_receives');
    }
};

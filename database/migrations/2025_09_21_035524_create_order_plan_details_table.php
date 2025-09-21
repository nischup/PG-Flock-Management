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
        Schema::create('order_plan_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_plan_id')->constrained()->onDelete('cascade');
            $table->string('order_volume');
            $table->date('shipping_date');
            $table->string('supply_base');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_plan_details');
    }
};

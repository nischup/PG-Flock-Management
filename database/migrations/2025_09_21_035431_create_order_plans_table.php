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
        Schema::create('order_plans', function (Blueprint $table) {
            $table->id();
            $table->string('order_from');
            $table->string('order_to');
            $table->text('cc')->nullable();
            $table->string('subject')->nullable(); 
            $table->text('message')->nullable();
            $table->string('attachment')->nullable(); // 👈 new column
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('approve_by')->nullable();
            $table->dateTime('approve_date')->nullable();
            $table->tinyInteger('status')->default(1); // 1 = created
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_plans');
    }
};

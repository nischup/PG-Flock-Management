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
        Schema::create('ps_lab_tests', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ps_receive_id');
            $table->foreign('ps_receive_id')->references('id')->on('ps_receives')->onDelete('cascade');
            $table->string('lab_type');
            $table->integer('female_qty')->default(0);
            $table->integer('male_qty')->default(0);
            $table->integer('total_qty')->default(0);
            $table->string('notes')->nullable();
            $table->tinyinteger('status')->default(1);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ps_lab_tests');
    }
};

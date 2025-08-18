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
     Schema::create('ps_chick_counts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ps_receive_id')
                  ->constrained('ps_receives')
                  ->onDelete('cascade');

            // Male chicks
            $table->integer('ps_male_box')->default(0);
            $table->integer('ps_male_approximate_qty')->default(0);
            $table->double('ps_male_totalqty')->default(0);
            $table->double('ps_male_challan_qty')->default(0);
            $table->double('ps_male_rate')->default(0);
            $table->double('ps_male_value_total')->default(0);

            // Female chicks
            $table->integer('ps_female_box')->default(0);
            $table->integer('ps_female_approximate_qty')->default(0);
            $table->double('ps_female_totalqty')->default(0);
            $table->double('ps_challan_qty')->default(0);
            $table->double('ps_female_rate')->default(0);
            $table->double('ps_female_value_total')->default(0);

            // Total Calculation
            $table->double('ps_totalbox')->default(0);
            $table->double('ps_value_total')->default(0);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ps_chick_counts');
    }
};

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
     Schema::create('doc_chick_counts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('doc_receive_id')
                  ->constrained('doc_receives')
                  ->onDelete('cascade');

            // Male chicks
            $table->integer('doc_male_box')->default(0);
            $table->integer('doc_male_approximate_qty')->default(0);
            $table->double('doc_male_totalqty')->default(0);
            $table->double('doc_male_challan_qty')->default(0);
            $table->double('doc_male_rate')->default(0);
            $table->double('doc_male_value_total')->default(0);

            // Female chicks
            $table->integer('doc_female_box')->default(0);
            $table->integer('doc_female_approximate_qty')->default(0);
            $table->double('doc_female_totalqty')->default(0);
            $table->double('doc_challan_qty')->default(0);
            $table->double('doc_female_rate')->default(0);
            $table->double('doc_female_value_total')->default(0);

            // Total Calculation
            $table->double('doc_totalbox')->default(0);
            $table->double('doc_value_total')->default(0);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('doc_chick_counts');
    }
};

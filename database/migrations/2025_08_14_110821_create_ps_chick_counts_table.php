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

                $table->unsignedInteger('ps_male_rec_box')->default(0);
                $table->decimal('ps_male_qty', 12, 2)->default(0);

                // Female chicks
                $table->unsignedInteger('ps_female_rec_box')->default(0);
                $table->decimal('ps_female_qty', 12, 2)->default(0);

                // Total Calculation
                $table->decimal('ps_total_qty', 12, 2)->default(0);
                $table->unsignedInteger('ps_total_re_box_qty')->default(0);
                $table->unsignedInteger('ps_challan_box_qty')->default(0);
                $table->decimal('ps_gross_weight', 10, 2)->default(0);
                $table->decimal('ps_net_weight', 10, 2)->default(0);

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

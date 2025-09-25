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
        Schema::table('batch_assigns', function (Blueprint $table) {
            $table->integer('batch_received_female_qty')->default(0);
            $table->integer('batch_received_male_qty')->default(0);
            $table->integer('batch_received_total_qty')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('batch_assigns', function (Blueprint $table) {
            $table->dropColumn([
                'batch_received_female_qty',
                'batch_received_male_qty',
                'batch_received_total_qty'
            ]);
        });
    }
};

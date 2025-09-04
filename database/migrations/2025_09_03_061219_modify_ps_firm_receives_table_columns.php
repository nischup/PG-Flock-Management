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
        Schema::table('ps_firm_receives', function (Blueprint $table) {
            // Rename existing box columns
            $table->renameColumn('firm_female_box_qty', 'firm_female_qty');
            $table->renameColumn('firm_male_box_qty', 'firm_male_qty');
            $table->renameColumn('firm_total_box_qty', 'firm_total_qty');

            // Modify other existing columns if needed
            $table->string('job_no')->nullable()->change();
            $table->string('receive_type')->nullable()->default('box');

            
            $table->string('source_type')->nullable();
            $table->unsignedTinyInteger('source_id');
            $table->unsignedTinyInteger('flock_id')->nullable();
            $table->string('flock_name')->nullable();
            $table->unsignedTinyInteger('receiving_company_id')->default(0)->change();
            $table->string('remarks')->nullable()->change();
            $table->unsignedTinyInteger('created_by')->nullable()->change();
            $table->unsignedTinyInteger('updated_by')->nullable()->change();
            $table->unsignedTinyInteger('status')->default(1)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ps_firm_receives', function (Blueprint $table) {
            // Rename columns back to original
            $table->renameColumn('firm_female_qty', 'firm_female_box_qty');
            $table->renameColumn('firm_male_qty', 'firm_male_box_qty');
            $table->renameColumn('firm_total_qty', 'firm_total_box_qty');
        });
    }
};

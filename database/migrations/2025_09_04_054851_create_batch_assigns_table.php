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
        Schema::create('batch_assigns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shed_receive_id')->constrained('shed_receives')->onDelete('cascade');
            // Snapshot fields for reporting
            $table->string('job_no')->nullable();
            $table->unsignedInteger('flock_no')->default(0);
            $table->foreignId('flock_id')->constrained('flocks');
            $table->foreignId('company_id')->constrained('companies');
            $table->foreignId('shed_id')->constrained('sheds');
            $table->integer('level')->nullable();
            $table->integer('batch_no')->default(1);
            $table->integer('batch_female_qty')->default(0);
            $table->integer('batch_male_qty')->default(0);
            $table->integer('batch_total_qty')->default(0);
            $table->integer('batch_female_mortality')->default(0);
            $table->integer('batch_male_mortality')->default(0);
            $table->integer('batch_total_mortality')->default(0);
            $table->integer('batch_excess_male')->nullable();
            $table->integer('batch_excess_female')->default(0);
            $table->integer('batch_sortage_male')->nullable();
            $table->integer('batch_sortage_female')->default(0);
            $table->decimal('percentage', 5, 2)->default(0);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('batch_assigns');
    }
};

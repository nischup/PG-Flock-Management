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
        Schema::create('movement_adjustments', function (Blueprint $table) {
            $table->id()->comment('Primary key');

            $table->foreignId('flock_id')
                  ->constrained()
                  ->cascadeOnDelete()
                  ->comment('Reference to the flock this adjustment belongs to');
            $table->integer('flock_no')->default(0);
            $table->tinyInteger('stage')
                  ->comment('Stage of adjustment: 1=PS Receive, 2=Firm Receive, 3=Shed Receive, 4=Batch Assign, 5=Bird Transfer');

            $table->unsignedBigInteger('stage_id')
                  ->comment('ID of the record in the relevant stage table (e.g., ps_receives, firm_receives, etc.)');

            $table->tinyInteger('type')
                  ->comment('Type of adjustment: 1=Mortality, 2=Excess, 3=Shortage');

            $table->integer('male_qty')->default(0)
                  ->comment('Number of male birds affected');

            $table->integer('female_qty')->default(0)
                  ->comment('Number of female birds affected');

            $table->integer('total_qty')->default(0)
                  ->comment('Total number of birds affected (male + female)');

            $table->date('date')
                  ->comment('Date of the adjustment');

            $table->text('remarks')->nullable()
                  ->comment('Optional notes or reason for the adjustment');

            $table->timestamps();

            $table->index(['stage', 'stage_id'], 'stage_stageid_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('movement_adjustments');
    }
};

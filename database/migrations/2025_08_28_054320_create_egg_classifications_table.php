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
        Schema::create('egg_classifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('batchassign_id')->constrained('batch_assigns')->cascadeOnDelete(); // batch being classified
            $table->date('classification_date');
            $table->unsignedInteger('total_eggs')->default(0);
            $table->unsignedInteger('rejected_eggs')->default(0);
            $table->unsignedInteger('technical_eggs')->default(0);
            $table->unsignedInteger('hatching_eggs')->default(0);
            $table->text('remarks')->nullable();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('egg_classifications');
    }
};

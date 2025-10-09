<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('egg_classification_grade_details', function (Blueprint $table) {
            $table->id();

            // Foreign key to egg_classification_grades
            $table->unsignedInteger('egg_classification_grade_id'); // ✅ OK (better: use unsignedBigInteger for consistency)
            
            // Foreign key to egg_grades
            $table->foreignId('egg_grade_id')
                ->constrained('egg_grades')
                ->cascadeOnDelete();

            $table->unsignedInteger('quantity')->default(0);
            $table->timestamps();

            // Foreign key definition
            $table->foreign('egg_classification_grade_id')
                ->references('id')
                ->on('egg_classification_grades')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('egg_classification_grade_details');
    }
};
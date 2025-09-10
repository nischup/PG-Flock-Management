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
        Schema::create('egg_classification_grades', function (Blueprint $table) {
           $table->id();
            $table->foreignId('classification_id')
                ->constrained('egg_classifications')
                ->cascadeOnDelete();
            $table->foreignId('egg_grade_id')
                ->constrained('egg_grades')
                ->cascadeOnDelete();
            $table->unsignedInteger('quantity')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('egg_classification_grades');
    }
};

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
        Schema::create('egg_types', function (Blueprint $table) {
            $table->id();
        $table->string('name');        // floor_egg, thin_egg, dirty_egg
        $table->tinyInteger('category'); // 1 = technical, 2 = rejected
        $table->tinyInteger('status')->default(1);
        $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('egg_types');
    }
};

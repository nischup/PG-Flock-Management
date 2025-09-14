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
        Schema::table('vaccine_schedules', function (Blueprint $table) {
            $table->string('flock_no', 50)->nullable()->change();
            $table->string('batch_no', 50)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vaccine_schedules', function (Blueprint $table) {
            $table->string('flock_no', 50)->nullable(false)->change();
            $table->string('batch_no', 50)->nullable(false)->change();
        });
    }
};

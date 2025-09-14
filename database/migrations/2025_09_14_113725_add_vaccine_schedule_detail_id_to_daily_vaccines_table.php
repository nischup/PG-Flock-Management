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
        Schema::table('daily_vaccines', function (Blueprint $table) {
            $table->unsignedBigInteger('vaccine_schedule_detail_id')->nullable()->after('vaccine_schedule_id');
            $table->foreign('vaccine_schedule_detail_id')->references('id')->on('vaccine_schedules_details')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('daily_vaccines', function (Blueprint $table) {
            $table->dropForeign(['vaccine_schedule_detail_id']);
            $table->dropColumn('vaccine_schedule_detail_id');
        });
    }
};

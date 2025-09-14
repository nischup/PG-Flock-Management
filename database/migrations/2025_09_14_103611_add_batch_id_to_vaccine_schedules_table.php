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
            $table->unsignedBigInteger('batch_id')->nullable()->after('flock_id');
            $table->foreign('batch_id')->references('id')->on('batches')->onDelete('cascade');
            $table->index('batch_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vaccine_schedules', function (Blueprint $table) {
            $table->dropForeign(['batch_id']);
            $table->dropIndex(['batch_id']);
            $table->dropColumn('batch_id');
        });
    }
};

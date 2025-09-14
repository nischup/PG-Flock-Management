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
            $table->unsignedBigInteger('flock_id')->nullable()->after('project_id');
            $table->foreign('flock_id')->references('id')->on('flocks')->onDelete('cascade');
            $table->index('flock_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vaccine_schedules', function (Blueprint $table) {
            $table->dropForeign(['flock_id']);
            $table->dropIndex(['flock_id']);
            $table->dropColumn('flock_id');
        });
    }
};

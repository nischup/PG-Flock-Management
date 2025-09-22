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
        Schema::table('movement_adjustments', function (Blueprint $table) {
            $table->string('job_no')->nullable()->after('id');
            $table->string('transaction_no')->nullable()->after('job_no');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('movement_adjustments', function (Blueprint $table) {
            $table->dropColumn(['job_no', 'transaction_no']);
        });
    }
};

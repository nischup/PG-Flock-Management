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
        Schema::table('batch_assigns', function (Blueprint $table) {
            $table->integer('stage')->default(1)->after('shed_id');
            $table->date('growing_start_date')->nullable()->after('stage');
            $table->date('transfer_date')->nullable()->after('growing_start_date');
            $table->tinyInteger('status')->default(1)->comment('1 = active, 0 = inactive');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('batch_assigns', function (Blueprint $table) {
            $table->dropColumn(['stage', 'growing_start_date', 'transfer_date','status']);
        });
    }
};

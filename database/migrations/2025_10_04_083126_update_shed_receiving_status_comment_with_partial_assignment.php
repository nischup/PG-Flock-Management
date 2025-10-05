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
        Schema::table('ps_firm_receives', function (Blueprint $table) {
            $table->tinyInteger('shed_receiving_status')
                ->default(0)
                ->comment('0 = Shed Receive Pending, 1 = Partially Assigned, 2 = Fully Assigned')
                ->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ps_firm_receives', function (Blueprint $table) {
            $table->tinyInteger('shed_receiving_status')
                ->default(0)
                ->comment('0 = Shed Receive Pending, 1 = Shed Receive')
                ->change();
        });
    }
};

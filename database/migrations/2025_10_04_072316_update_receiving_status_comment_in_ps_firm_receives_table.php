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
            $table->tinyInteger('receiving_status')
                ->default(0)
                ->comment('Shed Receive')
                ->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ps_firm_receives', function (Blueprint $table) {
            $table->tinyInteger('receiving_status')
                ->default(0)
                ->comment('0 = Default, 1 = Receive')
                ->change();
        });
    }
};

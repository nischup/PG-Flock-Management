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
        // Update existing records to set receiving_status to 0 (Pending) where it's currently 1
        \DB::table('ps_receives')
            ->where('receiving_status', 1)
            ->update(['receiving_status' => 0]);

        // Modify the column to change the default value and comment
        Schema::table('ps_receives', function (Blueprint $table) {
            $table->tinyInteger('receiving_status')
                ->default(0)
                ->comment('0 = Pending, 1 = Receive, 2 = Return')
                ->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert the column to the previous default value and comment
        Schema::table('ps_receives', function (Blueprint $table) {
            $table->tinyInteger('receiving_status')
                ->default(1)
                ->comment('1 = Receive, 2 = Return')
                ->change();
        });
    }
};

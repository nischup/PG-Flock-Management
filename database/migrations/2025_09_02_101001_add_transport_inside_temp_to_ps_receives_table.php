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
        Schema::table('ps_receives', function (Blueprint $table) {
            $table->decimal('transport_inside_temp', 5, 2)
              ->nullable()
              ->after('transport_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ps_receives', function (Blueprint $table) {
           $table->dropColumn('transport_inside_temp');
        });
    }
};

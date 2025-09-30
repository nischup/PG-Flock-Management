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
            $table->unsignedBigInteger('breed_type')->nullable()->after('shed_id');
            $table->foreign('breed_type')->references('id')->on('breed_types')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('batch_assigns', function (Blueprint $table) {
            $table->dropForeign(['breed_type']);
            $table->dropColumn('breed_type');
        });
    }
};

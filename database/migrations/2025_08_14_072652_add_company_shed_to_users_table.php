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
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('company_id')->default(0)->nullable(false);
            $table->unsignedBigInteger('shed_id')->default(0)->nullable(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
           if (Schema::hasColumn('users', 'company_id')) {
                $table->dropColumn('company_id');
            }
            if (Schema::hasColumn('users', 'shed_id')) {
                $table->dropColumn('shed_id');
            }
        });
    }
};

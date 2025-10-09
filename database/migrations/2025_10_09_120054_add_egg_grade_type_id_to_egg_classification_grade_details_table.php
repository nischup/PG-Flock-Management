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
        Schema::table('egg_classification_grade_details', function (Blueprint $table) {
            $table->unsignedInteger('egg_grade_type_id')
                  ->nullable()
                  ->after('egg_grade_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('egg_classification_grade_details', function (Blueprint $table) {
            $table->dropColumn('egg_grade_type_id');
        });
    }
};

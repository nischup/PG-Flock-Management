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
        Schema::table('bird_transfers', function (Blueprint $table) {
            $table->foreignId('to_project_id')
                  ->nullable()
                  ->after('to_shed_id') // place it after the column you prefer
                  ->constrained('projects')
                  ->onDelete('set null'); // optional, keeps data integrity
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bird_transfers', function (Blueprint $table) {
            $table->dropForeign(['to_project_id']);
            $table->dropColumn('to_project_id');
        });
    }
};

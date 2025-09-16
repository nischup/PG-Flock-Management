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
        // Add project_id to firm_receive
        Schema::table('ps_firm_receives', function (Blueprint $table) {
            $table->unsignedBigInteger('project_id')->nullable()->after('receiving_company_id'); // nullable for existing data
            $table->foreign('project_id')->references('id')->on('projects')->onDelete('set null');
        });

        // Add project_id to shed_receive
        Schema::table('shed_receives', function (Blueprint $table) {
            $table->unsignedBigInteger('project_id')->nullable()->after('flock_no');
            $table->foreign('project_id')->references('id')->on('projects')->onDelete('set null');
        });

        // Add project_id to batch_assign
        Schema::table('batch_assigns', function (Blueprint $table) {
            $table->unsignedBigInteger('project_id')->nullable()->after('company_id');
            $table->foreign('project_id')->references('id')->on('projects')->onDelete('set null');
        });

        // Add project_id to bird_transfer
        Schema::table('bird_transfers', function (Blueprint $table) {
            $table->unsignedBigInteger('project_id')->nullable()->after('from_company_id');
            $table->foreign('project_id')->references('id')->on('projects')->onDelete('set null');
        });

        // Add project_id to daily_operation
        Schema::table('daily_operations', function (Blueprint $table) {
            $table->unsignedBigInteger('project_id')->nullable()->after('company_id');
            $table->foreign('project_id')->references('id')->on('projects')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ps_firm_receives', function (Blueprint $table) {
            $table->dropForeign(['project_id']);
            $table->dropColumn('project_id');
        });

        Schema::table('shed_receives', function (Blueprint $table) {
            $table->dropForeign(['project_id']);
            $table->dropColumn('project_id');
        });

        Schema::table('batch_assigns', function (Blueprint $table) {
            $table->dropForeign(['project_id']);
            $table->dropColumn('project_id');
        });

        Schema::table('bird_transfers', function (Blueprint $table) {
            $table->dropForeign(['project_id']);
            $table->dropColumn('project_id');
        });

        Schema::table('daily_operations', function (Blueprint $table) {
            $table->dropForeign(['project_id']);
            $table->dropColumn('project_id');
        });
    }
};

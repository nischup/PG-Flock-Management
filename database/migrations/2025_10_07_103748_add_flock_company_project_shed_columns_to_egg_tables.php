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
        // Add columns to egg_classifications
        Schema::table('egg_classifications', function (Blueprint $table) {
            $table->unsignedBigInteger('flock_id')->nullable()->after('id');
            $table->unsignedBigInteger('company_id')->nullable()->after('flock_id');
            $table->unsignedBigInteger('project_id')->nullable()->after('company_id');
            $table->unsignedBigInteger('shed_id')->nullable()->after('project_id');
            $table->string('batch_no')->nullable()->after('shed_id');
            $table->string('flock_no')->nullable()->after('batch_no');

            
            $table->string('job_no')->nullable()->after('flock_id');
            $table->string('transaction_no')->nullable()->after('job_no');
        });

        // Add columns to egg_classification_grades
        Schema::table('egg_classification_grades', function (Blueprint $table) {
            $table->unsignedBigInteger('flock_id')->nullable()->after('id');
            $table->unsignedBigInteger('company_id')->nullable()->after('flock_id');
            $table->unsignedBigInteger('project_id')->nullable()->after('company_id');
            $table->unsignedBigInteger('batchassign_id');
            $table->unsignedBigInteger('shed_id')->nullable()->after('project_id');
            $table->string('batch_no')->nullable()->after('shed_id');
            $table->string('flock_no')->nullable()->after('batch_no');
            $table->string('job_no')->nullable()->after('flock_id');
            $table->string('transaction_no')->nullable()->after('job_no');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('egg_classifications', function (Blueprint $table) {
            $table->dropColumn(['flock_id','company_id','project_id','shed_id','batch_no','flock_no','job_no','transaction_no']);
        });

        Schema::table('egg_classification_grades', function (Blueprint $table) {
            $table->dropColumn(['flock_id','company_id','project_id','shed_id','batch_no','flock_no','job_no','transaction_no','batchassign_id']);
        });
    }
};

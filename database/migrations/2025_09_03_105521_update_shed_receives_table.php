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
        Schema::table('shed_receives', function (Blueprint $table) {
            if (!Schema::hasColumn('shed_receives', 'receive_id')) {
                $table->foreignId('receive_id')
                    ->nullable()
                    ->constrained('ps_firm_receives')
                    ->onDelete('cascade');
            }

            if (!Schema::hasColumn('shed_receives', 'job_no')) {
                $table->string('job_no')->nullable();
            }

            if (!Schema::hasColumn('shed_receives', 'flock_id')) {
                $table->unsignedBigInteger('flock_id')->nullable();
            }

            if (!Schema::hasColumn('shed_receives', 'flock_name')) {
                $table->string('flock_name')->nullable();
            }

            if (!Schema::hasColumn('shed_receives', 'shed_id')) {
                $table->unsignedBigInteger('shed_id')->nullable();
            }

            if (!Schema::hasColumn('shed_receives', 'shed_female_qty')) {
                $table->unsignedInteger('shed_female_qty')->default(0);
            }

            if (!Schema::hasColumn('shed_receives', 'shed_male_qty')) {
                $table->unsignedInteger('shed_male_qty')->default(0);
            }

            if (!Schema::hasColumn('shed_receives', 'shed_total_qty')) {
                $table->unsignedInteger('shed_total_qty')->default(0);
            }

            // Shortage columns
            if (!Schema::hasColumn('shed_receives', 'shed_sortage_male')) {
                $table->unsignedInteger('shed_sortage_male')->default(0);
            }
            if (!Schema::hasColumn('shed_receives', 'shed_sortage_female')) {
                $table->unsignedInteger('shed_sortage_female')->default(0);
            }
            if (!Schema::hasColumn('shed_receives', 'shed_sortage_total')) {
                $table->unsignedInteger('shed_sortage_total')->default(0);
            }

            // Excess columns
            if (!Schema::hasColumn('shed_receives', 'shed_excess_male')) {
                $table->unsignedInteger('shed_excess_male')->default(0);
            }
            if (!Schema::hasColumn('shed_receives', 'shed_excess_female')) {
                $table->unsignedInteger('shed_excess_female')->default(0);
            }
            if (!Schema::hasColumn('shed_receives', 'shed_excess_total')) {
                $table->unsignedInteger('shed_excess_total')->default(0);
            }

            if (!Schema::hasColumn('shed_receives', 'receipt_type')) {
                $table->string('receipt_type')->nullable()->default('chicks');
            }

            if (!Schema::hasColumn('shed_receives', 'remarks')) {
                $table->string('remarks')->nullable();
            }

            if (!Schema::hasColumn('shed_receives', 'status')) {
                $table->unsignedTinyInteger('status')->default(1);
            }

            if (!Schema::hasColumn('shed_receives', 'created_by')) {
                $table->unsignedBigInteger('created_by')->nullable();
            }

            if (!Schema::hasColumn('shed_receives', 'updated_by')) {
                $table->unsignedBigInteger('updated_by')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shed_receives', function (Blueprint $table) {
            $columns = [
                'receive_id',
                'job_no',
                'flock_id',
                'flock_name',
                'shed_id',
                'shed_female_qty',
                'shed_male_qty',
                'shed_total_qty',
                'shed_sortage_male',
                'shed_sortage_female',
                'shed_sortage_total',
                'shed_excess_male',
                'shed_excess_female',
                'shed_excess_total',
                'receipt_type',
                'remarks',
                'status',
                'created_by',
                'updated_by',
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('shed_receives', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};

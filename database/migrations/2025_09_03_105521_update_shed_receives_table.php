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

            if (!Schema::hasColumn('shed_receives', 'transaction_no')) {
                $table->string('transaction_no')->nullable();
            }

           

            if (!Schema::hasColumn('shed_receives', 'flock_id')) {
                $table->unsignedBigInteger('flock_id')->nullable();
            }

            if (!Schema::hasColumn('shed_receives', 'flock_no')) {
                $table->string('flock_no')->nullable();
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

            if (!Schema::hasColumn('shed_receives', 'company_id')) {
                $table->unsignedInteger('company_id')->default(0);
            }

            if (!Schema::hasColumn('shed_receives', 'shed_total_qty')) {
                $table->unsignedInteger('shed_total_qty')->default(0);
            }

            
            if (!Schema::hasColumn('shed_receives', 'receive_type')) {
                $table->string('receive_type')->nullable()->default('chicks');
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
           if (Schema::hasColumn('shed_receives', 'receive_id')) {
            $table->dropForeign(['receive_id']);
            $table->dropColumn('receive_id');
        }
            $columns = [
                'receive_id',
                'job_no',
                'flock_id',
                'company_id',
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
                'receive_type',
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

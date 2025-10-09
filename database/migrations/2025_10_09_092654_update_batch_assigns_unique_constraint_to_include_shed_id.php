<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('batch_assigns', function (Blueprint $table) {
            // First add the new unique constraint that includes shed_id and removes batch_no
            // This ensures each shed can only have one batch per level per company/project/flock
            $table->unique(
                ['company_id', 'project_id', 'flock_id', 'shed_id', 'level'],
                'unique_shed_level_assignment'
            );
        });

        // Use raw SQL to drop the old constraint since it's being used by foreign keys
        DB::statement('ALTER TABLE batch_assigns DROP INDEX unique_batch_assignment');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('batch_assigns', function (Blueprint $table) {
            // Drop the new unique constraint
            $table->dropUnique('unique_shed_level_assignment');

            // Restore the original unique constraint
            $table->unique(
                ['company_id', 'project_id', 'flock_id', 'level', 'batch_no'],
                'unique_batch_assignment'
            );
        });
    }
};

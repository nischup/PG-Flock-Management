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
        Schema::create('approval_matrix_configs', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Display name for the configuration
            $table->string('module_name'); // 'ps-receive', 'ps-firm-receive', 'batch-assign', etc.
            $table->string('approval_type')->default('sequential'); // 'sequential', 'parallel', 'conditional'
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('approval_matrix_configs');
    }
};

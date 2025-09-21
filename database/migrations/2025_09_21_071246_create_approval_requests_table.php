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
        Schema::create('approval_requests', function (Blueprint $table) {
            $table->id();
            $table->string('module_name'); // 'ps-receive', 'ps-firm-receive', etc.
            $table->unsignedBigInteger('record_id'); // ID of the main record
            $table->foreignId('approval_matrix_config_id')
                ->constrained('approval_matrix_configs')
                ->onDelete('cascade');
            $table->string('status')->default('pending'); // 'pending', 'approved', 'rejected', 'expired'
            $table->json('approval_data')->nullable(); // Store relevant data for approval
            $table->foreignId('initiated_by')
                ->constrained('users')
                ->onDelete('cascade');
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('approval_requests');
    }
};

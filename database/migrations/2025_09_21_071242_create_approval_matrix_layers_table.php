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
        Schema::create('approval_matrix_layers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('approval_matrix_config_id')
                ->constrained('approval_matrix_configs')
                ->onDelete('cascade');
            $table->integer('layer_order'); // Order of approval (1, 2, 3, etc.)
            $table->string('layer_name'); // 'Security', 'Store Incharge', 'Audit', etc.
            $table->string('role_name'); // Spatie role name
            $table->boolean('is_required')->default(true);
            $table->boolean('can_override')->default(false);
            $table->integer('timeout_hours')->nullable(); // Auto-approve after X hours
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
        Schema::dropIfExists('approval_matrix_layers');
    }
};

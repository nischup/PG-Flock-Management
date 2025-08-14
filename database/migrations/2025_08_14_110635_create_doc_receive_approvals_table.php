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
     Schema::create('doc_receive_approvals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('doc_receive_id')
                  ->constrained('doc_receives')
                  ->onDelete('cascade'); // delete approval records if DOC deleted
            $table->foreignId('approved_by')
                  ->constrained('users')
                  ->onDelete('cascade'); // link to user table
            $table->unsignedTinyInteger('approval_layer')->nullable(); // e.g., 1, 2, 3
            $table->unsignedBigInteger('approval_id')->nullable(); // extra reference if needed
            $table->timestamps(); // created_at (approval date), updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('doc_receive_approvals');
    }
};

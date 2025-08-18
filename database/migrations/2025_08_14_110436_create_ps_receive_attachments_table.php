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
        Schema::create('ps_receive_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ps_receive_id')
                  ->constrained('ps_receives')
                  ->onDelete('cascade'); // delete attachments if DOC deleted
            $table->string('file_path')->nullable(); // where the file is stored
            $table->string('file_type')->nullable(); // e.g. pdf, jpg, docx
            $table->timestamps(); // includes created_at & updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ps_receive_attachments');
    }
};

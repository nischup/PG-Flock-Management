<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();
            $table->string('name', 200);
            $table->enum('supplier_type', ['Local', 'Foreign'])->default('Local');
            $table->string('address', 500)->nullable();
            $table->string('origin')->nullable();
            $table->string('contact_person', 255)->nullable();
            $table->string('contact_person_email', 255)->nullable();
            $table->string('contact_person_mobile', 20)->nullable();
            $table->tinyInteger('status')->default(1);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('suppliers');
    }
};

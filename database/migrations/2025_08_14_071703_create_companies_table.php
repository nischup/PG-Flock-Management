<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->string('name', 200);
            $table->string('company_type', 100)->nullable();
            $table->string('location', 200)->nullable();
            $table->string('contact_person_name', 150)->nullable();
            $table->string('contact_person_phone', 50)->nullable();
            $table->string('contact_person_email', 100)->nullable();
            $table->string('contact_person_designation', 100)->nullable();
            $table->tinyInteger('status')->default(1);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};

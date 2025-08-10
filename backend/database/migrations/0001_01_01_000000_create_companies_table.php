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
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->string('company_name')->unique();
            $table->string('contact_email')->unique();
            // $table->string('phone_number')->nullable();
            // $table->string('address')->nullable();
            // $table->string('logo')->nullable(); // Path to company logo
            $table->string('subdomain')->nullable();
            $table->enum('status', ['active','pending','suspended'])->default('pending');
            $table->timestamps();

            //indexes
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};

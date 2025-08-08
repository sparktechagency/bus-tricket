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
        Schema::create('drivers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('staff_number');
            $table->string('pin_code');
            $table->string('license_number')->unique();
            $table->string('license_expiry_date');
            $table->date('date_of_birth')->nullable();
            $table->decimal('rating', 3, 2)->default(0.00);
            $table->tinyInteger('experience_years')->default(0);
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();

            // Add unique constraint for staff_number within the same company
            $table->unique(['company_id', 'staff_number']);
            //index for faster lookups
            $table->index(['staff_number', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('drivers');
    }
};

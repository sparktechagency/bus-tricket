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
        Schema::table('companies', function (Blueprint $table) {
            $table->string('stripe_publishable_key')->nullable()->after('status');
            $table->text('stripe_secret_key')->nullable()->after('stripe_publishable_key');
            $table->text('stripe_webhook_secret')->nullable()->after('stripe_secret_key');
            $table->text('fare_rules')->nullable()->after('stripe_webhook_secret');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn(['stripe_publishable_key', 'stripe_secret_key','stripe_webhook_secret', 'fare_rules']);
        });
    }
};

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
        Schema::table('passenger_wallets', function (Blueprint $table) {
            $table->boolean('auto_topup_enabled')->default(false)->after('balance');
            $table->decimal('auto_topup_threshold', 8, 2)->default(3.50)->after('auto_topup_enabled');
            $table->unsignedBigInteger('auto_topup_amount')->default(35)->after('auto_topup_threshold'); // Amount in cents
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('passenger_wallets', function (Blueprint $table) {
            $table->dropColumn('auto_topup_enabled');
            $table->dropColumn('auto_topup_threshold');
            $table->dropColumn('auto_topup_amount');
        });
    }
};

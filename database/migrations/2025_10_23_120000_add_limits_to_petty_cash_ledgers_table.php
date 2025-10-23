<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('petty_cash_ledgers', function (Blueprint $table) {
            $table->decimal('max_charge_request_amount', 18, 2)->default(0)->after('limit_amount');
            $table->decimal('max_transaction_amount', 18, 2)->default(0)->after('max_charge_request_amount');
        });
    }

    public function down(): void
    {
        Schema::table('petty_cash_ledgers', function (Blueprint $table) {
            $table->dropColumn(['max_charge_request_amount', 'max_transaction_amount']);
        });
    }
};

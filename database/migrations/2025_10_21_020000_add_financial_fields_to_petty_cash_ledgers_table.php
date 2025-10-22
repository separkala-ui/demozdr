<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('petty_cash_ledgers', function (Blueprint $table) {
            if (! Schema::hasColumn('petty_cash_ledgers', 'account_number')) {
                $table->string('account_number')->nullable()->after('assigned_user_id');
            }
            if (! Schema::hasColumn('petty_cash_ledgers', 'iban')) {
                $table->string('iban', 34)->nullable()->after('account_number');
            }
            if (! Schema::hasColumn('petty_cash_ledgers', 'card_number')) {
                $table->string('card_number', 20)->nullable()->after('iban');
            }
            if (! Schema::hasColumn('petty_cash_ledgers', 'account_holder')) {
                $table->string('account_holder')->nullable()->after('card_number');
            }
        });
    }

    public function down(): void
    {
        Schema::table('petty_cash_ledgers', function (Blueprint $table) {
            foreach (['account_number', 'iban', 'card_number', 'account_holder'] as $column) {
                if (Schema::hasColumn('petty_cash_ledgers', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('petty_cash_transactions', function (Blueprint $table) {
            if (! Schema::hasColumn('petty_cash_transactions', 'category')) {
                $table->string('category', 100)
                    ->nullable()
                    ->after('description')
                    ->index();
            }
        });
    }

    public function down(): void
    {
        Schema::table('petty_cash_transactions', function (Blueprint $table) {
            if (Schema::hasColumn('petty_cash_transactions', 'category')) {
                $table->dropIndex('petty_cash_transactions_category_index');
                $table->dropColumn('category');
            }
        });
    }
};

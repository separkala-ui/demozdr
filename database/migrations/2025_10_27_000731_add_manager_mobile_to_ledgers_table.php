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
        Schema::table('petty_cash_ledgers', function (Blueprint $table) {
            $table->string('manager_mobile', 15)->nullable()->after('account_holder')->comment('شماره موبایل مدیر شعبه');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('petty_cash_ledgers', function (Blueprint $table) {
            $table->dropColumn('manager_mobile');
        });
    }
};

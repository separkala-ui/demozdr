<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('petty_cash_cycles', function (Blueprint $table) {
            if (! Schema::hasColumn('petty_cash_cycles', 'transactions_count')) {
                $table->unsignedInteger('transactions_count')->nullable()->after('closing_balance');
            }

            if (! Schema::hasColumn('petty_cash_cycles', 'expenses_count')) {
                $table->unsignedInteger('expenses_count')->nullable()->after('transactions_count');
            }

            if (! Schema::hasColumn('petty_cash_cycles', 'total_charges')) {
                $table->decimal('total_charges', 18, 2)->nullable()->after('expenses_count');
            }

            if (! Schema::hasColumn('petty_cash_cycles', 'total_expenses')) {
                $table->decimal('total_expenses', 18, 2)->nullable()->after('total_charges');
            }

            if (! Schema::hasColumn('petty_cash_cycles', 'total_adjustments')) {
                $table->decimal('total_adjustments', 18, 2)->nullable()->after('total_expenses');
            }

            if (! Schema::hasColumn('petty_cash_cycles', 'summary')) {
                $table->json('summary')->nullable()->after('total_adjustments');
            }

            if (! Schema::hasColumn('petty_cash_cycles', 'report_path')) {
                $table->string('report_path')->nullable()->after('summary');
            }
        });

        Schema::table('petty_cash_transactions', function (Blueprint $table) {
            if (! Schema::hasColumn('petty_cash_transactions', 'archive_cycle_id')) {
                $table->foreignId('archive_cycle_id')
                    ->nullable()
                    ->after('carry_over_id')
                    ->constrained('petty_cash_cycles')
                    ->nullOnDelete();
            }

            if (! Schema::hasColumn('petty_cash_transactions', 'archived_at')) {
                $table->timestampTz('archived_at')->nullable()->after('archive_cycle_id');
            }

            if (! Schema::hasColumn('petty_cash_transactions', 'charge_origin')) {
                $table->string('charge_origin', 32)->nullable()->after('archived_at')->comment('quick_entry, request_form, carry_over, system, etc.');
            }
        });
    }

    public function down(): void
    {
        Schema::table('petty_cash_cycles', function (Blueprint $table) {
            foreach (['transactions_count', 'expenses_count', 'total_charges', 'total_expenses', 'total_adjustments', 'summary', 'report_path'] as $column) {
                if (Schema::hasColumn('petty_cash_cycles', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::table('petty_cash_transactions', function (Blueprint $table) {
            if (Schema::hasColumn('petty_cash_transactions', 'archive_cycle_id')) {
                $table->dropForeign(['archive_cycle_id']);
                $table->dropColumn('archive_cycle_id');
            }

            foreach (['archived_at', 'charge_origin'] as $column) {
                if (Schema::hasColumn('petty_cash_transactions', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};


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
        Schema::create('petty_cash_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ledger_id')->constrained('petty_cash_ledgers')->cascadeOnDelete();
            $table->string('type', 20)->comment('charge, expense, adjustment');
            $table->string('status', 20)->default('draft')->comment('draft, submitted, approved, rejected');
            $table->decimal('amount', 18, 2)->comment('Always positive; semantic defined by type');
            $table->decimal('amount_local_currency', 18, 2)->nullable()->comment('Optional second currency support');
            $table->string('currency', 3)->default('IRR');
            $table->dateTimeTz('transaction_date')->comment('Recorded date/time in UTC; displayed as Jalali to users');
            $table->string('reference_number')->nullable()->comment('Manual reference such as receipt number');
            $table->text('description')->nullable();
            $table->json('meta')->nullable()->comment('Flexible JSON payload (eg. rejection_reason)');
            $table->foreignId('requested_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestampTz('approved_at')->nullable();
            $table->foreignId('rejected_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestampTz('rejected_at')->nullable();
            $table->foreignId('carry_over_id')->nullable()->constrained('petty_cash_transactions')->nullOnDelete();
            $table->timestamps();

            $table->index(['ledger_id', 'transaction_date']);
            $table->index(['ledger_id', 'status']);
            $table->index('type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('petty_cash_transactions');
    }
};

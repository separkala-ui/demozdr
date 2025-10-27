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
        Schema::create('petty_cash_ledgers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('branch_id')->nullable()->comment('Optional relation to an external branches table');
            $table->string('branch_name')->comment('Display name of the branch');
            $table->decimal('limit_amount', 18, 2)->default(0)->comment('Approved petty cash ceiling');
            $table->decimal('opening_balance', 18, 2)->default(0)->comment('Balance carried from previous period');
            $table->decimal('current_balance', 18, 2)->default(0)->comment('Live balance updated after approvals');
            $table->timestampTz('last_reconciled_at')->nullable();
            $table->timestampTz('last_charge_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->json('settings')->nullable()->comment('Per-ledger configuration (alerts, thresholds, etc.)');
            $table->timestamps();

            $table->index('branch_id');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('petty_cash_ledgers');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Carbon;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('petty_cash_cycles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ledger_id')->constrained('petty_cash_ledgers')->cascadeOnDelete();
            $table->string('status', 32)->default('open');
            $table->timestampTz('opened_at');
            $table->decimal('opening_balance', 18, 2)->default(0);
            $table->foreignId('requested_close_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestampTz('requested_close_at')->nullable();
            $table->text('request_note')->nullable();
            $table->foreignId('closed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestampTz('closed_at')->nullable();
            $table->decimal('closing_balance', 18, 2)->nullable();
            $table->text('closing_note')->nullable();
            $table->timestamps();

            $table->index(['ledger_id', 'status']);
        });

        if (Schema::hasTable('petty_cash_ledgers')) {
            $now = Carbon::now();
            $ledgers = DB::table('petty_cash_ledgers')->select('id', 'opening_balance', 'created_at')->get();

            foreach ($ledgers as $ledger) {
                DB::table('petty_cash_cycles')->insert([
                    'ledger_id' => $ledger->id,
                    'status' => 'open',
                    'opened_at' => $ledger->created_at ?? $now,
                    'opening_balance' => $ledger->opening_balance ?? 0,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('petty_cash_cycles');
    }
};

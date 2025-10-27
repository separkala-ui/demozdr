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
        Schema::create('branch_users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ledger_id')->constrained('petty_cash_ledgers')->onDelete('cascade')->comment('شناسه شعبه');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade')->comment('شناسه کاربر');
            $table->string('access_type')->default('petty_cash')->comment('نوع دسترسی: petty_cash, inspection, quality_control, production_engineering');
            $table->boolean('is_active')->default(true)->comment('وضعیت فعال/غیرفعال');
            $table->text('permissions')->nullable()->comment('دسترسی‌های اضافی (JSON)');
            $table->timestamps();

            // Indexes
            $table->unique(['ledger_id', 'user_id', 'access_type'], 'branch_user_access_unique');
            $table->index('ledger_id');
            $table->index('user_id');
            $table->index('access_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('branch_users');
    }
};

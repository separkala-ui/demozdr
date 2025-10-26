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
        Schema::create('system_announcements', function (Blueprint $table) {
            $table->id();
            $table->string('title')->comment('عنوان اطلاعیه');
            $table->text('content')->comment('محتوای اطلاعیه');
            $table->string('type')->default('info')->comment('نوع (info, success, warning, danger)');
            $table->string('priority')->default('normal')->comment('اولویت (low, normal, high, urgent)');
            $table->boolean('is_active')->default(true)->comment('فعال/غیرفعال');
            $table->boolean('is_pinned')->default(false)->comment('سنجاق شده در بالا');
            $table->timestamp('starts_at')->nullable()->comment('تاریخ شروع نمایش');
            $table->timestamp('expires_at')->nullable()->comment('تاریخ انقضا');
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null')->comment('ایجاد شده توسط');
            $table->json('target_roles')->nullable()->comment('نقش‌های هدف (null = همه)');
            $table->json('target_users')->nullable()->comment('کاربران هدف (null = همه)');
            $table->string('icon')->nullable()->comment('آیکون (lucide icon name)');
            $table->string('action_url')->nullable()->comment('لینک عملیات');
            $table->string('action_text')->nullable()->comment('متن دکمه عملیات');
            $table->integer('view_count')->default(0)->comment('تعداد بازدید');
            $table->timestamps();
            $table->softDeletes();
            
            $table->index('type');
            $table->index('priority');
            $table->index('is_active');
            $table->index('is_pinned');
            $table->index(['starts_at', 'expires_at']);
            $table->index('created_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('system_announcements');
    }
};

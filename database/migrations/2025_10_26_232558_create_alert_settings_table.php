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
        Schema::create('alert_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique()->comment('کلید تنظیمات (مثلا: low_balance_threshold)');
            $table->string('category')->default('general')->comment('دسته‌بندی (general, petty_cash, transaction)');
            $table->string('type')->default('percentage')->comment('نوع (percentage, amount, count, boolean)');
            $table->text('value')->comment('مقدار تنظیمات (JSON برای مقادیر پیچیده)');
            $table->string('title_fa')->comment('عنوان فارسی');
            $table->text('description_fa')->nullable()->comment('توضیحات فارسی');
            $table->string('title_en')->nullable()->comment('عنوان انگلیسی');
            $table->text('description_en')->nullable()->comment('توضیحات انگلیسی');
            $table->boolean('is_active')->default(true)->comment('فعال/غیرفعال');
            $table->boolean('is_editable')->default(true)->comment('قابل ویرایش توسط مدیر');
            $table->integer('priority')->default(0)->comment('اولویت نمایش');
            $table->timestamps();
            
            $table->index('category');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('alert_settings');
    }
};

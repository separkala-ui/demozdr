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
        Schema::create('form_templates', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('category')->default('general');
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();
            $table->index('category');
        });

        Schema::create('form_template_fields', function (Blueprint $table) {
            $table->id();
            $table->foreignId('template_id')->constrained('form_templates')->onDelete('cascade');
            $table->string('label');
            $table->string('type');
            $table->text('description')->nullable();
            $table->integer('order')->default(0);
            $table->boolean('is_required')->default(true);
            $table->json('options')->nullable();
            $table->timestamps();
        });

        Schema::create('form_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('template_id')->constrained('form_templates')->onDelete('cascade');
            $table->foreignId('ledger_id')->constrained('petty_cash_ledgers')->onDelete('cascade');
            $table->foreignId('reporter_id')->constrained('users')->onDelete('cascade');
            $table->string('status')->default('in_progress');
            $table->timestamp('completed_at')->nullable();
            $table->decimal('overall_score', 5, 2)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('form_report_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('report_id')->constrained('form_reports')->onDelete('cascade');
            $table->foreignId('template_field_id')->constrained('form_template_fields')->onDelete('cascade');
            $table->text('value')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('form_report_answers');
        Schema::dropIfExists('form_reports');
        Schema::dropIfExists('form_template_fields');
        Schema::dropIfExists('form_templates');
    }
};

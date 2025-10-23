<div class="rounded-md border border-slate-200 bg-white/70 p-6 text-sm text-slate-700 shadow-sm dark:border-slate-800 dark:bg-slate-900/60 dark:text-slate-200">
    <p class="mb-4">
        {{ __('برای پیکربندی جمینای، از صفحهٔ اختصاصی زیر استفاده کنید. تمام تنظیمات پایتون غیرفعال شده‌اند و تنها Google Gemini پشتیبانی می‌شود.') }}
    </p>
    <a
        href="{{ route('admin.settings.smart-invoice.index') }}"
        class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:ring-offset-1"
    >
        <i class="fas fa-magic ml-1"></i>
        {{ __('ورود به تنظیمات جمینای') }}
    </a>
</div>

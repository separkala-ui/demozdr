<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class LocalizeHorizon
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        // Set locale to Persian for Horizon
        app()->setLocale('fa');
        
        $response = $next($request);
        
        // Add Persian translations and navigation bar via injection
        if ($response->getStatusCode() === 200 && 
            str_contains($response->getContent(), 'Laravel Horizon')) {
            
            $content = $response->getContent();
            
            // Navigation Bar HTML
            $navBar = <<<'HTML'
<div class="bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 px-4 py-3" style="position: sticky; top: 0; z-index: 1000;">
    <div class="flex items-center justify-between max-w-7xl mx-auto">
        <div class="flex items-center gap-4">
            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">پنل‌های موجود:</span>
            <a href="/admin" class="inline-flex items-center gap-2 px-3 py-1.5 text-sm rounded-md text-gray-600 hover:bg-gray-100 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                داشبورد اصلی
            </a>
            <a href="/filament/form-templates" class="inline-flex items-center gap-2 px-3 py-1.5 text-sm rounded-md text-gray-600 hover:bg-gray-100 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                فرم‌ساز
            </a>
            <div class="inline-flex items-center gap-2 px-3 py-1.5 text-sm bg-purple-100 text-purple-700 rounded-md font-medium">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                Horizon (فعلی) ✓
            </div>
        </div>
    </div>
</div>
HTML;
            
            // Inject navigation bar after body tag
            $content = preg_replace('/<body([^>]*)>/', '<body$1>' . $navBar, $content);
            
            // Inject Persian translations
            $persianScript = <<<'JS'
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Translate page title
    document.title = 'هورایزن - داشبورد مدیریت صف';
    
    // Translation map
    const translations = {
        'Dashboard': 'داشبورد',
        'Monitoring': 'نظارت',
        'Metrics': 'معیارها',
        'Batches': 'دسته‌ها',
        'Pending Jobs': 'کارهای در انتظار',
        'Completed Jobs': 'کارهای تکمیل شده',
        'Silenced Jobs': 'کارهای خاموش',
        'Failed Jobs': 'کارهای ناموفق',
        'Jobs Per Minute': 'کار در دقیقه',
        'Jobs Past Hour': 'کارهای ساعت گذشته',
        'Failed Jobs Past 7 Days': 'کارهای ناموفق 7 روز گذشته',
        'Status': 'وضعیت',
        'Inactive': 'غیرفعال',
        'Active': 'فعال',
        'Total Processes': 'مجموع پردازش‌ها',
        'Max Wait Time': 'حداکثر زمان انتظار',
        'Max Runtime': 'حداکثر زمان اجرا',
        'Max Throughput': 'حداکثر توان عملیاتی',
        'Overview': 'نمای کلی',
        'Recent Jobs': 'کارهای اخیر',
        'Retry': 'تلاش مجدد',
        'Delete': 'حذف',
        'Refresh': 'بروزرسانی'
    };
    
    // Apply translations
    function translatePage() {
        document.querySelectorAll('a, button, h1, h2, h3, h4, h5, h6, span, th, td, label').forEach(el => {
            const text = el.textContent.trim();
            if (translations[text]) {
                el.textContent = el.textContent.replace(text, translations[text]);
            }
        });
    }
    
    translatePage();
    
    // Re-translate after any dynamic updates
    const observer = new MutationObserver(translatePage);
    observer.observe(document.body, { childList: true, subtree: true });
});
</script>
JS;
            
            $content = str_replace('</head>', $persianScript . '</head>', $content);
            $response->setContent($content);
        }
        
        return $response;
    }
}


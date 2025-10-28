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
        
        // Add Persian translations via JavaScript injection
        if ($response->getStatusCode() === 200 && 
            str_contains($response->getContent(), 'Laravel Horizon')) {
            
            $content = $response->getContent();
            
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


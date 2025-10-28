<x-layouts.backend-layout>
    <div class="max-w-2xl mx-auto">
        <x-card>
            <div class="text-center py-12">
                <div class="mb-6">
                    <svg class="w-16 h-16 text-green-500 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                
                <h1 class="text-3xl font-bold text-green-600 mb-2">فرم با موفقیت ثبت شد</h1>
                <p class="text-gray-600 mb-6">شماره پیگیری: #{{ $report->id }}</p>

                <div class="bg-gray-50 p-4 rounded-lg mb-6 text-left">
                    <p class="text-sm text-gray-700">
                        <strong>فرم:</strong> {{ $report->template->title }}<br>
                        <strong>تاریخ:</strong> {{ jdate($report->completed_at)->format('Y-m-d H:i') }}<br>
                        <strong>وضعیت:</strong> 
                        <span class="badge badge-success">ثبت‌شده</span>
                    </p>
                </div>

                <div class="flex gap-4 justify-center">
                    <a href="{{ route('admin.forms.preview', $report->template) }}" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                        دانلود نسخه PDF
                    </a>
                    <a href="{{ route('admin.dashboard') }}" class="px-6 py-2 bg-gray-400 text-white rounded-lg hover:bg-gray-500">
                        بازگشت به داشبورد
                    </a>
                </div>
            </div>
        </x-card>
    </div>
</x-layouts.backend-layout>

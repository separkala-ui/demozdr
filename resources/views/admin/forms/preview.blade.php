<x-layouts.backend-layout>
    <div class="max-w-3xl mx-auto">
        <x-card>
            <x-slot name="header">
                <h1 class="text-2xl font-bold">پیش‌نمایش: {{ $template->title }}</h1>
            </x-slot>

            @if($template->description)
                <div class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                    <p class="text-gray-700">{{ $template->description }}</p>
                </div>
            @endif

            <div class="space-y-6 mb-6">
                @foreach($fields as $field)
                    <div class="border-b pb-4">
                        <p class="font-medium text-gray-900">{{ $field->label }}</p>
                        <p class="text-sm text-gray-500">نوع: {{ $field->type }} 
                            @if($field->required)
                                <span class="text-red-500">(الزامی)</span>
                            @endif
                        </p>
                        @if($field->options)
                            <p class="text-sm text-gray-600 mt-2">
                                <strong>گزینه‌ها:</strong> {{ str_replace("\n", "، ", trim($field->options)) }}
                            </p>
                        @endif
                    </div>
                @endforeach
            </div>

            <div class="flex justify-between pt-6 border-t">
                <a href="{{ route('admin.form-templates.index') }}" class="px-4 py-2 bg-gray-400 text-white rounded-lg hover:bg-gray-500">
                    بازگشت
                </a>
                <a href="{{ route('admin.forms.fill', $template) }}" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                    پر کردن فرم
                </a>
            </div>
        </x-card>
    </div>
</x-layouts.backend-layout>

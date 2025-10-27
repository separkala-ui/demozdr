<x-layouts.backend-layout>
    <div class="max-w-3xl mx-auto">
        <x-card>
            <x-slot name="header">
                <h1 class="text-2xl font-bold">{{ $template->title }}</h1>
            </x-slot>

            @if($template->description)
                <div class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                    <p class="text-gray-700">{{ $template->description }}</p>
                </div>
            @endif

            <form action="{{ route('forms.submit', $template) }}" method="POST" class="space-y-6">
                @csrf

                @foreach($fields as $field)
                    <div class="form-group">
                        <label for="field_{{ $field->id }}" class="block text-sm font-medium text-gray-700 mb-2">
                            {{ $field->label }}
                            @if($field->required)
                                <span class="text-red-500">*</span>
                            @endif
                        </label>

                        @if($field->type === 'text')
                            <input 
                                type="text" 
                                name="field_{{ $field->id }}" 
                                id="field_{{ $field->id }}"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                @if($field->required) required @endif
                            >

                        @elseif($field->type === 'number')
                            <input 
                                type="number" 
                                name="field_{{ $field->id }}" 
                                id="field_{{ $field->id }}"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                @if($field->required) required @endif
                            >

                        @elseif($field->type === 'date')
                            <input 
                                type="date" 
                                name="field_{{ $field->id }}" 
                                id="field_{{ $field->id }}"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                @if($field->required) required @endif
                            >

                        @elseif($field->type === 'select')
                            <select 
                                name="field_{{ $field->id }}" 
                                id="field_{{ $field->id }}"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                @if($field->required) required @endif
                            >
                                <option value="">انتخاب کنید...</option>
                                @foreach(explode("\n", $field->options) as $option)
                                    <option value="{{ trim($option) }}">{{ trim($option) }}</option>
                                @endforeach
                            </select>

                        @elseif($field->type === 'textarea')
                            <textarea 
                                name="field_{{ $field->id }}" 
                                id="field_{{ $field->id }}"
                                rows="4"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                @if($field->required) required @endif
                            ></textarea>

                        @elseif($field->type === 'checkbox')
                            <div class="flex items-center">
                                <input 
                                    type="checkbox" 
                                    name="field_{{ $field->id }}" 
                                    id="field_{{ $field->id }}"
                                    value="1"
                                    class="w-4 h-4 text-blue-600 rounded focus:ring-2 focus:ring-blue-500"
                                >
                                <label for="field_{{ $field->id }}" class="mr-2 text-gray-700">
                                    تایید می‌کنم
                                </label>
                            </div>

                        @elseif($field->type === 'file')
                            <input 
                                type="file" 
                                name="field_{{ $field->id }}" 
                                id="field_{{ $field->id }}"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                @if($field->required) required @endif
                            >
                        @endif

                        @error("field_{{ $field->id }}")
                            <span class="text-red-500 text-sm mt-1">{{ $message }}</span>
                        @enderror
                    </div>
                @endforeach

                <div class="flex justify-between pt-6 border-t">
                    <a href="{{ route('forms.preview', $template) }}" class="px-4 py-2 bg-gray-400 text-white rounded-lg hover:bg-gray-500">
                        پیش‌نمایش
                    </a>
                    <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                        ثبت فرم
                    </button>
                </div>
            </form>
        </x-card>
    </div>
</x-layouts.backend-layout>

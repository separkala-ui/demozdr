@extends('backend.layouts.app')

@section('title', 'تنظیمات هوشمند فاکتور')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-brain me-2"></i>
                        تنظیمات هوشمند فاکتور
                    </h3>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.settings.smart-invoice.update') }}">
                        @csrf
                        @method('PUT')

                        <!-- Service Selection -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label for="primary_service" class="form-label">سرویس اصلی</label>
                                <select name="primary_service" id="primary_service" class="form-select">
                                    <option value="python" {{ old('primary_service', config('smart-invoice.primary_service')) == 'python' ? 'selected' : '' }}>
                                        Python OCR Service
                                    </option>
                                    <option value="gemini" {{ old('primary_service', config('smart-invoice.primary_service')) == 'gemini' ? 'selected' : '' }}>
                                        Google Gemini AI
                                    </option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="fallback_service" class="form-label">سرویس پشتیبان</label>
                                <select name="fallback_service" id="fallback_service" class="form-select">
                                    <option value="gemini" {{ old('fallback_service', config('smart-invoice.fallback_service')) == 'gemini' ? 'selected' : '' }}>
                                        Google Gemini AI
                                    </option>
                                    <option value="python" {{ old('fallback_service', config('smart-invoice.fallback_service')) == 'python' ? 'selected' : '' }}>
                                        Python OCR Service
                                    </option>
                                </select>
                            </div>
                        </div>

                        <!-- Python Service Settings -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="card-title">
                                    <i class="fab fa-python me-2"></i>
                                    تنظیمات سرویس Python
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <label for="python_endpoint" class="form-label">آدرس سرویس</label>
                                        <input type="url" name="python_endpoint" id="python_endpoint" 
                                               class="form-control" 
                                               value="{{ old('python_endpoint', config('smart-invoice.endpoint')) }}"
                                               placeholder="http://your-python-server:8000">
                                    </div>
                                    <div class="col-md-6">
                                        <label for="python_timeout" class="form-label">مدت انتظار (ثانیه)</label>
                                        <input type="number" name="python_timeout" id="python_timeout" 
                                               class="form-control" 
                                               value="{{ old('python_timeout', config('smart-invoice.timeout')) }}"
                                               min="10" max="120">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Gemini Service Settings -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="card-title">
                                    <i class="fab fa-google me-2"></i>
                                    تنظیمات Google Gemini
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-check form-switch mb-3">
                                            <input class="form-check-input" type="checkbox" name="gemini_enabled" id="gemini_enabled" 
                                                   {{ old('gemini_enabled', config('smart-invoice.gemini.enabled')) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="gemini_enabled">
                                                فعال‌سازی Gemini
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="gemini_model" class="form-label">مدل Gemini</label>
                                        <select name="gemini_model" id="gemini_model" class="form-select">
                                            <option value="gemini-1.5-flash" {{ old('gemini_model', config('smart-invoice.gemini.model')) == 'gemini-1.5-flash' ? 'selected' : '' }}>
                                                Gemini 1.5 Flash
                                            </option>
                                            <option value="gemini-1.5-pro" {{ old('gemini_model', config('smart-invoice.gemini.model')) == 'gemini-1.5-pro' ? 'selected' : '' }}>
                                                Gemini 1.5 Pro
                                            </option>
                                        </select>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <label for="gemini_api_key" class="form-label">کلید API</label>
                                        <input type="password" name="gemini_api_key" id="gemini_api_key" 
                                               class="form-control" 
                                               value="{{ old('gemini_api_key', config('smart-invoice.gemini.api_key')) }}"
                                               placeholder="AIza...">
                                        <div class="form-text">
                                            <a href="https://makersuite.google.com/app/apikey" target="_blank">
                                                دریافت کلید API از Google AI Studio
                                            </a>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="gemini_timeout" class="form-label">مدت انتظار (ثانیه)</label>
                                        <input type="number" name="gemini_timeout" id="gemini_timeout" 
                                               class="form-control" 
                                               value="{{ old('gemini_timeout', config('smart-invoice.gemini.timeout')) }}"
                                               min="10" max="60">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Advanced Settings -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="card-title">
                                    <i class="fas fa-cog me-2"></i>
                                    تنظیمات پیشرفته
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <label for="confidence_threshold" class="form-label">حداقل اعتماد</label>
                                        <input type="number" name="confidence_threshold" id="confidence_threshold" 
                                               class="form-control" 
                                               value="{{ old('confidence_threshold', config('smart-invoice.confidence_threshold')) }}"
                                               min="0" max="1" step="0.1">
                                        <div class="form-text">مقدار اعتماد بین 0.0 تا 1.0</div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" name="analytics_enabled" id="analytics_enabled" 
                                                   {{ old('analytics_enabled', config('smart-invoice.analytics')) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="analytics_enabled">
                                                فعال‌سازی آنالیتیکس
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Service Status -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="card-title">
                                    <i class="fas fa-info-circle me-2"></i>
                                    وضعیت سرویس‌ها
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="d-flex align-items-center">
                                            <span class="badge bg-{{ config('smart-invoice.endpoint') ? 'success' : 'danger' }} me-2">
                                                {{ config('smart-invoice.endpoint') ? 'فعال' : 'غیرفعال' }}
                                            </span>
                                            <span>Python OCR Service</span>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="d-flex align-items-center">
                                            <span class="badge bg-{{ config('smart-invoice.gemini.api_key') ? 'success' : 'danger' }} me-2">
                                                {{ config('smart-invoice.gemini.api_key') ? 'فعال' : 'غیرفعال' }}
                                            </span>
                                            <span>Google Gemini AI</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('admin.settings.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-right me-2"></i>
                                بازگشت
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>
                                ذخیره تنظیمات
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Toggle Gemini settings based on enabled status
    const geminiEnabled = document.getElementById('gemini_enabled');
    const geminiSettings = document.querySelectorAll('#gemini_api_key, #gemini_model, #gemini_timeout');
    
    function toggleGeminiSettings() {
        geminiSettings.forEach(setting => {
            setting.disabled = !geminiEnabled.checked;
        });
    }
    
    geminiEnabled.addEventListener('change', toggleGeminiSettings);
    toggleGeminiSettings();
});
</script>
@endpush



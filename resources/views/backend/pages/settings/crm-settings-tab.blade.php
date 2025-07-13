@php
    $crmEnabled = class_exists('\Modules\Crm\Providers\CrmServiceProvider');
@endphp

@if ($crmEnabled)
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <h3 class="text-base font-medium text-gray-800 dark:text-white/90">
                {{ __('CRM Module Settings') }}
            </h3>
            <a href="{{ route('admin.crm.settings.index') }}" class="text-sm text-primary hover:text-primary-600 dark:text-primary-400">
                {{ __('Manage CRM Settings') }} →
            </a>
        </div>
        
        <p class="text-sm text-gray-500 dark:text-gray-400">
            {{ __('Configure your CRM module settings including pipelines, deals, contacts, and more.') }}
        </p>
    </div>
@else
    <div class="p-4 rounded-lg bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-700/30">
        <div class="flex">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-yellow-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M8.485 2.495c.673-1.167 2.357-1.167 3.03 0l6.28 10.875c.673 1.167-.17 2.625-1.516 2.625H3.72c-1.347 0-2.189-1.458-1.515-2.625L8.485 2.495zM10 5a.75.75 0 01.75.75v4.5a.75.75 0 01-1.5 0v-4.5A.75.75 0 0110 5zm0 10a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd" />
                </svg>
            </div>
            <div class="ml-3">
                <h3 class="text-sm font-medium text-yellow-800 dark:text-yellow-200">{{ __('CRM Module Not Installed') }}</h3>
                <div class="mt-2 text-sm text-yellow-700 dark:text-yellow-300">
                    <p>{{ __('The CRM module is not currently installed. Please install and enable the CRM module to access these settings.') }}</p>
                </div>
                <div class="mt-4">
                    <div class="-mx-2 -my-1.5 flex">
                        <a href="{{ route('admin.modules.index') }}" class="rounded-md bg-yellow-50 dark:bg-yellow-900/30 px-2 py-1.5 text-sm font-medium text-yellow-800 dark:text-yellow-300 hover:bg-yellow-100 dark:hover:bg-yellow-900/50 focus:outline-none focus:ring-2 focus:ring-yellow-600 dark:focus:ring-yellow-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800">
                            {{ __('Go to Modules') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif
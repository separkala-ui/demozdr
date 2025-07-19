@extends('backend.auth.layouts.app')

@section('title')
    {{ __('Reset Password') }} | {{ config('app.name') }}
@endsection

@section('admin-content')
<div>
    <div class="mb-5 sm:mb-8">
        <h1 class="mb-2 font-semibold text-gray-700 text-title-sm dark:text-white/90 sm:text-title-md">
            {{ __('Reset Password') }}
        </h1>
    </div>
    <div>
        <form action="{{ route('admin.password.reset.submit') }}" method="POST">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">
            <div class="space-y-5">
                <x-messages />
                <!-- Email -->
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        {{ __('E-Mail Address') }}<span class="text-error-500">*</span>
                    </label>
                    <input autofocus type="text" id="email" name="email" autocomplete="username"
                        value="{{ $email ?? old('email') }}" placeholder="Enter your email address"
                        class="dark:bg-dark-900 h-11 w-full rounded-md border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-700 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800
                        @error('email') is-invalid @enderror">
                </div>
                
                <x-inputs.password 
                    name="password" 
                    label="{{ __('Password') }}<span class='text-error-500'>*</span>"
                    placeholder="{{ __('Enter your password') }}"
                    required="true" />

                <x-inputs.password 
                    name="password_confirmation" 
                    label="{{ __('Confirm Password') }}<span class='text-error-500'>*</span>"
                    placeholder="{{ __('Confirm your password') }}"
                    required="true" />
                    
                <!-- Button -->
                <div>
                    <button type="submit"
                        class="flex items-center justify-center w-full px-4 py-3 text-sm font-medium text-white transition rounded-md bg-brand-500 shadow-theme-xs hover:bg-brand-600">
                        Reset Password
                        <iconify-icon icon="lucide:log-in" class="ml-2"></iconify-icon>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
                        class="flex items-center justify-center w-full px-4 py-3 text-sm font-medium text-white transition rounded-md bg-brand-500 shadow-theme-xs hover:bg-brand-600">
                        Reset Password
                        <iconify-icon icon="lucide:log-in" class="ml-2"></iconify-icon>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

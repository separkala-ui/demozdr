@extends('backend.layouts.app')

@section('title')
    {{ $breadcrumbs['title'] }} | {{ config('app.name') }}
@endsection

@section('admin-content')
    <div class="p-4 mx-auto max-w-7xl md:p-6">
        <x-breadcrumbs :breadcrumbs="$breadcrumbs" />

        {!! ld_apply_filters('profile_edit_breadcrumbs', '') !!}

        <div class="space-y-6">
            <div class="rounded-md border border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900">
                <div class="px-5 py-2.5 sm:px-6 sm:py-5">
                    <h3 class="text-base font-medium text-gray-700 dark:text-white">{{ __('Edit Profile') }} -
                        {{ $user->name }}</h3>
                </div>
                <div class="p-5 space-y-6 border-t border-gray-100 dark:border-gray-800 sm:p-6">
                    <x-messages />
                    <form action="{{ route('profile.update') }}" method="POST" class="space-y-6">
                        @method('PUT')
                        @csrf
                        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                            <div class="space-y-1">
                                <label for="name"
                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Name') }}</label>
                                <input type="text" name="name" id="name" required value="{{ $user->name }}"
                                    class="form-control">
                            </div>
                            <div class="space-y-1">
                                <label for="email"
                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Email') }}</label>
                                <input type="email" name="email" id="email" required value="{{ $user->email }}"
                                    class="form-control">
                            </div>
                            <x-inputs.password name="password" label="{{ __('Password (Optional)') }}" />
                            <x-inputs.password name="password_confirmation" label="{{ __('Confirm Password (Optional)') }}" />
                            {!! ld_apply_filters('profile_edit_fields', '', $user) !!}
                        </div>
                        {!! ld_apply_filters('profile_edit_after_fields', '', $user) !!}

                        <x-buttons.submit-buttons cancelUrl="{{ route('admin.dashboard') }}" />
                        {!! ld_apply_filters('profile_edit_fields', '', $user) !!}
                    </div>
                    {!! ld_apply_filters('profile_edit_after_fields', '', $user) !!}
                </form>
            </div>
        </div>
    </div>
@endsection
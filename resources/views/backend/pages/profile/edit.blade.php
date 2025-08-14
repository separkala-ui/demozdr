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
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                        {{ __("General Information") }}</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        {{ __('Update your account profile information and email address.') }}</p>
                </div>
                <div class="p-5 space-y-6 border-t border-gray-100 dark:border-gray-800 sm:p-6">
                    <form action="{{ route('profile.update') }}" method="POST" class="space-y-6" enctype="multipart/form-data">
                        @method('PUT')
                        @csrf
                        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                            <div class="space-y-1">
                                <label for="first_name"
                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('First Name') }}</label>
                                <input type="text" name="first_name" id="first_name" required value="{{ $user->first_name }}"
                                    placeholder="{{ __('Enter First Name') }}" class="form-control">
                            </div>
                            <div class="space-y-1">
                                <label for="last_name"
                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Last Name') }}</label>
                                <input type="text" name="last_name" id="last_name" required value="{{ $user->last_name }}"
                                    placeholder="{{ __('Enter Last Name') }}" class="form-control">
                            </div>
                            <div class="space-y-1">
                                <label for="email"
                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Email') }}</label>
                                <input type="email" name="email" id="email" required value="{{ $user->email }}"
                                    class="form-control">
                            </div>
                            <div>
                                <x-media-selector
                                    name="avatar_id"
                                    label="{{ __('Avatar') }}"
                                    :multiple="false"
                                    allowedTypes="images"
                                    :existingMedia="$user->avatar ? [['id' => $user->avatar->id, 'url' => $user->avatar->getUrl(), 'name' => $user->avatar->name]] : null"
                                    :required="false"
                                    height="150px"
                                />
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

            <!-- Additional Metadata Section -->
            <div class="rounded-md border border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900">
                <div class="px-5 py-2.5 sm:px-6 sm:py-5">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                        {{ __('Additional Information') }}</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        {{ __('Additional profile settings and preferences.') }}</p>
                </div>
                <div class="p-5 space-y-6 border-t border-gray-100 dark:border-gray-800 sm:p-6">
                    <form action="{{ route('profile.update.additional') }}" method="POST" class="space-y-6">
                        @method('PUT')
                        @csrf

                        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                            <div class="space-y-1">
                                <label for="display_name"
                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Display Name') }}</label>
                                <input type="text" name="display_name" id="display_name"
                                    value="{{ $userMeta['display_name'] ?? '' }}"
                                    placeholder="{{ __('Enter Display Name') }}" class="form-control">
                            </div>

                            <div class="space-y-1 sm:col-span-2">
                                <label for="bio"
                                       class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Bio') }}</label>
                                <textarea name="bio" id="bio" rows="4"
                                          placeholder="{{ __('Tell us about yourself...') }}" class="form-control">{{ $userMeta['bio'] ?? '' }}</textarea>
                            </div>

                            <x-searchable-select
                                name="timezone"
                                label="{{ __('Timezone') }}"
                                placeholder="{{ __('Select Timezone') }}"
                                searchPlaceholder="{{ __('Search timezones...') }}"
                                :options="$timezones"
                                :selected="$userMeta['timezone'] ?? ''"
                            />

                            <x-searchable-select
                                name="locale"
                                label="{{ __('Locale') }}"
                                placeholder="{{ __('Select Locale') }}"
                                searchPlaceholder="{{ __('Search locales...') }}"
                                :options="$locales"
                                :selected="$userMeta['locale'] ?? ''"
                            />
                        </div>

                        <x-buttons.submit-buttons cancelUrl="{{ route('admin.dashboard') }}" />
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

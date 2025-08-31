<x-layouts.backend-layout :breadcrumbs="$breadcrumbs">
    <div class="rounded-md border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="p-5 space-y-6 border-t border-gray-100 dark:border-gray-800 sm:p-6">
            <form
                action="{{ route('admin.users.store') }}"
                method="POST"
                enctype="multipart/form-data"
                data-prevent-unsaved-changes
            >
                @csrf

                @include('backend.pages.users.partials.form', [
                    'user' => null,
                    'roles' => $roles,
                    'timezones' => $timezones ?? [],
                    'locales' => $locales ?? [],
                    'userMeta' => [],
                    'mode' => 'create',
                    'showUsername' => true,
                    'showRoles' => true,
                    'showAdditional' => false,
                    'showImage' => false,
                ])
            </form>
        </div>
    </div>
</x-layouts.backend-layout>

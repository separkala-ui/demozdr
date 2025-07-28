<div class="bg-white rounded-lg p-6 flex flex-col items-center justify-center text-center">

    <iconify-icon icon="bi:megaphone" class="text-gray-300 dark:text-gray-600 text-6xl mb-4"></iconify-icon>
    <div class="font-semibold crm:text-lg text-gray-800 mb-1">
        {{ $title ?? __('No Datas found') }}
    </div>
    <div class="text-gray-500 mb-4">
        {{ $description ?? __('Get started by creating your first email campaign.') }}
    </div>
    @if (!empty($actionUrl) && $actionUrl !=='')
        <a href="{{ $actionUrl }}"
            class="btn btn-primary">
            <iconify-icon icon="feather:plus" class="crm:mr-2 crm:mt-1"></iconify-icon>
            {{ $actionLabel ?? __('Create') }}
        </a>
    @elseif (!empty($action))
        {{ $action }}
    @endif
</div>

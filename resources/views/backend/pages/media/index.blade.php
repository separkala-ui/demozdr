@extends('backend.layouts.app')

@section('title')
    {{ $breadcrumbs['title'] }} | {{ config('app.name') }}
@endsection

@section('admin-content')
<div class="p-4 mx-auto max-w-(--breakpoint-2xl) md:p-6" x-data="{
    selectedMedia: [],
    selectAll: false,
    bulkDeleteModalOpen: false,
    viewMode: localStorage.getItem('mediaViewMode') || 'grid',
    uploadModalOpen: false,
    
    toggleViewMode() {
        this.viewMode = this.viewMode === 'grid' ? 'list' : 'grid';
        localStorage.setItem('mediaViewMode', this.viewMode);
    }
}" id="mediaManager">
    <x-breadcrumbs :breadcrumbs="$breadcrumbs" />

    {!! ld_apply_filters('media_after_breadcrumbs', '') !!}

    <!-- Statistics Cards -->
    <div class="grid grid-cols-2 gap-4 mb-6 md:grid-cols-5 md:gap-6">
        <div class="p-4 bg-white rounded-md border border-gray-200 dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="flex items-center">
                <iconify-icon icon="lucide:files" class="text-2xl text-blue-500 mr-3"></iconify-icon>
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-300">{{ __('Total Files') }}</p>
                    <p class="text-lg font-semibold text-gray-700 dark:text-white">{{ $stats['total'] }}</p>
                </div>
            </div>
        </div>
        
        <div class="p-4 bg-white rounded-md border border-gray-200 dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="flex items-center">
                <iconify-icon icon="lucide:image" class="text-2xl text-green-500 mr-3"></iconify-icon>
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-300">{{ __('Images') }}</p>
                    <p class="text-lg font-semibold text-gray-700 dark:text-white">{{ $stats['images'] }}</p>
                </div>
            </div>
        </div>
        
        <div class="p-4 bg-white rounded-md border border-gray-200 dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="flex items-center">
                <iconify-icon icon="lucide:video" class="text-2xl text-purple-500 mr-3"></iconify-icon>
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-300">{{ __('Videos') }}</p>
                    <p class="text-lg font-semibold text-gray-700 dark:text-white">{{ $stats['videos'] }}</p>
                </div>
            </div>
        </div>

        <div class="p-4 bg-white rounded-md border border-gray-200 dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="flex items-center">
                <iconify-icon icon="lucide:file-text" class="text-2xl text-orange-500 mr-3"></iconify-icon>
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-300">{{ __('Documents') }}</p>
                    <p class="text-lg font-semibold text-gray-700 dark:text-white">{{ $stats['documents'] }}</p>
                </div>
            </div>
        </div>

        <div class="p-4 bg-white rounded-md border border-gray-200 dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="flex items-center">
                <iconify-icon icon="lucide:hard-drive" class="text-2xl text-red-500 mr-3"></iconify-icon>
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-300">{{ __('Storage Used') }}</p>
                    <p class="text-lg font-semibold text-gray-700 dark:text-white">{{ $stats['total_size'] }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="space-y-6">
        <div class="rounded-md border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="px-5 py-4 sm:px-6 sm:py-5 flex flex-col md:flex-row justify-between items-center gap-3">
                @include('backend.partials.search-form', [
                    'placeholder' => __('Search media files...'),
                ])

                <div class="flex items-center gap-3">
                    <!-- Bulk Actions dropdown -->
                    <div class="flex items-center justify-center" x-show="selectedMedia.length > 0">
                        <button id="bulkActionsButton" data-dropdown-toggle="bulkActionsDropdown" class="btn-secondary flex items-center justify-center gap-2 text-sm" type="button">
                            <iconify-icon icon="lucide:more-vertical"></iconify-icon>
                            <span>{{ __('Bulk Actions') }} (<span x-text="selectedMedia.length"></span>)</span>
                            <iconify-icon icon="lucide:chevron-down"></iconify-icon>
                        </button>

                        <!-- Bulk Actions dropdown menu -->
                        <div id="bulkActionsDropdown" class="z-10 hidden w-48 p-2 bg-white rounded-md shadow dark:bg-gray-700">
                            <ul class="space-y-2">
                                <li class="cursor-pointer flex items-center gap-1 text-sm text-red-600 dark:text-red-500 hover:bg-red-50 dark:hover:bg-red-500 dark:hover:text-red-50 px-2 py-1.5 rounded transition-colors duration-300"
                                    @click="bulkDeleteModalOpen = true">
                                    <iconify-icon icon="lucide:trash"></iconify-icon> {{ __('Delete Selected') }}
                                </li>
                            </ul>
                        </div>
                    </div>

                    <!-- Type Filter dropdown -->
                    <div class="flex items-center justify-center">
                        <button id="typeDropdownButton" data-dropdown-toggle="typeDropdown" class="btn-secondary flex items-center justify-center gap-2 text-sm" type="button">
                            <iconify-icon icon="lucide:filter"></iconify-icon>
                            <span class="hidden sm:inline">{{ __('Type') }}</span>
                            @if(request('type'))
                                <span class="px-2 py-0.5 text-xs bg-blue-100 text-blue-800 rounded-full dark:bg-blue-900/20 dark:text-primary">
                                    {{ ucfirst(request('type')) }}
                                </span>
                            @endif
                            <iconify-icon icon="lucide:chevron-down"></iconify-icon>
                        </button>

                        <!-- Type dropdown menu -->
                        <div id="typeDropdown" class="z-10 hidden w-48 p-2 bg-white rounded-md shadow dark:bg-gray-700">
                            <ul class="space-y-2">
                                <li class="cursor-pointer text-sm text-gray-700 dark:text-white hover:bg-gray-200 dark:hover:bg-gray-600 px-2 py-1.5 rounded {{ !request('type') ? 'bg-gray-200 dark:bg-gray-600' : '' }}"
                                    onclick="window.location.href='{{ route('admin.media.index', array_merge(request()->query(), ['type' => null])) }}'">
                                    {{ __('All Types') }}
                                </li>
                                <li class="cursor-pointer text-sm text-gray-700 dark:text-white hover:bg-gray-200 dark:hover:bg-gray-600 px-2 py-1.5 rounded {{ request('type') === 'images' ? 'bg-gray-200 dark:bg-gray-600' : '' }}"
                                    onclick="window.location.href='{{ route('admin.media.index', array_merge(request()->query(), ['type' => 'images'])) }}'">
                                    {{ __('Images') }}
                                </li>
                                <li class="cursor-pointer text-sm text-gray-700 dark:text-white hover:bg-gray-200 dark:hover:bg-gray-600 px-2 py-1.5 rounded {{ request('type') === 'videos' ? 'bg-gray-200 dark:bg-gray-600' : '' }}"
                                    onclick="window.location.href='{{ route('admin.media.index', array_merge(request()->query(), ['type' => 'videos'])) }}'">
                                    {{ __('Videos') }}
                                </li>
                                <li class="cursor-pointer text-sm text-gray-700 dark:text-white hover:bg-gray-200 dark:hover:bg-gray-600 px-2 py-1.5 rounded {{ request('type') === 'documents' ? 'bg-gray-200 dark:bg-gray-600' : '' }}"
                                    onclick="window.location.href='{{ route('admin.media.index', array_merge(request()->query(), ['type' => 'documents'])) }}'">
                                    {{ __('Documents') }}
                                </li>
                            </ul>
                        </div>
                    </div>

                    <!-- View Mode Toggle -->
                    <button @click="toggleViewMode()" class="btn-secondary flex items-center gap-2">
                        <iconify-icon :icon="viewMode === 'grid' ? 'lucide:list' : 'lucide:grid-3x3'" class="text-sm"></iconify-icon>
                        <span class="hidden sm:inline" x-text="viewMode === 'grid' ? '{{ __('List View') }}' : '{{ __('Grid View') }}'"></span>
                    </button>

                    @if (auth()->user()->can('media.upload'))
                    <button @click="uploadModalOpen = true" class="btn-primary flex items-center gap-2">
                        <iconify-icon icon="lucide:upload" height="16"></iconify-icon>
                        {{ __('Upload Media') }}
                    </button>
                    @endif
                </div>
            </div>

            <div class="border-t border-gray-100 dark:border-gray-800">
                <!-- Grid View -->
                <div x-show="viewMode === 'grid'" class="p-5 sm:p-6">
                    @if($media->count() > 0)
                        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4">
                            @foreach($media as $item)
                                <div class="relative group border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden hover:shadow-md transition-shadow duration-200 bg-white dark:bg-gray-800">
                                    <div class="absolute top-2 left-2 z-10">
                                        <input type="checkbox" value="{{ $item->id }}" x-model="selectedMedia"
                                               class="form-checkbox h-4 w-4 text-primary border-gray-300 rounded focus:ring-primary dark:focus:ring-primary dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                    </div>

                                    <div class="aspect-square">
                                        @if(str_starts_with($item->mime_type, 'image/'))
                                            <img src="{{ asset('storage/media/' . $item->file_name) }}"
                                                 alt="{{ $item->name }}"
                                                 class="w-full h-full object-cover"
                                                 loading="lazy">
                                        @elseif(str_starts_with($item->mime_type, 'video/'))
                                            <div class="w-full h-full bg-gradient-to-br from-purple-100 to-purple-200 dark:from-purple-900 dark:to-purple-800 flex items-center justify-center">
                                                <div class="text-center">
                                                    <iconify-icon icon="lucide:video" class="text-4xl text-purple-600 dark:text-purple-300 mb-2"></iconify-icon>
                                                    <p class="text-xs text-purple-600 dark:text-purple-300 font-medium">Video</p>
                                                </div>
                                            </div>
                                        @elseif(str_starts_with($item->mime_type, 'application/pdf'))
                                            <div class="w-full h-full bg-gradient-to-br from-red-100 to-red-200 dark:from-red-900 dark:to-red-800 flex items-center justify-center">
                                                <div class="text-center">
                                                    <iconify-icon icon="lucide:file-text" class="text-4xl text-red-600 dark:text-red-300 mb-2"></iconify-icon>
                                                    <p class="text-xs text-red-600 dark:text-red-300 font-medium">PDF</p>
                                                </div>
                                            </div>
                                        @else
                                            <div class="w-full h-full bg-gradient-to-br from-gray-100 to-gray-200 dark:from-gray-700 dark:to-gray-600 flex items-center justify-center">
                                                <div class="text-center">
                                                    <iconify-icon icon="lucide:file" class="text-4xl text-gray-600 dark:text-gray-300 mb-2"></iconify-icon>
                                                    <p class="text-xs text-gray-600 dark:text-gray-300 font-medium">{{ strtoupper(pathinfo($item->file_name, PATHINFO_EXTENSION)) }}</p>
                                                </div>
                                            </div>
                                        @endif
                                    </div>

                                    <div class="p-3 border-t border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800">
                                        <p class="text-xs font-medium text-gray-700 dark:text-white truncate" title="{{ $item->name }}">
                                            {{ $item->name }}
                                        </p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                            {{ strtoupper(pathinfo($item->file_name, PATHINFO_EXTENSION)) }} • {{ \Illuminate\Support\Str::limit($item->human_readable_size, 10) }}
                                        </p>
                                    </div>

                                    <!-- Actions overlay -->
                                    <div class="absolute inset-0 bg-black bg-opacity-50 opacity-0 group-hover:opacity-100 transition-opacity duration-200 flex items-center justify-center gap-2">
                                        @if(str_starts_with($item->mime_type, 'image/'))
                                        <button class="p-2 bg-white rounded-full text-gray-700 hover:bg-gray-100 transition-colors"
                                                onclick="openImageModal('{{ asset('storage/media/' . $item->file_name) }}', '{{ $item->name }}')"
                                                title="{{ __('View') }}">
                                            <iconify-icon icon="lucide:eye" class="text-sm"></iconify-icon>
                                        </button>
                                        @endif
                                        <button class="p-2 bg-white rounded-full text-gray-700 hover:bg-gray-100 transition-colors"
                                                onclick="copyToClipboard('{{ asset('storage/media/' . $item->file_name) }}')"
                                                title="{{ __('Copy URL') }}">
                                            <iconify-icon icon="lucide:copy" class="text-sm"></iconify-icon>
                                        </button>
                                        @if (auth()->user()->can('media.delete'))
                                        <button class="p-2 bg-red-500 rounded-full text-white hover:bg-red-600 transition-colors"
                                                onclick="deleteMedia({{ $item->id }})"
                                                title="{{ __('Delete') }}">
                                            <iconify-icon icon="lucide:trash" class="text-sm"></iconify-icon>
                                        </button>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-12">
                            <iconify-icon icon="lucide:image" class="text-6xl text-gray-300 dark:text-gray-600 mb-4 mx-auto"></iconify-icon>
                            <p class="text-gray-500 dark:text-gray-400 mb-4">{{ __('No media files found') }}</p>
                            @if (auth()->user()->can('media.upload'))
                            <button @click="uploadModalOpen = true" class="btn-primary inline-flex items-center gap-2">
                                <iconify-icon icon="lucide:upload" height="16"></iconify-icon>
                                {{ __('Upload Your First Media File') }}
                            </button>
                            @endif
                        </div>
                    @endif
                </div>

                <!-- List View -->
                <div x-show="viewMode === 'list'" class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 dark:bg-gray-800">
                            <tr class="border-b border-gray-100 dark:border-gray-800">
                                <th width="5%" class="p-2 bg-gray-50 dark:bg-gray-800 dark:text-white text-left px-5 sm:px-6">
                                    <input type="checkbox" x-model="selectAll"
                                           @click="selectAll = !selectAll; selectedMedia = selectAll ? [...document.querySelectorAll('.media-checkbox')].map(cb => cb.value) : [];"
                                           class="form-checkbox h-4 w-4 text-primary border-gray-300 rounded focus:ring-primary dark:focus:ring-primary dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                </th>
                                <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    {{ __('File') }}
                                </th>
                                <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    {{ __('Type') }}
                                </th>
                                <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    {{ __('Size') }}
                                </th>
                                <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    {{ __('Date') }}
                                </th>
                                <th class="px-5 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    {{ __('Actions') }}
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse($media as $item)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-800">
                                    <td class="px-5 py-4 sm:px-6">
                                        <input type="checkbox" value="{{ $item->id }}" x-model="selectedMedia"
                                               class="media-checkbox form-checkbox h-4 w-4 text-primary border-gray-300 rounded focus:ring-primary dark:focus:ring-primary dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                    </td>
                                    <td class="px-5 py-4 flex items-center">
                                        <div class="flex-shrink-0 h-12 w-12 mr-3">
                                            @if(str_starts_with($item->mime_type, 'image/'))
                                                <img src="{{ asset('storage/media/' . $item->file_name) }}"
                                                     alt="{{ $item->name }}" 
                                                     class="h-12 w-12 object-cover rounded border border-gray-200 dark:border-gray-700"
                                                     loading="lazy">
                                            @else
                                                <div class="h-12 w-12 bg-gray-200 dark:bg-gray-700 rounded flex items-center justify-center border border-gray-200 dark:border-gray-700">
                                                    @if(str_starts_with($item->mime_type, 'video/'))
                                                        <iconify-icon icon="lucide:video" class="text-purple-400"></iconify-icon>
                                                    @elseif(str_starts_with($item->mime_type, 'application/pdf'))
                                                        <iconify-icon icon="lucide:file-text" class="text-red-400"></iconify-icon>
                                                    @else
                                                        <iconify-icon icon="lucide:file" class="text-gray-400"></iconify-icon>
                                                    @endif
                                                </div>
                                            @endif
                                        </div>
                                        <div>
                                            <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $item->name }}</p>
                                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ $item->file_name }}</p>
                                        </div>
                                    </td>
                                    <td class="px-5 py-4 text-sm text-gray-900 dark:text-white">
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300">
                                            {{ strtoupper(pathinfo($item->file_name, PATHINFO_EXTENSION)) }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-4 text-sm text-gray-900 dark:text-white">
                                        {{ $item->human_readable_size }}
                                    </td>
                                    <td class="px-5 py-4 text-sm text-gray-900 dark:text-white">
                                        {{ $item->created_at->format('M d, Y') }}
                                    </td>
                                    <td class="px-5 py-4 text-center">
                                        <div class="flex items-center justify-center gap-2">
                                            <button class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300"
                                                    onclick="copyToClipboard('{{ $item->getUrl() }}')"
                                                    title="{{ __('Copy URL') }}">
                                                <iconify-icon icon="lucide:copy" class="text-sm"></iconify-icon>
                                            </button>
                                            @if (auth()->user()->can('media.delete'))
                                            <button class="text-red-400 hover:text-red-600"
                                                    onclick="deleteMedia({{ $item->id }})"
                                                    title="{{ __('Delete') }}">
                                                <iconify-icon icon="lucide:trash" class="text-sm"></iconify-icon>
                                            </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-5 py-8 text-center text-gray-500 dark:text-gray-400">
                                        <div class="flex flex-col items-center">
                                            <iconify-icon icon="lucide:image" class="text-4xl mb-2"></iconify-icon>
                                            <p class="mb-4">{{ __('No media files found') }}</p>
                                            @if (auth()->user()->can('media.create'))
                                            <button @click="uploadModalOpen = true" class="btn-primary inline-flex items-center gap-2">
                                                <iconify-icon icon="lucide:upload" height="16"></iconify-icon>
                                                {{ __('Upload Media') }}
                                            </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="px-5 py-4 border-t border-gray-100 dark:border-gray-800">
                    {{ $media->links() }}
                </div>
            </div>
        </div>
    </div>

    <!-- Upload Modal -->
    @include('backend.pages.media.partials.upload-modal')

    <!-- Bulk Delete Modal -->
    @include('backend.pages.media.partials.bulk-delete-modal')
</div>

<!-- Image Modal -->
<div id="imageModal" class="fixed inset-0 z-50 hidden bg-black bg-opacity-75 flex items-center justify-center" onclick="closeImageModal()">
    <div class="max-w-4xl max-h-[90vh] p-4">
        <img id="modalImage" src="" alt="" class="max-w-full max-h-full object-contain">
    </div>
    <button onclick="closeImageModal()" class="absolute top-4 right-4 text-white hover:text-gray-300">
        <iconify-icon icon="lucide:x" class="text-2xl"></iconify-icon>
    </button>
</div>

@push('scripts')
<script>
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(() => {
        // Show success message
        if (window.toast) {
            window.toast('{{ __("URL copied to clipboard") }}', 'success');
        }
    });
}

function deleteMedia(id) {
    if (confirm('{{ __("Are you sure you want to delete this media file?") }}')) {
        fetch(`/admin/media/${id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
                'Accept': 'application/json',
            },
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert(data.message || '{{ __("Error deleting media file") }}');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('{{ __("Error deleting media file") }}');
        });
    }
}

function openImageModal(src, alt) {
    const modal = document.getElementById('imageModal');
    const img = document.getElementById('modalImage');
    img.src = src;
    img.alt = alt;
    modal.classList.remove('hidden');
}

function closeImageModal() {
    const modal = document.getElementById('imageModal');
    modal.classList.add('hidden');
}

// Close modal on escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeImageModal();
    }
});
</script>
@endpush
@endsection

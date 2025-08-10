@props([
    'id' => 'mediaModal',
    'title' => __('Select Media'),
    'multiple' => false,
    'allowedTypes' => 'all', // 'all', 'images', 'videos', 'documents'
    'onSelect' => null,
    'buttonText' => __('Select Media'),
    'buttonClass' => 'btn-primary'
])

<!-- Media Modal Button -->
<button
    type="button"
    class="{{ $buttonClass }}"
    onclick="openMediaModal('{{ $id }}', {{ $multiple ? 'true' : 'false' }}, '{{ $allowedTypes }}', {{ $onSelect ? "'{$onSelect}'" : 'null' }})"
>
    <iconify-icon icon="lucide:image" class="mr-2"></iconify-icon>
    {{ $buttonText }}
</button>

<!-- Media Modal -->
<div id="{{ $id }}" class="fixed inset-0 z-50 hidden bg-black/20 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white/95 dark:bg-gray-800/95 backdrop-blur-md rounded-lg shadow-2xl border border-white/20 dark:border-gray-700/50 max-w-7xl w-full h-[90vh] flex flex-col">
        <!-- Modal Header -->
        <div class="flex items-center justify-between p-6 border-b border-gray-200 dark:border-gray-700">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ $title }}</h3>
            <button
                type="button"
                onclick="closeMediaModal('{{ $id }}')"
                class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300"
            >
                <iconify-icon icon="lucide:x" class="text-xl"></iconify-icon>
            </button>
        </div>

        <!-- Modal Content -->
        <div class="flex-1 flex overflow-hidden">
            <!-- Left Sidebar - Filters -->
            <div class="w-64 bg-gray-50 dark:bg-gray-900 border-r border-gray-200 dark:border-gray-700 p-4">
                <!-- Upload Section -->
                <div class="mb-6">
                    <button
                        type="button"
                        onclick="triggerFileUpload('{{ $id }}')"
                        class="w-full btn-primary flex items-center justify-center gap-2"
                    >
                        <iconify-icon icon="lucide:upload"></iconify-icon>
                        Upload Files
                    </button>
                    <input
                        type="file"
                        id="{{ $id }}_fileInput"
                        class="hidden"
                        {{ $multiple ? 'multiple' : '' }}
                        accept="{{ $allowedTypes === 'images' ? 'image/*' : ($allowedTypes === 'videos' ? 'video/*' : ($allowedTypes === 'documents' ? '.pdf,.doc,.docx,.txt' : '*')) }}"
                        onchange="handleFileUpload(event, '{{ $id }}')"
                    >
                </div>

                <!-- Filter Section -->
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Filter by Type</label>
                        <select
                            id="{{ $id }}_typeFilter"
                            class="form-control w-full"
                            onchange="filterMediaByType('{{ $id }}', this.value)"
                        >
                            <option value="all">All Files</option>
                            <option value="images">Images</option>
                            <option value="videos">Videos</option>
                            <option value="documents">Documents</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Search</label>
                        <input
                            type="text"
                            id="{{ $id }}_searchInput"
                            class="form-control w-full"
                            placeholder="Search files..."
                            oninput="searchMedia('{{ $id }}', this.value)"
                        >
                    </div>
                </div>

                <!-- Selected Files Count -->
                <div id="{{ $id }}_selectedInfo" class="mt-6 p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg hidden">
                    <p class="text-sm text-blue-700 dark:text-blue-300">
                        <span id="{{ $id }}_selectedCount">0</span> file(s) selected
                    </p>
                </div>
            </div>

            <!-- Main Content Area -->
            <div class="flex-1 flex flex-col">
                <!-- Loading State -->
                <div id="{{ $id }}_loading" class="flex-1 flex items-center justify-center">
                    <div class="text-center">
                        <iconify-icon icon="lucide:loader-2" class="text-4xl text-gray-400 animate-spin mb-4"></iconify-icon>
                        <p class="text-gray-500 dark:text-gray-400">Loading media files...</p>
                    </div>
                </div>

                <!-- Media Grid -->
                <div id="{{ $id }}_mediaGrid" class="flex-1 p-6 overflow-y-auto hidden">
                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4" id="{{ $id }}_mediaContainer">
                        <!-- Media items will be loaded here -->
                    </div>
                    
                    <!-- Load More Button -->
                    <div id="{{ $id }}_loadMoreSection" class="flex justify-center mt-6 hidden">
                        <button
                            type="button"
                            id="{{ $id }}_loadMoreButton"
                            onclick="loadMoreMedia('{{ $id }}')"
                            class="btn-default px-6 py-2 flex items-center gap-2"
                        >
                            <iconify-icon icon="lucide:chevron-down" class="text-lg"></iconify-icon>
                            Load More (100 items)
                        </button>
                    </div>
                </div>

                <!-- Empty State -->
                <div id="{{ $id }}_emptyState" class="flex-1 flex items-center justify-center hidden">
                    <div class="text-center">
                        <iconify-icon icon="lucide:image" class="text-6xl text-gray-300 dark:text-gray-600 mb-4"></iconify-icon>
                        <p class="text-gray-500 dark:text-gray-400 mb-4">No media files found</p>
                        <button
                            type="button"
                            onclick="triggerFileUpload('{{ $id }}')"
                            class="btn-primary"
                        >
                            Upload Your First File
                        </button>
                    </div>
                </div>
            </div>

            <!-- Right Sidebar - Media Details -->
            <div class="w-64 bg-white dark:bg-gray-800 border-l border-gray-200 dark:border-gray-700 flex flex-col">
                <!-- No Selection State -->
                <div id="{{ $id }}_noSelection" class="flex-1 flex items-center justify-center p-6">
                    <div class="text-center">
                        <iconify-icon icon="lucide:mouse-pointer-click" class="text-4xl text-gray-300 dark:text-gray-600 mb-3"></iconify-icon>
                        <p class="text-gray-500 dark:text-gray-400 text-sm">Select a file to view details</p>
                    </div>
                </div>

                <!-- Media Details -->
                <div id="{{ $id }}_mediaDetails" class="hidden flex-1 flex flex-col">
                    <!-- Preview Area -->
                    <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                        <div id="{{ $id }}_previewContainer" class="bg-gray-50 dark:bg-gray-900 rounded-lg overflow-hidden relative group">
                            <!-- Preview content will be inserted here -->
                        </div>
                    </div>

                    <!-- File Info -->
                    <div class="flex-1 p-4 overflow-y-auto">
                        <div class="space-y-4">
                            <div>
                                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">File Name</label>
                                <p id="{{ $id }}_fileName" class="text-sm text-gray-900 dark:text-white break-all"></p>
                            </div>
                            
                            <div>
                                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">File Type</label>
                                <p id="{{ $id }}_fileType" class="text-sm text-gray-900 dark:text-white"></p>
                            </div>
                            
                            <div>
                                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">File Size</label>
                                <p id="{{ $id }}_fileSize" class="text-sm text-gray-900 dark:text-white"></p>
                            </div>
                            
                            <div id="{{ $id }}_imageDimensions" class="hidden">
                                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Dimensions</label>
                                <p id="{{ $id }}_dimensions" class="text-sm text-gray-900 dark:text-white"></p>
                            </div>
                            
                            <div>
                                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Uploaded</label>
                                <p id="{{ $id }}_uploadDate" class="text-sm text-gray-900 dark:text-white"></p>
                            </div>
                            
                            <div>
                                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">URL</label>
                                <div class="flex items-center gap-2">
                                    <input id="{{ $id }}_fileUrl" type="text" readonly class="form-control text-xs flex-1" />
                                    <button type="button" onclick="copyToClipboard('{{ $id }}_fileUrl')" class="btn-default p-2" title="{{ __('Copy URL') }}">
                                        <iconify-icon icon="lucide:copy" class="text-sm"></iconify-icon>
                                    </button>
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div class="flex gap-2 pt-2">
                                <button type="button" 
                                        id="{{ $id }}_downloadButton"
                                        class="flex-1 btn-default flex items-center justify-center gap-2 text-sm"
                                        title="{{ __('Download') }}">
                                    <iconify-icon icon="lucide:download" class="text-sm"></iconify-icon>
                                    <span>{{ __('Download') }}</span>
                                </button>
                                <button type="button" 
                                        id="{{ $id }}_fullViewButton"
                                        class="flex-1 btn-default flex items-center justify-center gap-2 text-sm hidden"
                                        title="{{ __('Full View') }}">
                                    <iconify-icon icon="lucide:maximize-2" class="text-sm"></iconify-icon>
                                    <span>{{ __('View') }}</span>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Selection Actions -->
                    <div class="p-4 border-t border-gray-200 dark:border-gray-700" x-show="false">
                        <!-- Remove the individual select button -->
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Footer -->
        <div class="flex items-center justify-between p-6 border-t border-gray-200 dark:border-gray-700">
            <div class="text-sm text-gray-500 dark:text-gray-400">
                <span id="{{ $id }}_totalFiles">0</span> files
                <span id="{{ $id }}_filterInfo" class="ml-1"></span>
                <span id="{{ $id }}_paginationInfo" class="ml-2 text-xs"></span>
            </div>
            <div class="flex gap-3">
                <button
                    type="button"
                    onclick="closeMediaModal('{{ $id }}')"
                    class="btn-default"
                >
                    Cancel
                </button>
                <button
                    type="button"
                    id="{{ $id }}_selectButton"
                    onclick="confirmMediaSelection('{{ $id }}')"
                    class="btn-primary"
                    disabled
                >
                    {{ $multiple ? __('Select Files') : __('Select') }}
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Image Modal for Full View -->
<div id="imageModal" class="fixed inset-0 z-[60] hidden bg-black bg-opacity-75"
    onclick="closeImageModal()">
    <div class="max-w-4xl max-h-[90vh] p-4">
        <img id="modalImage" src="" alt="" class="max-w-full max-h-full object-contain">
    </div>
    <button onclick="closeImageModal()" class="absolute top-4 right-4 text-white hover:text-gray-300">
        <iconify-icon icon="lucide:x" class="text-2xl"></iconify-icon>
    </button>
</div>


@push('scripts')

<script>
// Global media modal functionality
window.mediaModalData = {};

function openMediaModal(modalId, multiple = false, allowedTypes = 'all', onSelectCallback = null) {
    const modal = document.getElementById(modalId);
    if (!modal) return;

    // Initialize modal data
    window.mediaModalData[modalId] = {
        multiple: multiple,
        allowedTypes: allowedTypes,
        onSelectCallback: onSelectCallback,
        selectedFiles: [],
        allFiles: [],
        currentPage: 1,
        totalPages: 1,
        totalCount: 0,
        isLoading: false,
        hasMorePages: true,
        currentFile: null,
        currentFilters: {
            search: '',
            type: 'all'
        }
    };

    modal.classList.remove('hidden');
    loadMediaFiles(modalId, true);
}

function closeMediaModal(modalId) {
    const modal = document.getElementById(modalId);
    if (!modal) return;

    modal.classList.add('hidden');

    // Reset modal state
    if (window.mediaModalData[modalId]) {
        window.mediaModalData[modalId].selectedFiles = [];
        window.mediaModalData[modalId].allFiles = [];
        window.mediaModalData[modalId].currentPage = 1;
        window.mediaModalData[modalId].hasMorePages = true;
        window.mediaModalData[modalId].currentFile = null;
        updateSelectedInfo(modalId);
        
        // Reset details sidebar
        document.getElementById(`${modalId}_noSelection`).classList.remove('hidden');
        document.getElementById(`${modalId}_mediaDetails`).classList.add('hidden');
    }
}

async function loadMediaFiles(modalId, isInitialLoad = false) {
    const modalData = window.mediaModalData[modalId];
    const loadingEl = document.getElementById(`${modalId}_loading`);
    const gridEl = document.getElementById(`${modalId}_mediaGrid`);
    const emptyEl = document.getElementById(`${modalId}_emptyState`);

    // Prevent multiple simultaneous requests
    if (modalData.isLoading) return;
    modalData.isLoading = true;

    // Show loading state only for initial load
    if (isInitialLoad) {
        loadingEl.classList.remove('hidden');
        gridEl.classList.add('hidden');
        emptyEl.classList.add('hidden');
    }

    try {
        const params = new URLSearchParams({
            page: modalData.currentPage.toString(),
            per_page: 100,
            search: modalData.currentFilters.search,
            type: modalData.currentFilters.type === 'all' ? '' : modalData.currentFilters.type,
            sort: 'created_at',
            direction: 'desc'
        });

        const response = await fetch(`/admin/media/api?${params}`);
        const data = await response.json();

        if (data.success) {
            // Store the total count for reference first
            modalData.totalCount = data.pagination.total;
            modalData.totalPages = data.pagination.last_page;
            modalData.hasMorePages = data.pagination.has_more_pages;

            if (isInitialLoad) {
                modalData.allFiles = data.media;
                renderMediaFiles(modalId, data.media, isInitialLoad);
            } else {
                modalData.allFiles = [...modalData.allFiles, ...data.media];
                renderMediaFiles(modalId, data.media, isInitialLoad); // Pass only new files for rendering
                // Update load more button after loading more items
                updateLoadMoreButton(modalId);
            }

            updateTotalFilesCount(modalId, data.pagination.total);

            if (modalData.allFiles.length > 0) {
                if (isInitialLoad) {
                    loadingEl.classList.add('hidden');
                    gridEl.classList.remove('hidden');
                    // Show load more button if there are more pages
                    updateLoadMoreButton(modalId);
                }
            } else {
                if (isInitialLoad) {
                    loadingEl.classList.add('hidden');
                    emptyEl.classList.remove('hidden');
                }
            }
        } else {
            throw new Error(data.message || 'Failed to load media');
        }
    } catch (error) {
        console.error('Error loading media files:', error);
        if (isInitialLoad) {
            loadingEl.classList.add('hidden');
            emptyEl.classList.remove('hidden');
        }
    } finally {
        modalData.isLoading = false;
    }
}

function renderMediaFiles(modalId, files, isInitialLoad = false) {
    const container = document.getElementById(`${modalId}_mediaContainer`);
    const modalData = window.mediaModalData[modalId];

    if (isInitialLoad) {
        container.innerHTML = '';
    }

    // For both initial load and pagination, render the provided files
    files.forEach(file => {
        // Check if this file is already rendered to avoid duplicates
        if (!isInitialLoad) {
            const existingItem = container.querySelector(`[data-file-id="${file.id}"]`);
            if (existingItem) {
                console.log('File already exists, skipping:', file.id);
                return;
            }
        }

        // Filter by allowed types
        if (modalData.allowedTypes !== 'all') {
            const isAllowed = checkFileTypeAllowed(file.mime_type, modalData.allowedTypes);
            if (!isAllowed) return;
        }

        const mediaItem = createMediaItem(modalId, file);
        container.appendChild(mediaItem);
    });
    
    // Update checkbox states after rendering
    if (!isInitialLoad) {
        updateCheckboxStates(modalId);
    }
}

function createMediaItem(modalId, file) {
    const modalData = window.mediaModalData[modalId];
    const div = document.createElement('div');
    div.className = 'relative group border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden hover:shadow-md transition-all duration-200 cursor-pointer';
    div.dataset.fileId = file.id;
    div.onclick = () => selectMediaFile(modalId, file);

    const isImage = file.mime_type.startsWith('image/');
    const isVideo = file.mime_type.startsWith('video/');
    const isPdf = file.mime_type.includes('pdf');

    let thumbnailHtml = '';
    if (isImage) {
        const imgSrc = file.thumbnail_url || file.url;
        thumbnailHtml = `<img src="${imgSrc}" alt="${file.name}" class="w-full h-32 object-cover" loading="lazy">`;
    } else if (isVideo) {
        thumbnailHtml = `
            <div class="w-full h-32 bg-gradient-to-br from-purple-100 to-purple-200 dark:from-purple-900 dark:to-purple-800 flex items-center justify-center">
                <iconify-icon icon="lucide:video" class="text-3xl text-purple-600 dark:text-purple-300"></iconify-icon>
            </div>`;
    } else if (isPdf) {
        thumbnailHtml = `
            <div class="w-full h-32 bg-gradient-to-br from-red-100 to-red-200 dark:from-red-900 dark:to-red-800 flex items-center justify-center">
                <iconify-icon icon="lucide:file-text" class="text-3xl text-red-600 dark:text-red-300"></iconify-icon>
            </div>`;
    } else {
        thumbnailHtml = `
            <div class="w-full h-32 bg-gradient-to-br from-gray-100 to-gray-200 dark:from-gray-700 dark:to-gray-600 flex items-center justify-center">
                <iconify-icon icon="lucide:file" class="text-3xl text-gray-600 dark:text-gray-300"></iconify-icon>
            </div>`;
    }

    div.innerHTML = `
        ${thumbnailHtml}
        <div class="p-3 bg-white dark:bg-gray-800">
            <p class="text-xs font-medium text-gray-700 dark:text-white truncate" title="${file.name}">
                ${file.name}
            </p>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                ${file.extension?.toUpperCase() || 'FILE'} • ${file.human_readable_size || '0 KB'}
            </p>
        </div>
        ${modalData.multiple ? `
            <div class="absolute top-2 left-2">
                <input type="checkbox" 
                       class="form-checkbox media-checkbox w-4 h-4 text-blue-600 bg-white border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600" 
                       data-file-id="${file.id}"
                       onchange="toggleFileSelection(event, '${modalId}', ${file.id})"
                       onclick="event.stopPropagation()">
            </div>
        ` : ''}
    `;

    return div;
}

function selectMediaFile(modalId, file) {
    const modalData = window.mediaModalData[modalId];
    
    // Update current file and show details
    modalData.currentFile = file;
    showMediaDetails(modalId, file);
    
    // Update visual selection state
    updateMediaItemSelection(modalId);
    
    // For single selection, automatically add to selectedFiles when viewing
    if (!modalData.multiple) {
        modalData.selectedFiles = [file];
        updateSelectedInfo(modalId);
    }
}

function toggleFileSelection(event, modalId, fileId) {
    const modalData = window.mediaModalData[modalId];
    const checkbox = event.target;
    
    // Find the file object from allFiles
    const file = modalData.allFiles.find(f => f.id == fileId);
    if (!file) return;
    
    if (checkbox.checked) {
        // Add to selection if not already selected
        const isAlreadySelected = modalData.selectedFiles.some(f => f.id === file.id);
        if (!isAlreadySelected) {
            modalData.selectedFiles.push(file);
        }
    } else {
        // Remove from selection
        modalData.selectedFiles = modalData.selectedFiles.filter(f => f.id !== file.id);
    }
    
    updateSelectedInfo(modalId);
}

function updateCheckboxStates(modalId) {
    const modalData = window.mediaModalData[modalId];
    
    // Update checkbox states to match selectedFiles
    document.querySelectorAll('.media-checkbox').forEach(checkbox => {
        const fileId = checkbox.dataset.fileId;
        const isSelected = modalData.selectedFiles.some(f => f.id == fileId);
        checkbox.checked = isSelected;
    });
}

function showMediaDetails(modalId, file) {
    const noSelectionEl = document.getElementById(`${modalId}_noSelection`);
    const detailsEl = document.getElementById(`${modalId}_mediaDetails`);
    const previewContainer = document.getElementById(`${modalId}_previewContainer`);
    
    // Hide no selection state and show details
    noSelectionEl.classList.add('hidden');
    detailsEl.classList.remove('hidden');
    detailsEl.classList.add('flex', 'flex-col');
    
    // Update file information
    document.getElementById(`${modalId}_fileName`).textContent = file.name;
    document.getElementById(`${modalId}_fileType`).textContent = file.extension?.toUpperCase() || 'Unknown';
    document.getElementById(`${modalId}_fileSize`).textContent = file.human_readable_size || '0 KB';
    document.getElementById(`${modalId}_fileUrl`).value = file.url;
    
    // Update upload date
    const uploadDate = file.created_at ? new Date(file.created_at).toLocaleDateString() : 'Unknown';
    document.getElementById(`${modalId}_uploadDate`).textContent = uploadDate;
    
    // Show/hide dimensions for images
    const dimensionsEl = document.getElementById(`${modalId}_imageDimensions`);
    if (file.mime_type.startsWith('image/') && (file.width || file.height)) {
        document.getElementById(`${modalId}_dimensions`).textContent = `${file.width || 0} × ${file.height || 0} pixels`;
        dimensionsEl.classList.remove('hidden');
    } else {
        dimensionsEl.classList.add('hidden');
    }
    
    // Generate preview
    generateMediaPreview(modalId, file, previewContainer);
    
    // Setup action buttons
    setupActionButtons(modalId, file);
}

function generateMediaPreview(modalId, file, container) {
    const isImage = file.mime_type.startsWith('image/');
    const isVideo = file.mime_type.startsWith('video/');
    const isPdf = file.mime_type.includes('pdf');
    
    if (isImage) {
        container.innerHTML = `
            <img src="${file.thumbnail_url || file.url}" 
                 alt="${file.name}" 
                 class="w-full h-48 object-contain bg-gray-100 dark:bg-gray-800 cursor-pointer"
                 loading="lazy"
                 onclick="openImageModal('${file.url}', '${file.name}')">
            <div class="absolute inset-0 bg-black/0 group-hover:bg-black/10 transition-colors duration-200 flex items-center justify-center opacity-0 group-hover:opacity-100">
                <button onclick="openImageModal('${file.url}', '${file.name}')" 
                        class="p-2 bg-white/90 backdrop-blur-sm rounded-md text-gray-700 hover:bg-white transition-colors shadow-lg"
                        title="View Full Size">
                    <iconify-icon icon="lucide:maximize-2" class="text-lg"></iconify-icon>
                </button>
            </div>
        `;
    } else if (isVideo) {
        container.innerHTML = `
            <div class="w-full h-48 bg-gradient-to-br from-purple-100 to-purple-200 dark:from-purple-900 dark:to-purple-800 flex items-center justify-center">
                <iconify-icon icon="lucide:video" class="text-6xl text-purple-600 dark:text-purple-300"></iconify-icon>
            </div>
        `;
    } else if (isPdf) {
        container.innerHTML = `
            <div class="w-full h-48 bg-gradient-to-br from-red-100 to-red-200 dark:from-red-900 dark:to-red-800 flex items-center justify-center">
                <iconify-icon icon="lucide:file-text" class="text-6xl text-red-600 dark:text-red-300"></iconify-icon>
            </div>
        `;
    } else {
        container.innerHTML = `
            <div class="w-full h-48 bg-gradient-to-br from-gray-100 to-gray-200 dark:from-gray-700 dark:to-gray-600 flex items-center justify-center">
                <iconify-icon icon="lucide:file" class="text-6xl text-gray-600 dark:text-gray-300"></iconify-icon>
            </div>
        `;
    }
}

function setupActionButtons(modalId, file) {
    const downloadButton = document.getElementById(`${modalId}_downloadButton`);
    const fullViewButton = document.getElementById(`${modalId}_fullViewButton`);
    const isImage = file.mime_type.startsWith('image/');
    
    // Setup download button
    downloadButton.onclick = () => {
        const link = document.createElement('a');
        link.href = file.url;
        link.download = file.name;
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    };
    
    // Setup full view button (only for images)
    if (isImage) {
        fullViewButton.classList.remove('hidden');
        fullViewButton.onclick = () => openImageModal(file.url, file.name);
    } else {
        fullViewButton.classList.add('hidden');
    }
}

function updateMediaItemSelection(modalId) {
    const modalData = window.mediaModalData[modalId];
    
    // Remove previous active states
    document.querySelectorAll(`[data-file-id]`).forEach(item => {
        item.classList.remove('ring-2', 'ring-blue-500', 'bg-blue-50', 'dark:bg-blue-900/20');
    });
    
    // Add active state to current file
    if (modalData.currentFile) {
        const activeItem = document.querySelector(`[data-file-id="${modalData.currentFile.id}"]`);
        if (activeItem) {
            activeItem.classList.add('ring-2', 'ring-blue-500', 'bg-blue-50', 'dark:bg-blue-900/20');
        }
    }
}

function copyToClipboard(inputId) {
    const input = document.getElementById(inputId);
    if (input) {
        input.select();
        document.execCommand('copy');
        
        // Show feedback
        if (window.showToast) {
            window.showToast('success', 'Copied', 'URL copied to clipboard');
        }
    }
}

function updateSelectedInfo(modalId) {
    const modalData = window.mediaModalData[modalId];
    const selectedInfo = document.getElementById(`${modalId}_selectedInfo`);
    const selectedCount = document.getElementById(`${modalId}_selectedCount`);
    const selectButton = document.getElementById(`${modalId}_selectButton`);

    const count = modalData.selectedFiles.length;

    if (count > 0) {
        selectedInfo.classList.remove('hidden');
        selectedCount.textContent = count;
        selectButton.disabled = false;
    } else {
        selectedInfo.classList.add('hidden');
        selectButton.disabled = true;
    }
}

function updateTotalFilesCount(modalId, count) {
    const totalFilesEl = document.getElementById(`${modalId}_totalFiles`);
    const filterInfoEl = document.getElementById(`${modalId}_filterInfo`);
    const paginationInfoEl = document.getElementById(`${modalId}_paginationInfo`);
    const modalData = window.mediaModalData[modalId];

    if (totalFilesEl) {
        totalFilesEl.textContent = count;
    }

    if (filterInfoEl && modalData) {
        const hasSearch = modalData.currentFilters.search.length > 0;
        const hasTypeFilter = modalData.currentFilters.type !== 'all';

        if (hasSearch || hasTypeFilter) {
            let filterText = 'matching';
            if (hasSearch && hasTypeFilter) {
                filterText += ` "${modalData.currentFilters.search}" in ${modalData.currentFilters.type}`;
            } else if (hasSearch) {
                filterText += ` "${modalData.currentFilters.search}"`;
            } else if (hasTypeFilter) {
                filterText += ` ${modalData.currentFilters.type}`;
            }
            filterInfoEl.textContent = filterText;
        } else {
            filterInfoEl.textContent = 'available';
        }
    }

    if (paginationInfoEl && modalData) {
        const loadedCount = modalData.allFiles.length;
        if (modalData.hasMorePages && loadedCount < count) {
            paginationInfoEl.textContent = `• ${loadedCount} loaded of ${count} total`;
        } else if (loadedCount >= count && count > 100) {
            paginationInfoEl.textContent = '• all loaded';
        } else {
            paginationInfoEl.textContent = '';
        }
    }
}

function confirmMediaSelection(modalId) {
    const modalData = window.mediaModalData[modalId];

    if (modalData.selectedFiles.length === 0) return;

    // Execute callback if provided
    if (modalData.onSelectCallback && typeof window[modalData.onSelectCallback] === 'function') {
        window[modalData.onSelectCallback](modalData.selectedFiles);
    }

    // Dispatch custom event
    const event = new CustomEvent('mediaSelected', {
        detail: {
            modalId: modalId,
            files: modalData.selectedFiles,
            multiple: modalData.multiple
        }
    });
    document.dispatchEvent(event);

    closeMediaModal(modalId);
}

function loadMoreMedia(modalId) {
    console.log('loadMoreMedia', modalId);
    const modalData = window.mediaModalData[modalId];
    
    if (!modalData.hasMorePages || modalData.isLoading) return;
    
    modalData.currentPage++;
    loadMediaFiles(modalId, false);
}

function updateLoadMoreButton(modalId) {
    const modalData = window.mediaModalData[modalId];
    const loadMoreSection = document.getElementById(`${modalId}_loadMoreSection`);
    const loadMoreButton = document.getElementById(`${modalId}_loadMoreButton`);
    
    if (!loadMoreSection || !loadMoreButton) return;
    
    if (modalData.hasMorePages) {
        loadMoreSection.classList.remove('hidden');
        loadMoreButton.disabled = false;
        
        // Update button text to show remaining items
        const remainingItems = modalData.totalCount - modalData.allFiles.length;
        const itemsToLoad = Math.min(100, remainingItems);
        loadMoreButton.innerHTML = `
            <iconify-icon icon="lucide:chevron-down" class="text-lg"></iconify-icon>
            Load More (${itemsToLoad} items)
        `;
    } else {
        loadMoreSection.classList.add('hidden');
    }
}

function triggerFileUpload(modalId) {
    const fileInput = document.getElementById(`${modalId}_fileInput`);
    fileInput.click();
}

async function handleFileUpload(event, modalId) {
    const files = Array.from(event.target.files);
    if (files.length === 0) return;

    const formData = new FormData();
    files.forEach(file => {
        formData.append('files[]', file);
    });

    try {
        const response = await fetch('/admin/media', {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        });

        const data = await response.json();

        if (data.success) {
            // Reload media files
            loadMediaFiles(modalId, true);

            // Show success message
            if (window.showToast) {
                window.showToast('success', 'Success', 'Files uploaded successfully');
            }
        } else {
            throw new Error(data.message || 'Upload failed');
        }
    } catch (error) {
        console.error('Upload error:', error);
        if (window.showToast) {
            window.showToast('error', 'Error', 'Failed to upload files');
        }
    }

    // Reset file input
    event.target.value = '';
}

function setupInfiniteScroll(modalId) {
    const scrollContainer = document.getElementById(`${modalId}_mediaGrid`);
    const modalData = window.mediaModalData[modalId];

    // Remove any existing scroll listeners
    scrollContainer.removeEventListener('scroll', modalData.scrollHandler);

    // Create the scroll handler
    modalData.scrollHandler = function() {
        const scrollTop = this.scrollTop;
        const scrollHeight = this.scrollHeight;
        const clientHeight = this.clientHeight;

        console.log('Scroll detected:', { scrollTop, scrollHeight, clientHeight, nearBottom: scrollTop + clientHeight >= scrollHeight - 100 });

        // Check if we're near the bottom (within 100px)
        if (scrollTop + clientHeight >= scrollHeight - 100) {
            console.log('Near bottom! hasMorePages:', modalData.hasMorePages, 'isLoading:', modalData.isLoading);
            if (modalData.hasMorePages && !modalData.isLoading) {
                console.log('Loading next page:', modalData.currentPage + 1);
                modalData.currentPage++;
                loadMediaFiles(modalId, false);
            }
        }
    };

    scrollContainer.addEventListener('scroll', modalData.scrollHandler);
}

function showLoadingIndicator(modalId) {
    const container = document.getElementById(`${modalId}_mediaContainer`);
    let loadingIndicator = document.getElementById(`${modalId}_loadingMore`);

    if (!loadingIndicator) {
        loadingIndicator = document.createElement('div');
        loadingIndicator.id = `${modalId}_loadingMore`;
        loadingIndicator.className = 'col-span-full flex items-center justify-center py-8';
        loadingIndicator.innerHTML = `
            <div class="text-center">
                <iconify-icon icon="lucide:loader-2" class="text-2xl text-gray-400 animate-spin mb-2"></iconify-icon>
                <p class="text-sm text-gray-500 dark:text-gray-400">Loading more files...</p>
            </div>
        `;
        container.appendChild(loadingIndicator);
    }

    loadingIndicator.classList.remove('hidden');
}

function hideLoadingIndicator(modalId) {
    const loadingIndicator = document.getElementById(`${modalId}_loadingMore`);
    if (loadingIndicator) {
        loadingIndicator.classList.add('hidden');
    }
}

function filterMediaByType(modalId, type) {
    const modalData = window.mediaModalData[modalId];
    modalData.currentFilters.type = type;
    modalData.currentPage = 1;
    modalData.hasMorePages = true;
    modalData.allFiles = [];

    loadMediaFiles(modalId, true);
}

function searchMedia(modalId, searchTerm) {
    const modalData = window.mediaModalData[modalId];
    modalData.currentFilters.search = searchTerm.trim();
    modalData.currentPage = 1;
    modalData.hasMorePages = true;
    modalData.allFiles = [];

    // Debounce the search to avoid too many API calls
    clearTimeout(modalData.searchTimeout);
    modalData.searchTimeout = setTimeout(() => {
        loadMediaFiles(modalId, true);
    }, 300);
}

function checkFileTypeAllowed(mimeType, allowedType) {
    switch (allowedType) {
        case 'images':
            return mimeType.startsWith('image/');
        case 'videos':
            return mimeType.startsWith('video/');
        case 'documents':
            return mimeType.includes('pdf') ||
                   mimeType.includes('document') ||
                   mimeType.includes('text') ||
                   mimeType.includes('msword') ||
                   mimeType.includes('officedocument');
        default:
            return true;
    }
}

// Image modal functions
function openImageModal(src, alt) {
    const modal = document.getElementById('imageModal');
    const img = document.getElementById('modalImage');
    img.src = src;
    img.alt = alt;
    modal.classList.remove('hidden');
    modal.classList.add('flex', 'items-center', 'justify-center');
}

function closeImageModal() {
    const modal = document.getElementById('imageModal');
    modal.classList.add('hidden');
    modal.classList.remove('flex', 'items-center', 'justify-center');
}

// Close modal on escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        // Close image modal first if open
        const imageModal = document.getElementById('imageModal');
        if (imageModal && !imageModal.classList.contains('hidden')) {
            closeImageModal();
            return;
        }
        
        // Then close media modals
        const openModals = document.querySelectorAll('[id$="Modal"]:not(.hidden)');
        openModals.forEach(modal => {
            if (modal.id.includes('media') || modal.id.includes('Media')) {
                closeMediaModal(modal.id);
            }
        });
    }
});
</script>
@endpush

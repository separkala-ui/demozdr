@props([
    'editorId' => 'editor',
    'height' => '100px',
    'maxHeight' => '500px',
    'type' => 'full', // Options: 'full', 'basic', 'minimal'
    'customToolbar' => null, // For custom toolbar configuration
])

@once
<link rel="stylesheet" href="{{ asset('vendor/quill/quill.min.css') }}" />
<style>
    .ql-editor {
        min-height: {{ $height }};
        max-height: {{ $maxHeight }};
        overflow-y: auto;
    }
    .ql-toolbar.ql-snow {
        border-radius: 10px 10px 0px 0px;
        margin-bottom: 0px;
    }
    .ql-container {
        height: {{ $height }};
    }
    /* Create a container for Quill to target */
    .quill-container {
        border: 1px solid #ccc;
        border-radius: 0 0 10px 10px;
        background: transparent;
    }
    .dark .quill-container {
        border-color: #4b5563;
        color: #e5e7eb;
    }
    .dark .ql-snow {
        border-color: #4b5563;
    }
    .dark .ql-toolbar.ql-snow .ql-picker-label,
    .dark .ql-toolbar.ql-snow .ql-picker-options,
    .dark .ql-toolbar.ql-snow button,
    .dark .ql-toolbar.ql-snow span {
        color: #e5e7eb;
    }
    .dark .ql-snow .ql-stroke {
        stroke: #e5e7eb;
    }
    .dark .ql-snow .ql-fill {
        fill: #e5e7eb;
    }
    .dark .ql-editor.ql-blank::before {
        color: rgba(255, 255, 255, 0.6);
    }

    /* Alternative using iconify icon */
    .ql-toolbar .ql-media-modal {
        width: 28px;
        height: 28px;
    }
    
    .ql-toolbar .ql-media-modal:after {
        content: '';
        background-image: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21,15 16,10 5,21"/></svg>');
        background-size: 18px;
        background-repeat: no-repeat;
        background-position: center;
        width: 100%;
        height: 100%;
        display: block;
    }
</style>

<script src="{{ asset('vendor/quill/quill.min.js') }}"></script>
@endonce

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const editorId = '{{ $editorId }}';
        const editorType = '{{ $type }}';
        const textareaElement = document.getElementById(editorId);
        const customToolbar = @json($customToolbar);

        if (!textareaElement) {
            console.error(`Textarea with ID "${editorId}" not found`);
            return;
        }

        // Create a div after the textarea to host Quill
        const quillContainer = document.createElement('div');
        quillContainer.id = `quill-${editorId}`;
        quillContainer.className = 'quill-container';
        textareaElement.insertAdjacentElement('afterend', quillContainer);

        // Store original textarea content
        const initialContent = textareaElement.value || '';

        // Define toolbar configurations based on type (removed default image handler)
        const toolbarConfigs = {
            full: [
                ['bold', 'italic', 'underline', 'strike'],
                [{ 'header': [1, 2, 3, 4, 5, 6, false] }],
                ['blockquote'],
                [{ 'align': [] }],
                [{ 'list': 'ordered' }, { 'list': 'bullet' }],
                [{ 'indent': '-1' }, { 'indent': '+1' }],
                [{ 'color': [] }, { 'background': [] }],
                [{ 'font': [] }],
                ['link', 'media-modal', 'video', 'code-block']
            ],
            basic: [
                ['bold', 'italic', 'underline'],
                [{ 'header': [1, 2, 3, false] }],
                [{ 'list': 'ordered' }, { 'list': 'bullet' }],
                ['link', 'media-modal']
            ],
            minimal: [
                ['bold', 'italic'],
                [{ 'list': 'ordered' }, { 'list': 'bullet' }]
            ]
        };

        // Select toolbar configuration based on type or use custom if provided
        const toolbarConfig = customToolbar ? JSON.parse(customToolbar) :
                             (toolbarConfigs[editorType] || toolbarConfigs.basic);

        // Custom media modal handler
        const mediaModalHandler = function() {
            const modalId = `quillMediaModal_${editorId}`;
            
            // Create media modal if it doesn't exist
            if (!document.getElementById(modalId)) {
                const modalHtml = createMediaModalForQuill(modalId, editorId);
                document.body.insertAdjacentHTML('beforeend', modalHtml);
            }
            
            // Open media modal
            openMediaModal(modalId, false, 'all', `handleQuillMediaSelect_${editorId}`);
        };

        // Initialize Quill on the container div
        const quill = new Quill(`#quill-${editorId}`, {
            theme: "snow",
            placeholder: '{{ __('Type here...') }}',
            modules: {
                toolbar: {
                    container: toolbarConfig,
                    handlers: {
                        'media-modal': mediaModalHandler
                    }
                }
            }
        });

        window['quill_' + editorId] = quill;

        // Create media selection handler function for this specific editor
        window[`handleQuillMediaSelect_${editorId}`] = function(files) {
            if (files.length > 0) {
                const file = files[0];
                const range = quill.getSelection(true);
                
                if (file.mime_type && file.mime_type.startsWith('image/')) {
                    // Insert image
                    quill.insertEmbed(range.index, 'image', file.url, 'user');
                } else {
                    // Insert as link for non-image files
                    quill.insertText(range.index, file.name, 'link', file.url, 'user');
                }
                
                // Move cursor after inserted content
                quill.setSelection(range.index + 1);
            }
        };

        // Helper function to create media modal HTML for Quill
        window.createMediaModalForQuill = function(modalId, editorId) {
            return `
                <div id="${modalId}" class="fixed inset-0 z-50 hidden bg-black bg-opacity-50 flex items-center justify-center p-4">
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-6xl w-full max-h-[90vh] flex flex-col">
                        <!-- Modal Header -->
                        <div class="flex items-center justify-between p-6 border-b border-gray-200 dark:border-gray-700">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Select Media</h3>
                            <button type="button" onclick="closeMediaModal('${modalId}')" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                                <iconify-icon icon="lucide:x" class="text-xl"></iconify-icon>
                            </button>
                        </div>

                        <!-- Modal Content -->
                        <div class="flex-1 flex overflow-hidden">
                            <!-- Sidebar -->
                            <div class="w-64 bg-gray-50 dark:bg-gray-900 border-r border-gray-200 dark:border-gray-700 p-4">
                                <!-- Upload Section -->
                                <div class="mb-6">
                                    <button type="button" onclick="triggerFileUpload('${modalId}')" class="w-full btn-primary flex items-center justify-center gap-2">
                                        <iconify-icon icon="lucide:upload"></iconify-icon>
                                        Upload Files
                                    </button>
                                    <input type="file" id="${modalId}_fileInput" class="hidden" multiple accept="*" onchange="handleFileUpload(event, '${modalId}')">
                                </div>

                                <!-- Filter Section -->
                                <div class="space-y-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Filter by Type</label>
                                        <select id="${modalId}_typeFilter" class="form-control w-full" onchange="filterMediaByType('${modalId}', this.value)">
                                            <option value="all">All Files</option>
                                            <option value="images">Images</option>
                                            <option value="videos">Videos</option>
                                            <option value="documents">Documents</option>
                                        </select>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Search</label>
                                        <input type="text" id="${modalId}_searchInput" class="form-control w-full" placeholder="Search files..." oninput="searchMedia('${modalId}', this.value)">
                                    </div>
                                </div>

                                <!-- Selected Files Info -->
                                <div id="${modalId}_selectedInfo" class="mt-6 p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg hidden">
                                    <p class="text-sm text-blue-700 dark:text-blue-300">
                                        <span id="${modalId}_selectedCount">0</span> file(s) selected
                                    </p>
                                </div>
                            </div>

                            <!-- Main Content Area -->
                            <div class="flex-1 flex flex-col">
                                <!-- Loading State -->
                                <div id="${modalId}_loading" class="flex-1 flex items-center justify-center">
                                    <div class="text-center">
                                        <iconify-icon icon="lucide:loader-2" class="text-4xl text-gray-400 animate-spin mb-4"></iconify-icon>
                                        <p class="text-gray-500 dark:text-gray-400">Loading media files...</p>
                                    </div>
                                </div>

                                <!-- Media Grid -->
                                <div id="${modalId}_mediaGrid" class="flex-1 p-6 overflow-y-auto hidden">
                                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4" id="${modalId}_mediaContainer">
                                        <!-- Media items will be loaded here -->
                                    </div>
                                </div>

                                <!-- Empty State -->
                                <div id="${modalId}_emptyState" class="flex-1 flex items-center justify-center hidden">
                                    <div class="text-center">
                                        <iconify-icon icon="lucide:image" class="text-6xl text-gray-300 dark:text-gray-600 mb-4"></iconify-icon>
                                        <p class="text-gray-500 dark:text-gray-400 mb-4">No media files found</p>
                                        <button type="button" onclick="triggerFileUpload('${modalId}')" class="btn-primary">
                                            Upload Your First File
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Modal Footer -->
                        <div class="flex items-center justify-between p-6 border-t border-gray-200 dark:border-gray-700">
                            <div class="text-sm text-gray-500 dark:text-gray-400">
                                <span id="${modalId}_totalFiles">0</span> files available
                            </div>
                            <div class="flex gap-3">
                                <button type="button" onclick="closeMediaModal('${modalId}')" class="btn-secondary">
                                    Cancel
                                </button>
                                <button type="button" id="${modalId}_selectButton" onclick="confirmMediaSelection('${modalId}')" class="btn-primary" disabled>
                                    Select
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        };

        // Set initial content from textarea
        if (initialContent) {
            quill.clipboard.dangerouslyPasteHTML(initialContent);
        }

        // Hide textarea visually but keep it in the DOM for form submission
        textareaElement.style.display = 'none';

        // Update textarea on editor change for form submission
        quill.on('text-change', function() {
            textareaElement.value = quill.root.innerHTML;
            
            // Trigger form change detection for the unsaved changes warning
            const event = new Event('input', { bubbles: true });
            textareaElement.dispatchEvent(event);
        });

        // Also update on form submit to ensure the latest content is captured
        const form = textareaElement.closest('form');
        if (form) {
            form.addEventListener('submit', function() {
                textareaElement.value = quill.root.innerHTML;
            });
        }

        // Include media modal functionality if not already loaded
        if (!window.mediaModalData) {
            loadMediaModalFunctions();
        }
    });

    // Load media modal functions if not already available
    function loadMediaModalFunctions() {
        // Global media modal functionality
        window.mediaModalData = {};

        window.openMediaModal = function(modalId, multiple = false, allowedTypes = 'all', onSelectCallback = null) {
            const modal = document.getElementById(modalId);
            if (!modal) return;

            // Initialize modal data
            window.mediaModalData[modalId] = {
                multiple: multiple,
                allowedTypes: allowedTypes,
                onSelectCallback: onSelectCallback,
                selectedFiles: [],
                allFiles: []
            };

            modal.classList.remove('hidden');
            loadMediaFiles(modalId);
        };

        window.closeMediaModal = function(modalId) {
            const modal = document.getElementById(modalId);
            if (!modal) return;

            modal.classList.add('hidden');
            
            // Reset modal state
            if (window.mediaModalData[modalId]) {
                window.mediaModalData[modalId].selectedFiles = [];
                updateSelectedInfo(modalId);
            }
        };

        window.loadMediaFiles = async function(modalId) {
            const loadingEl = document.getElementById(`${modalId}_loading`);
            const gridEl = document.getElementById(`${modalId}_mediaGrid`);
            const emptyEl = document.getElementById(`${modalId}_emptyState`);

            // Show loading state
            loadingEl.classList.remove('hidden');
            gridEl.classList.add('hidden');
            emptyEl.classList.add('hidden');

            try {
                const response = await fetch('/admin/media/api');
                const data = await response.json();

                if (data.success && data.media.length > 0) {
                    window.mediaModalData[modalId].allFiles = data.media;
                    renderMediaFiles(modalId, data.media);
                    updateTotalFilesCount(modalId, data.media.length);
                    
                    loadingEl.classList.add('hidden');
                    gridEl.classList.remove('hidden');
                } else {
                    loadingEl.classList.add('hidden');
                    emptyEl.classList.remove('hidden');
                }
            } catch (error) {
                console.error('Error loading media files:', error);
                loadingEl.classList.add('hidden');
                emptyEl.classList.remove('hidden');
            }
        };

        window.renderMediaFiles = function(modalId, files) {
            const container = document.getElementById(`${modalId}_mediaContainer`);
            const modalData = window.mediaModalData[modalId];
            
            container.innerHTML = '';

            files.forEach(file => {
                // Filter by allowed types
                if (modalData.allowedTypes !== 'all') {
                    const isAllowed = checkFileTypeAllowed(file.mime_type, modalData.allowedTypes);
                    if (!isAllowed) return;
                }

                const mediaItem = createMediaItem(modalId, file);
                container.appendChild(mediaItem);
            });
        };

        window.createMediaItem = function(modalId, file) {
            const div = document.createElement('div');
            div.className = 'relative group border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden hover:shadow-md transition-all duration-200 cursor-pointer';
            div.dataset.fileId = file.id;
            div.onclick = () => toggleFileSelection(modalId, file);

            const isImage = file.mime_type.startsWith('image/');
            const isVideo = file.mime_type.startsWith('video/');
            const isPdf = file.mime_type.includes('pdf');

            let thumbnailHtml = '';
            if (isImage) {
                thumbnailHtml = `<img src="${file.url}" alt="${file.name}" class="w-full h-32 object-cover" loading="lazy">`;
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
                <div class="absolute inset-0 bg-blue-500 bg-opacity-20 opacity-0 transition-opacity duration-200 flex items-center justify-center media-selected-overlay">
                    <iconify-icon icon="lucide:check" class="text-2xl text-white bg-blue-500 rounded-full p-1"></iconify-icon>
                </div>
            `;

            return div;
        };

        window.toggleFileSelection = function(modalId, file) {
            const modalData = window.mediaModalData[modalId];
            const fileElement = document.querySelector(`[data-file-id="${file.id}"]`);
            const overlay = fileElement.querySelector('.media-selected-overlay');
            
            const isSelected = modalData.selectedFiles.some(f => f.id === file.id);
            
            if (isSelected) {
                // Deselect
                modalData.selectedFiles = modalData.selectedFiles.filter(f => f.id !== file.id);
                overlay.classList.add('opacity-0');
                fileElement.classList.remove('ring-2', 'ring-blue-500');
            } else {
                // Select (single selection for Quill)
                modalData.selectedFiles.forEach(f => {
                    const prevElement = document.querySelector(`[data-file-id="${f.id}"]`);
                    if (prevElement) {
                        prevElement.querySelector('.media-selected-overlay').classList.add('opacity-0');
                        prevElement.classList.remove('ring-2', 'ring-blue-500');
                    }
                });
                modalData.selectedFiles = [file];
                
                overlay.classList.remove('opacity-0');
                fileElement.classList.add('ring-2', 'ring-blue-500');
            }
            
            updateSelectedInfo(modalId);
        };

        window.updateSelectedInfo = function(modalId) {
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
        };

        window.updateTotalFilesCount = function(modalId, count) {
            const totalFilesEl = document.getElementById(`${modalId}_totalFiles`);
            if (totalFilesEl) {
                totalFilesEl.textContent = count;
            }
        };

        window.confirmMediaSelection = function(modalId) {
            const modalData = window.mediaModalData[modalId];
            
            if (modalData.selectedFiles.length === 0) return;
            
            // Execute callback if provided
            if (modalData.onSelectCallback && typeof window[modalData.onSelectCallback] === 'function') {
                window[modalData.onSelectCallback](modalData.selectedFiles);
            }
            
            closeMediaModal(modalId);
        };

        window.triggerFileUpload = function(modalId) {
            const fileInput = document.getElementById(`${modalId}_fileInput`);
            fileInput.click();
        };

        window.handleFileUpload = async function(event, modalId) {
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
                    loadMediaFiles(modalId);
                } else {
                    throw new Error(data.message || 'Upload failed');
                }
            } catch (error) {
                console.error('Upload error:', error);
            }

            // Reset file input
            event.target.value = '';
        };

        window.filterMediaByType = function(modalId, type) {
            const modalData = window.mediaModalData[modalId];
            let filteredFiles = modalData.allFiles;

            if (type !== 'all') {
                filteredFiles = modalData.allFiles.filter(file => 
                    checkFileTypeAllowed(file.mime_type, type)
                );
            }

            renderMediaFiles(modalId, filteredFiles);
            updateTotalFilesCount(modalId, filteredFiles.length);
        };

        window.searchMedia = function(modalId, searchTerm) {
            const modalData = window.mediaModalData[modalId];
            let filteredFiles = modalData.allFiles;

            if (searchTerm.trim()) {
                filteredFiles = modalData.allFiles.filter(file => 
                    file.name.toLowerCase().includes(searchTerm.toLowerCase()) ||
                    file.file_name.toLowerCase().includes(searchTerm.toLowerCase())
                );
            }

            renderMediaFiles(modalId, filteredFiles);
            updateTotalFilesCount(modalId, filteredFiles.length);
        };

        window.checkFileTypeAllowed = function(mimeType, allowedType) {
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
        };
    }
</script>

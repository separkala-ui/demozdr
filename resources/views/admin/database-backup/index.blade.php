@extends('backend.layouts.app')

@section('title', 'مدیریت بک‌آپ دیتابیس')

@section('content')
<div class="container-fluid px-4 py-6">
    <!-- Header -->
    <div class="mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">مدیریت بک‌آپ دیتابیس</h1>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">ایجاد، دانلود و بازگردانی نسخه پشتیبان از دیتابیس</p>
            </div>
            <div class="flex gap-3">
                <button onclick="openUploadModal()" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                    </svg>
                    آپلود بک‌آپ
                </button>
                <button onclick="createBackup()" id="createBackupBtn" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    ایجاد بک‌آپ جدید
                </button>
            </div>
        </div>
    </div>

    <!-- Alert Messages -->
    <div id="alertContainer"></div>

    <!-- Backups List -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm overflow-hidden">
        <div class="p-6 border-b border-gray-200 dark:border-gray-700">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">لیست بک‌آپ‌ها</h2>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">مجموع: <span id="backupCount">{{ count($backups) }}</span> بک‌آپ</p>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full" id="backupsTable">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">نام فایل</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">حجم</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">تاریخ ایجاد</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">عملیات</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($backups as $backup)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <svg class="w-5 h-5 text-blue-500 ml-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                <span class="text-sm font-medium text-gray-900 dark:text-white">{{ $backup['filename'] }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="text-sm text-gray-600 dark:text-gray-400">{{ $backup['size_formatted'] }}</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="text-sm text-gray-600 dark:text-gray-400">{{ $backup['created_at_jalali'] }}</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            <div class="flex gap-2">
                                <a href="{{ route('admin.database-backup.download', $backup['filename']) }}" class="px-3 py-1.5 bg-blue-600 text-white rounded hover:bg-blue-700 transition-colors flex items-center gap-1" title="دانلود">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                    </svg>
                                    دانلود
                                </a>
                                <button onclick="restoreBackup('{{ $backup['filename'] }}')" class="px-3 py-1.5 bg-green-600 text-white rounded hover:bg-green-700 transition-colors flex items-center gap-1" title="بازگردانی">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                    </svg>
                                    بازگردانی
                                </button>
                                <button onclick="deleteBackup('{{ $backup['filename'] }}')" class="px-3 py-1.5 bg-red-600 text-white rounded hover:bg-red-700 transition-colors flex items-center gap-1" title="حذف">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                    حذف
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-6 py-12 text-center">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                            </svg>
                            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">هیچ بک‌آپی وجود ندارد</p>
                            <button onclick="createBackup()" class="mt-4 px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                                ایجاد اولین بک‌آپ
                            </button>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Upload Modal -->
    <div id="uploadModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
        <div class="bg-white dark:bg-gray-800 rounded-lg p-6 max-w-md w-full mx-4">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">آپلود فایل بک‌آپ</h3>
                <button onclick="closeUploadModal()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <form id="uploadForm" enctype="multipart/form-data">
                @csrf
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        انتخاب فایل SQL
                    </label>
                    <input type="file" name="backup_file" id="backupFile" accept=".sql" required
                        class="block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 dark:text-gray-400 focus:outline-none dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400">
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">فقط فایل‌های .sql با حداکثر حجم 500MB</p>
                </div>
                <div class="flex gap-3">
                    <button type="submit" class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                        آپلود
                    </button>
                    <button type="button" onclick="closeUploadModal()" class="flex-1 px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition-colors">
                        انصراف
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
// ایجاد بک‌آپ جدید
function createBackup() {
    const btn = document.getElementById('createBackupBtn');
    btn.disabled = true;
    btn.innerHTML = '<svg class="animate-spin h-5 w-5 ml-2" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> در حال ایجاد...';

    fetch('{{ route("admin.database-backup.create") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('success', data.message);
            updateBackupList(data.backups);
        } else {
            showAlert('error', data.message);
        }
    })
    .catch(error => {
        showAlert('error', 'خطا در ایجاد بک‌آپ');
        console.error(error);
    })
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg> ایجاد بک‌آپ جدید';
    });
}

// بازگردانی بک‌آپ
function restoreBackup(filename) {
    if (!confirm('⚠️ هشدار: این عملیات تمام داده‌های فعلی دیتابیس را جایگزین می‌کند!\n\nآیا مطمئن هستید؟')) {
        return;
    }

    const url = '{{ route("admin.database-backup.restore", ":filename") }}'.replace(':filename', filename);

    showAlert('info', 'در حال بازگردانی بک‌آپ...');

    fetch(url, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('success', data.message + ' - صفحه در حال بارگذاری مجدد...');
            setTimeout(() => {
                window.location.reload();
            }, 2000);
        } else {
            showAlert('error', data.message);
        }
    })
    .catch(error => {
        showAlert('error', 'خطا در بازگردانی بک‌آپ');
        console.error(error);
    });
}

// حذف بک‌آپ
function deleteBackup(filename) {
    if (!confirm('آیا از حذف این بک‌آپ مطمئن هستید؟')) {
        return;
    }

    const url = '{{ route("admin.database-backup.delete", ":filename") }}'.replace(':filename', filename);

    fetch(url, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('success', data.message);
            updateBackupList(data.backups);
        } else {
            showAlert('error', data.message);
        }
    })
    .catch(error => {
        showAlert('error', 'خطا در حذف بک‌آپ');
        console.error(error);
    });
}

// آپلود بک‌آپ
document.getElementById('uploadForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    fetch('{{ route("admin.database-backup.upload") }}', {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('success', data.message);
            updateBackupList(data.backups);
            closeUploadModal();
            document.getElementById('uploadForm').reset();
        } else {
            showAlert('error', data.message);
        }
    })
    .catch(error => {
        showAlert('error', 'خطا در آپلود فایل');
        console.error(error);
    });
});

// نمایش پیام
function showAlert(type, message) {
    const colors = {
        success: 'bg-green-100 border-green-500 text-green-700',
        error: 'bg-red-100 border-red-500 text-red-700',
        info: 'bg-blue-100 border-blue-500 text-blue-700'
    };

    const alert = document.createElement('div');
    alert.className = `${colors[type]} border-r-4 p-4 mb-4 rounded`;
    alert.innerHTML = `
        <div class="flex items-center">
            <p>${message}</p>
            <button onclick="this.parentElement.parentElement.remove()" class="mr-auto">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                </svg>
            </button>
        </div>
    `;

    document.getElementById('alertContainer').appendChild(alert);

    setTimeout(() => {
        alert.remove();
    }, 5000);
}

// به‌روزرسانی لیست بک‌آپ‌ها
function updateBackupList(backups) {
    location.reload(); // برای سادگی، صفحه را reload می‌کنیم
}

// باز کردن مودال آپلود
function openUploadModal() {
    document.getElementById('uploadModal').classList.remove('hidden');
}

// بستن مودال آپلود
function closeUploadModal() {
    document.getElementById('uploadModal').classList.add('hidden');
}
</script>
@endpush
@endsection


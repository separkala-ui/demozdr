<?php

declare(strict_types=1);

namespace App\Http\Controllers\Backend\PettyCash;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DebugInvoiceUploadController extends Controller
{
    private const STORAGE_PATH = 'petty-cash-debug';

    public function create()
    {
        Storage::makeDirectory(self::STORAGE_PATH);

        $storedFiles = collect(Storage::files(self::STORAGE_PATH))
            ->map(function (string $path) {
                return [
                    'name' => basename($path),
                    'size' => Storage::size($path),
                    'uploaded_at' => Storage::lastModified($path),
                    'path' => storage_path('app/' . $path),
                    'download_url' => route('admin.petty-cash.debug.download', ['filename' => basename($path)]),
                ];
            })
            ->sortByDesc('uploaded_at')
            ->values();

        return view('backend.pages.petty-cash.debug-upload', [
            'files' => $storedFiles,
            'storagePath' => storage_path('app/' . self::STORAGE_PATH),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'invoice_files' => ['required', 'array'],
            'invoice_files.*' => ['file', 'max:20480'], // 20MB per file
        ], [
            'invoice_files.required' => __('لطفاً حداقل یک فایل انتخاب کنید.'),
            'invoice_files.*.max' => __('حجم هر فایل نباید بیشتر از ۲۰ مگابایت باشد.'),
        ]);

        $uploadedFiles = [];

        foreach ($request->file('invoice_files', []) as $file) {
            $originalName = $file->getClientOriginalName();
            $safeName = Str::slug(pathinfo($originalName, PATHINFO_FILENAME));
            $extension = $file->getClientOriginalExtension();
            $timestamp = now()->format('Ymd_His');
            $random = Str::random(5);

            $filename = $safeName !== ''
                ? "{$timestamp}_{$random}_{$safeName}.{$extension}"
                : "{$timestamp}_{$random}.{$extension}";

            $storedPath = $file->storeAs(self::STORAGE_PATH, $filename);
            $uploadedFiles[] = storage_path('app/' . $storedPath);
        }

        return redirect()
            ->route('admin.petty-cash.debug.upload')
            ->with('success', __(':count فایل با موفقیت ذخیره شد.', ['count' => count($uploadedFiles)]))
            ->with('uploaded_paths', $uploadedFiles);
    }

    public function download(string $filename)
    {
        $path = self::STORAGE_PATH . '/' . $filename;

        if (! Storage::exists($path)) {
            abort(404);
        }

        return Storage::download($path);
    }
}

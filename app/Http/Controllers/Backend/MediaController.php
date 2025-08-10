<?php

declare(strict_types=1);

namespace App\Http\Controllers\Backend;

use App\Helper\MediaHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Backend\MediaBulkDeleteRequest;
use App\Http\Requests\Backend\MediaUploadRequest;
use App\Services\MediaLibraryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MediaController extends Controller
{
    public function __construct(private readonly MediaLibraryService $mediaLibraryService)
    {
    }

    public function index(Request $request)
    {
        $this->checkAuthorization(Auth::user(), ['media.view']);

        // Check for PHP upload limit errors first
        $phpError = MediaHelper::checkPhpUploadError();
        if ($phpError) {
            return redirect()->back()->withErrors([
                'upload_error' => $phpError['message'],
            ])->withInput();
        }

        $breadcrumbs = [
            'title' => __('Media Library'),
            'links' => [
                [
                    'name' => __('Dashboard'),
                    'url' => route('admin.dashboard'),
                ],
                [
                    'name' => __('Media Library'),
                    'url' => '#',
                ],
            ],
        ];

        $result = $this->mediaLibraryService->getMediaList(
            $request->get('search'),
            $request->get('type'),
            $request->get('sort', 'created_at'),
            $request->get('direction', 'desc'),
            50
        );

        // Get upload limits for frontend
        $uploadLimits = MediaHelper::getUploadLimits();

        return view('backend.pages.media.index', [
            'media' => $result['media'],
            'breadcrumbs' => $breadcrumbs,
            'stats' => $result['stats'],
            'uploadLimits' => $uploadLimits,
        ]);
    }

    public function store(MediaUploadRequest $request)
    {
        $this->checkAuthorization(Auth::user(), ['media.create']);

        // Double-check for PHP upload errors in case they weren't caught earlier
        $phpError = MediaHelper::checkPhpUploadError();
        if ($phpError) {
            return response()->json([
                'success' => false,
                'message' => $phpError['message'],
                'error_type' => 'php_upload_limit',
                'uploaded_size' => $phpError['uploaded_size'],
                'limit' => $phpError['limit'],
                'limit_formatted' => $phpError['limit_formatted'],
            ], 422);
        }

        try {
            $uploadedFiles = $this->mediaLibraryService->uploadMedia($request->file('files', []));

            return response()->json([
                'success' => true,
                'message' => __('Files uploaded successfully'),
                'files' => $uploadedFiles,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('Upload failed: :error', ['error' => $e->getMessage()]),
                'error_type' => 'upload_failed',
            ], 500);
        }
    }

    public function destroy($id)
    {
        $this->checkAuthorization(Auth::user(), ['media.delete']);

        $this->mediaLibraryService->deleteMedia($id);

        return response()->json([
            'success' => true,
            'message' => __('Media deleted successfully'),
        ]);
    }

    public function bulkDelete(MediaBulkDeleteRequest $request)
    {
        $this->checkAuthorization(Auth::user(), ['media.delete']);

        $this->mediaLibraryService->bulkDeleteMedia($request->ids);

        return redirect()->back()->with('success', __('Selected media deleted successfully'));
    }

    public function api(Request $request)
    {
        $this->checkAuthorization(Auth::user(), ['media.view']);

        $result = $this->mediaLibraryService->getMediaList(
            $request->get('search'),
            $request->get('type'),
            $request->get('sort', 'created_at'),
            $request->get('direction', 'desc'),
            100 // Return more items for modal
        );

        // Transform media for API response
        $mediaItems = $result['media']->map(function ($item) {
            return [
                'id' => $item->id,
                'name' => $item->name,
                'file_name' => $item->file_name,
                'mime_type' => $item->mime_type,
                'size' => $item->size,
                'human_readable_size' => $item->human_readable_size,
                'url' => asset('storage/media/' . $item->file_name),
                'extension' => pathinfo($item->file_name, PATHINFO_EXTENSION),
                'created_at' => $item->created_at->format('Y-m-d H:i:s'),
            ];
        });

        return response()->json([
            'success' => true,
            'media' => $mediaItems,
            'stats' => $result['stats'],
        ]);
    }

    /**
     * Get upload limits for frontend consumption
     */
    public function getUploadLimits()
    {
        $this->checkAuthorization(Auth::user(), ['media.view']);

        $limits = MediaHelper::getUploadLimits();

        return response()->json([
            'success' => true,
            'limits' => $limits,
        ]);
    }
}

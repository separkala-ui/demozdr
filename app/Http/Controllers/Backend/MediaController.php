<?php

declare(strict_types=1);

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Spatie\MediaLibrary\MediaCollections\Models\Media as SpatieMedia;

class MediaController extends Controller
{
    public function index(Request $request)
    {
        $this->checkAuthorization(Auth::user(), ['media.view']);

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

        $query = SpatieMedia::query()
            ->latest();

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('file_name', 'like', "%{$search}%")
                    ->orWhere('mime_type', 'like', "%{$search}%");
            });
        }

        // Filter by type
        if ($request->filled('type')) {
            $type = $request->get('type');
            switch ($type) {
                case 'images':
                    $query->where('mime_type', 'like', 'image/%');
                    break;
                case 'videos':
                    $query->where('mime_type', 'like', 'video/%');
                    break;
                case 'documents':
                    $query->whereNotIn('mime_type', function ($q) {
                        $q->select('mime_type')
                            ->from('media')
                            ->where('mime_type', 'like', 'image/%')
                            ->orWhere('mime_type', 'like', 'video/%');
                    });
                    break;
            }
        }

        // Sort functionality
        $sort = $request->get('sort', 'created_at');
        $direction = $request->get('direction', 'desc');

        if (in_array($sort, ['name', 'size', 'created_at', 'mime_type'])) {
            $query->orderBy($sort, $direction);
        }

        $media = $query->paginate(24)->withQueryString();

        // Add human readable size to each media item
        $media->getCollection()->transform(function ($item) {
            $item->human_readable_size = $this->formatBytes($item->size);
            return $item;
        });

        // Get statistics
        $stats = [
            'total' => SpatieMedia::count(),
            'images' => SpatieMedia::where('mime_type', 'like', 'image/%')->count(),
            'videos' => SpatieMedia::where('mime_type', 'like', 'video/%')->count(),
            'documents' => SpatieMedia::whereNotLike('mime_type', 'image/%')
                ->whereNotLike('mime_type', 'video/%')
                ->count(),
            'total_size' => $this->formatBytes(SpatieMedia::sum('size')),
        ];

        return view('backend.pages.media.index', compact('media', 'breadcrumbs', 'stats'));
    }

    public function store(Request $request)
    {
        $this->checkAuthorization(Auth::user(), ['media.upload']);

        $request->validate([
            'files.*' => 'required|file|max:10240', // 10MB max
        ]);

        $uploadedFiles = [];

        foreach ($request->file('files', []) as $file) {
            // Store the file first
            $path = $file->store('media', 'public');

            // Create media record
            $mediaItem = SpatieMedia::create([
                'model_type' => SpatieMedia::class,
                'model_id' => 0,
                'uuid' => null,
                'collection_name' => 'default',
                'name' => pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME),
                'file_name' => basename($path),
                'mime_type' => $file->getMimeType(),
                'disk' => 'public',
                'conversions_disk' => 'public',
                'size' => $file->getSize(),
                'manipulations' => '[]',
                'custom_properties' => '[]',
                'generated_conversions' => '[]',
                'responsive_images' => '[]',
                'order_column' => null,
            ]);

            $uploadedFiles[] = $mediaItem;
        }

        return response()->json([
            'success' => true,
            'message' => __('Files uploaded successfully'),
            'files' => $uploadedFiles,
        ]);
    }

    public function destroy($id)
    {
        $this->checkAuthorization(Auth::user(), ['media.delete']);

        $media = SpatieMedia::findOrFail($id);

        // Delete the physical file
        if (Storage::disk($media->disk)->exists($media->getPath())) {
            Storage::disk($media->disk)->delete($media->getPath());
        }

        $media->delete();

        return response()->json([
            'success' => true,
            'message' => __('Media deleted successfully'),
        ]);
    }

    public function bulkDelete(Request $request)
    {
        $this->checkAuthorization(Auth::user(), ['media.delete']);

        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:media,id',
        ]);

        $media = SpatieMedia::whereIn('id', $request->ids)->get();

        foreach ($media as $item) {
            if (Storage::disk($item->disk)->exists($item->getPath())) {
                Storage::disk($item->disk)->delete($item->getPath());
            }
            $item->delete();
        }

        return redirect()->back()->with('success', __('Selected media deleted successfully'));
    }

    private function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes > 1024; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, $precision) . ' ' . $units[$i];
    }
}

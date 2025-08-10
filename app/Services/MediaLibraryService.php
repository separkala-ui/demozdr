<?php

declare(strict_types=1);

namespace App\Services;

use App\Concerns\HandlesMediaOperations;
use App\Contacts\MediaInterface;
use Illuminate\Http\Request;
use Spatie\MediaLibrary\MediaCollections\Models\Media as SpatieMedia;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MediaLibraryService
{
    use HandlesMediaOperations;

    public function getMediaList(
        ?string $search = null,
        ?string $type = null,
        string $sort = 'created_at',
        string $direction = 'desc',
        int $perPage = 24
    ): array {
        $query = SpatieMedia::query()->latest();

        // Apply search filter
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('file_name', 'like', "%{$search}%")
                    ->orWhere('mime_type', 'like', "%{$search}%");
            });
        }

        // Apply type filter
        if ($type) {
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

        // Apply sorting
        if (in_array($sort, ['name', 'size', 'created_at', 'mime_type'])) {
            $query->orderBy($sort, $direction);
        }

        // Paginate results
        $media = $query->paginate($perPage)->withQueryString();

        // Enhance media items with additional information
        $media->getCollection()->transform(function ($item) {
            $item->human_readable_size = $this->formatFileSize($item->size);
            $item->file_type_category = $this->getFileTypeCategory($item->mime_type);
            $item->icon = $this->getMediaIcon($item->mime_type);
            return $item;
        });

        // Get statistics
        $stats = $this->getMediaStatistics();

        return [
            'media' => $media,
            'stats' => $stats,
        ];
    }

    public function getMediaStatistics(): array
    {
        return [
            'total' => SpatieMedia::count(),
            'images' => SpatieMedia::where('mime_type', 'like', 'image/%')->count(),
            'videos' => SpatieMedia::where('mime_type', 'like', 'video/%')->count(),
            'documents' => SpatieMedia::whereNotLike('mime_type', 'image/%')
                ->whereNotLike('mime_type', 'video/%')
                ->count(),
            'total_size' => $this->formatFileSize((int) SpatieMedia::sum('size')),
        ];
    }

    public function uploadMedia(array $files): array
    {
        $uploadedFiles = [];

        foreach ($files as $file) {
            // Skip files that don't pass security checks
            if (! $this->isSecureFile($file)) {
                continue;
            }

            // Generate a secure filename
            $safeFileName = $this->generateUniqueFilename($file->getClientOriginalName());

            // Store the file with a secure name
            $path = $file->storeAs('media', $safeFileName, 'public');

            // Create media record directly in the media table for standalone uploads
            $mediaItem = SpatieMedia::create([
                'model_type' => '', // Empty for standalone media
                'model_id' => 0,   // 0 for standalone media
                'uuid' => Str::uuid(),
                'collection_name' => 'uploads',
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

        return $uploadedFiles;
    }

    public function deleteMedia(int $id): bool
    {
        $media = SpatieMedia::findOrFail($id);

        // Delete the physical file
        if (Storage::disk($media->disk)->exists($media->getPath())) {
            Storage::disk($media->disk)->delete($media->getPath());
        }

        return $media->delete();
    }

    public function bulkDeleteMedia(array $ids): int
    {
        $deleteCount = 0;
        $media = SpatieMedia::whereIn('id', $ids)->get();

        foreach ($media as $item) {
            if (Storage::disk($item->disk)->exists($item->getPath())) {
                Storage::disk($item->disk)->delete($item->getPath());
            }
            if ($item->delete()) {
                $deleteCount++;
            }
        }

        return $deleteCount;
    }

    public function uploadFromRequest(
        MediaInterface $model,
        Request $request,
        string $requestKey,
        string $collection = 'default'
    ): void {
        if ($request->hasFile($requestKey)) {
            $file = $request->file($requestKey);

            // Security checks
            if ($this->isSecureFile($file)) {
                $model->addMedia($file)
                    ->sanitizingFileName(function ($fileName) {
                        return $this->sanitizeFilename($fileName);
                    })
                    ->toMediaCollection($collection);
            }
        }
    }

    public function uploadMultipleFromRequest(
        MediaInterface $model,
        Request $request,
        string $requestKey,
        string $collection = 'default'
    ): void {
        if ($request->hasFile($requestKey)) {
            foreach ($request->file($requestKey) as $file) {
                // Security checks
                if ($this->isSecureFile($file)) {
                    $model->addMedia($file)
                        ->sanitizingFileName(function ($fileName) {
                            return $this->sanitizeFilename($fileName);
                        })
                        ->toMediaCollection($collection);
                }
            }
        }
    }

    public function clearMediaCollection(MediaInterface $model, string $collection = 'default'): void
    {
        $model->clearMediaCollection($collection);
    }
}

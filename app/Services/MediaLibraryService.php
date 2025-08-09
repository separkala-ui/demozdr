<?php

declare(strict_types=1);

namespace App\Services;

use App\Contacts\MediaInterface;
use Illuminate\Http\Request;

class MediaLibraryService
{
    public function uploadFromRequest(
        MediaInterface $model,
        Request $request,
        string $requestKey,
        string $collection = 'default'
    ): void {
        if ($request->hasFile($requestKey)) {
            $model->addMediaFromRequest($requestKey)
                ->toMediaCollection($collection);
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
                $model->addMedia($file)
                    ->toMediaCollection($collection);
            }
        }
    }

    public function clearMediaCollection(MediaInterface $model, string $collection = 'default'): void
    {
        $model->clearMediaCollection($collection);
    }
}

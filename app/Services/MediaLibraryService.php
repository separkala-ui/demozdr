<?php

declare(strict_types=1);

namespace App\Services;

use App\Contacts\MediaInterface;
use App\Helper\MediaHelper;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;

class MediaLibraryService
{
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
                $model->addMediaFromRequest($requestKey)
                    ->sanitizingFileName(function($fileName) {
                        return MediaHelper::sanitizeFilename($fileName);
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
                        ->sanitizingFileName(function($fileName) {
                            return MediaHelper::sanitizeFilename($fileName);
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

    private function isSecureFile(UploadedFile $file): bool
    {
        if (MediaHelper::isDangerousFile($file)) {
            return false;
        }

        if (!MediaHelper::validateFileHeaders($file)) {
            return false;
        }

        return true;
    }
}

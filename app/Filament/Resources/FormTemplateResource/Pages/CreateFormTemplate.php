<?php

namespace App\Filament\Resources\FormTemplateResource\Pages;

use App\Filament\Resources\FormTemplateResource;
use Filament\Resources\Pages\CreateRecord;

class CreateFormTemplate extends CreateRecord
{
    protected static string $resource = FormTemplateResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by'] = auth()->id();
        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}

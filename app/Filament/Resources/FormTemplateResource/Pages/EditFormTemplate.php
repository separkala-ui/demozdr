<?php

namespace App\Filament\Resources\FormTemplateResource\Pages;

use App\Filament\Resources\FormTemplateResource;
use Filament\Resources\Pages\EditRecord;
use Filament\Actions;

class EditFormTemplate extends EditRecord
{
    protected static string $resource = FormTemplateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}

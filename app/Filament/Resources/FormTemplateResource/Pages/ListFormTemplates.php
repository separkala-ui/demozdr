<?php

namespace App\Filament\Resources\FormTemplateResource\Pages;

use App\Filament\Resources\FormTemplateResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions;

class ListFormTemplates extends ListRecords
{
    protected static string $resource = FormTemplateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('الگوی جدید'),
        ];
    }
}

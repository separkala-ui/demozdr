<?php

namespace App\Filament\Resources\FormReportResource\Pages;

use App\Filament\Resources\FormReportResource;
use Filament\Resources\Pages\ViewRecord;

class ViewFormReport extends ViewRecord
{
    protected static string $resource = FormReportResource::class;

    protected static string $view = 'filament.resources.form-report-resource.pages.view-form-report';
}

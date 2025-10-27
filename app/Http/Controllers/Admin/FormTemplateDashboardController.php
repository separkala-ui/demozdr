<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DynamicForms\FormTemplate;
use App\Models\DynamicForms\FormReport;
use App\Models\DynamicForms\FormTemplateField;
use Illuminate\Http\Request;

class FormTemplateDashboardController extends Controller
{
    public function index()
    {
        // Statistics
        $totalTemplates = FormTemplate::count();
        $activeTemplates = FormTemplate::where('is_active', true)->count();
        $totalReports = FormReport::count();
        $todayReports = FormReport::whereDate('created_at', today())->count();
        $totalFields = FormTemplateField::count();
        $avgFieldsPerTemplate = $totalTemplates > 0 
            ? round($totalFields / $totalTemplates, 1) 
            : 0;

        // Templates by Category
        $templatesByCategory = [
            [
                'label' => '🔍 کنترل کیفیت',
                'count' => FormTemplate::where('category', 'qc')->count(),
                'percentage' => $totalTemplates > 0 
                    ? round((FormTemplate::where('category', 'qc')->count() / $totalTemplates) * 100) 
                    : 0,
                'color' => 'bg-blue-600'
            ],
            [
                'label' => '🔎 بازرسی شعبه',
                'count' => FormTemplate::where('category', 'inspection')->count(),
                'percentage' => $totalTemplates > 0 
                    ? round((FormTemplate::where('category', 'inspection')->count() / $totalTemplates) * 100) 
                    : 0,
                'color' => 'bg-yellow-600'
            ],
            [
                'label' => '🏭 تولید',
                'count' => FormTemplate::where('category', 'production')->count(),
                'percentage' => $totalTemplates > 0 
                    ? round((FormTemplate::where('category', 'production')->count() / $totalTemplates) * 100) 
                    : 0,
                'color' => 'bg-red-600'
            ],
            [
                'label' => '📋 سایر',
                'count' => FormTemplate::where('category', 'other')->count(),
                'percentage' => $totalTemplates > 0 
                    ? round((FormTemplate::where('category', 'other')->count() / $totalTemplates) * 100) 
                    : 0,
                'color' => 'bg-gray-600'
            ],
        ];

        // Recent Templates
        $recentTemplates = FormTemplate::with('creator')
            ->latest()
            ->take(5)
            ->get();

        return view('admin.form-templates.dashboard', compact(
            'totalTemplates',
            'activeTemplates',
            'totalReports',
            'todayReports',
            'totalFields',
            'avgFieldsPerTemplate',
            'templatesByCategory',
            'recentTemplates'
        ));
    }
}


<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DynamicForms\FormReport;

class QualityControlController extends Controller
{
    public function index()
    {
        $reports = FormReport::whereHas('template', function ($q) {
            $q->where('category', 'quality_control');
        })->paginate(15);
        return view('admin.quality-control.index', compact('reports'));
    }

    public function create()
    {
        return view('admin.quality-control.create');
    }

    public function store()
    {
        return redirect()->route('admin.quality-control.index')->with('success', __('کنترل کیفیت ثبت شد.'));
    }

    public function show($id)
    {
        $report = FormReport::findOrFail($id);
        return view('admin.quality-control.show', compact('report'));
    }
}

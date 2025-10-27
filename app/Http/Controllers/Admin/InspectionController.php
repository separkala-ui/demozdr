<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DynamicForms\FormReport;

class InspectionController extends Controller
{
    public function index()
    {
        $reports = FormReport::whereHas('template', function ($q) {
            $q->where('category', 'inspection');
        })->paginate(15);
        return view('admin.inspection.index', compact('reports'));
    }

    public function create()
    {
        return view('admin.inspection.create');
    }

    public function store()
    {
        return redirect()->route('admin.inspection.index')->with('success', __('بازرسی ثبت شد.'));
    }

    public function show($id)
    {
        $report = FormReport::findOrFail($id);
        return view('admin.inspection.show', compact('report'));
    }
}

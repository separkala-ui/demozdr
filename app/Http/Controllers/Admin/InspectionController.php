<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

class InspectionController extends Controller
{
    public function index()
    {
        return view('admin.inspection.index');
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
        // TODO: Fetch report from database when FormReport model is available
        // $report = FormReport::findOrFail($id);
        return view('admin.inspection.show');
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

class QualityControlController extends Controller
{
    public function index()
    {
        return view('admin.quality-control.index');
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
        // TODO: Fetch report from database when FormReport model is available
        // $report = FormReport::findOrFail($id);
        return view('admin.quality-control.show');
    }
}

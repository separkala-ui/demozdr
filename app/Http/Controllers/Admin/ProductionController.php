<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

class ProductionController extends Controller
{
    public function index()
    {
        return view('admin.production.index');
    }

    public function create()
    {
        return view('admin.production.create');
    }

    public function store()
    {
        return redirect()->route('admin.production.index')->with('success', __('درخواست تولید ثبت شد.'));
    }

    public function show($id)
    {
        // TODO: Fetch report from database when FormReport model is available
        // $report = FormReport::findOrFail($id);
        return view('admin.production.show');
    }
}

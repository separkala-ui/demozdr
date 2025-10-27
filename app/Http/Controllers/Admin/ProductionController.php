<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DynamicForms\FormReport;

class ProductionController extends Controller
{
    public function index()
    {
        $reports = FormReport::whereHas('template', function ($q) {
            $q->where('category', 'goods_request');
        })->paginate(15);
        return view('admin.production.index', compact('reports'));
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
        $report = FormReport::findOrFail($id);
        return view('admin.production.show', compact('report'));
    }
}

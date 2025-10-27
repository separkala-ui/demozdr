<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DynamicForms\FormTemplate;
use Illuminate\Http\Request;

class OperationalFormsController extends Controller
{
    public function index()
    {
        $templates = FormTemplate::paginate(15);
        return view('admin.operational-forms.index', compact('templates'));
    }

    public function create()
    {
        return view('admin.operational-forms.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category' => 'required|in:general,inspection,goods_request,quality_control',
        ]);

        FormTemplate::create(array_merge($validated, ['created_by' => auth()->id()]));

        return redirect()->route('admin.operational-forms.index')->with('success', 'فرم با موفقیت ایجاد شد.');
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Models\DynamicForms\FormTemplate;
use App\Models\DynamicForms\FormReport;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class FormTemplateController extends Controller
{
    /**
     * Show form filling interface
     */
    public function fillForm(FormTemplate $template)
    {
        if (!$template->is_active) {
            abort(403, 'این فرم فعال نیست');
        }

        $fields = $template->fields()->orderBy('order')->get();

        return view('admin.forms.fill', compact('template', 'fields'));
    }

    /**
     * Submit form data
     */
    public function submitForm(Request $request, FormTemplate $template)
    {
        if (!$template->is_active) {
            abort(403, 'این فرم فعال نیست');
        }

        // ذخیره گزارش
        $report = FormReport::create([
            'template_id' => $template->id,
            'reporter_id' => auth()->id(),
            'status' => 'submitted',
            'completed_at' => now(),
        ]);

        // ذخیره پاسخ‌ها
        foreach ($template->fields as $field) {
            if ($request->has('field_' . $field->id)) {
                $report->answers()->create([
                    'field_id' => $field->id,
                    'value' => $request->input('field_' . $field->id),
                ]);
            }
        }

        return redirect()->route('admin.forms.success', $report)
            ->with('success', 'فرم با موفقیت ثبت شد');
    }

    /**
     * Show success page
     */
    public function success(FormReport $report)
    {
        return view('admin.forms.success', compact('report'));
    }

    /**
     * Preview form before submission
     */
    public function preview(FormTemplate $template)
    {
        $fields = $template->fields()->orderBy('order')->get();

        return view('admin.forms.preview', compact('template', 'fields'));
    }
}

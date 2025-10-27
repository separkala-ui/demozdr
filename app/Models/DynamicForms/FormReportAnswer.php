<?php

namespace App\Models\DynamicForms;

use Illuminate\Database\Eloquent\Model;

class FormReportAnswer extends Model
{
    protected $table = 'form_report_answers';
    protected $fillable = ['report_id', 'template_field_id', 'value', 'notes'];

    public function report()
    {
        return $this->belongsTo(FormReport::class, 'report_id');
    }

    public function field()
    {
        return $this->belongsTo(FormTemplateField::class, 'template_field_id');
    }
}

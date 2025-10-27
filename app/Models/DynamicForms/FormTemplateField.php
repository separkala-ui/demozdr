<?php

namespace App\Models\DynamicForms;

use Illuminate\Database\Eloquent\Model;

class FormTemplateField extends Model
{
    protected $table = 'form_template_fields';
    protected $fillable = ['template_id', 'label', 'type', 'description', 'order', 'is_required', 'options'];
    protected $casts = ['is_required' => 'boolean', 'options' => 'json'];

    public function template()
    {
        return $this->belongsTo(FormTemplate::class, 'template_id');
    }

    public function answers()
    {
        return $this->hasMany(FormReportAnswer::class, 'template_field_id');
    }
}

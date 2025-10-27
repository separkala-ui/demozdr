<?php

namespace App\Models\DynamicForms;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FormTemplate extends Model
{
    use SoftDeletes;

    protected $table = 'form_templates';
    protected $fillable = ['title', 'description', 'category', 'is_active', 'created_by'];
    protected $casts = ['is_active' => 'boolean'];

    public function fields()
    {
        return $this->hasMany(FormTemplateField::class, 'template_id')->orderBy('order');
    }

    public function reports()
    {
        return $this->hasMany(FormReport::class, 'template_id');
    }

    public function creator()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }
}

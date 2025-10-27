<?php

namespace App\Models\DynamicForms;

use Illuminate\Database\Eloquent\Model;

class FormReport extends Model
{
    protected $table = 'form_reports';
    protected $fillable = ['template_id', 'ledger_id', 'reporter_id', 'status', 'completed_at', 'overall_score', 'notes'];
    protected $casts = ['completed_at' => 'datetime', 'overall_score' => 'float'];

    public function template()
    {
        return $this->belongsTo(FormTemplate::class, 'template_id');
    }

    public function ledger()
    {
        return $this->belongsTo(\App\Models\PettyCashLedger::class, 'ledger_id');
    }

    public function reporter()
    {
        return $this->belongsTo(\App\Models\User::class, 'reporter_id');
    }

    public function answers()
    {
        return $this->hasMany(FormReportAnswer::class, 'report_id');
    }
}

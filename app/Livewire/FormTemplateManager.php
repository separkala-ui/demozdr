<?php

namespace App\Livewire;

use App\Models\DynamicForms\FormTemplate;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('backend.layouts.app')]
class FormTemplateManager extends Component
{
    public $templates;
    public $activeTab = 'list'; // 'list' or 'create'
    public $editingTemplate = null;
    public $formData = [
        'title' => '',
        'description' => '',
        'category' => 'other',
        'is_active' => true,
    ];

    public function mount()
    {
        $this->loadTemplates();
    }

    public function loadTemplates()
    {
        $this->templates = FormTemplate::all();
    }

    public function switchTab($tab)
    {
        $this->activeTab = $tab;
        if ($tab === 'list') {
            $this->editingTemplate = null;
            $this->formData = [
                'title' => '',
                'description' => '',
                'category' => 'other',
                'is_active' => true,
            ];
        }
    }

    public function create()
    {
        $this->activeTab = 'create';
        $this->editingTemplate = null;
        $this->formData = [
            'title' => '',
            'description' => '',
            'category' => 'other',
            'is_active' => true,
        ];
    }

    public function edit(FormTemplate $template)
    {
        $this->editingTemplate = $template;
        $this->formData = $template->toArray();
        $this->activeTab = 'create';
    }

    public function save()
    {
        $this->validate([
            'formData.title' => 'required|string|max:255',
            'formData.description' => 'nullable|string',
            'formData.category' => 'required|in:qc,inspection,production,other',
        ]);

        if ($this->editingTemplate) {
            $this->editingTemplate->update($this->formData);
            session()->flash('success', 'فرم با موفقیت به‌روزرسانی شد');
        } else {
            $this->formData['created_by'] = auth()->id();
            FormTemplate::create($this->formData);
            session()->flash('success', 'فرم با موفقیت ایجاد شد');
        }

        $this->activeTab = 'list';
        $this->loadTemplates();
    }

    public function delete(FormTemplate $template)
    {
        $template->delete();
        session()->flash('success', 'فرم با موفقیت حذف شد');
        $this->loadTemplates();
    }

    public function render()
    {
        return view('livewire.form-template-manager', [
            'templates' => $this->templates,
        ]);
    }
}

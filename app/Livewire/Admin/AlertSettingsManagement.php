<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use App\Models\AlertSetting;
use Livewire\WithPagination;

class AlertSettingsManagement extends Component
{
    use WithPagination;

    public $search = '';
    public $categoryFilter = 'all';
    public $editingId = null;
    public $editingValue = null;

    protected $queryString = ['search', 'categoryFilter'];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function editSetting($id)
    {
        $setting = AlertSetting::find($id);
        
        if ($setting && $setting->is_editable) {
            $this->editingId = $id;
            $this->editingValue = $setting->value;
        }
    }

    public function saveSetting()
    {
        $setting = AlertSetting::find($this->editingId);
        
        if (!$setting || !$setting->is_editable) {
            session()->flash('error', 'این تنظیم قابل ویرایش نیست');
            return;
        }

        // Validation based on type
        $this->validate([
            'editingValue' => match($setting->type) {
                'percentage' => ['required', 'numeric', 'min:0', 'max:100'],
                'amount' => ['required', 'numeric', 'min:0'],
                'count' => ['required', 'integer', 'min:0'],
                'boolean' => ['required', 'in:true,false'],
                default => ['required'],
            }
        ]);

        $setting->value = $this->editingValue;
        $setting->save();

        $this->editingId = null;
        $this->editingValue = null;

        session()->flash('success', 'تنظیمات با موفقیت ذخیره شد');
        
        $this->dispatch('setting-updated');
    }

    public function cancelEdit()
    {
        $this->editingId = null;
        $this->editingValue = null;
    }

    public function toggleActive($id)
    {
        $setting = AlertSetting::find($id);
        
        if ($setting) {
            $setting->is_active = !$setting->is_active;
            $setting->save();
            
            session()->flash('success', 'وضعیت تنظیمات تغییر کرد');
        }
    }

    public function render()
    {
        $settings = AlertSetting::query()
            ->when($this->search, function ($q) {
                $q->where(function ($query) {
                    $query->where('title_fa', 'like', '%' . $this->search . '%')
                        ->orWhere('key', 'like', '%' . $this->search . '%')
                        ->orWhere('description_fa', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->categoryFilter !== 'all', function ($q) {
                $q->where('category', $this->categoryFilter);
            })
            ->orderBy('category')
            ->orderBy('priority', 'desc')
            ->paginate(15);

        $categories = AlertSetting::distinct()->pluck('category')->toArray();

        return view('livewire.admin.alert-settings-management', [
            'settings' => $settings,
            'categories' => $categories,
        ]);
    }
}

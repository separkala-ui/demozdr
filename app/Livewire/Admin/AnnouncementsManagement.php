<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use App\Models\SystemAnnouncement;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;

class AnnouncementsManagement extends Component
{
    use WithPagination;

    public $search = '';
    public $typeFilter = 'all';
    public $showModal = false;
    
    public $announcementId = null;
    public $title = '';
    public $content = '';
    public $type = 'info';
    public $priority = 'normal';
    public $is_active = true;
    public $is_pinned = false;
    public $starts_at = null;
    public $expires_at = null;
    public $icon = null;
    public $action_url = null;
    public $action_text = null;

    protected $rules = [
        'title' => ['required', 'string', 'max:255'],
        'content' => ['required', 'string'],
        'type' => ['required', 'in:info,success,warning,danger'],
        'priority' => ['required', 'in:low,normal,high,urgent'],
        'is_active' => ['boolean'],
        'is_pinned' => ['boolean'],
        'starts_at' => ['nullable', 'date'],
        'expires_at' => ['nullable', 'date', 'after:starts_at'],
        'icon' => ['nullable', 'string', 'max:100'],
        'action_url' => ['nullable', 'url', 'max:500'],
        'action_text' => ['nullable', 'string', 'max:100'],
    ];

    public function openCreateModal()
    {
        $this->reset(['announcementId', 'title', 'content', 'type', 'priority', 'is_active', 'is_pinned', 'starts_at', 'expires_at', 'icon', 'action_url', 'action_text']);
        $this->showModal = true;
    }

    public function openEditModal($id)
    {
        $announcement = SystemAnnouncement::findOrFail($id);
        
        $this->announcementId = $announcement->id;
        $this->title = $announcement->title;
        $this->content = $announcement->content;
        $this->type = $announcement->type;
        $this->priority = $announcement->priority;
        $this->is_active = $announcement->is_active;
        $this->is_pinned = $announcement->is_pinned;
        $this->starts_at = $announcement->starts_at?->format('Y-m-d\TH:i');
        $this->expires_at = $announcement->expires_at?->format('Y-m-d\TH:i');
        $this->icon = $announcement->icon;
        $this->action_url = $announcement->action_url;
        $this->action_text = $announcement->action_text;
        
        $this->showModal = true;
    }

    public function save()
    {
        $this->validate();

        $data = [
            'title' => $this->title,
            'content' => $this->content,
            'type' => $this->type,
            'priority' => $this->priority,
            'is_active' => $this->is_active,
            'is_pinned' => $this->is_pinned,
            'starts_at' => $this->starts_at,
            'expires_at' => $this->expires_at,
            'icon' => $this->icon,
            'action_url' => $this->action_url,
            'action_text' => $this->action_text,
        ];

        if ($this->announcementId) {
            $announcement = SystemAnnouncement::findOrFail($this->announcementId);
            $announcement->update($data);
            session()->flash('success', 'اطلاعیه با موفقیت بروزرسانی شد');
        } else {
            $data['created_by'] = Auth::id();
            SystemAnnouncement::create($data);
            session()->flash('success', 'اطلاعیه با موفقیت ایجاد شد');
        }

        $this->showModal = false;
        $this->dispatch('announcement-saved');
    }

    public function delete($id)
    {
        SystemAnnouncement::findOrFail($id)->delete();
        session()->flash('success', 'اطلاعیه با موفقیت حذف شد');
    }

    public function toggleActive($id)
    {
        $announcement = SystemAnnouncement::findOrFail($id);
        $announcement->is_active = !$announcement->is_active;
        $announcement->save();
        
        session()->flash('success', 'وضعیت اطلاعیه تغییر کرد');
    }

    public function render()
    {
        $announcements = SystemAnnouncement::with('creator')
            ->when($this->search, function ($q) {
                $q->where(function ($query) {
                    $query->where('title', 'like', '%' . $this->search . '%')
                        ->orWhere('content', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->typeFilter !== 'all', function ($q) {
                $q->where('type', $this->typeFilter);
            })
            ->byPriority()
            ->paginate(10);

        return view('livewire.admin.announcements-management', compact('announcements'));
    }
}

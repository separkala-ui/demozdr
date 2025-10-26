<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\SystemAnnouncement;
use Illuminate\Support\Facades\Auth;

class AnnouncementsWidget extends Component
{
    public $showAll = false;
    public $dismissedIds = [];

    public function mount()
    {
        // Load dismissed IDs from session
        $this->dismissedIds = session()->get('dismissed_announcements', []);
    }

    public function dismiss($id)
    {
        $this->dismissedIds[] = $id;
        session()->put('dismissed_announcements', $this->dismissedIds);
    }

    public function toggleShowAll()
    {
        $this->showAll = !$this->showAll;
    }

    public function markAsViewed($id)
    {
        $announcement = SystemAnnouncement::find($id);
        if ($announcement) {
            $announcement->incrementViews();
        }
    }

    public function render()
    {
        $user = Auth::user();

        $query = SystemAnnouncement::visible()
            ->forUser($user)
            ->byPriority();

        if (!$this->showAll) {
            $query->limit(3);
        }

        // Filter out dismissed announcements
        if (!empty($this->dismissedIds)) {
            $query->whereNotIn('id', $this->dismissedIds);
        }

        $announcements = $query->get();
        $totalCount = SystemAnnouncement::visible()->forUser($user)->count();

        return view('livewire.announcements-widget', [
            'announcements' => $announcements,
            'totalCount' => $totalCount,
        ]);
    }
}

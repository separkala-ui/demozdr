<?php

namespace App\Livewire\Admin;

use App\Models\PettyCashLedger;
use App\Models\User;
use App\Models\BranchUser;
use Livewire\Component;

class BranchUsersManagement extends Component
{
    public PettyCashLedger $ledger;
    public $branchUsers = [];

    public $search = '';
    public $searchResults = [];
    public $selectedUserId = null;
    public $selectedAccessType = 'petty_cash';

    public $accessTypes = [
        'petty_cash' => 'مسئول تنخواه',
        'inspection' => 'بازرس',
        'quality_control' => 'کنترل کیفیت',
        'production_engineering' => 'مهندسی تولید',
        'production_manager' => 'مدیر تولید',
    ];

    public function mount(PettyCashLedger $ledger)
    {
        $this->ledger = $ledger;
        $this->loadBranchUsers();
    }

    public function updatedSearch($value)
    {
        if (strlen($value) > 2) {
            $this->searchResults = User::where(function ($query) use ($value) {
                $query->where('first_name', 'like', '%' . $value . '%')
                    ->orWhere('last_name', 'like', '%' . $value . '%')
                    ->orWhere('email', 'like', '%' . $value . '%');
            })
            ->whereDoesntHave('branchUsers', function ($query) {
                $query->where('ledger_id', $this->ledger->id);
            })
            ->limit(5)
            ->get();
        } else {
            $this->searchResults = [];
        }
    }
    
    public function loadBranchUsers()
    {
        $this->branchUsers = $this->ledger->branchUsers()->with('user')->get();
    }

    public function selectUser(User $user)
    {
        $this->selectedUserId = $user->id;
        $this->search = $user->full_name;
        $this->searchResults = [];
    }
    
    public function addUser()
    {
        $this->validate([
            'selectedUserId' => 'required|exists:users,id',
            'selectedAccessType' => 'required|in:' . implode(',', array_keys($this->accessTypes)),
        ], [
            'selectedUserId.required' => 'لطفاً یک کاربر را انتخاب کنید.',
            'selectedAccessType.required' => 'لطفاً سطح دسترسی را انتخاب کنید.',
        ]);

        $existing = BranchUser::where('ledger_id', $this->ledger->id)
            ->where('user_id', $this->selectedUserId)
            ->where('access_type', $this->selectedAccessType)
            ->exists();

        if (!$existing) {
            BranchUser::create([
                'ledger_id' => $this->ledger->id,
                'user_id' => $this->selectedUserId,
                'access_type' => $this->selectedAccessType,
                'is_active' => true,
            ]);
            $this->loadBranchUsers();
            $this->reset(['search', 'selectedUserId', 'selectedAccessType', 'searchResults']);
            session()->flash('success', 'کاربر با موفقیت به شعبه اضافه شد.');
        } else {
            session()->flash('error', 'این کاربر با این سطح دسترسی قبلاً اضافه شده است.');
        }
    }

    public function removeUser(int $branchUserId)
    {
        BranchUser::find($branchUserId)?->delete();
        $this->loadBranchUsers();
        session()->flash('success', 'دسترسی کاربر با موفقیت حذف شد.');
    }

    public function render()
    {
        return view('livewire.admin.branch-users-management');
    }
}

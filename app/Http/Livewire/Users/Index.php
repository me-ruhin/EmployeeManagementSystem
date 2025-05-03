<?php

namespace App\Http\Livewire\Users;

use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public $search = '';
    public $role = '';
    public $status = '';
    public $confirmingUserDeletion = false;
    public $userIdBeingDeleted = null;
    public $sortField = 'name';
    public $sortDirection = 'asc';

    protected $queryString = [
        'search' => ['except' => ''],
        'role' => ['except' => ''],
        'status' => ['except' => ''],
        'sortField' => ['except' => 'name'],
        'sortDirection' => ['except' => 'asc'],
    ];

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function confirmUserDeletion($userId)
    {
        $this->confirmingUserDeletion = true;
        $this->userIdBeingDeleted = $userId;
    }

    public function deleteUser()
    {
        $user = User::find($this->userIdBeingDeleted);
        
        if ($user) {
            // Don't allow deleting oneself
            if ($user->id === auth()->id()) {
                session()->flash('error', 'You cannot delete your own account.');
                $this->confirmingUserDeletion = false;
                $this->userIdBeingDeleted = null;
                return;
            }
            
            $user->delete();
            session()->flash('success', 'User deleted successfully.');
        }
        
        $this->confirmingUserDeletion = false;
        $this->userIdBeingDeleted = null;
    }

    public function render()
    {
        $users = User::query()
            ->when($this->search, function ($query) {
                return $query->where(function ($query) {
                    $query->where('name', 'like', '%' . $this->search . '%')
                        ->orWhere('email', 'like', '%' . $this->search . '%')
                        ->orWhere('phone', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->role, function ($query) {
                return $query->where('role', $this->role);
            })
            ->when($this->status, function ($query) {
                return $query->where('status', $this->status);
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate(10);

        return view('livewire.users.index', [
            'users' => $users,
        ]);
    }
}

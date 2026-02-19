<?php

namespace App\Livewire\Admin;

use App\Models\Pharmacy;
use App\Models\User;
use Livewire\Component;

class UsersPage extends Component
{
    public function mount(): void
    {
        abort_unless(auth()->user()?->can('manage_users'), 403);
    }

    public function render()
    {
        $users = User::query()
            ->with('roles:name')
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'role', 'is_active', 'created_at']);

        return view('livewire.admin.users-page', [
            'users' => $users,
            'pharmacies' => Pharmacy::query()->orderBy('name')->get(['id', 'name', 'is_active']),
            'roles' => [
                User::ROLE_ADMIN,
                User::ROLE_PHARMACY,
                User::ROLE_SUPPORT,
                User::ROLE_CUSTOMER,
                User::ROLE_PATIENT,
            ],
        ])->layout('components.layouts.app.sidebar', ['title' => __('Users')]);
    }
}

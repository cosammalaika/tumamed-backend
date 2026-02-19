<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function create(Request $request): RedirectResponse
    {
        $returnTo = $this->sanitizeReturnTo($request->query('return_to'));
        $separator = str_contains($returnTo, '?') ? '&' : '?';

        return redirect()->to($returnTo.$separator.'openCreateUser=1');
    }

    public function store(Request $request, AuditLogger $auditLogger): RedirectResponse
    {
        $roles = [
            User::ROLE_ADMIN,
            User::ROLE_PHARMACY,
            User::ROLE_SUPPORT,
            User::ROLE_CUSTOMER,
        ];

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role' => ['required', Rule::in($roles)],
            'pharmacy_id' => [
                'nullable',
                'string',
                'required_if:role,'.User::ROLE_PHARMACY,
                'exists:pharmacies,id',
            ],
            'is_active' => ['sometimes', 'boolean'],
            'return_to' => ['nullable', 'string'],
        ]);

        $role = strtoupper($data['role']);
        $isAdminRole = $role === User::ROLE_ADMIN;
        if ($isAdminRole && ! $request->user()?->isAdmin()) {
            abort(403, 'Only admins can assign admin role.');
        }

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'role' => $role,
            'pharmacy_id' => $role === User::ROLE_PHARMACY ? Arr::get($data, 'pharmacy_id') : null,
            'is_active' => (bool) ($data['is_active'] ?? true),
        ]);

        Role::firstOrCreate(['name' => $role, 'guard_name' => 'web']);
        $user->syncRoles([$role]);

        $auditLogger->log('ADMIN_CREATE_USER', [
            'user_id' => $user->id,
            'role' => $role,
            'created_by' => $request->user()?->id,
        ], $user, null, $request);

        return redirect($this->sanitizeReturnTo(Arr::get($data, 'return_to')))
            ->with('success', 'User created successfully.');
    }

    private function sanitizeReturnTo(?string $returnTo): string
    {
        $default = route('admin.users');

        if (! is_string($returnTo) || $returnTo === '') {
            return $default;
        }

        if (str_starts_with($returnTo, '/')) {
            $returnTo = url($returnTo);
        }

        $adminUsersPrefix = rtrim(route('admin.users'), '/');
        if (! str_starts_with($returnTo, $adminUsersPrefix)) {
            return $default;
        }

        return $returnTo;
    }
}

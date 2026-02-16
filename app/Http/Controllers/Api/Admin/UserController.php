<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UserRequest;
use App\Models\User;
use App\Services\AuditLogger;

class UserController extends Controller
{
    public function store(UserRequest $request, AuditLogger $auditLogger)
    {
        $data = $request->validated();

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'role' => $data['role'],
            'is_active' => true,
            'pharmacy_id' => $data['pharmacy_id'] ?? null,
        ]);

        $user->syncRoles([$data['role']]);

        $auditLogger->log('ADMIN_CREATE_USER', [
            'user_id' => $user->id,
            'role' => $data['role'],
        ], $user, null, $request);

        return response()->json($user, 201);
    }
}

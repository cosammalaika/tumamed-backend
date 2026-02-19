<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Services\AuditLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Illuminate\Validation\Rule;

class AuthController extends Controller
{
    public function register(Request $request, AuditLogger $auditLogger): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $role = User::ROLE_PATIENT;

        $user = User::create([
            'name' => $validated['name'],
            'email' => strtolower(trim($validated['email'])),
            'password' => $validated['password'],
            'role' => $role,
            'is_active' => true,
            'pharmacy_id' => null,
        ]);

        Role::firstOrCreate(['name' => $role, 'guard_name' => 'web']);
        $user->syncRoles([$role]);

        $token = $user->createToken('api-token', [$role])->plainTextToken;

        $auditLogger->log('USER_REGISTER', ['email' => $user->email, 'role' => $role], $user, null, $request);

        return response()->json([
            'success' => true,
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'is_active' => (bool) $user->is_active,
                'phone' => $user->phone,
                'pharmacy_id' => $user->pharmacy_id,
            ],
        ], 201);
    }

    public function login(Request $request, AuditLogger $auditLogger): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        /** @var User|null $user */
        $user = User::where('email', strtolower(trim($validated['email'])))->first();

        if (! $user || ! Hash::check($validated['password'], $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials',
            ], 401);
        }

        if (! $user->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Account disabled',
            ], 403);
        }

        $token = $user->createToken('api-token', [$user->role])->plainTextToken;

        $auditLogger->log('USER_LOGIN', ['email' => $user->email, 'role' => $user->role], null, null, $request);

        return response()->json([
            'success' => true,
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'is_active' => (bool) $user->is_active,
                'phone' => $user->phone,
                'pharmacy_id' => $user->pharmacy_id,
            ],
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()?->currentAccessToken()?->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully.',
        ]);
    }
}

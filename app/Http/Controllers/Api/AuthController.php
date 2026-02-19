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
        return $this->performRegister($request, $auditLogger, false);
    }

    public function patientRegister(Request $request, AuditLogger $auditLogger): JsonResponse
    {
        return $this->performRegister($request, $auditLogger, true);
    }

    public function login(Request $request, AuditLogger $auditLogger): JsonResponse
    {
        return $this->performLogin($request, $auditLogger, false);
    }

    public function patientLogin(Request $request, AuditLogger $auditLogger): JsonResponse
    {
        return $this->performLogin($request, $auditLogger, true);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()?->currentAccessToken()?->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully.',
        ]);
    }

    private function performRegister(Request $request, AuditLogger $auditLogger, bool $patientOnly): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'phone' => ['nullable', 'string', 'max:32'],
        ]);

        $role = User::ROLE_PATIENT;

        $user = User::create([
            'name' => $validated['name'],
            'email' => strtolower(trim($validated['email'])),
            'password' => $validated['password'],
            'phone' => $validated['phone'] ?? null,
            'role' => $role,
            'is_active' => true,
            'pharmacy_id' => null,
        ]);

        Role::firstOrCreate(['name' => $role, 'guard_name' => 'web']);
        $user->syncRoles([$role]);

        $token = $user->createToken('api-token', [$role])->plainTextToken;

        $auditLogger->log(
            $patientOnly ? 'PATIENT_REGISTER' : 'USER_REGISTER',
            ['email' => $user->email, 'role' => $role],
            $user,
            null,
            $request
        );

        return $this->successAuthResponse($user, $token, 201);
    }

    private function performLogin(Request $request, AuditLogger $auditLogger, bool $patientOnly): JsonResponse
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
                'message' => 'Invalid email or password',
            ], $patientOnly ? 422 : 401);
        }

        if (! $user->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Account disabled',
            ], 403);
        }

        if ($patientOnly && strtoupper((string) $user->role) !== User::ROLE_PATIENT) {
            return response()->json([
                'success' => false,
                'message' => 'This app is for patients only.',
            ], 403);
        }

        $token = $user->createToken('api-token', [$user->role])->plainTextToken;

        $auditLogger->log(
            $patientOnly ? 'PATIENT_LOGIN' : 'USER_LOGIN',
            ['email' => $user->email, 'role' => $user->role],
            null,
            null,
            $request
        );

        return $this->successAuthResponse($user, $token);
    }

    private function successAuthResponse(User $user, string $token, int $status = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'is_active' => (bool) $user->is_active,
                'status' => $user->is_active ? 'active' : 'disabled',
                'phone' => $user->phone,
                'pharmacy_id' => $user->pharmacy_id,
            ],
        ], $status);
    }
}

<?php

namespace App\Actions\Fortify;

use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique(User::class),
            ],
            'password' => $this->passwordRules(),
            'role' => ['sometimes', 'string', Rule::in([User::ROLE_ADMIN, User::ROLE_PHARMACY])],
            'pharmacy_id' => [
                'nullable',
                'string',
                'required_if:role,'.User::ROLE_PHARMACY,
                Rule::exists('pharmacies', 'id'),
            ],
        ])->validate();

        $user = User::create([
            'name' => $input['name'],
            'email' => $input['email'],
            'password' => $input['password'],
            'role' => $input['role'] ?? User::ROLE_ADMIN,
            'is_active' => true,
            'pharmacy_id' => $input['pharmacy_id'] ?? null,
        ]);

        Role::findOrCreate($user->role, 'web');
        $user->syncRoles([$user->role]);

        return $user;
    }
}

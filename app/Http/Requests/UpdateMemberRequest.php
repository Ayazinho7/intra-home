<?php

namespace App\Http\Requests;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UpdateMemberRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('member'));
    }

    public function rules(): array
    {
        $member = $this->route('member');

        // Eltern kendi rolünü düşüremez; yoksa evde eltern kalmayabilir.
        $roles = $this->user()->is($member)
            ? [Role::Eltern->value]
            : array_column(Role::cases(), 'value');

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique(User::class)->ignore($member)],
            // Boş bırakılırsa şifre değişmez.
            'password' => ['nullable', 'string', 'confirmed', Password::defaults()],
            'role' => ['required', Rule::in($roles)],
            'color' => ['required', 'regex:/^#[0-9a-fA-F]{6}$/'],
        ];
    }
}

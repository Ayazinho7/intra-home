{{-- Ekle ve düzenle formlarının ortak alanları. $member düzenlemede dolu, eklemede null. --}}
@php($member ??= null)

<flux:input name="name" :label="__('Name')" :value="old('name', $member?->name)" required autofocus />

<flux:input name="email" type="email" :label="__('Email')" :value="old('email', $member?->email)" required />

<flux:select name="role" :label="__('Role')">
    @foreach ($roles as $role)
        <flux:select.option :value="$role->value" :selected="old('role', $member?->role?->value ?? 'kind') === $role->value">
            {{ $role->label() }}
        </flux:select.option>
    @endforeach
</flux:select>

<flux:field>
    <flux:label>{{ __('Color') }}</flux:label>
    <input type="color" name="color" value="{{ old('color', $member?->color ?? '#64748b') }}"
           class="h-10 w-20 cursor-pointer rounded-lg border border-zinc-200 bg-white p-1 dark:border-zinc-600 dark:bg-zinc-700">
    <flux:error name="color" />
</flux:field>

<flux:input name="password" type="password" :label="__('Password')" autocomplete="new-password"
            :description="$member ? __('Leave empty to keep the current password.') : null" :required="! $member" />

<flux:input name="password_confirmation" type="password" :label="__('Confirm password')" autocomplete="new-password" :required="! $member" />

<?php

namespace App\Http\Controllers;

use App\Enums\Role;
use App\Http\Requests\StoreMemberRequest;
use App\Http\Requests\UpdateMemberRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

// Ev üyeleri (Mitglieder). Resource controller: index, create, store, edit, update, destroy.
// {member} route parametresi, route model binding ile otomatik User modeline dönüşür.
class MemberController extends Controller
{
    public function index(): View
    {
        Gate::authorize('viewAny', User::class);

        return view('members.index', [
            'members' => User::orderBy('name')->get(),
        ]);
    }

    public function create(): View
    {
        Gate::authorize('create', User::class);

        return view('members.create', ['roles' => Role::cases()]);
    }

    // Yetki ve doğrulama StoreMemberRequest içinde; buraya yalnız geçerli veri gelir.
    public function store(StoreMemberRequest $request): RedirectResponse
    {
        // Şifreyi User modelindeki 'hashed' cast'i şifreler.
        User::create($request->validated());

        return redirect()->route('members.index')->with('status', __('Member added.'));
    }

    public function edit(User $member): View
    {
        Gate::authorize('update', $member);

        return view('members.edit', ['member' => $member, 'roles' => Role::cases()]);
    }

    public function update(UpdateMemberRequest $request, User $member): RedirectResponse
    {
        $data = $request->validated();

        // Şifre alanı boşsa mevcut şifreye dokunma.
        if (blank($data['password'] ?? null)) {
            unset($data['password']);
        }

        $member->update($data);

        return redirect()->route('members.index')->with('status', __('Member saved.'));
    }

    public function destroy(User $member): RedirectResponse
    {
        Gate::authorize('delete', $member);

        $member->delete();

        return redirect()->route('members.index')->with('status', __('Member deleted.'));
    }
}

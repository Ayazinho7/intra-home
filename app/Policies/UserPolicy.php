<?php

namespace App\Policies;

use App\Models\User;

// Ev üyeleri (users tablosu) üzerinde kim ne yapabilir.
// Laravel bu sınıfı adından bulur: User modeli → UserPolicy.
class UserPolicy
{
    // Giriş yapan herkes üye listesini görebilir (Gast dahil).
    public function viewAny(User $user): bool
    {
        return true;
    }

    // Yeni üyeyi yalnız eltern ekler.
    public function create(User $user): bool
    {
        return $user->canWrite() && $user->isEltern();
    }

    public function update(User $user, User $member): bool
    {
        return $user->canWrite() && $user->isEltern();
    }

    // Eltern kendini silemez: evde hiç eltern kalmazdı.
    public function delete(User $user, User $member): bool
    {
        return $user->canWrite() && $user->isEltern() && $user->isNot($member);
    }
}

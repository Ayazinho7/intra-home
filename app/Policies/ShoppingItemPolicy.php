<?php

namespace App\Policies;

use App\Models\ShoppingItem;
use App\Models\User;

class ShoppingItemPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->canWrite();
    }

    public function update(User $user, ShoppingItem $item): bool
    {
        return $user->canWrite() && ($user->isEltern() || $item->added_by === $user->id);
    }

    public function delete(User $user, ShoppingItem $item): bool
    {
        return $user->canWrite() && ($user->isEltern() || $item->added_by === $user->id);
    }

    // Ortak listedeki ürünü yazma yetkisi olan herkes satın alabilir.
    public function buy(User $user, ShoppingItem $item): bool
    {
        return $user->canWrite();
    }
}

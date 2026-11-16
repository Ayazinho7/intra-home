<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShoppingItem extends Model
{
    protected $fillable = ['name', 'quantity', 'category'];

    protected function casts(): array
    {
        return ['bought_at' => 'datetime'];
    }

    public function addedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'added_by');
    }

    public function boughtBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'bought_by');
    }
}

<?php

namespace App\Http\Requests;

class UpdateShoppingItemRequest extends StoreShoppingItemRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('shopping_item'));
    }
}

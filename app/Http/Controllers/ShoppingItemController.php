<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreShoppingItemRequest;
use App\Http\Requests\UpdateShoppingItemRequest;
use App\Models\ShoppingItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class ShoppingItemController extends Controller
{
    public function index(): View
    {
        Gate::authorize('viewAny', ShoppingItem::class);

        return view('shopping-items.index', [
            'items' => ShoppingItem::query()
                ->where(fn ($query) => $query->whereNull('bought_at')->orWhere('bought_at', '>', now()->subDays(7)))
                ->orderByRaw('CASE WHEN bought_at IS NULL THEN 0 ELSE 1 END')
                ->orderBy('created_at')->orderBy('id')->get(),
        ]);
    }

    public function create(): View
    {
        Gate::authorize('create', ShoppingItem::class);

        return view('shopping-items.create');
    }

    public function store(StoreShoppingItemRequest $request): RedirectResponse
    {
        $item = new ShoppingItem($request->validated());
        $item->added_by = $request->user()->id;
        $item->save();

        return redirect()->route('shopping-items.index')->with('status', __('Shopping item added.'));
    }

    public function edit(ShoppingItem $shoppingItem): View
    {
        Gate::authorize('update', $shoppingItem);

        return view('shopping-items.edit', ['item' => $shoppingItem]);
    }

    public function update(UpdateShoppingItemRequest $request, ShoppingItem $shoppingItem): RedirectResponse
    {
        $shoppingItem->update($request->validated());

        return redirect()->route('shopping-items.index')->with('status', __('Shopping item saved.'));
    }

    public function destroy(ShoppingItem $shoppingItem): RedirectResponse
    {
        Gate::authorize('delete', $shoppingItem);
        $shoppingItem->delete();

        return redirect()->route('shopping-items.index')->with('status', __('Shopping item deleted.'));
    }

    public function buy(ShoppingItem $shoppingItem): RedirectResponse
    {
        Gate::authorize('buy', $shoppingItem);

        // Tekrarlanan istekler ilk satın alma zamanını ve kişisini korur.
        ShoppingItem::whereKey($shoppingItem->id)->whereNull('bought_at')->update([
            'bought_by' => auth()->id(),
            'bought_at' => now(),
        ]);

        return redirect()->route('shopping-items.index');
    }
}

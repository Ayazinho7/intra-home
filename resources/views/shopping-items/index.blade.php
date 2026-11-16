<x-layouts.app :title="__('Shopping list')">
    <div class="mx-auto flex w-full max-w-3xl flex-col gap-6">
        <div class="flex items-center justify-between gap-4">
            <flux:heading size="xl" level="1">{{ __('Shopping list') }}</flux:heading>
            @can('create', App\Models\ShoppingItem::class)
                <flux:button variant="primary" icon="plus" :href="route('shopping-items.create')" wire:navigate>{{ __('Add shopping item') }}</flux:button>
            @endcan
        </div>
        @if (session('status'))
            <flux:callout variant="success" icon="check-circle" :heading="session('status')" />
        @endif
        <ul class="divide-y divide-zinc-200 overflow-hidden rounded-xl border border-zinc-200 dark:divide-zinc-700 dark:border-zinc-700">
            @forelse ($items as $item)
                <li class="flex flex-wrap items-center gap-3 p-4">
                    <div class="min-w-0 flex-1 {{ $item->bought_at ? 'text-zinc-500 line-through' : '' }}">
                        <div class="font-medium">{{ $item->name }}</div>
                        <div class="text-sm text-zinc-500">{{ collect([$item->quantity, $item->category])->filter()->join(' · ') }}</div>
                    </div>
                    @if ($item->bought_at)
                        <flux:badge>{{ __('Bought') }}</flux:badge>
                    @else
                        @can('buy', $item)
                            <form method="POST" action="{{ route('shopping-items.buy', $item) }}">
                                @csrf
                                @method('PATCH')
                                <flux:button type="submit" icon="check" class="min-h-11">{{ __('Bought') }}</flux:button>
                            </form>
                        @endcan
                    @endif
                    @can('update', $item)
                        <flux:button size="sm" variant="ghost" icon="pencil-square" :href="route('shopping-items.edit', $item)" wire:navigate :aria-label="__('Edit')" />
                    @endcan
                    @can('delete', $item)
                        <form method="POST" action="{{ route('shopping-items.destroy', $item) }}" onsubmit="return confirm(@js(__('Really delete :name?', ['name' => $item->name])))">
                            @csrf
                            @method('DELETE')
                            <flux:button size="sm" variant="ghost" icon="trash" type="submit" :aria-label="__('Delete')" />
                        </form>
                    @endcan
                </li>
            @empty
                <li class="p-4 text-zinc-500">{{ __('The shopping list is empty.') }}</li>
            @endforelse
        </ul>
    </div>
</x-layouts.app>

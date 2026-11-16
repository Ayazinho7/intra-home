<x-layouts.app :title="__('Edit shopping item')">
    <div class="mx-auto flex w-full max-w-lg flex-col gap-6">
        <flux:heading size="xl" level="1">{{ __('Edit shopping item') }}</flux:heading>
        <form method="POST" action="{{ route('shopping-items.update', $item) }}" class="flex flex-col gap-6">
            @csrf
            @method('PUT')
            @include('shopping-items._form')
            <div class="flex gap-2">
                <flux:button variant="primary" type="submit">{{ __('Save') }}</flux:button>
                <flux:button variant="ghost" :href="route('shopping-items.index')" wire:navigate>{{ __('Cancel') }}</flux:button>
            </div>
        </form>
    </div>
</x-layouts.app>

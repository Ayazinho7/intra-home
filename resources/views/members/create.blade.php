<x-layouts.app :title="__('Add member')">
    <div class="mx-auto flex w-full max-w-lg flex-col gap-6">
        <flux:heading size="xl" level="1">{{ __('Add member') }}</flux:heading>

        <form method="POST" action="{{ route('members.store') }}" class="flex flex-col gap-6">
            @csrf
            @include('members._form')

            <div class="flex gap-2">
                <flux:button variant="primary" type="submit">{{ __('Save') }}</flux:button>
                <flux:button variant="ghost" :href="route('members.index')" wire:navigate>{{ __('Cancel') }}</flux:button>
            </div>
        </form>
    </div>
</x-layouts.app>

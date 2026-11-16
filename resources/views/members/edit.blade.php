<x-layouts.app :title="__('Edit member')">
    <div class="mx-auto flex w-full max-w-lg flex-col gap-6">
        <flux:heading size="xl" level="1">{{ __('Edit member') }}</flux:heading>

        <form method="POST" action="{{ route('members.update', $member) }}" class="flex flex-col gap-6">
            @csrf
            {{-- HTML formu yalnız GET/POST bilir; @method Laravel'e bunun PUT olduğunu söyler. --}}
            @method('PUT')
            @include('members._form', ['member' => $member])

            <div class="flex gap-2">
                <flux:button variant="primary" type="submit">{{ __('Save') }}</flux:button>
                <flux:button variant="ghost" :href="route('members.index')" wire:navigate>{{ __('Cancel') }}</flux:button>
            </div>
        </form>
    </div>
</x-layouts.app>

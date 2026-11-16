<x-layouts.app :title="__('Members')">
    <div class="mx-auto flex w-full max-w-3xl flex-col gap-6">
        <div class="flex items-center justify-between gap-4">
            <div>
                <flux:heading size="xl" level="1">{{ __('Members') }}</flux:heading>
                <flux:subheading>{{ __('Everyone in the household.') }}</flux:subheading>
            </div>

            {{-- @can policy'ye sorar: yalnız eltern bu düğmeyi görür. --}}
            @can('create', App\Models\User::class)
                <flux:button variant="primary" icon="plus" :href="route('members.create')" wire:navigate>
                    {{ __('Add member') }}
                </flux:button>
            @endcan
        </div>

        @if (session('status'))
            <flux:callout variant="success" icon="check-circle" :heading="session('status')" />
        @endif

        <ul class="divide-y divide-zinc-200 overflow-hidden rounded-xl border border-zinc-200 dark:divide-zinc-700 dark:border-zinc-700">
            @foreach ($members as $member)
                <li class="flex items-center gap-3 p-4">
                    {{-- Kişinin rengi: sonraki modüllerde listelerde de bu nokta görünür. --}}
                    <span class="flex size-10 shrink-0 items-center justify-center rounded-full text-sm font-semibold text-white"
                          style="background-color: {{ $member->color }}">
                        {{ $member->initials() }}
                    </span>

                    <div class="min-w-0 flex-1">
                        <div class="truncate font-medium">
                            {{ $member->name }}
                            @if ($member->is(auth()->user()))
                                <span class="text-sm text-zinc-500">({{ __('you') }})</span>
                            @endif
                        </div>
                        <div class="truncate text-sm text-zinc-500">{{ $member->email }}</div>
                    </div>

                    <flux:badge size="sm">{{ $member->role->label() }}</flux:badge>

                    @can('update', $member)
                        <flux:button size="sm" variant="ghost" icon="pencil-square" :href="route('members.edit', $member)" wire:navigate :aria-label="__('Edit')" />
                    @endcan

                    @can('delete', $member)
                        <form method="POST" action="{{ route('members.destroy', $member) }}"
                              onsubmit="return confirm(@js(__('Really delete :name?', ['name' => $member->name])))">
                            @csrf
                            @method('DELETE')
                            <flux:button size="sm" variant="ghost" icon="trash" type="submit" :aria-label="__('Delete')" />
                        </form>
                    @endcan
                </li>
            @endforeach
        </ul>
    </div>
</x-layouts.app>

<?php

use App\Models\User;
use Livewire\Volt\Component;
use Livewire\WithPagination;
use function Livewire\Volt\layout;

layout('components.layouts.app', ['title' => __('Global Showrooms')]);

new class extends Component {
    use WithPagination;

    public $search = '';

    public function updatingSearch() { $this->resetPage(); }

    public function with()
    {
        return [
            'owners' => User::role('Owner')
                ->where(function($query) {
                    $query->where('name', 'like', '%' . $this->search . '%')
                          ->orWhere('public_key', 'like', '%' . $this->search . '%')
                          ->orWhere('public_slug', 'like', '%' . $this->search . '%');
                })
                ->latest()
                ->paginate(15),
        ];
    }
} ?><div class="flex flex-col gap-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-neutral-100">{{ __('Showroom Control') }}</h1>
                <p class="text-sm text-neutral-400">{{ __('Manage and monitor all public owner showrooms.') }}</p>
            </div>
        </div>

        <div class="flex items-center gap-4">
            <div class="relative flex-1">
                <flux:input 
                    wire:model.live.debounce.300ms="search" 
                    icon="magnifying-glass" 
                    placeholder="{{ __('Search by owner name or showroom slug/key...') }}" 
                />
            </div>
        </div>

        <div class="rounded-xl border border-neutral-800 bg-neutral-900/70 shadow-lg backdrop-blur overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-neutral-400">
                    <thead class="border-b border-neutral-800 text-xs uppercase text-neutral-500">
                        <tr>
                            <th class="px-6 py-4 font-medium">{{ __('Owner') }}</th>
                            <th class="px-6 py-4 font-medium">{{ __('Showroom Path') }}</th>
                            <th class="px-6 py-4 font-medium">{{ __('Status') }}</th>
                            <th class="px-6 py-4"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-neutral-800">
                        @forelse ($owners as $owner)
                            <tr class="hover:bg-neutral-800/30 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2">
                                        <flux:profile :initials="$owner->initials()" size="xs" />
                                        <span class="text-sm font-medium text-neutral-100">{{ $owner->name }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2">
                                        <code class="text-[10px] bg-neutral-800 px-1.5 py-0.5 rounded text-blue-400">/u/{{ $owner->public_slug ?: $owner->public_key }}</code>
                                        <flux:button variant="ghost" icon="arrow-top-right-on-square" size="xs" :href="route('showroom.profile', ['key' => $owner->public_slug ?: $owner->public_key])" target="_blank" />
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <flux:badge :color="$owner->is_active ? 'emerald' : 'red'">
                                        {{ $owner->is_active ? __('Public') : __('Suspended') }}
                                    </flux:badge>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <flux:dropdown>
                                        <flux:button variant="ghost" icon="ellipsis-horizontal" size="xs" />
                                        <flux:menu>
                                            <flux:menu.item icon="pencil">{{ __('Customize Link') }}</flux:menu.item>
                                            @if($owner->is_active)
                                                <flux:menu.item icon="eye-slash" variant="danger">{{ __('Disable Showroom') }}</flux:menu.item>
                                            @else
                                                <flux:menu.item icon="check-circle">{{ __('Enable Showroom') }}</flux:menu.item>
                                            @endif
                                        </flux:menu>
                                    </flux:dropdown>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center py-12 text-neutral-500 italic">
                                    {{ __('No showrooms found.') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($owners->hasPages())
                <div class="p-4 border-t border-neutral-800">
                    {{ $owners->links() }}
                </div>
            @endif
        </div>
    </div>

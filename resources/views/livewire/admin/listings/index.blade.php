<?php

use App\Models\Listing;
use Livewire\Volt\Component;
use Livewire\WithPagination;
use function Livewire\Volt\layout;

layout('components.layouts.app', ['title' => __('Global Listings')]);

new class extends Component {
    use WithPagination;

    public $search = '';
    public $status = '';

    public function updatingSearch() { $this->resetPage(); }
    public function updatingStatus() { $this->resetPage(); }

    public function toggleModeration($listingId, $action)
    {
        $listing = Listing::findOrFail($listingId);
        
        if ($action === 'approve') {
            $listing->update(['status' => 'AVAILABLE']);
        } elseif ($action === 'hide') {
            $listing->update(['status' => 'UNAVAILABLE']);
        }

        Flux::toast(__('Listing status updated.'));
    }

    public function with()
    {
        return [
            'listings' => Listing::with('owner')
                ->where(function($query) {
                    $query->where('title', 'like', '%' . $this->search . '%')
                          ->orWhereHas('owner', function($q) {
                              $q->where('name', 'like', '%' . $this->search . '%');
                          });
                })
                ->when($this->status, fn($q) => $q->where('status', $this->status))
                ->latest()
                ->paginate(10),
        ];
    }
} ?><div class="flex flex-col gap-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-neutral-100">{{ __('Global Listings Group') }}</h1>
                <p class="text-sm text-neutral-400">{{ __('Monitor and moderate all listings across the entire platform.') }}</p>
            </div>
        </div>

        <div class="flex items-center gap-4">
            <div class="relative flex-1">
                <flux:input 
                    wire:model.live.debounce.300ms="search" 
                    icon="magnifying-glass" 
                    placeholder="{{ __('Search by title or owner name...') }}" 
                />
            </div>
            <flux:select wire:model.live="status" class="w-48">
                <flux:select.option value="">{{ __('All Statuses') }}</flux:select.option>
                <flux:select.option value="AVAILABLE">{{ __('Available') }}</flux:select.option>
                <flux:select.option value="UNAVAILABLE">{{ __('Unavailable') }}</flux:select.option>
            </flux:select>
        </div>

        <div class="rounded-xl border border-neutral-800 bg-neutral-900/70 shadow-lg backdrop-blur overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-neutral-400">
                    <thead class="border-b border-neutral-800 text-xs uppercase text-neutral-500">
                        <tr>
                            <th class="px-6 py-4 font-medium">{{ __('Listing') }}</th>
                            <th class="px-6 py-4 font-medium">{{ __('Owner') }}</th>
                            <th class="px-6 py-4 font-medium">{{ __('Price') }}</th>
                            <th class="px-6 py-4 font-medium">{{ __('Status') }}</th>
                            <th class="px-6 py-4"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-neutral-800">
                        @forelse ($listings as $listing)
                            <tr class="hover:bg-neutral-800/30 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="flex flex-col">
                                        <span class="font-medium text-neutral-100">{{ $listing->title }}</span>
                                        <span class="text-xs text-neutral-500">{{ $listing->location_text }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2">
                                        <flux:profile :initials="$listing->owner->initials()" size="xs" />
                                        <span class="text-sm text-neutral-300">{{ $listing->owner->name }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-neutral-400 text-sm font-mono">${{ number_format($listing->price_per_night, 2) }}</td>
                                <td class="px-6 py-4">
                                    <flux:badge :color="$listing->status === 'AVAILABLE' ? 'emerald' : 'zinc'">
                                        {{ $listing->status }}
                                    </flux:badge>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <flux:dropdown>
                                        <flux:button variant="ghost" icon="ellipsis-horizontal" size="xs" />
                                        <flux:menu>
                                            <flux:menu.item icon="eye" target="_blank" :href="route('listing.show', $listing->slug)">{{ __('View Publicly') }}</flux:menu.item>
                                            <flux:menu.separator />
                                            @if($listing->status === 'UNAVAILABLE')
                                                <flux:menu.item icon="check-circle" wire:click="toggleModeration({{ $listing->id }}, 'approve')">{{ __('Approve Listing') }}</flux:menu.item>
                                            @else
                                                <flux:menu.item icon="eye-slash" variant="danger" wire:click="toggleModeration({{ $listing->id }}, 'hide')">{{ __('Hide / Unpublish') }}</flux:menu.item>
                                            @endif
                                            <flux:menu.item icon="trash" variant="danger">{{ __('Delete (Permanent)') }}</flux:menu.item>
                                        </flux:menu>
                                    </flux:dropdown>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-12 text-neutral-500 italic">
                                    {{ __('No listings found.') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($listings->hasPages())
                <div class="p-4 border-t border-neutral-800">
                    {{ $listings->links() }}
                </div>
            @endif
        </div>
    </div>

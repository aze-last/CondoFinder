<?php

use App\Models\Listing;
use Livewire\Volt\Component;
use Livewire\WithPagination;

new class extends Component {
    use WithPagination;

    public int $listingIdToDelete;

    public function with(): array
    {
        return [
            'listings' => Listing::with('categories')
                ->where('owner_id', auth()->id())
                ->latest()
                ->paginate(10),
        ];
    }

    public function delete(Listing $listing): void
    {
        if ($listing->owner_id !== auth()->id()) {
            abort(403);
        }

        $listing->delete();

        $this->modal('delete-listing')->close();

        \Flux::toast(__('Listing deleted successfully.'));
    }
}; ?>

<section class="space-y-4">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-semibold text-neutral-100">{{ __('Your listings') }}</h1>
            <p class="text-sm text-neutral-400">{{ __('Track and manage every unit you have published.') }}</p>
        </div>
        <flux:button variant="primary" icon="plus" href="{{ route('dashboard.listings.create') }}" wire:navigate>
            {{ __('Add listing') }}
        </flux:button>
    </div>

    <div class="overflow-hidden rounded-xl border border-neutral-800/80 bg-neutral-900/70 shadow-lg shadow-black/20">
        <table class="min-w-full divide-y divide-neutral-800">
            <thead class="bg-neutral-900/80">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-neutral-400 w-16">{{ __('Photo') }}</th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-neutral-400">{{ __('Title') }}</th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-neutral-400">{{ __('Status') }}</th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-neutral-400">{{ __('Nightly rate') }}</th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-neutral-400">{{ __('Location') }}</th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-neutral-400">{{ __('Created') }}</th>
                    <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wide text-neutral-400">{{ __('Actions') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-neutral-800">
                @forelse ($listings as $listing)
                    <tr class="hover:bg-neutral-800/40">
                        <td class="px-4 py-3">
                            @php
                                $photo = $listing->getFirstMediaUrl('listings') ?: 'data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="160" height="160" viewBox="0 0 160 160"><rect width="160" height="160" fill="%23252525"/><text x="50%" y="52%" dominant-baseline="middle" text-anchor="middle" fill="%23b3b3b3" font-family="Arial" font-size="14">No Image</text></svg>';
                            @endphp
                            <img src="{{ $photo }}" class="h-10 w-10 object-cover rounded shadow-sm border border-neutral-800">
                        </td>
                        <td class="px-4 py-3 text-sm text-neutral-100">
                            <div class="font-medium">{{ $listing->title }}</div>
                            <div class="text-xs text-neutral-500">{{ $listing->categories?->pluck('name')->join(', ') }}</div>
                        </td>
                        <td class="px-4 py-3 text-sm">
                            <flux:badge variant="{{ $listing->status === 'AVAILABLE' ? 'outline' : 'ghost' }}" size="sm">
                                {{ ucfirst(strtolower($listing->status)) }}
                            </flux:badge>
                        </td>
                        <td class="px-4 py-3 text-sm text-neutral-100">${{ number_format($listing->price_per_night, 2) }}</td>
                        <td class="px-4 py-3 text-sm text-neutral-300">{{ $listing->location_text }}</td>
                        <td class="px-4 py-3 text-sm text-neutral-300">{{ $listing->created_at->format('M d, Y') }}</td>
                        <td class="px-4 py-3 text-right flex items-center justify-end gap-2">
                             <flux:button variant="ghost" icon="pencil-square" size="sm" href="{{ route('dashboard.listings.edit', $listing) }}" wire:navigate />
                             <flux:modal.trigger name="delete-listing">
                                <flux:button 
                                    variant="ghost" 
                                    icon="trash" 
                                    size="sm" 
                                    wire:click="$set('listingIdToDelete', {{ $listing->id }})"
                                />
                             </flux:modal.trigger>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-6 text-center text-sm text-neutral-400">
                            {{ __('No listings yet. Create your first one to start receiving inquiries.') }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div>
        {{ $listings->links() }}
    </div>

    <flux:modal name="delete-listing" class="min-w-[22rem]">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">{{ __('Delete listing?') }}</flux:heading>
                <flux:subheading>
                    {{ __('This action cannot be undone. All data related to this listing will be permanently removed.') }}
                </flux:subheading>
            </div>

            <div class="flex gap-2">
                <flux:spacer />

                <flux:modal.close>
                    <flux:button variant="ghost">{{ __('Cancel') }}</flux:button>
                </flux:modal.close>

                <flux:button type="submit" variant="danger" wire:click="delete({{ $listingIdToDelete ?? 0 }})">{{ __('Delete listing') }}</flux:button>
            </div>
        </div>
    </flux:modal>
</section>

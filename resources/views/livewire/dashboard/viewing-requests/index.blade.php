<?php

use App\Models\ViewingRequest;
use Livewire\Volt\Component;
use Livewire\WithPagination;

new class extends Component {
    use WithPagination;

    public function mount(): void
    {
        auth()->user()->unreadNotifications()
            ->where('type', 'App\Notifications\ViewingRequestReceived')
            ->get()
            ->markAsRead();
    }

    public function updateStatus(ViewingRequest $request, string $status): void
    {
        $request->update(['status' => $status]);
        $message = match ($status) {
            'APPROVED' => __('Request approved. Please confirm by texting the client at :phone.', ['phone' => $request->customer_phone]),
            'DECLINED' => __('Request declined. Optional: notify the client at :phone.', ['phone' => $request->customer_phone]),
            default => __('Request updated to :status', ['status' => $status]),
        };
        session()->flash('status', $message);
    }

    public function delete(ViewingRequest $request): void
    {
        $request->delete();
    }

    public function with(): array
    {
        return [
            'requests' => ViewingRequest::where('owner_id', auth()->id())
                ->with('listing')
                ->latest()
                ->paginate(15),
        ];
    }
}; ?>

<section class="space-y-6">
    <div>
        <h1 class="text-xl font-semibold text-neutral-100">{{ __('Viewing Requests') }}</h1>
        <p class="text-sm text-neutral-400">{{ __('Manage your showing schedule and approve potential client visits.') }}</p>
    </div>

    @if (session('status'))
        <div class="rounded-lg border border-emerald-700/50 bg-emerald-900/30 px-4 py-3 text-sm text-emerald-100 italic">
            {{ session('status') }}
        </div>
    @endif

    <div class="overflow-hidden rounded-xl border border-neutral-800/80 bg-neutral-900/70 shadow-lg shadow-black/20">
        <table class="min-w-full divide-y divide-neutral-800">
            <thead class="bg-neutral-900/80">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-neutral-400">{{ __('Customer') }}</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-neutral-400">{{ __('Listing') }}</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-neutral-400">{{ __('Preferred Date/Time') }}</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-neutral-400">{{ __('Status') }}</th>
                    <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wide text-neutral-400">{{ __('Actions') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-neutral-800">
                @forelse ($requests as $request)
                    <tr class="hover:bg-neutral-800/40 transition-colors">
                        <td class="px-6 py-4">
                            <div class="text-sm font-medium text-neutral-100">{{ $request->customer_name }}</div>
                            <div class="text-xs text-neutral-500">{{ $request->customer_email }}</div>
                            <div class="text-xs text-neutral-500">{{ $request->customer_phone }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm text-neutral-300 truncate max-w-[150px]">
                                {{ $request->listing?->title ?? __('Deleted Listing') }}
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm text-neutral-100">{{ $request->preferred_datetime->format('M d, Y') }}</div>
                            <div class="text-xs text-neutral-500">{{ $request->preferred_datetime->format('g:i A') }}</div>
                        </td>
                        <td class="px-6 py-4">
                            @php
                                $color = match($request->status) {
                                    'APPROVED' => 'emerald',
                                    'DECLINED' => 'red',
                                    default => 'amber',
                                };
                            @endphp
                            <flux:badge :color="$color" size="sm" variant="outline">{{ $request->status }}</flux:badge>
                        </td>
                        <td class="px-6 py-4 text-right text-sm font-medium space-x-1">
                            @if($request->status === 'PENDING')
                                <flux:button variant="ghost" size="sm" icon="check" class="text-emerald-500" wire:click="updateStatus({{ $request->id }}, 'APPROVED')" />
                                <flux:button variant="ghost" size="sm" icon="x-mark" class="text-red-500" wire:click="updateStatus({{ $request->id }}, 'DECLINED')" />
                            @endif
                            <flux:button variant="ghost" size="sm" icon="trash" wire:click="delete({{ $request->id }})" />
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center text-sm text-neutral-500 italic">
                            {{ __('No viewing requests received yet.') }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div>
        {{ $requests->links() }}
    </div>
</section>

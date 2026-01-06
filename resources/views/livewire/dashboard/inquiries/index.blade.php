<?php

use App\Models\Inquiry;
use Livewire\Volt\Component;
use Livewire\WithPagination;

new class extends Component {
    use WithPagination;

    public function mount(): void
    {
        auth()->user()->unreadNotifications()
            ->where('type', 'App\Notifications\InquiryReceived')
            ->get()
            ->markAsRead();
    }

    public function markAsRead(Inquiry $inquiry): void
    {
        $inquiry->update(['is_read' => true]);
    }

    public function delete(Inquiry $inquiry): void
    {
        $inquiry->delete();
    }

    public function with(): array
    {
        return [
            'inquiries' => Inquiry::where('owner_id', auth()->id())
                ->with('listing')
                ->latest()
                ->paginate(15),
        ];
    }
}; ?>

<section class="space-y-6">
    <div>
        <h1 class="text-xl font-semibold text-neutral-100">{{ __('Inquiries') }}</h1>
        <p class="text-sm text-neutral-400">{{ __('Respond to potential buyers and renters who contacted you.') }}</p>
    </div>

    <div class="overflow-hidden rounded-xl border border-neutral-800/80 bg-neutral-900/70 shadow-lg shadow-black/20">
        <table class="min-w-full divide-y divide-neutral-800">
            <thead class="bg-neutral-900/80">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-neutral-400">{{ __('Status') }}</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-neutral-400">{{ __('Customer') }}</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-neutral-400">{{ __('Listing') }}</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-neutral-400">{{ __('Message') }}</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-neutral-400">{{ __('Date') }}</th>
                    <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wide text-neutral-400">{{ __('Actions') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-neutral-800">
                @forelse ($inquiries as $inquiry)
                    <tr class="hover:bg-neutral-800/40 transition-colors {{ !$inquiry->is_read ? 'bg-primary-900/5' : '' }}">
                        <td class="px-6 py-4">
                            @if (!$inquiry->is_read)
                                <flux:badge color="primary" size="sm" class="animate-pulse">{{ __('New') }}</flux:badge>
                            @else
                                <flux:badge variant="ghost" size="sm">{{ __('Read') }}</flux:badge>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm font-medium text-neutral-100">{{ $inquiry->customer_name }}</div>
                            <div class="text-xs text-neutral-500">{{ $inquiry->customer_email }}</div>
                            @if($inquiry->customer_phone)
                                <div class="text-xs text-neutral-500">{{ $inquiry->customer_phone }}</div>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm text-neutral-300 truncate max-w-[150px]">
                                {{ $inquiry->listing?->title ?? __('Deleted Listing') }}
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <p class="text-sm text-neutral-400 line-clamp-2 max-w-sm">{{ $inquiry->message }}</p>
                        </td>
                        <td class="px-6 py-4 text-sm text-neutral-500">
                            {{ $inquiry->created_at->diffForHumans() }}
                        </td>
                        <td class="px-6 py-4 text-right text-sm font-medium space-x-1">
                            @if(!$inquiry->is_read)
                                <flux:button variant="ghost" size="sm" icon="check" wire:click="markAsRead({{ $inquiry->id }})" />
                            @endif
                            <flux:button variant="ghost" size="sm" icon="trash" wire:click="delete({{ $inquiry->id }})" class="text-red-500" />
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-sm text-neutral-500 italic">
                            {{ __('No inquiries received yet.') }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div>
        {{ $inquiries->links() }}
    </div>
</section>

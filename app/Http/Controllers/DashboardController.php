<?php

namespace App\Http\Controllers;

use App\Models\Inquiry;
use App\Models\Listing;
use App\Models\ViewingRequest;
use Illuminate\Contracts\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $ownerId = auth()->id();

        $totalListings = Listing::where('owner_id', $ownerId)->count();
        $activeListings = Listing::where('owner_id', $ownerId)->available()->count();
        $unavailableListings = Listing::where('owner_id', $ownerId)->where('status', 'UNAVAILABLE')->count();

        $avgNightlyRate = Listing::where('owner_id', $ownerId)->avg('price_per_night') ?? 0;
        $occupancyPercent = $totalListings > 0
            ? round(($unavailableListings / $totalListings) * 100)
            : 0;

        $openViewingRequests = ViewingRequest::where('owner_id', $ownerId)
            ->where('status', 'PENDING')
            ->count();

        $upcomingViewings = ViewingRequest::with('listing')
            ->where('owner_id', $ownerId)
            ->where('preferred_datetime', '>=', now())
            ->orderBy('preferred_datetime')
            ->limit(5)
            ->get();

        $recentInquiries = Inquiry::with('listing')
            ->where('owner_id', $ownerId)
            ->latest()
            ->limit(5)
            ->get();

        $recentListings = Listing::where('owner_id', $ownerId)
            ->latest()
            ->limit(6)
            ->get();

        // Build month counts in PHP to stay driver-agnostic (SQLite in tests has no DATE_FORMAT)
        $monthlyListingCounts = Listing::where('owner_id', $ownerId)
            ->where('created_at', '>=', now()->subMonths(5)->startOfMonth())
            ->get()
            ->groupBy(fn ($listing) => $listing->created_at->format('Y-m'))
            ->map->count();

        $monthlySeries = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $key = $month->format('Y-m');
            $monthlySeries[] = [
                'label' => $month->format('M'),
                'value' => $monthlyListingCounts[$key] ?? 0,
            ];
        }

        return view('dashboard', [
            'stats' => [
                'activeListings' => $activeListings,
                'totalListings' => $totalListings,
                'avgNightlyRate' => $avgNightlyRate,
                'occupancyPercent' => $occupancyPercent,
                'openViewingRequests' => $openViewingRequests,
            ],
            'upcomingViewings' => $upcomingViewings,
            'recentInquiries' => $recentInquiries,
            'recentListings' => $recentListings,
            'monthlySeries' => $monthlySeries,
            'shareLink' => route('showroom.profile', ['key' => auth()->user()->public_slug ?: auth()->user()->public_key]),
        ]);
    }
}

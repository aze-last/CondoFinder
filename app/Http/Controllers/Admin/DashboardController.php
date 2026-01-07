<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Listing;
use App\Models\Inquiry;
use App\Models\ViewingRequest;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $totalOwners = User::role('Owner')->count();
        $totalListings = Listing::count();
        $totalInquiries24h = Inquiry::where('created_at', '>=', now()->subDay())->count();
        $totalInquiries7d = Inquiry::where('created_at', '>=', now()->subDays(7))->count();
        
        $pendingViewings = ViewingRequest::where('status', 'PENDING')->count();
        $approvedViewings = ViewingRequest::where('status', 'APPROVED')->count();

        // Activity Log placeholder (can be expanded later)
        $recentActivity = []; 

        // Platform growth (Last 6 months)
        $ownerGrowth = User::role('Owner')
            ->where('created_at', '>=', now()->subMonths(5)->startOfMonth())
            ->get()
            ->groupBy(fn ($user) => $user->created_at->format('Y-m'))
            ->map->count();

        $monthlySeries = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $key = $month->format('Y-m');
            $monthlySeries[] = [
                'label' => $month->format('M'),
                'value' => $ownerGrowth[$key] ?? 0,
            ];
        }

        return view('admin.dashboard', [
            'stats' => [
                'totalOwners' => $totalOwners,
                'totalListings' => $totalListings,
                'inquiries24h' => $totalInquiries24h,
                'inquiries7d' => $totalInquiries7d,
                'pendingViewings' => $pendingViewings,
                'approvedViewings' => $approvedViewings,
            ],
            'monthlySeries' => $monthlySeries,
        ]);
    }
}

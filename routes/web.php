<?php

use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;
use Livewire\Volt\Volt;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Volt::route('/u/{key}', 'showroom.index')->name('showroom.profile');
Volt::route('/listing/{listing:slug}', 'showroom.show')->name('listing.show');

Route::get('/auth/google', [\App\Http\Controllers\Auth\SocialiteController::class, 'redirectToGoogle'])->name('auth.google');
Route::get('/auth/google/callback', [\App\Http\Controllers\Auth\SocialiteController::class, 'handleGoogleCallback']);

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', DashboardController::class)->name('dashboard');

    // Admin Routes
    Route::middleware([\Spatie\Permission\Middleware\RoleMiddleware::class . ':Super Admin'])->prefix('admin')->name('admin.')->group(function () {
        Route::get('dashboard', [App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('dashboard');
        Volt::route('owners', 'admin.owners.index')->name('owners.index');
        Volt::route('showrooms', 'admin.showrooms.index')->name('showrooms.index');
        Volt::route('listings', 'admin.listings.index')->name('listings.index');
        Volt::route('inquiries', 'admin.inquiries.index')->name('inquiries.index');
        Volt::route('viewing-requests', 'admin.viewing-requests.index')->name('viewing-requests.index');
    });

    Volt::route('dashboard/listings', 'dashboard.listings.index')->name('dashboard.listings.index');
    Volt::route('dashboard/listings/create', 'dashboard.listings.form')->name('dashboard.listings.create');
    Volt::route('dashboard/listings/{listing}/edit', 'dashboard.listings.form')->name('dashboard.listings.edit');
    
    Volt::route('dashboard/categories', 'dashboard.categories.index')->name('dashboard.categories.index');
    Volt::route('dashboard/categories/{category}/edit', 'dashboard.categories.index')->name('dashboard.categories.edit'); // I'll handle create/edit in index or a modal for categories for cleaner UX

    Volt::route('dashboard/inquiries', 'dashboard.inquiries.index')->name('dashboard.inquiries.index');
    Volt::route('dashboard/viewing-requests', 'dashboard.viewing-requests.index')->name('dashboard.viewing-requests.index');
    Volt::route('dashboard/calendar', 'dashboard.calendar.index')->name('dashboard.calendar.index');
    Volt::route('dashboard/leads', 'dashboard.leads.index')->name('dashboard.leads.index');
    Volt::route('dashboard/analytics', 'dashboard.analytics.index')->name('dashboard.analytics.index');
    Volt::route('dashboard/reports', 'dashboard.reports.index')->name('dashboard.reports.index');
    Volt::route('dashboard/documents', 'dashboard.documents.index')->name('dashboard.documents.index');
    Volt::route('dashboard/support', 'dashboard.support.index')->name('dashboard.support.index');
});

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Volt::route('settings/profile', 'settings.profile')->name('profile.edit');
    Volt::route('settings/password', 'settings.password')->name('user-password.edit');
    Volt::route('settings/appearance', 'settings.appearance')->name('appearance.edit');

    Volt::route('settings/two-factor', 'settings.two-factor')
        ->middleware(
            when(
                Features::canManageTwoFactorAuthentication()
                    && Features::optionEnabled(Features::twoFactorAuthentication(), 'confirmPassword'),
                ['password.confirm'],
                [],
            ),
        )
        ->name('two-factor.show');
});

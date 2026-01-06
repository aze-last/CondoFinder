<?php

use App\Models\User;
use App\Models\Notification;
use App\Models\ViewingRequest;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->handle(Illuminate\Http\Request::capture());

$user = User::find(3);
if ($user) {
    echo "User: {$user->name} ({$user->email})\n";
    echo "Unread Notifications: " . $user->unreadNotifications()->count() . "\n";
    foreach ($user->unreadNotifications as $notification) {
        echo "- type: {$notification->type} | created at: {$notification->created_at}\n";
    }
}

$request = ViewingRequest::latest()->first();
if ($request) {
    echo "\nLatest Viewing Request:\n";
    echo "- ID: {$request->id}\n";
    echo "- Listing: {$request->listing?->title} (ID: {$request->listing_id})\n";
    echo "- Owner ID: {$request->owner_id}\n";
    echo "- Customer: {$request->customer_name}\n";
    echo "- Created At: {$request->created_at}\n";
} else {
    echo "\nNo viewing requests found\n";
}

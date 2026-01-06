<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ViewingRequestReceived extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(public \App\Models\ViewingRequest $request)
    {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
                    ->subject(__('New Viewing Request for :title', ['title' => $this->request->listing->title]))
                    ->line(__('A new viewing request has been scheduled by :name.', ['name' => $this->request->customer_name]))
                    ->line(__('Preferred Date: :date', ['date' => $this->request->preferred_datetime->format('M d, Y')]))
                    ->line(__('Preferred Time: :time', ['time' => $this->request->preferred_datetime->format('g:i A')]))
                    ->action(__('Review Request'), route('dashboard.viewing-requests.index'))
                    ->line(__('Thank you for using CondoFinder!'));
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'request_id' => $this->request->id,
            'customer_name' => $this->request->customer_name,
            'listing_title' => $this->request->listing->title,
        ];
    }
}

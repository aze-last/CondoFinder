<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class InquiryReceived extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(public \App\Models\Inquiry $inquiry)
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
                    ->subject(__('New Inquiry for :title', ['title' => $this->inquiry->listing->title]))
                    ->line(__('You have received a new inquiry from :name.', ['name' => $this->inquiry->customer_name]))
                    ->line('"' . $this->inquiry->message . '"')
                    ->action(__('View Inquiry'), route('dashboard.inquiries.index'))
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
            'inquiry_id' => $this->inquiry->id,
            'customer_name' => $this->inquiry->customer_name,
            'listing_title' => $this->inquiry->listing->title,
        ];
    }
}

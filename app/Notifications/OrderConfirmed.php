<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderConfirmed extends Notification
{
    use Queueable;

    public function __construct(public Order $order) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $siteName = \App\Models\SiteSetting::get('general.site_name', config('app.name'));
        $symbol   = \App\Models\SiteSetting::get('general.currency_symbol', '৳');

        return (new MailMessage)
            ->subject("Order Confirmed — #{$this->order->order_number}")
            ->greeting("Hi {$this->order->customer_name}!")
            ->line("Thank you for your order. We've received it and will start processing shortly.")
            ->line("**Order Number:** #{$this->order->order_number}")
            ->line("**Total:** {$symbol}" . number_format($this->order->total, 2))
            ->line("**Payment:** " . ucwords(str_replace('_', ' ', $this->order->payment_method ?? 'N/A')))
            ->action('Track Your Order', url('/track?order_number=' . $this->order->order_number))
            ->line("If you have any questions, please contact us.")
            ->salutation("Thanks,\n{$siteName}");
    }
}

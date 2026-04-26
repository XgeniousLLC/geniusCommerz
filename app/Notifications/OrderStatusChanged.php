<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderStatusChanged extends Notification
{
    use Queueable;

    public function __construct(public Order $order, public string $newStatus) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $siteName = \App\Models\SiteSetting::get('general.site_name', config('app.name'));
        $symbol   = \App\Models\SiteSetting::get('general.currency_symbol', '৳');

        $statusLabel = ucfirst($this->newStatus);
        $message     = match($this->newStatus) {
            'processing' => 'Your order is now being processed and will be dispatched soon.',
            'shipped'    => 'Great news! Your order is on its way.' . ($this->order->tracking_number ? " Tracking #: {$this->order->tracking_number}" : ''),
            'delivered'  => 'Your order has been delivered. We hope you love it!',
            'cancelled'  => 'Your order has been cancelled. If you have questions, please contact us.',
            default      => "Your order status has been updated to: {$statusLabel}.",
        };

        $mail = (new MailMessage)
            ->subject("Order #{$this->order->order_number} — {$statusLabel}")
            ->greeting("Hi {$this->order->customer_name}!")
            ->line($message)
            ->line("**Order:** #{$this->order->order_number}")
            ->line("**Status:** {$statusLabel}");

        if ($this->newStatus === 'delivered') {
            $mail->action('Leave a Review', url('/account/reviews'));
        } else {
            $mail->action('Track Your Order', url('/track?order_number=' . $this->order->order_number));
        }

        return $mail->salutation("Thanks,\n{$siteName}");
    }
}

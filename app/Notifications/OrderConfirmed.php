<?php

namespace App\Notifications;

use App\Models\Order;
use App\Models\SiteSetting;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderConfirmed extends Notification
{
    use Queueable;

    public function __construct(public Order $order) {}

    public function via(object $notifiable): array
    {
        if (SiteSetting::get('notifications.email_on_order_confirmed', '1') !== '1') {
            return [];
        }
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $siteName = SiteSetting::get('general.site_name', config('app.name'));
        $symbol   = SiteSetting::get('general.currency_symbol', '৳');

        $defaultSubject = "Order Confirmed — #{$this->order->order_number}";
        $defaultBody    = "Thank you for your order. We've received it and will start processing shortly.\n\nOrder: #{$this->order->order_number}\nTotal: {$symbol}" . number_format($this->order->total, 2);

        $subject = $this->replacePlaceholders(
            SiteSetting::get('notifications.email_subject_confirmed', $defaultSubject),
            $symbol
        );
        $body = $this->replacePlaceholders(
            SiteSetting::get('notifications.email_body_confirmed', $defaultBody),
            $symbol
        );

        $mail = (new MailMessage)
            ->subject($subject)
            ->greeting("Hi {$this->order->customer_name}!");

        foreach (explode("\n", $body) as $line) {
            $mail->line($line);
        }

        return $mail
            ->action('Track Your Order', url('/track?order_number=' . $this->order->order_number))
            ->salutation("Thanks,\n{$siteName}");
    }

    private function replacePlaceholders(string $text, string $symbol): string
    {
        return str_replace(
            ['{{order_number}}', '{{customer_name}}', '{{total}}', '{{tracking}}', '{{site_name}}'],
            [
                $this->order->order_number,
                $this->order->customer_name,
                $symbol . number_format($this->order->total, 2),
                $this->order->tracking_number ?? '',
                SiteSetting::get('general.site_name', config('app.name')),
            ],
            $text
        );
    }
}

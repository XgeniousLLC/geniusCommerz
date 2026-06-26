<?php

namespace App\Notifications;

use App\Models\Order;
use App\Models\SiteSetting;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderStatusChanged extends Notification
{
    use Queueable;

    public function __construct(public Order $order, public string $newStatus) {}

    public function via(object $notifiable): array
    {
        if (SiteSetting::get('notifications.email_on_status_changed', '1') !== '1') {
            return [];
        }
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $siteName    = SiteSetting::get('general.site_name', config('app.name'));
        $symbol      = SiteSetting::get('general.currency_symbol', '৳');
        $statusLabel = ucfirst($this->newStatus);

        // Per-status subject/body keys
        $statusKey = match($this->newStatus) {
            'shipped'   => 'shipped',
            'delivered' => 'delivered',
            'cancelled' => 'cancelled',
            default     => 'status',
        };

        $defaultSubjects = [
            'shipped'   => "Your Order #{$this->order->order_number} Has Shipped",
            'delivered' => "Your Order #{$this->order->order_number} Has Been Delivered",
            'cancelled' => "Your Order #{$this->order->order_number} Has Been Cancelled",
            'status'    => "Your Order #{$this->order->order_number} Status Update",
        ];
        $defaultBodies = [
            'shipped'   => "Great news! Your order is on its way." . ($this->order->tracking_number ? "\nTracking: {$this->order->tracking_number}" : ''),
            'delivered' => 'Your order has been delivered. We hope you love it! Thank you for shopping with us.',
            'cancelled' => 'Your order has been cancelled. If you have questions, please contact us.',
            'status'    => "Your order #{$this->order->order_number} status has been updated to: {$statusLabel}.",
        ];

        $subject = $this->replacePlaceholders(
            SiteSetting::get("notifications.email_subject_{$statusKey}", $defaultSubjects[$statusKey]),
            $symbol,
            $statusLabel
        );
        $body = $this->replacePlaceholders(
            SiteSetting::get("notifications.email_body_{$statusKey}", $defaultBodies[$statusKey]),
            $symbol,
            $statusLabel
        );

        $mail = (new MailMessage)
            ->subject($subject)
            ->greeting("Hi {$this->order->customer_name}!");

        foreach (explode("\n", $body) as $line) {
            $mail->line($line);
        }

        if ($this->newStatus === 'delivered') {
            $mail->action('Leave a Review', url('/account/reviews'));
        } else {
            $mail->action('Track Your Order', url('/track?order_number=' . $this->order->order_number));
        }

        return $mail->salutation("Thanks,\n{$siteName}");
    }

    private function replacePlaceholders(string $text, string $symbol, string $statusLabel): string
    {
        return str_replace(
            ['{{order_number}}', '{{customer_name}}', '{{total}}', '{{tracking}}', '{{site_name}}', '{{status}}'],
            [
                $this->order->order_number,
                $this->order->customer_name,
                $symbol . number_format($this->order->total, 2),
                $this->order->tracking_number ?? '',
                SiteSetting::get('general.site_name', config('app.name')),
                $statusLabel,
            ],
            $text
        );
    }
}

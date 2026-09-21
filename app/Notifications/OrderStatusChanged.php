<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Channels\SmsChannel;
use App\Channels\WhatsAppChannel;
use App\Enums\NormalizedStatus;
use App\Models\Customer;
use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Tells a customer their parcel moved.
 *
 * Every word here is BD-EX's own. The carrier that actually produced the scan is
 * never named, and neither is its tracking number — the customer quotes their
 * BD-EX number and nothing else.
 */
final class OrderStatusChanged extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly Order $order,
        public readonly NormalizedStatus $status,
    ) {}

    /**
     * The channels this particular customer asked for.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        $channels = $notifiable instanceof Customer ? $notifiable->channels() : ['mail'];

        return collect($channels)
            ->map(fn (string $channel): string => match ($channel) {
                'sms' => SmsChannel::class,
                'whatsapp' => WhatsAppChannel::class,
                'database' => 'database',
                default => 'mail',
            })
            ->unique()
            ->values()
            ->all();
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("{$this->status->getLabel()} — {$this->order->bdex_tracking_number}")
            ->greeting('Hello '.($notifiable->name ?? 'there').',')
            ->line("Your shipment {$this->order->bdex_tracking_number} is now **{$this->status->getLabel()}**.")
            ->line($this->status->customerDetail())
            ->action('Track your parcel', url('/#top'))
            ->line('Quote your BD-EX number if you need to talk to us about this shipment.');
    }

    public function toSms(object $notifiable): string
    {
        return sprintf(
            'BD-EX Cargo: %s is now %s. %s Track at %s',
            $this->order->bdex_tracking_number,
            strtolower($this->status->getLabel()),
            $this->status->customerDetail(),
            url('/'),
        );
    }

    public function toWhatsApp(object $notifiable): string
    {
        return $this->toSms($notifiable);
    }

    /** @return array<string, mixed> */
    public function toDatabase(object $notifiable): array
    {
        return [
            'order_id' => $this->order->id,
            'tracking_number' => $this->order->bdex_tracking_number,
            'status' => $this->status->value,
            'status_label' => $this->status->getLabel(),
            'detail' => $this->status->customerDetail(),
        ];
    }
}

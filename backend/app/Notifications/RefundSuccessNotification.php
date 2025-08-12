<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Stripe\Refund;

class RefundSuccessNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected Refund $refund;

    /**
     * Create a new notification instance.
     */
    public function __construct(Refund $refund)
    {
        $this->refund = $refund;
    }

    /**
     * Get the notification's delivery channels.
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
        $amount = number_format($this->refund->amount / 100, 2);
        $currency = strtoupper($this->refund->currency);

        return (new MailMessage)
            ->subject('Your Refund Has Been Processed')
            ->markdown('emails.payment.refund_invoice', [
                'user' => $notifiable,
                'amount' => $amount,
                'currency' => $currency,
                'charge_id' => $this->refund->charge,
                'refund_date' => now()->toFormattedDateString(),
            ]);
    }

    /**
     * Get the array representation of the notification for the database.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $amount = number_format($this->refund->amount / 100, 2);
        $currency = strtoupper($this->refund->currency);

        return [
            'title' => 'Refund Processed',
            'message' => "Your refund of {$amount} {$currency} has been successfully processed.",
            'amount' => $amount,
            'charge_id' => $this->refund->charge,
        ];
    }
}

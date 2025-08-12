<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Stripe\PaymentIntent;

class PaymentSuccessNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected PaymentIntent $paymentIntent;

    /**
     * Create a new notification instance.
     */
    public function __construct(PaymentIntent $paymentIntent)
    {
        $this->paymentIntent = $paymentIntent;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        // Add 'database' to send both an email and a database notification
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $amount = number_format($this->paymentIntent->amount / 100, 2);
        $currency = strtoupper($this->paymentIntent->currency);

        return (new MailMessage)
                    ->subject('Your Payment Invoice')
                    ->markdown('emails.payment.invoice', [
                        'user' => $notifiable,
                        'amount' => $amount,
                        'currency' => $currency,
                        'charge_id' => $this->paymentIntent->latest_charge,
                        'transaction_date' => now()->toFormattedDateString(),
                    ]);
    }

    /**
     * Get the array representation of the notification for database storage.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $amount = number_format($this->paymentIntent->amount / 100, 2);
        $currency = strtoupper($this->paymentIntent->currency);

        return [
            'title' => 'Top-up Successful',
            'message' => "You have successfully topped up your wallet with {$amount} {$currency}.",
            'amount' => $amount,
            'charge_id' => $this->paymentIntent->latest_charge,
        ];
    }
}

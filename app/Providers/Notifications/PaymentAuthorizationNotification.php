<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Payment;
use App\Models\Purchase;

class PaymentAuthorizationNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $payment;
    protected $purchase;

    public function __construct(Payment $payment, Purchase $purchase)
    {
        $this->payment = $payment;
        $this->purchase = $purchase;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        $approveUrl = url('/approve-payment/' . $this->purchase->id);

        return (new MailMessage)
            ->subject('Payment Authorization Request - Purchase #' . $this->purchase->reference_no)
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('A payment has been authorized and requires your approval.')
            ->line('**Purchase Reference:** ' . $this->purchase->reference_no)
            ->line('**Supplier:** ' . optional($this->purchase->supplier)->name)
            ->line('**Warehouse:** ' . optional($this->purchase->warehouse)->name)
            ->line('**Amount:** ' . number_format($this->payment->amount, 2))
            ->line('**Authorized By:** ' . optional($this->payment->authorizer)->name)
            ->action('Approve Payment', $approveUrl)
            ->line('Thank you for reviewing this payment.');
    }

    public function toArray($notifiable)
    {
        return [];
    }
}


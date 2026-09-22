<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class SendNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Notification payload.
     *
     * Keys:
     *  - sender_id     (int|string|null)
     *  - receiver_id   (int|string|null)
     *  - reminder_date (string|null)  e.g. "2025-01-15"
     *  - document_name (mixed)
     *  - message       (string)
     *
     * @var array{sender_id: int|string|null, receiver_id: int|string|null, reminder_date: string|null, document_name: mixed, message: string}
     */
    private array $payload;

    /**
     * Create a new notification instance.
     *
     * @param  array{sender_id?: int|string|null, receiver_id?: int|string|null, reminder_date?: string|null, document_name?: mixed, message?: string}  $payload
     * @return void
     */
    public function __construct(array $payload)
    {
        // Merge with defaults so toArray() never hits an undefined key.
        $this->payload = array_merge([
            'sender_id'     => null,
            'receiver_id'   => null,
            'reminder_date' => null,
            'document_name' => null,
            'message'       => '',
        ], $payload);
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array<int, string>
     */
    public function via($notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification
     * (persisted to the notifications table by the database channel).
     *
     * @param  mixed  $notifiable
     * @return array<string, mixed>
     */
    public function toArray($notifiable): array
    {
        $reminderDate = $this->payload['reminder_date']
            ? date('Y-m-d', strtotime($this->payload['reminder_date']))
            : null;

        return [
            'sender_id'     => $this->payload['sender_id'],
            'receiver_id'   => $this->payload['receiver_id'],
            'reminder_date' => $reminderDate,
            'document_name' => $this->payload['document_name'],
            'message'       => $this->payload['message'],
        ];
    }

    /**
     * Get the database representation (Laravel prefers this over toArray
     * for the 'database' channel when both exist).
     *
     * @param  mixed  $notifiable
     * @return array<string, mixed>
     */
    public function toDatabase($notifiable): array
    {
        return $this->toArray($notifiable);
    }
}
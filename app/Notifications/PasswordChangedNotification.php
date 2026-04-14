<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PasswordChangedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct()
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
        return ['database', 'mail', 'broadcast'];
    }

    public function viaQueues(): array
    {
        return [
            'mail' => 'emails',
            'database' => 'default'
        ];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Keamanan Akun: Password Berhasil Diubah')
            ->view('emails.password-changed', [
                'user' => $notifiable
            ]);
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => 'password_changed',
            'title' => 'Password Berhasil Diubah.',
            'message' => 'Password akun Anda baru saja diperbarui. Akun anda kini aman.',
            'action_url' => null
        ];
    }

    public function toBroadcast($notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'type' => 'password_changed',
            'title' => 'Password Berhasil Diubah.',
            'message' => 'Password akun Anda baru saja diperbarui. Akun anda kini aman.',
            'action_url' => null
        ]);
    }
}

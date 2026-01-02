<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewUserRegisterNotification extends Notification implements ShouldQueue
{
    use Queueable;

    // public $delay = 5;
    public $newUser;

    /**
     * Create a new notification instance.
     */
    public function __construct(User $newUser)
    {
        $this->newUser = $newUser;

        $this->delay = 20;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database','mail'];
    }

    public function viaQueues(): array
    {
        return [
            'mail' => 'emails',
        ];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('New User Registration: ' . $this->newUser->username)
            ->view('emails.new-user-registered', [
                'newUser' => $this->newUser
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
            'type' => 'info',
            'title' => 'Pendaftaran User Baru',
            'message' => "User {$this->newUser->username} ({$this->newUser->email}) baru saja mendaftar.",
            'action_url' => '/admin/users',
            'users_id' => $this->newUser->id
        ];
    }
}

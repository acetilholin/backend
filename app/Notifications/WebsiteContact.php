<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\HtmlString;

class WebsiteContact extends Notification
{
    use Queueable;

    public $email, $fullname, $message;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($fullname, $email, $message)
    {
        $this->email = $email;
        $this->fullname = $fullname;
        $this->message = $message;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->from('no-reply@ogrevanjejanjic.si')
            ->subject('Sporočilo od ' . $this->fullname)
            ->line('Iz spletne strani' . env('WEBSITE_URL') . ' je bilo poslano sporočilo:')
            ->line(new HtmlString('<div class="text-center">Sporočilo: <b>'.$this->message.'</b></div>'))
            ->replyTo($this->email);
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}

<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\HtmlString;

class User extends Notification
{
    use Queueable;

    public $email, $password;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($email, $password)
    {
        $this->email = $email;
        $this->password = $password;
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
            ->subject('Nov uporabnik')
            ->line('Ustvarjen je nov uporabnik s podatki')
            ->line(new HtmlString('<div class="text-center">Email: <b>'.$this->email.'</b></div>'))
            ->line(new HtmlString('<div class="text-center">Geslo: <b>'.$this->password.'</b></div>'))
            ->line(new HtmlString('<br><div class="text-center text-custom-red">Geslo spremenite ob prvi prijavi!</div>'))
            ->action(Lang::get('Prijava'), env('LOGIN_URL'));
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

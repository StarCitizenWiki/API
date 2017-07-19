<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Auth\User;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * Class UserRegistered
 *
 * @package App\Mail
 */
class UserRegistered extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $user;
    private $password;

    /**
     * Create a new message instance.
     *
     * @param User   $user     User Object
     * @param String $password User Password
     */
    public function __construct(User $user, String $password)
    {
        $this->user = $user;
        $this->password = $password;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $this->subject('Star Citizen Wiki API - Account');

        return $this->markdown('mail.registered')->with('password', $this->password);
    }
}

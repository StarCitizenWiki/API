<?php declare(strict_types = 1);

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Auth\User;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * Class UserRegistered
 */
class UserRegistered extends Mailable implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    public $user;

    /**
     * Create a new message instance.
     *
     * @param \Illuminate\Foundation\Auth\User $user User Object
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $this->subject('Star Citizen Wiki API - Account');

        return $this->markdown('emails.registered');
    }
}

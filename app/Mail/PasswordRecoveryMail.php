<?php
// app/Mail/PasswordRecoveryMail.php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PasswordRecoveryMail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $recover_url;

    public function __construct(User $user, $recover_url)
    {
        $this->user = $user;
        $this->recover_url = $recover_url;
    }

    public function build()
    {
        return $this->subject('Password Recovery')
                    ->view('password_recovery')
                    ->with([
                        'username' => $this->user->username,
                        'recover_url' => $this->recover_url,
                    ]);
    }
}

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
    public $verification_code;

    public function __construct(User $user, $verification_code)
    {
        $this->user = $user;
        $this->verification_code = $verification_code;
    }

    public function build()
    {
        return $this->subject('Password Recovery')
                    ->view('password_recovery')
                    ->with([
                        'username' => $this->user->username,
                        'verification_code' => $this->verification_code,
                    ]);
    }
}

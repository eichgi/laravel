<?php

namespace App\Http\Traits;

use Illuminate\Support\Facades\Mail;

trait Notifiable
{
    public function notifyToAdmin($content)
    {
        Mail::raw($content, function ($message) {
            $message->subject('Unavailable Service')->to('admin@platform.com');
        });
    }
}

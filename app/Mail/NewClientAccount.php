<?php

namespace App\Mail;

use App\Models\ClientUser;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class NewClientAccount extends Mailable
{
    use Queueable, SerializesModels;

    public $client;
    public $password;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(ClientUser $client, string $password)
    {
        $this->client = $client;
        $this->password = $password;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('Sua nova conta foi criada!')
                    ->markdown('emails.new_client_account');
    }
}

<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendEmail extends Mailable
{
    use Queueable, SerializesModels;

    protected $email_subject;
    protected $email_body;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($email_subject, $email_body)
    {
        $this->email_subject = $email_subject;
        $this->email_body = $email_body;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $email_body = $this->email_body;
        $data = array("body"=>$email_body);
        return $this->subject($this->email_subject)->view('emails.email')->with($data);
    }
}

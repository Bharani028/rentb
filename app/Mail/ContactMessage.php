<?php
// app/Mail/ContactMessage.php
namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ContactMessage extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $name,
        public string $email,
        public string $subjectLine,
        public string $bodyText
    ) {}

    public function build()
    {
        return $this->subject('[Contact] '.$this->subjectLine)
            ->replyTo($this->email, $this->name)
            ->markdown('emails.contact', [
                'name'   => $this->name,
                'email'  => $this->email,
                'subject'=> $this->subjectLine,
                'body'   => $this->bodyText,
            ]);
    }
}

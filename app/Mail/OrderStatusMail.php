<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OrderStatusMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    /**
     * @param  array<string, mixed>  $payload
     */
    public function __construct(public array $payload)
    {
    }

    public function build(): self
    {
        return $this
            ->subject((string) ($this->payload['subject'] ?? 'Cap nhat don hang'))
            ->view('emails.orders.status');
    }
}

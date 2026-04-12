<?php

namespace App\Jobs;

use App\Services\OrderEmailService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\SerializesModels;

class SendOrderStatusEmailJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 3;

    public int $timeout = 120;

    public function __construct(
        public int $orderId,
        public string $type
    ) {
        $this->onConnection('database');
        $this->onQueue('emails');
    }

    public function handle(OrderEmailService $orderEmailService): void
    {
        $orderEmailService->sendOrderStatusEmailNow($this->orderId, $this->type);
    }
}

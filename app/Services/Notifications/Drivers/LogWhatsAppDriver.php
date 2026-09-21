<?php

declare(strict_types=1);

namespace App\Services\Notifications\Drivers;

use App\Services\Notifications\WhatsAppDriver;
use Illuminate\Support\Facades\Log;

final class LogWhatsAppDriver implements WhatsAppDriver
{
    public function send(string $to, string $message): void
    {
        Log::info('WhatsApp (not sent — no provider configured)', ['to' => $to, 'message' => $message]);
    }

    public function name(): string
    {
        return 'log';
    }
}

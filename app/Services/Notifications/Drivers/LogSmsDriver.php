<?php

declare(strict_types=1);

namespace App\Services\Notifications\Drivers;

use App\Services\Notifications\SmsDriver;
use Illuminate\Support\Facades\Log;

/**
 * Writes the message to the log instead of sending it.
 *
 * The default until a real provider is configured, so a system with no SMS
 * account still behaves correctly end to end — and so nobody's phone is billed by
 * a staging environment.
 */
final class LogSmsDriver implements SmsDriver
{
    public function send(string $to, string $message): void
    {
        Log::channel(config('logging.default'))->info('SMS (not sent — no provider configured)', [
            'to' => $to,
            'message' => $message,
        ]);
    }

    public function name(): string
    {
        return 'log';
    }
}

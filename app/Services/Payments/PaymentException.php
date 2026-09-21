<?php

declare(strict_types=1);

namespace App\Services\Payments;

use RuntimeException;

/** A gateway refused, or could not be reached. Always recorded against the order. */
final class PaymentException extends RuntimeException {}

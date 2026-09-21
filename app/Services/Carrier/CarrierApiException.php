<?php

declare(strict_types=1);

namespace App\Services\Carrier;

use RuntimeException;

/**
 * A carrier could not be reached, or answered with something unusable.
 *
 * Always caught by the polling job and written to carrier_sync_logs: one
 * carrier being down must never stop the others syncing, and must never change
 * a customer's status.
 */
final class CarrierApiException extends RuntimeException {}

<?php

declare(strict_types=1);

namespace App\Services\Notifications;

use RuntimeException;

/**
 * A message could not be delivered.
 *
 * Caught by the notification layer and logged: a customer's status must still be
 * correct in the database and on the tracking page even when their SMS bounced.
 */
final class NotificationDeliveryException extends RuntimeException {}

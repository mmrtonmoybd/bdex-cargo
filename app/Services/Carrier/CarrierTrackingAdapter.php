<?php

declare(strict_types=1);

namespace App\Services\Carrier;

use App\Enums\NormalizedStatus;

/**
 * The whole contract between BD-EX and a carrier.
 *
 * One method, because one question is being asked: where is this consignment?
 * Each implementation knows its carrier's HTTP shape and its status vocabulary,
 * and translates that vocabulary into {@see NormalizedStatus} so
 * nothing downstream ever sees a carrier's own wording.
 *
 * Adding a carrier nobody has coded against means writing one class against this
 * interface. That is the one genuinely unavoidable piece of PHP in the whole
 * integration story — a wire protocol cannot be invented from admin UI clicks.
 * Everything afterwards (credentials, activation, which carrier a consignment
 * belongs to) is data.
 */
interface CarrierTrackingAdapter
{
    /**
     * @throws CarrierApiException when the carrier cannot be reached or its answer
     *                             cannot be understood. The caller logs it.
     */
    public function fetchStatus(string $carrierTrackingNumber): NormalizedTrackingResult;
}

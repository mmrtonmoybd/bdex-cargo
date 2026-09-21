<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Compliance\ProhibitedItemScanner;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Live prohibited-items check for the booking wizard.
 *
 * Reports matches so the customer can fix the packing list; it never blocks the
 * booking, because a keyword is a hint and a human decides.
 */
final class ValidateItemsController extends Controller
{
    public function __invoke(Request $request, ProhibitedItemScanner $scanner): JsonResponse
    {
        $data = $request->validate([
            'text' => ['required', 'string', 'max:5000'],
        ]);

        $matched = $scanner->scanAll([$data['text']]);

        return response()->json([
            'flagged' => $matched !== [],
            'matched_terms' => $matched,
            'message' => $matched === []
                ? null
                : 'These may not be shipped: '.implode(', ', $matched).'. Remove them from the box and the packing list.',
        ]);
    }
}

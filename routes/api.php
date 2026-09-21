<?php

declare(strict_types=1);

use App\Http\Controllers\Api\BookingEnquiryController;
use App\Http\Controllers\Api\QuotePreviewController;
use App\Http\Controllers\Api\TrackingLookupController;
use Illuminate\Support\Facades\Route;

/*
 * The public site's own endpoints. No auth — they expose nothing a visitor
 * cannot already see — but rate limited, because they are the calculator's and
 * the tracking box's only route to the server.
 */
Route::middleware('throttle:60,1')->group(function (): void {
    Route::post('/quote/preview', QuotePreviewController::class)->name('api.quote.preview');
    Route::post('/booking', BookingEnquiryController::class)->name('api.booking.store');

    Route::get('/track', fn () => response()->json(['found' => false], 404))->name('api.track.index');
    Route::get('/track/{reference}', TrackingLookupController::class)->name('api.track.show');
});

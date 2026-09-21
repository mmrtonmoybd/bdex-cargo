<?php

declare(strict_types=1);

use App\Http\Controllers\Api\BookingController;
use App\Http\Controllers\Api\QuotePreviewController;
use App\Http\Controllers\Api\TrackingLookupController;
use App\Http\Controllers\Api\ValidateItemsController;
use Illuminate\Support\Facades\Route;

/*
 * The public site's own endpoints. No auth — they expose nothing a visitor
 * cannot already see — but rate limited, because they are the calculator's and
 * the tracking box's only route to the server.
 */
Route::middleware('throttle:60,1')->group(function (): void {
    Route::post('/quote/preview', QuotePreviewController::class)->name('api.quote.preview');
    Route::post('/booking', BookingController::class)->name('api.booking.store');

    // Live packing-list check while the customer types, so a prohibited item is
    // caught before the parcel is packed rather than at customs.
    Route::post('/items/validate', ValidateItemsController::class)->name('api.items.validate');

    Route::get('/track', fn () => response()->json(['found' => false], 404))->name('api.track.index');
    Route::get('/track/{reference}', TrackingLookupController::class)->name('api.track.show');
});

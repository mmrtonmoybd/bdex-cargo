<?php

declare(strict_types=1);

use App\Http\Controllers\DocumentDownloadController;
use App\Http\Controllers\Public\HomeController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');

// The design is a single page; these keep older links working.
Route::redirect('/quote', '/#calculator')->name('quote');
Route::redirect('/quote/tv', '/#rates')->name('quote.tv');
Route::redirect('/book', '/#booking')->name('book');
Route::redirect('/track', '/#top')->name('track');

// Generated paperwork. Staff go through the panel's own permissions; customers
// get a signed link to their own invoice and nothing else.
Route::get('/documents/{document}', [DocumentDownloadController::class, 'document'])
    ->middleware('auth')
    ->name('documents.download');

Route::get('/invoices/{invoice}/download', [DocumentDownloadController::class, 'invoice'])
    ->name('invoices.download');

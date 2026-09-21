<?php

declare(strict_types=1);

use App\Http\Controllers\Public\HomeController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');

// The design is a single page; these keep older links working.
Route::redirect('/quote', '/#calculator')->name('quote');
Route::redirect('/quote/tv', '/#rates')->name('quote.tv');
Route::redirect('/book', '/#booking')->name('book');
Route::redirect('/track', '/#top')->name('track');

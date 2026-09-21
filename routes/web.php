<?php

declare(strict_types=1);

use App\Http\Controllers\Public\HomeController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');

Route::view('/quote', 'public.quote')->name('quote');
Route::view('/quote/tv', 'public.quote-tv')->name('quote.tv');

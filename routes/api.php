<?php

use App\Http\Controllers\NytBooksApiController;
use Illuminate\Support\Facades\Route;

Route::group([
    'prefix' => '/books/v3',
    'as' => 'nyt.books.v3.',
], function () {
    Route::get('/list/bestsellers/history', [NytBooksApiController::class, 'listBestSellersHistory'])
        ->name('list.bestsellers.history');
})->middleware('auth:sanctum');

<?php

use App\Http\Controllers\PageController;
use App\Http\Middleware\SetLocale;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/it/', 301);

Route::prefix('{locale}')
    ->where(['locale' => 'it|en'])
    ->middleware(SetLocale::class)
    ->group(function () {
        Route::get('/{slug?}', [PageController::class, 'show'])->name('page');
    });

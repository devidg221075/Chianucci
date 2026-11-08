<?php

use App\Http\Controllers\CatalogController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\PageController;
use App\Http\Middleware\SetLocale;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/it/', 301);

foreach (config('site.locales') as $locale) {
    Route::middleware(SetLocale::class)->group(function () use ($locale) {
        Route::get("/$locale/".config("site.catalog_slug.$locale"), [CatalogController::class, 'index'])
            ->defaults('locale', $locale)->name("catalog.$locale");
        Route::post("/$locale/".config("site.pages.contatti.$locale"), [ContactController::class, 'store'])
            ->defaults('locale', $locale)->middleware('throttle:5,1')->name("contact.$locale");
    });
}

Route::prefix('{locale}')
    ->where(['locale' => 'it|en'])
    ->middleware(SetLocale::class)
    ->group(function () {
        Route::get('/prodotto/{slug}', [CatalogController::class, 'show'])->name('product');
        Route::get('/{slug?}', [PageController::class, 'show'])->name('page');
    });

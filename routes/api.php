<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\SearchController;

Route::prefix('v1')->group(function () {
    Route::get('/search', [SearchController::class, 'search_auto_complete'])->name('search.autocomplete');
    Route::post('/search/select', [SearchController::class, 'select_suggestion'])->name('search.select');
    Route::get('/search/usage', [SearchController::class, 'usage_summary'])->name('search.usage');
});

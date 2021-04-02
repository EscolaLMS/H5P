<?php

use Illuminate\Support\Facades\Route;
use EscolaLms\HeadlessH5P\Http\Controllers\LibraryApiController;

Route::group(['middleware' => ['api'], 'prefix' => 'api/hh5p'], function () {
    Route::get('/hi', function () {
        return 'Hello World';
    });
    Route::post('library', [LibraryApiController::class, 'store'])->name('hh5p.library.store');
});

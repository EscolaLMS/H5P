<?php

use EscolaLms\HeadlessH5P\Http\Controllers\ContentApiController;
use EscolaLms\HeadlessH5P\Http\Controllers\EditorApiController;
use EscolaLms\HeadlessH5P\Http\Controllers\FilesApiController;
use EscolaLms\HeadlessH5P\Http\Controllers\LibraryApiController;
use EscolaLms\HeadlessH5P\Http\Middleware\QueryToken;

use Illuminate\Support\Facades\Route;

Route::group(['middleware' => ['api'], 'prefix' => 'api'], function () {
    Route::group(['prefix' => 'admin/hh5p'], function () {
        Route::post('library', [LibraryApiController::class, 'store'])->name('hh5p.library.store');
        Route::get('library', [LibraryApiController::class, 'index'])->name('hh5p.library.list');
        Route::delete('library/{id}', [LibraryApiController::class, 'destroy'])->name('hh5p.library.delete');
        Route::get('editor', EditorApiController::class)->name('hh5p.editor.settings');
        Route::get('editor/{id}', EditorApiController::class)->name('hh5p.editor.contentSettings');
        Route::get('libraries', [LibraryApiController::class, 'libraries'])->name('hh5p.library.admin.get.libraries');
        Route::post('libraries', [LibraryApiController::class, 'libraries'])->name('hh5p.library.admin.post.libraries');
        Route::post('content/upload', [ContentApiController::class, 'upload'])->name('hh5p.content.upload');
        Route::post('content', [ContentApiController::class, 'store'])->name('hh5p.content.store');
        Route::post('content/{id}', [ContentApiController::class, 'update'])->name('hh5p.content.update');
        Route::delete('content/{id}', [ContentApiController::class, 'destroy'])->name('hh5p.content.destroy');
        Route::get('content', [ContentApiController::class, 'index'])->name('hh5p.content.index');
        Route::get('content/{id}/export', [ContentApiController::class, 'download'])->name('hh5p.content.export');
        Route::get('content/{id}', [ContentApiController::class, 'show'])->name('hh5p.content.admin.show');
        Route::post('files', FilesApiController::class)->name('hh5p.files.upload')->middleware('signed');;
        Route::delete('unused', [ContentApiController::class, 'deleteUnused'])->name('hh5p.content.deleteUnused');
    });

    Route::group(['prefix' => 'hh5p'], function () {
        Route::get('content/{uuid}', [ContentApiController::class, 'frontShow'])->name('hh5p.content.show');

        Route::get('/', function () {
            return 'Hello World';
        })->name('hh5p.index'); // DO not remove this is needed as prefix for editor ajax calls
    });
});

Route::group(['middleware' => [QueryToken::class], 'prefix' => 'api/hh5p'], function () {
    Route::get('libraries', [LibraryApiController::class, 'libraries'])->name('hh5p.library.get.libraries');
    Route::post('libraries', [LibraryApiController::class, 'libraries'])->name('hh5p.library.post.libraries');
    Route::post('files/{nonce}', FilesApiController::class)->name('hh5p.files.upload.nonce');

    Route::get('content-type-cache', [LibraryApiController::class, 'contentTypeCache'])->name('hh5p.library.content-type-cache');
    Route::get('content-hub-metadata-cache', [LibraryApiController::class, 'contentHubMetadata'])->name('hh5p.library.content-hub-metadata-cache');

    Route::post('library-install', [LibraryApiController::class, 'libraryInstall'])->name('hh5p.ajax.library-install');
    Route::post('library-upload', [LibraryApiController::class, 'libraryUpload'])->name('hh5p.ajax.library-upload');

    Route::post('filter', [LibraryApiController::class, 'filter'])->name('hh5p.ajax.filter');
});

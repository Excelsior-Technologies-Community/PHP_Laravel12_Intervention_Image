<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ImageController;

Route::get('/', function () {
    return redirect()->route('image.index');
});

Route::get(
    'image-upload',
    [ImageController::class, 'index']
)->name('image.index');

Route::post(
    'image-upload',
    [ImageController::class, 'store']
)->name('image.store');

Route::get(
    'image-variants-zip/{filename}',
    [ImageController::class, 'downloadZip']
)->name('image.download-zip');
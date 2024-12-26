<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\Filecontroller;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::controller(Filecontroller::class)
    ->middleware(['auth', 'verified'])->group(function () {
        Route::get('/my-files/{folder?}', 'myFiles')
            ->where('folder', '(.*)')
            ->name('myFiles');
        Route::get('/starred-files', 'starredFiles')->name('starredFiles');
        Route::get('/trash', 'trash')->name('trash');
        Route::post('/folder/create', 'createFolder')->name('folder.create');
        Route::post('/file', 'store')->name('file.store');
        Route::delete('/file', 'destroy')->name('file.delete');
        Route::delete('/file/delete_forever', 'delete_forever')->name('file.delete_forever');
        Route::post('/file/restore', 'restore')->name('file.restore');
        Route::get('/file/download', 'download')->name('file.download');
        Route::post('/file/add-to-favorites', 'addtoFavorites')->name('file.addtoFavorites');
    });
Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';

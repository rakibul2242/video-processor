<?php

use App\Http\Controllers\ChunkVideoController;
use App\Http\Controllers\VideoController;
use App\Livewire\VideoProcess;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;
use Livewire\Volt\Volt;

// Route::get('/', function () {
//     return view('welcome');
// })->name('home');

// Route::view('dashboard', 'dashboard')
//     ->middleware(['auth', 'verified'])
//     ->name('dashboard');

Route::get('test', VideoProcess::class)->withoutMiddleware(['auth', 'verified'])->name('test');

Route::get('/', [ChunkVideoController::class, 'showUploadForm']);
Route::post('/', [ChunkVideoController::class, 'chunkUpload'])->name('video.upload');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [VideoController::class, 'showUploadForm'])->name('dashboard');
    Route::post('/dashboard', [VideoController::class, 'chunkUpload']);
});

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Volt::route('settings/profile', 'settings.profile')->name('profile.edit');
    Volt::route('settings/password', 'settings.password')->name('user-password.edit');
    Volt::route('settings/appearance', 'settings.appearance')->name('appearance.edit');

    Volt::route('settings/two-factor', 'settings.two-factor')
        ->middleware(
            when(
                Features::canManageTwoFactorAuthentication()
                && Features::optionEnabled(Features::twoFactorAuthentication(), 'confirmPassword'),
                ['password.confirm'],
                [],
            ),
        )
        ->name('two-factor.show');
});

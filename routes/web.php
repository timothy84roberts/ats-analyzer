<?php

use App\Http\Controllers\AtsAnalysisController;
use App\Http\Controllers\CountryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\JobApplicationController;
use App\Http\Controllers\JobApplicationNoteController;
use App\Http\Controllers\PlatformController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SettingsController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/dashboard');

Route::get('/home', [HomeController::class, 'home'])->name('home.index');
Route::get('/about', [HomeController::class, 'about'])->name('about.index');
Route::get('/contact', [HomeController::class, 'contact'])->name('contact.index');
Route::get('/success', [HomeController::class, 'success'])->name('contact.success');
Route::post('/send_contact', [HomeController::class, 'sendContact'])->name('contact.send');

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    Route::get('applications/{application}/resume', [JobApplicationController::class, 'showResume'])
        ->name('applications.resume');
    Route::post('applications/{application}/notes', [JobApplicationNoteController::class, 'store'])
        ->name('applications.notes.store');
    Route::resource('applications', JobApplicationController::class);

    Route::get('/settings', SettingsController::class)->name('settings.index');

    Route::resource('settings/countries', CountryController::class)->only(['index', 'create', 'store', 'destroy']);
    Route::resource('settings/platforms', PlatformController::class)->except(['show']);

    Route::get('/ats-scanner', [AtsAnalysisController::class, 'index'])->name('ats-scanner.index');
    Route::post('/ats-scanner/analyze', [AtsAnalysisController::class, 'analyze'])->name('ats-scanner.analyze');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';

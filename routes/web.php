<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ListingController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/explore', [HomeController::class, 'explore'])->name('explore');
Route::get('/listings/{slug}', [HomeController::class, 'show'])->name('listing.show');
Route::get('/creators', [HomeController::class, 'creators'])->name('creators');
Route::get('/creators/{slug}', [HomeController::class, 'creator'])->name('creator.show');
Route::get('/become-a-creator', [HomeController::class, 'becomeCreator'])->name('become-creator');
Route::get('/dashboard', fn () => redirect()->route('admin.dashboard'))->middleware(['auth', 'verified'])->name('dashboard');
Route::middleware(['auth', 'can:admin'])->prefix('admin')->name('admin.')->group(function () {
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
Route::get('/listings/creator/{id}', [ListingController::class, 'creator'])->name('listings.creator');
Route::resource('listings', ListingController::class)->except('show');
});
Route::middleware('auth')->group(function () {
Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});
require __DIR__.'/auth.php';

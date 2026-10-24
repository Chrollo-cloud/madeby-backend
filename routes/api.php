<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CreatorController;
use App\Http\Controllers\Api\ListingController;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('login', [AuthController::class, 'login'])->middleware('throttle:5,1');
Route::get('categories', fn () => response()->json(['data' => Category::orderBy('name')->get(['id', 'name'])]));
foreach (['products', 'commissions', 'listings'] as $resource) {
    Route::get($resource, [ListingController::class, 'index'])->name("api.{$resource}.index");
    Route::get("{$resource}/{listing:slug}", [ListingController::class, 'show'])->name("api.{$resource}.show");
}
Route::get('creators', [CreatorController::class, 'index']);
Route::get('creators/{creator:slug}', [CreatorController::class, 'show']);
Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('user', fn (Request $request) => response()->json(['data' => $request->user()]));
    Route::post('logout', [AuthController::class, 'logout']);
    Route::middleware('can:admin')->group(function () {
        Route::post('listings', [ListingController::class, 'store']);
        Route::match(['put', 'patch'], 'listings/{listing:slug}', [ListingController::class, 'update']);
        Route::delete('listings/{listing:slug}', [ListingController::class, 'destroy']);
        Route::post('creators', [CreatorController::class, 'store']);
        Route::match(['put', 'patch'], 'creators/{creator:slug}', [CreatorController::class, 'update']);
        Route::delete('creators/{creator:slug}', [CreatorController::class, 'destroy']);
    });
});

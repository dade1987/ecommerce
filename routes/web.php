<?php

use App\Http\Controllers\CategoryController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PageController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use Z3d0X\FilamentFabricator\Facades\FilamentFabricator;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

require __DIR__ . '/auth.php';

/*Route::get('/', function () {
    return view('welcome');
})->name('home');*/

Route::redirect('/', '/categories')->name('home');

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::resource('{container0}/{item0?}/{container1?}/{item1?}/{container2?}/{item2?}', PageController::class);

    //Route::domain('{team}.example.com')->group(function () {
    /*Route::prefix('{team}')->group(function () {
        Route::resource('{container0}/{item0?}/{container1?}/{item2?}', PageController::class);
    });*/
});

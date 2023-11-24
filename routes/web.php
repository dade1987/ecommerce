<?php

use App\Filament\Pages\Welcome;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PageController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\GuttenbergController;
use Z3d0X\FilamentFabricator\Resources\PageResource;
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

Route::redirect('/', '/home')->name('home');


Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');


    //Route::domain('{team}.example.com')->group(function () {
    /*Route::prefix('{team}')->group(function () {
        Route::resource('{container0}/{item0?}/{container1?}/{item2?}', PageController::class);
    });*/
});

/*Route::get('/guttenberg', function () {
    return view('guttenberg');
});

Route::get('/guttenberg-example/{id}', GuttenbergController::class);*/

Route::resource('{container0}/{item0?}/{container1?}/{item1?}/{container2?}/{item2?}', PageController::class);

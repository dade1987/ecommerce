<?php

use App\Http\Controllers\ArticleController;
use App\Http\Controllers\ImageController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TagController;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\DebugMediaController;


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

require __DIR__.'/auth.php';

/*Route::get('/', function () {
    return view('welcome');
})->name('home');*/

Route::prefix(config('curator.glide.route_path', 'curator'))
    ->get('/{path}', [DebugMediaController::class, 'show'])
    ->where('path', '.*')
    ->name('curator.media.debug');
    
Route::redirect('/', '/home')->name('home');

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

});

//Route::resource('articles', ArticleController::class);
//Route::resource('tags', TagController::class);

Route::get('{container0}/{item0?}/{container1?}/{item1?}/{container2?}/{item2?}', [PageController::class, 'index']);

//Route::get('/storage/{path}', [ImageController::class, 'show'])->where('path', '.*')->name('image.optimizer');


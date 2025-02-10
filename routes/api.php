<?php

use App\Http\Controllers\Api\ChatbotController;
use App\Http\Controllers\QuoterController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

//TO-DO: finire per separare frontend da backend
//Route::apiResource('{container0}/{item0?}/{container1?}/{item1?}/{container2?}/{item2?}/', ApiController::class);

//usare https://filamentphp.com/plugins/rupadana-api-service

Route::post('/send-message', [QuoterController::class, 'sendMessage']);
Route::post('/create-thread', [QuoterController::class, 'createThread']);
Route::post('/upload-file', [QuoterController::class, 'uploadFile']);

//TEST FUNCTION CALLING
Route::get('/products/{slug}', function (Request $request, $slug) {
    $team = App\Models\Team::where('slug', $slug)->firstOrFail();
    $query = App\Models\Product::where('team_id', $team->id);

    if ($request->has('name')) {
        $query->where('name', 'like', '%'.$request->input('name').'%');
    }

    return response()->json($query->get());
});

Route::get('/teams/{slug}', function ($slug) {
    $team = App\Models\Team::where('slug', $slug)->firstOrFail();

    return response()->json($team);
});

Route::post('/chatbot', [ChatbotController::class, 'handleChat']);

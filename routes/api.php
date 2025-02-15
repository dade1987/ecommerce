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
Route::get('/events/{slug}', function ($slug) {
    $team = App\Models\Team::where('slug', $slug)->firstOrFail();
    $events = App\Models\Event::where('team_id', $team->id)
        ->where('name', 'Disponibile')
        ->where('starts_at', '>=', now())
        ->orderBy('starts_at', 'asc')
        ->get(['starts_at', 'ends_at', 'name', 'featured_image_id', 'description']);

    return response()->json($events);
});

Route::post('/order/{slug}', function (Request $request, $slug) {
    $team = App\Models\Team::where('slug', $slug)->firstOrFail();

    $order = new App\Models\Order();
    $order->team_id = $team->id;
    $order->delivery_date = $request->input('delivery_date');
    $order->phone = $request->input('user_phone'); // Aggiungi il numero di telefono all'ordine
    $order->save();

    $productIds = $request->input('product_ids', []);
    $order->products()->attach($productIds);

    return response()->json([
        'order_id' => $order->id,
        'message' => 'Ordine creato con successo',
    ]);
});

Route::post('/customers', function (Request $request) {
    $customer = new App\Models\Customer();
    $customer->name = $request->input('name');
    $customer->phone = $request->input('phone');
    $customer->email = $request->input('email');
    $customer->save();

    return response()->json([
        'customer_id' => $customer->id,
        'message' => 'Cliente creato con successo',
    ]);
});

Route::get('/faqs', function (Request $request) {
    $query = $request->input('query');

    $faqs = App\Models\Faq::whereRaw('MATCH(question, answer) AGAINST(? IN NATURAL LANGUAGE MODE)', [$query])
        ->get(['question', 'answer']);

    return response()->json($faqs);
});

// Esempio di domanda curl:
// curl -X GET "https://cavalliniservice.com/api/faqs?query=la%20tua%20domanda"

Route::post('/calzaturiero/extract-product-info', [App\Http\Controllers\Api\CalzaturieroController::class, 'extractProductInfo']);

// Per fare una prova in curl, usa il seguente comando:
// curl -X POST -F "file=@/path/to/your/file.pdf" https://cavalliniservice.com/api/calzaturiero/extract-product-info
// Assicurati di sostituire "/path/to/your/file.pdf" con il percorso effettivo del file PDF che vuoi caricare.

Route::post('/chatbot', [ChatbotController::class, 'handleChat']);

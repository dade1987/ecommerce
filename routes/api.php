<?php

use App\Http\Controllers\Api\CalzaturieroController;
use App\Http\Controllers\Api\ChatbotController;
use App\Http\Controllers\Api\SommelierApiController;
use App\Http\Controllers\Api\RealtimeChatController;
use App\Http\Controllers\Api\RealtimeChatWebsiteController;
use App\Http\Controllers\Api\NeuronWebsiteStreamController;
use App\Http\Controllers\Api\TtsController;
use App\Http\Controllers\Api\SommelierChatbotController;
use App\Http\Controllers\Api\ChatTranscriptController;
use App\Http\Controllers\QuoterController;
use App\Http\Controllers\Api\TestController;
use App\Http\Controllers\Api\StaticController;
use App\Http\Controllers\Api\OperatorFeedbackController;
use App\Models\ProductionPhase;
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
    // Validazione input
    $validated = $request->validate([
        'delivery_date' => 'required|date|after:now',
        'user_phone' => 'required|string|max:20|regex:/^[0-9\-\+\(\)\s]+$/',
        'product_ids' => 'nullable|array',
        'product_ids.*' => 'integer|exists:products,id',
    ]);

    $team = App\Models\Team::where('slug', $slug)->firstOrFail();

    // Verifica che i prodotti appartengano al team
    if (!empty($validated['product_ids'])) {
        $validProducts = App\Models\Product::where('team_id', $team->id)
            ->whereIn('id', $validated['product_ids'])
            ->pluck('id')
            ->toArray();

        if (count($validProducts) !== count($validated['product_ids'])) {
            return response()->json([
                'error' => 'Alcuni prodotti non sono validi per questo team',
            ], 422);
        }
    }

    $order = new App\Models\Order();
    $order->team_id = $team->id;
    $order->delivery_date = $validated['delivery_date'];
    $order->phone = $validated['user_phone'];
    $order->save();

    if (!empty($validated['product_ids'])) {
        $order->products()->attach($validated['product_ids']);
    }

    return response()->json([
        'order_id' => $order->id,
        'message' => 'Ordine creato con successo',
    ], 201);
})->middleware('throttle:10,1'); // Max 10 richieste al minuto per IP

Route::post('/customers', function (Request $request) {
    // Validazione input per prevenire injection e dati malformati
    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'phone' => 'required|string|max:20|regex:/^[0-9\-\+\(\)\s]+$/',
        'email' => 'required|email:rfc,dns|max:255',
    ]);

    // Verifica se esiste già un cliente con questa email
    $existingCustomer = App\Models\Customer::where('email', $validated['email'])->first();
    if ($existingCustomer) {
        return response()->json([
            'customer_id' => $existingCustomer->id,
            'message' => 'Cliente già esistente',
        ], 200);
    }

    $customer = App\Models\Customer::create($validated);

    return response()->json([
        'customer_id' => $customer->id,
        'message' => 'Cliente creato con successo',
    ], 201);
})->middleware('throttle:10,1'); // Max 10 richieste al minuto per IP

Route::get('/faqs/{teamslug}', function (Request $request, $teamslug) {
    $query = $request->input('query');
    $team = App\Models\Team::where('slug', $teamslug)->firstOrFail();

    $faqs = App\Models\Faq::where('team_id', $team->id)
        ->whereRaw('MATCH(question, answer) AGAINST(? IN NATURAL LANGUAGE MODE)', [$query])
        ->get(['question', 'answer']);

    return response()->json($faqs);
});

Route::get('/visit/{uuid}', function ($uuid) {
    $customer = App\Models\Customer::where('uuid', $uuid)->first();
    if (! $customer) {
        return response()->json(['message' => 'Cliente non trovato'], 404);
    }

    $customer->status = 'in_negotiation';
    $customer->save();

    return response()->json([
        'customer_id' => $customer->id,
        'message' => 'Cliente aggiornato con successo',
    ]);
});

// Esempio di domanda curl:
// curl -X GET "https://cavalliniservice.com/api/faqs?query=la%20tua%20domanda"

Route::post('/calzaturiero/process-order/{slug}', [CalzaturieroController::class, 'processCustomerOrder']);
//Route::post('/calzaturiero/test-process-order/{slug}', [CalzaturieroController::class, 'testProcessCustomerOrder']);
// Per fare una prova in curl, usa il seguente comando:
// curl -X POST -F "file=@/path/to/your/file.pdf" https://cavalliniservice.com/api/calzaturiero/extract-product-info
// Assicurati di sostituire "/path/to/your/file.pdf" con il percorso effettivo del file PDF che vuoi caricare.

// Chatbot endpoints con rate limiting per prevenire abusi e costi OpenAI eccessivi
Route::post('/chatbot', [ChatbotController::class, 'handleChat'])
    ->middleware('throttle:30,1'); // Max 30 richieste al minuto per IP
Route::get('/chatbot/stream', [RealtimeChatController::class, 'stream'])
    ->middleware('throttle:30,1');
Route::get('/chatbot/website-stream', [RealtimeChatWebsiteController::class, 'websiteStream'])
    ->middleware('throttle:30,1');
Route::get('/chatbot/neuron-website-stream', [NeuronWebsiteStreamController::class, 'stream'])
    ->middleware('throttle:30,1');
Route::post('/tts', [TtsController::class, 'synthesize'])
    ->middleware('throttle:20,1'); // TTS più costoso, limite più basso
Route::post('/chatbot/email-transcript', [ChatTranscriptController::class, 'emailTranscript'])
    ->middleware('throttle:5,1'); // Invio email limitato
Route::get('/chatbot/history', [ChatTranscriptController::class, 'history'])
    ->middleware('throttle:60,1');

// Endpoint per servire immagini/risorse statiche con CORS
Route::get('/static/{filename}', function (Request $request, $filename) {
    // Validazione nome file - solo caratteri sicuri
    if (!preg_match('/^[a-zA-Z0-9\-_\.\/]+$/', $filename)) {
        return response()->json(['error' => 'Invalid filename format'], 400);
    }

    // Blocca path traversal esplicito
    if (str_contains($filename, '..') || str_contains($filename, '//')) {
        return response()->json(['error' => 'Invalid path'], 400);
    }

    // Whitelist di estensioni permesse
    $allowedExtensions = ['glb', 'gltf', 'png', 'jpg', 'jpeg', 'webp', 'gif', 'svg'];
    $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

    if (!in_array($extension, $allowedExtensions)) {
        return response()->json(['error' => 'File type not allowed'], 403);
    }

    // Costruisci il path dalla cartella public
    $basePath = realpath(public_path());
    $filePath = realpath(public_path($filename));

    // Verifica che il file esista e sia dentro public_path (protezione path traversal)
    if (!$filePath || !$basePath) {
        return response()->json(['error' => 'File not found'], 404);
    }

    if (!str_starts_with($filePath, $basePath)) {
        return response()->json(['error' => 'Access denied'], 403);
    }

    if (!file_exists($filePath) || !is_file($filePath)) {
        return response()->json(['error' => 'File not found'], 404);
    }

    // Serve il file (CORS headers aggiunti automaticamente dal middleware)
    return response()->file($filePath);
})->where('filename', '[a-zA-Z0-9\-_\.\/]+');

// CORS è gestito dal config/cors.php e dal middleware \Illuminate\Http\Middleware\HandleCors

// Endpoint per il chatbot sommelier
Route::post('sommelier/chat', [SommelierChatbotController::class, 'handleChat']);
Route::post('sommelier/thread', [SommelierChatbotController::class, 'createThread']);

// Endpoint per le API dei vini e dei relativi servizi
Route::get('wines', [SommelierApiController::class, 'getWines']);
Route::get('pairing', [SommelierApiController::class, 'getPairing']);
Route::get('trivia', [SommelierApiController::class, 'getTrivia']);
Route::get('events', [SommelierApiController::class, 'getEvents']);

Route::get('/avatar/{teamslug}', function ($teamslug) {
    $team = App\Models\Team::where('slug', $teamslug)->firstOrFail();

    return response()->json([
        'team' => [
            'name' => $team->name,
            'logo' => $team->logo,
        ],
    ]);
});

Route::get('/test', TestController::class);
Route::get('/static', StaticController::class);

/*
|--------------------------------------------------------------------------
| Operator Feedback API Routes
|--------------------------------------------------------------------------
|
| API endpoints per gestire i feedback degli operatori.
| Questi endpoint sono progettati per essere utilizzati da sistemi esterni
| come Cursor per leggere e aggiornare lo stato delle richieste operative.
|
*/

// Endpoint di test per verificare che l'API funzioni
Route::get('/operator-feedback/ping', [OperatorFeedbackController::class, 'ping']);

// Endpoint per ottenere feedback filtrati (es. ?status=pending)
Route::get('/operator-feedback', [OperatorFeedbackController::class, 'index']);

// Endpoint per ottenere un singolo feedback
Route::get('/operator-feedback/{id}', [OperatorFeedbackController::class, 'show']);

// Endpoint per creare un nuovo feedback
Route::post('/operator-feedback', [OperatorFeedbackController::class, 'store']);

// Endpoint per aggiornare un feedback completo
Route::put('/operator-feedback/{id}', [OperatorFeedbackController::class, 'update']);

// Endpoint semplificato per aggiornare solo lo status
Route::post('/operator-feedback/{id}/status', [OperatorFeedbackController::class, 'updateStatus']);

// Endpoint per eliminare un feedback
Route::delete('/operator-feedback/{id}', [OperatorFeedbackController::class, 'destroy']);

/*
|--------------------------------------------------------------------------
| Esempi di utilizzo API OperatorFeedback
|--------------------------------------------------------------------------
|
| GET /api/operator-feedback?status=pending
| - Ottiene tutti i feedback con status "pending"
|
| POST /api/operator-feedback/{id}/status
| - Body: {"status": "in_progress"}
| - Aggiorna lo status del feedback con ID specificato
|
| Valori status ammessi: pending, in_progress, done, rejected
|
*/

Route::get('/gantt-data', function () {
    $phases = ProductionPhase::whereNotNull('scheduled_start_time')
        ->with('workstation')
        ->get();

    $tasks = $phases->map(function ($phase) {
        return [
            'id' => 'phase_' . $phase->id,
            'name' => $phase->name,
            'start' => $phase->scheduled_start_time->format('Y-m-d'),
            'end' => $phase->scheduled_end_time->format('Y-m-d'),
            'progress' => $phase->is_completed ? 100 : 0,
            'custom_class' => 'bar-' . strtolower($phase->workstation->name ?? 'default'),
        ];
    });

    return response()->json($tasks);
})->middleware('auth:sanctum'); // Assuming authentication is needed

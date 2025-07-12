<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\OperatorFeedback;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;

/**
 * Controller API per gestire i feedback degli operatori
 * 
 * Questa API è progettata per essere utilizzata da sistemi esterni (come Cursor)
 * per leggere le richieste operative e aggiornare i loro stati.
 */
class OperatorFeedbackController extends Controller
{
    /**
     * Ottiene tutti i feedback degli operatori, filtrabili per status
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $query = OperatorFeedback::query();

        // Filtro per status se presente
        if ($request->has('status')) {
            $status = $request->input('status');
            
            // Valida che il status sia valido
            if (!in_array($status, ['pending', 'in_progress', 'done', 'rejected'])) {
                return response()->json([
                    'error' => 'Status non valido',
                    'valid_statuses' => ['pending', 'in_progress', 'done', 'rejected']
                ], 400);
            }
            
            $query->where('status', $status);
        }

        // Ordina per data di creazione (più recenti prima)
        $feedback = $query->orderBy('created_at', 'desc')->get();

        return response()->json([
            'success' => true,
            'data' => $feedback,
            'count' => $feedback->count(),
            'message' => 'Feedback recuperati con successo'
        ]);
    }

    /**
     * Mostra un singolo feedback
     * 
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        $feedback = OperatorFeedback::find($id);

        if (!$feedback) {
            return response()->json([
                'error' => 'Feedback non trovato'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $feedback,
            'message' => 'Feedback recuperato con successo'
        ]);
    }

    /**
     * Crea un nuovo feedback
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'titolo' => 'required|string|max:255',
            'descrizione' => 'required|string',
            'status' => ['sometimes', Rule::in(['pending', 'in_progress', 'done', 'rejected'])],
            'metadata' => 'sometimes|array',
        ]);

        // Imposta status default se non fornito
        if (!isset($validated['status'])) {
            $validated['status'] = 'pending';
        }

        $feedback = OperatorFeedback::create($validated);

        return response()->json([
            'success' => true,
            'data' => $feedback,
            'message' => 'Feedback creato con successo'
        ], 201);
    }

    /**
     * Aggiorna lo status di un feedback esistente
     * 
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $feedback = OperatorFeedback::find($id);

        if (!$feedback) {
            return response()->json([
                'error' => 'Feedback non trovato'
            ], 404);
        }

        // Valida solo i campi che possono essere aggiornati
        $validated = $request->validate([
            'status' => ['required', Rule::in(['pending', 'in_progress', 'done', 'rejected'])],
            'metadata' => 'sometimes|array',
        ]);

        $feedback->update($validated);

        return response()->json([
            'success' => true,
            'data' => $feedback,
            'message' => 'Feedback aggiornato con successo'
        ]);
    }

    /**
     * Aggiorna solo lo status di un feedback (metodo semplificato)
     * 
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function updateStatus(Request $request, int $id): JsonResponse
    {
        $feedback = OperatorFeedback::find($id);

        if (!$feedback) {
            return response()->json([
                'error' => 'Feedback non trovato'
            ], 404);
        }

        $validated = $request->validate([
            'status' => ['required', Rule::in(['in_progress', 'done', 'rejected'])],
        ]);

        $feedback->update([
            'status' => $validated['status']
        ]);

        return response()->json([
            'success' => true,
            'data' => $feedback,
            'message' => "Status aggiornato a: {$validated['status']}"
        ]);
    }

    /**
     * Elimina un feedback
     * 
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        $feedback = OperatorFeedback::find($id);

        if (!$feedback) {
            return response()->json([
                'error' => 'Feedback non trovato'
            ], 404);
        }

        $feedback->delete();

        return response()->json([
            'success' => true,
            'message' => 'Feedback eliminato con successo'
        ]);
    }

    /**
     * Endpoint di test per verificare la connessione API
     * 
     * @return JsonResponse
     */
    public function ping(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'API OperatorFeedback funzionante',
            'timestamp' => now()->toISOString(),
            'endpoints' => [
                'GET /api/operator-feedback' => 'Lista tutti i feedback',
                'GET /api/operator-feedback?status=pending' => 'Feedback in attesa',
                'GET /api/operator-feedback/{id}' => 'Dettaglio feedback',
                'POST /api/operator-feedback' => 'Crea nuovo feedback',
                'PUT /api/operator-feedback/{id}' => 'Aggiorna feedback completo',
                'POST /api/operator-feedback/{id}/status' => 'Aggiorna solo status',
                'DELETE /api/operator-feedback/{id}' => 'Elimina feedback',
            ]
        ]);
    }
}

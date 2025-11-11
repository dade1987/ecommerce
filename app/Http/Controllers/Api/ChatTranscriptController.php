<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Quoter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use App\Notifications\ChatTranscriptNotification;

class ChatTranscriptController extends Controller
{
    /**
     * Invia via email la trascrizione della chat per uno specifico thread_id.
     */
    public function emailTranscript(Request $request)
    {
        $email = (string) $request->input('email', '');
        $threadId = (string) $request->input('thread_id', '');

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return response()->json(['ok' => false, 'error' => 'Email non valida'], 422);
        }
        if ($threadId === '') {
            return response()->json(['ok' => false, 'error' => 'thread_id mancante'], 422);
        }

        $messages = Quoter::where('thread_id', $threadId)
            ->orderBy('created_at', 'asc')
            ->get(['role', 'content', 'created_at']);

        if ($messages->isEmpty()) {
            return response()->json(['ok' => false, 'error' => 'Nessuna conversazione trovata per questo thread'], 404);
        }

        try {
            Notification::route('mail', $email)
                ->notify(new ChatTranscriptNotification($threadId));
        } catch (\Throwable $e) {
            Log::error('Email transcript send error', ['error' => $e->getMessage(), 'thread_id' => $threadId, 'email' => $email]);
            return response()->json(['ok' => false, 'error' => 'Invio email fallito'], 500);
        }

        return response()->json(['ok' => true]);
    }
}



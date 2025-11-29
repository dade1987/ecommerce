<?php

namespace Modules\Avatar3DReact\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Modules\Avatar3DReact\Services\AzureTTSService;

class Avatar3DTTSController extends Controller
{
    public function __construct(
        private AzureTTSService $ttsService
    ) {}

    /**
     * Convert text to speech with viseme data for lip sync
     */
    public function talk(Request $request): JsonResponse
    {
        $request->validate([
            'text' => 'required|string|max:5000',
            'voice' => 'nullable|string',
        ]);

        $text = $request->input('text');
        $voice = $request->input('voice', config('avatar3d.azure.default_voice', 'it-IT-ElsaNeural'));

        try {
            $result = $this->ttsService->synthesize($text, $voice);

            return response()->json([
                'success' => true,
                'blendData' => $result['blendData'],
                'filename' => $result['filename'],
            ]);
        } catch (\Exception $e) {
            report($e);

            return response()->json([
                'success' => false,
                'error' => 'TTS synthesis failed',
                'message' => config('app.debug') ? $e->getMessage() : 'Internal error',
            ], 500);
        }
    }

    /**
     * Clean up old audio files (called by scheduler)
     */
    public function cleanup(): JsonResponse
    {
        $deleted = $this->ttsService->cleanupOldFiles();

        return response()->json([
            'success' => true,
            'deleted' => $deleted,
        ]);
    }
}
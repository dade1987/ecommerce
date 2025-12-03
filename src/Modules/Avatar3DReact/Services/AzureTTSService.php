<?php

namespace Modules\Avatar3DReact\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Str;

class AzureTTSService
{
    private string $speechKey;
    private string $speechRegion;
    private string $outputPath;
    private string $ttsServiceUrl;
    private bool $useNodeProcess;

    public function __construct()
    {
        $this->speechKey = config('avatar3d.azure.speech_key');
        $this->speechRegion = config('avatar3d.azure.speech_region');
        $this->outputPath = public_path('avatar3d/audio');
        $this->ttsServiceUrl = config('avatar3d.tts_service_url', 'http://localhost:8000');
        $this->useNodeProcess = config('avatar3d.use_node_process', false);

        // Ensure output directory exists
        if (!file_exists($this->outputPath)) {
            mkdir($this->outputPath, 0755, true);
        }
    }

    /**
     * Synthesize text to speech with viseme data
     */
    public function synthesize(string $text, string $voice = 'it-IT-IsabellaNeural'): array
    {
        if ($this->useNodeProcess) {
            return $this->synthesizeViaNodeProcess($text, $voice);
        }

        return $this->synthesizeViaHttpService($text, $voice);
    }

    /**
     * Call external Node.js TTS microservice via HTTP
     */
    private function synthesizeViaHttpService(string $text, string $voice): array
    {
        $response = Http::timeout(60)
            ->post("{$this->ttsServiceUrl}/talk", [
                'text' => $text,
                'voice' => $voice,
            ]);

        if (!$response->successful()) {
            throw new \Exception('TTS service error: ' . $response->body());
        }

        $data = $response->json();

        // The Node service returns { blendData: [...], filename: '/speech-xxx.mp3' }
        // We need to proxy the audio file or adjust the URL

        return [
            'blendData' => $data['blendData'] ?? [],
            'filename' => $this->proxyAudioFile($data['filename'] ?? ''),
        ];
    }

    /**
     * Proxy audio file from Node service to local storage
     */
    private function proxyAudioFile(string $remoteFilename): string
    {
        if (empty($remoteFilename)) {
            return '';
        }

        // Download audio from Node service and save locally
        $audioResponse = Http::timeout(30)
            ->get("{$this->ttsServiceUrl}{$remoteFilename}");

        if (!$audioResponse->successful()) {
            throw new \Exception('Failed to download audio file');
        }

        $localFilename = 'speech-' . Str::random(8) . '.mp3';
        $localPath = "{$this->outputPath}/{$localFilename}";

        file_put_contents($localPath, $audioResponse->body());

        return "/avatar3d/audio/{$localFilename}";
    }

    /**
     * Call Node.js script directly (requires Node.js installed)
     */
    private function synthesizeViaNodeProcess(string $text, string $voice): array
    {
        $randomString = Str::random(8);
        $filename = "speech-{$randomString}.mp3";
        $outputFile = "{$this->outputPath}/{$filename}";
        $nodeScriptPath = base_path('src/Modules/Avatar3DReact/scripts/tts.js');

        $result = Process::timeout(60)->run([
            'node',
            $nodeScriptPath,
            '--text', $text,
            '--voice', $voice,
            '--output', $outputFile,
            '--key', $this->speechKey,
            '--region', $this->speechRegion,
        ]);

        if (!$result->successful()) {
            throw new \Exception('TTS process failed: ' . $result->errorOutput());
        }

        $output = $result->output();
        $data = json_decode($output, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception('Failed to parse TTS output: ' . $output);
        }

        return [
            'blendData' => $data['blendData'] ?? [],
            'filename' => "/avatar3d/audio/{$filename}",
        ];
    }

    /**
     * Clean up old audio files (older than 1 hour)
     */
    public function cleanupOldFiles(int $maxAgeSeconds = 3600): int
    {
        $deleted = 0;
        $files = glob("{$this->outputPath}/speech-*.mp3");

        foreach ($files as $file) {
            if (filemtime($file) < (time() - $maxAgeSeconds)) {
                unlink($file);
                $deleted++;
            }
        }

        return $deleted;
    }
}

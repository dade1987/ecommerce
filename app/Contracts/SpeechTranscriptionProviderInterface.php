<?php

namespace App\Contracts;

use Illuminate\Http\UploadedFile;

interface SpeechTranscriptionProviderInterface
{
    /**
     * @throws \RuntimeException on configuration/runtime errors
     */
    public function transcribe(UploadedFile $audio, string $language): string;
}

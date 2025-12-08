import assert from 'node:assert/strict';
import fs from 'node:fs';
import path from 'node:path';

/**
 * Test "statici" per la UX/logica di WhisperSpeechRecognition.js.
 *
 * Non eseguiamo il codice in un browser, ma verifichiamo proprietà
 * chiave del wrapper (es. allowedLangs e chiamata all'endpoint
 * /api/whisper/transcribe) per evitare regressioni.
 */

const filePath = path.resolve('resources/js/utils/WhisperSpeechRecognition.js');
const source = fs.readFileSync(filePath, 'utf8');

// Deve invocare l'endpoint corretto per la trascrizione Whisper
{
  const hasEndpoint = source.includes('/api/whisper/transcribe');
  assert.ok(
    hasEndpoint,
    'WhisperSpeechRecognition deve invocare /api/whisper/transcribe come endpoint backend.',
  );
}

// Deve supportare la whitelist di lingue consentite (allowed_langs)
{
  const handlesAllowedLangs =
    source.includes('allowedLangs') && source.includes('allowed_langs');

  assert.ok(
    handlesAllowedLangs,
    'WhisperSpeechRecognition deve propagare allowedLangs verso il backend (campo allowed_langs).',
  );
}

// VAD più sensibile: deve usare una soglia di silenzio bassa
{
  const hasSilenceThreshold = source.includes('_silenceThreshold') && source.includes('0.03');

  assert.ok(
    hasSilenceThreshold,
    'WhisperSpeechRecognition deve utilizzare una soglia di silenzio più sensibile (0.03) per il VAD.',
  );
}



import assert from 'node:assert/strict';

/**
 * Test di smoke/UX per il componente LiveTranslator.vue.
 *
 * NOTA: questi test non montano il componente con un framework di test,
 * ma verificano comunque alcune convenzioni critiche dell'UX a livello
 * di template/script (senza introdurre nuovi runner JS).
 */

import fs from 'node:fs';
import path from 'node:path';

const componentPath = path.resolve('resources/js/components/LiveTranslator.vue');
const source = fs.readFileSync(componentPath, 'utf8');

// Verifica che il label "Lingua di traduzione" sia presente (regressione su UX lingue)
{
  const hasLabel = source.includes('translation language') ||
    source.includes('Lingua di traduzione') ||
    source.includes('langBLabel');

  assert.ok(
    hasLabel,
    'LiveTranslator.vue deve esporre la label per la lingua di traduzione (langBLabel).',
  );
}

// Verifica che il pulsante principale usi il testo "Registra" quando non in ascolto
{
  const hasRecordLabel = source.includes('Registra') || source.includes('speakerASpeak');

  assert.ok(
    hasRecordLabel,
    'LiveTranslator.vue deve avere il pulsante principale etichettato come "Registra" (o tramite ui.speakerASpeak).',
  );
}

// Verifica che il modal di chiarificazione intenzione utilizzi il campo interlocutor_role
{
  const usesInterlocutorRole = source.includes('clarifyIntentInterlocutorRole') &&
    source.includes('interlocutor_role');

  assert.ok(
    usesInterlocutorRole,
    'LiveTranslator.vue deve propagare il ruolo dell’interlocutore (clarifyIntentInterlocutorRole → interlocutor_role).',
  );
}

// Regressione critica: in modalità call "leggi traduzione senza auricolari"
// il mic NON deve riaccendersi in onend (con Whisper onend arriva prima di onresult).
{
  const hasOldOnendFallback = source.includes('fallback auto-resume (no TTS started / empty text)');
  assert.ok(
    !hasOldOnendFallback,
    'LiveTranslator.vue: non deve esistere un fallback auto-resume in onend per pendingAutoResumeSpeakerAfterTts (causa resume troppo presto).',
  );
}

// Deve esistere il resume post-TTS in call (no auricolari) per l’auto-pausa.
{
  const hasResumeAfterTts = source.includes('resuming CALL listening after TTS (auto-pause)') &&
    source.includes('pendingAutoResumeSpeakerAfterTts');

  assert.ok(
    hasResumeAfterTts,
    'LiveTranslator.vue: deve riaccendere il microfono dopo la lettura TTS quando pendingAutoResumeSpeakerAfterTts è impostato.',
  );
}

// Se Whisper restituisce un risultato vuoto/filtrato (quindi niente TTS), deve comunque ripartire dal punto giusto (onresult).
{
  const hasResumeAfterEmptyFiltered = source.includes('resuming CALL listening after empty/filtered result (auto-pause)');
  assert.ok(
    hasResumeAfterEmptyFiltered,
    'LiveTranslator.vue: deve gestire il resume dopo risultato vuoto/filtrato in onresult (auto-pause), per evitare mic bloccato.',
  );
}









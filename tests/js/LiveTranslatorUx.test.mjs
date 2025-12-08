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



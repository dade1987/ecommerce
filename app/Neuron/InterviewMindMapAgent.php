<?php

declare(strict_types=1);

namespace App\Neuron;

use Illuminate\Support\Facades\Log;
use NeuronAI\Agent;
use NeuronAI\Providers\AIProviderInterface;
use NeuronAI\Providers\OpenAI\OpenAI;

/**
 * InterviewMindMapAgent
 *
 * Agente Neuron dedicato a generare una "mappa mentale"
 * multilingua basata su:
 * - storia delle conversazioni del thread (QuoterChatHistory)
 *   inclusa la trascrizione testuale della call
 *
 * Output: riassunto strutturato dei temi trattati, in due lingue.
 */
class InterviewMindMapAgent extends Agent
{
    protected ?string $locale = null;

    protected string $langA = 'it';

    protected string $langB = 'en';

    public function withLocale(?string $locale): self
    {
        $this->locale = $locale;

        return $this;
    }

    public function withLanguages(string $langA, string $langB): self
    {
        $this->langA = $langA;
        $this->langB = $langB;

        return $this;
    }

    protected function provider(): AIProviderInterface
    {
        return new OpenAI(
            key: config('services.openai.key'),
            model: 'gpt-4o-mini',
        );
    }

    public function instructions(): string
    {
        $locale = $this->locale ?? 'it';

        // Mappa i codici lingua ai nomi completi per le istruzioni
        $langNames = [
            'it' => 'ITALIANO',
            'en' => 'INGLESE',
            'es' => 'SPAGNOLO',
            'fr' => 'FRANCESE',
            'de' => 'TEDESCO',
            'pt' => 'PORTOGHESE',
        ];

        $langAName = $langNames[$this->langA] ?? strtoupper($this->langA);
        $langBName = $langNames[$this->langB] ?? strtoupper($this->langB);

        $base = <<<TXT
Sei un assistente per colloqui tecnici che costruisce una MAPPA MENTALE
dei temi discussi durante il colloquio, come se fossero POST‑IT collegati tra loro.

CONTESTO (STORIA DELLA CONVERSAZIONE):
- Hai accesso alla chat history tramite il memory provider.
- La history contiene anche il testo della call (trascrizione) passato dall'applicazione.
- Usa la storia per capire:
  * argomenti tecnici trattati,
  * domande dell'intervistatore,
  * risposte dell'utente,
  * esempi concreti o casi d'uso citati,
  * direzione generale del colloquio.

OBIETTIVO:
- Generare una mappa mentale compatta ma chiara dei temi TECNICI emersi.
- Ogni nodo deve comportarsi come un POST‑IT con:
  * un TITOLO breve del concetto (la cosa da ricordare),
  * una BREVE NOTA che riassume cosa è stato detto su quel concetto (1‑2 frasi).
- La mappa mentale serve all'utente per:
  * ricordare velocemente di cosa si è parlato,
  * collegare gli argomenti tra loro,
  * ritrovare esempi e storie citate durante la call.

REGOLE:
- Concentrati sui temi TECNICI, tecnologie, metodologie, ruoli, domini di competenza.
- Non inventare esperienze biografiche: resta aderente a ciò che appare nella history.
- Puoi sintetizzare o riformulare, ma le note devono riflettere il SUCCO di ciò che è stato detto.
- Struttura i concetti per:
  * nodi principali (macro‑temi),
  * nodi collegati (sotto‑temi, tecnologie correlate, esempi pratici),
  * relazioni significative tra i nodi.
- I nomi dei nodi, le relazioni e le note devono essere nella lingua {$langBName}.
- Obiettivo indicativo: 8‑20 nodi totali, non più di 3 livelli di profondità.

FORMATO OUTPUT (SEMPRE LO STESSO):
- Devi restituire SOLO un oggetto JSON **valido** senza testo aggiuntivo, senza commenti, senza markdown.
- La struttura JSON deve essere ESATTAMENTE:

{
  "nodes": [
    {
      "id": "string identificativa unica del nodo (es: \"llm\")",
      "label": "titolo breve e leggibile del concetto in {$langBName} (es: \"Large Language Models\")",
      "note": "breve annotazione in {$langBName} che ricorda COSA è stato detto su questo tema (1-2 frasi, es: \"Hai raccontato come usi i LLM per riassumere trascrizioni di meeting.\")",
      "group": "categoria o tema principale (es: \"Machine Learning\")",
      "importance": 0.0-1.0 (numero float che indica l'importanza relativa del nodo)
    }
  ],
  "edges": [
    {
      "from": "id del nodo sorgente",
      "to": "id del nodo destinazione",
      "label": "etichetta breve della relazione in {$langBName} (es: \"usa\", \"dipende da\", \"estende\")",
      "strength": 0.0-1.0 (numero float che indica la forza della relazione)
    }
  ]
}

VINCOLI IMPORTANTI:
- OUTPUT SOLO JSON: nessun testo prima o dopo l'oggetto JSON.
- Usa SEMPRE array `nodes` e `edges` anche se vuoti.
- Ogni `id` di nodo deve essere univoco.
- Ogni nodo deve avere SEMPRE un `label` non vuoto e una `note` non vuota.
- I label, le note e le relazioni DEVONO essere in {$langBName}.
- Mantieni il JSON il più compatto possibile compatibilmente con la leggibilità dei label.
TXT;

        Log::debug('InterviewMindMapAgent.instructions', [
            'locale' => $locale,
            'lang_a' => $this->langA,
            'lang_b' => $this->langB,
            'prompt_length' => strlen($base),
        ]);

        return $base;
    }
}

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
 * - CV dell'utente
 * - storia delle conversazioni del thread (QuoterChatHistory)
 *
 * Output: riassunto strutturato dei temi trattati, in due lingue.
 */
class InterviewMindMapAgent extends Agent
{
    protected ?string $locale = null;

    protected ?string $cvText = null;

    protected string $langA = 'it';

    protected string $langB = 'en';

    public function withLocale(?string $locale): self
    {
        $this->locale = $locale;

        return $this;
    }

    public function withCv(?string $cvText): self
    {
        $this->cvText = $cvText;

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
        $cvPreview = $this->cvText ? mb_substr($this->cvText, 0, 8000) : '';

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

        $base = <<<TXT
Sei un assistente per colloqui tecnici che costruisce una MAPPA MENTALE
dei temi discussi durante il colloquio.

CONTESTO (CV DELL'UTENTE - TESTO GREZZO):
{$cvPreview}

CONTESTO (STORIA DELLA CONVERSAZIONE):
- Hai accesso alla chat history tramite il memory provider.
- Usa la storia per capire:
  * argomenti tecnici trattati,
  * domande dell'intervistatore,
  * suggerimenti già forniti,
  * direzione generale del colloquio.

OBIETTIVO:
- Generare una mappa mentale compatta ma chiara dei temi TECNICI emersi.
- La mappa mentale serve all'utente per:
  * avere una visione globale degli argomenti toccati,
  * capire quali competenze tecniche sta mostrando,
  * trovare velocemente spunti per approfondire.

REGOLE:
- Concentrati sui temi TECNICI, tecnologie, metodologie, ruoli, domini di competenza.
- Collega i temi al CV quando possibile, anche solo vagamente.
- Se il CV parla di machine learning e nella conversazione si citano "LLM",
  spiega correttamente che sono "Large Language Models" e collegali al ML.
- NON inventare esperienze specifiche non presenti nel CV, ma puoi usare frasi
  tecniche generali per descrivere le conoscenze.
- Struttura i concetti per:
  * nodi principali (macro-temi),
  * nodi collegati (sotto-temi, tecnologie correlate, esempi pratici),
  * relazioni significative tra i nodi.
- I nomi dei nodi e delle relazioni devono essere nella lingua {$langAName}.

FORMATO OUTPUT (SEMPRE LO STESSO):
- Devi restituire SOLO un oggetto JSON **valido** senza testo aggiuntivo, senza commenti, senza markdown.
- La struttura JSON deve essere ESATTAMENTE:

{
  "nodes": [
    {
      "id": "string identificativa unica del nodo (es: \"llm\")",
      "label": "etichetta leggibile del concetto in {$langAName} (es: \"Large Language Models\")",
      "group": "categoria o tema principale (es: \"Machine Learning\")",
      "importance": 0.0-1.0 (numero float che indica l'importanza relativa del nodo)
    }
  ],
  "edges": [
    {
      "from": "id del nodo sorgente",
      "to": "id del nodo destinazione",
      "label": "etichetta breve della relazione in {$langAName} (es: \"usa\", \"dipende da\", \"estende\")",
      "strength": 0.0-1.0 (numero float che indica la forza della relazione)
    }
  ]
}

VINCOLI IMPORTANTI:
- OUTPUT SOLO JSON: nessun testo prima o dopo l'oggetto JSON.
- Usa SEMPRE array `nodes` e `edges` anche se vuoti.
- Ogni `id` di nodo deve essere univoco.
- I label e le descrizioni DEVONO essere in {$langAName}.
- Mantieni il JSON il più compatto possibile compatibilmente con la leggibilità dei label.
TXT;

        Log::debug('InterviewMindMapAgent.instructions', [
            'locale' => $locale,
            'lang_a' => $this->langA,
            'lang_b' => $this->langB,
            'cv_present' => ! empty($cvPreview),
            'prompt_length' => strlen($base),
        ]);

        return $base;
    }
}

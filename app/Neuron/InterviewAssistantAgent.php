<?php

declare(strict_types=1);

namespace App\Neuron;

use Illuminate\Support\Facades\Log;
use NeuronAI\Agent;
use NeuronAI\Providers\AIProviderInterface;
use NeuronAI\Providers\OpenAI\OpenAI;

/**
 * InterviewAssistantAgent
 *
 * Agente Neuron dedicato a suggerire risposte per colloqui di lavoro
 * basandosi ESCLUSIVAMENTE sul CV fornito dall'utente.
 *
 * Caratteristiche:
 * - Non inventa esperienze o competenze non presenti nel CV.
 * - Se la risposta non è deducibile dal CV, fornisce comunque un suggerimento GENERICO e neutro
 *   (consigli validi per qualsiasi candidato) senza attribuire all'utente esperienze specifiche.
 * - Restituisce sempre suggerimenti nelle due lingue specificate dall'utente.
 */
class InterviewAssistantAgent extends Agent
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
        $langBName = $langNames[$this->langB] ?? strtoupper($this->langB);

        $base = <<<TXT
Sei un assistente per colloqui di lavoro che suggerisce risposte all'utente
in tempo reale durante il colloquio.

CONTESTO (CV DELL'UTENTE - TESTO GREZZO):
{$cvPreview}

REGOLE FONDAMENTALI:
- La PRIORITÀ è fornire SEMPRE un buon suggerimento GENERALE, anche se il CV è vuoto o non parla dell'argomento.
- Usa le informazioni contenute nel CV riportato sopra quando sono disponibili, per adattare meglio il suggerimento
  alla storia dell'utente (esempi concreti, tecnologie, ruoli, settori).
- NON inventare mai esperienze, corsi, certificazioni, competenze o date che non siano presenti nel CV.
- Quando qualcosa NON è nel CV, dai comunque un suggerimento GENERICO e neutro
  (formulazioni standard, modi educati di rispondere, struttura della risposta),
  ma senza dire o implicare che l'utente abbia fatto qualcosa che nel CV non c'è.
- Mantieni il tono professionale ma naturale, come se stessi suggerendo cosa dire a voce.

FORMATO OUTPUT (SEMPRE LO STESSO):
Devi fornire il suggerimento in DUE lingue specifiche: {$langAName} e {$langBName}.

Struttura ESATTA dell'output:
{$langAName}:
<testo della risposta suggerita in {$langAName}, una o più frasi>

{$langBName}:
<testo della risposta corrispondente in {$langBName}, una o più frasi>

NON aggiungere altre sezioni, NON aggiungere spiegazioni metatestuali.
Usa frasi brevi e chiare, adatte a essere lette velocemente durante un colloquio.
TXT;

        Log::debug('InterviewAssistantAgent.instructions', [
            'locale' => $locale,
            'lang_a' => $this->langA,
            'lang_b' => $this->langB,
            'cv_present' => ! empty($cvPreview),
            'prompt_length' => strlen($base),
        ]);

        return $base;
    }
}

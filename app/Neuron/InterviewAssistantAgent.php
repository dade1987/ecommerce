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
Sei un assistente esperto per colloqui tecnici che suggerisce frasi tecniche per dimostrare competenze
all'utente in tempo reale durante il colloquio.

CONTESTO (CV DELL'UTENTE - TESTO GREZZO):
{$cvPreview}

REGOLE FONDAMENTALI:
- Il tuo obiettivo è fornire ESATTAMENTE 10 frasi tecniche che l'utente può dire durante il colloquio
  per dimostrare le proprie competenze tecniche sull'argomento discusso.
- Analizza attentamente il CV per capire le competenze tecniche, tecnologie, esperienze e settori dell'utente.
- Quando l'intervistatore menziona un argomento tecnico (es. "LLM", "machine learning", "API REST", "Docker"),
  genera 10 frasi tecniche che:
  * Dimostrano conoscenza tecnica dell'argomento
  * Sono collegate al background del CV (anche solo vagamente)
  * Possono essere vicine alle competenze del CV anche se non esplicitamente scritte
  * Mostrano competenza e professionalità tecnica
- Usa la STORIA DELLE CONVERSAZIONI PRECEDENTI per mantenere coerenza e contesto.
- Le frasi devono essere:
  * TECNICHE: usa terminologia corretta e professionale (es. "Ho lavorato con Large Language Models utilizzando transformer architecture")
  * SPECIFICHE: menziona tecnologie, pattern, metodologie concrete
  * DIMOSTRATIVE: mostrano competenza pratica (es. "Ho implementato API REST con autenticazione JWT")
  * COLLEGATE AL CV: anche solo vagamente, ma sempre credibili rispetto al background
  * BREVI: una frase per punto, massimo 2 righe ciascuna
- NON inventare esperienze specifiche non presenti nel CV, ma puoi suggerire frasi tecniche generali
  che dimostrano conoscenza anche se l'esperienza specifica non è scritta (es. se il CV parla di Python
  e si discute di ML, puoi suggerire frasi su scikit-learn anche se non è esplicitamente menzionato).
- Mantieni il tono professionale e tecnico, come se stessi suggerendo cosa dire per impressionare positivamente.

FORMATO OUTPUT (SEMPRE LO STESSO):
Devi fornire ESATTAMENTE 10 frasi tecniche in DUE lingue specifiche: {$langAName} e {$langBName}.

Struttura ESATTA dell'output:
{$langAName}:
1. <prima frase tecnica in {$langAName}>
2. <seconda frase tecnica in {$langAName}>
3. <terza frase tecnica in {$langAName}>
4. <quarta frase tecnica in {$langAName}>
5. <quinta frase tecnica in {$langAName}>
6. <sesta frase tecnica in {$langAName}>
7. <settima frase tecnica in {$langAName}>
8. <ottava frase tecnica in {$langAName}>
9. <nona frase tecnica in {$langAName}>
10. <decima frase tecnica in {$langAName}>

{$langBName}:
1. <prima frase tecnica in {$langBName}>
2. <seconda frase tecnica in {$langBName}>
3. <terza frase tecnica in {$langBName}>
4. <quarta frase tecnica in {$langBName}>
5. <quinta frase tecnica in {$langBName}>
6. <sesta frase tecnica in {$langBName}>
7. <settima frase tecnica in {$langBName}>
8. <ottava frase tecnica in {$langBName}>
9. <nona frase tecnica in {$langBName}>
10. <decima frase tecnica in {$langBName}>

NON aggiungere altre sezioni, NON aggiungere spiegazioni metatestuali.
Ogni frase deve essere tecnica, specifica e dimostrare competenza sull'argomento discusso.
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

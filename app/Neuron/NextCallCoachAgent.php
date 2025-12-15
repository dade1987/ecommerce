<?php

declare(strict_types=1);

namespace App\Neuron;

use Illuminate\Support\Facades\Log;
use NeuronAI\Agent;
use NeuronAI\Providers\AIProviderInterface;
use NeuronAI\Providers\OpenAI\OpenAI;

/**
 * NextCallCoachAgent
 *
 * Agente Neuron dedicato a suggerire come migliorare
 * la prossima call di colloquio, a partire da:
 * - obiettivo dichiarato dall'utente
 * - testo della conversazione/trascrizione
 * - eventuale history nel thread Quoter
 */
class NextCallCoachAgent extends Agent
{
    protected ?string $locale = null;

    protected ?string $goalText = null;

    protected ?string $transcriptText = null;

    protected string $langA = 'it';

    protected string $langB = 'en';

    public function withLocale(?string $locale): self
    {
        $this->locale = $locale;

        return $this;
    }

    public function withGoal(?string $goal): self
    {
        $this->goalText = $goal;

        return $this;
    }

    public function withTranscript(?string $transcript): self
    {
        $this->transcriptText = $transcript;

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
        $goalPreview = $this->goalText ? mb_substr($this->goalText, 0, 2000) : '';
        $transcriptPreview = $this->transcriptText ? mb_substr($this->transcriptText, 0, 8000) : '';

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
Sei un COACH DI COLLOQUI tecnici.
Devi analizzare la call precedente e l'obiettivo dichiarato per la prossima call,
e restituire consigli pratici per migliorare la prossima call.

CONTESTO (OBIETTIVO PROSSIMA CALL):
{$goalPreview}

CONTESTO (TRASCRIZIONE / APPUNTI CALL):
{$transcriptPreview}

CONTESTO (HISTORY):
- Hai accesso alla chat history tramite il memory provider.
- Usa la storia per capire:
  * tono del colloquio,
  * temi tecnici ricorrenti,
  * punti di forza e debolezza emersi.

OBIETTIVO:
- Analizzare COME è andata QUESTA call (testo + history) rispetto a una call \"ideale\" con lo stesso obiettivo.
- Fornire consigli CONCRETI e AZIONABILI per:
  * migliorare la chiarezza e la struttura delle risposte,
  * evidenziare meglio le competenze tecniche,
  * gestire meglio il tempo e le domande,
  * collegare meglio le esperienze all'obiettivo dichiarato,
  * correggere errori o mancanze emerse in questa call.

REGOLE:
- Parti SEMPRE da ciò che è successo in questa call: osserva il modo in cui l'utente ha risposto (lunghezza, chiarezza, esempi, sicurezza, uso di parole chiave, coerenza col CV/obiettivo).
- Ogni consiglio deve essere formulato come un confronto implicito tra la call attuale e una versione migliore:
  * es. \"Nella call attuale X, la prossima volta fai Y\" oppure \"Quando dici <tipo di frase>, potresti riformularla così: ...\".
- Non inventare fatti biografici: resta sul piano di strategie e comportamenti osservabili nella call.
- Evita frasi vaghe tipo \"preparati meglio\": ogni punto deve dire ESPRESSAMENTE cosa cambiare o migliorare.
- Ogni consiglio deve essere una singola frase chiara e operativa.

FORMATO OUTPUT (SEMPRE LO STESSO):
Devi fornire ESATTAMENTE 10 consigli in DUE lingue specifiche: {$langAName} e {$langBName}.

Struttura ESATTA dell'output:
{$langAName}:
1. <prima frase in {$langAName}>
2. <seconda frase in {$langAName}>
3. <terza frase in {$langAName}>
4. <quarta frase in {$langAName}>
5. <quinta frase in {$langAName}>
6. <sesta frase in {$langAName}>
7. <settima frase in {$langAName}>
8. <ottava frase in {$langAName}>
9. <nona frase in {$langAName}>
10. <decima frase in {$langAName}>

{$langBName}:
1. <prima frase in {$langBName}>
2. <seconda frase in {$langBName}>
3. <terza frase in {$langBName}>
4. <quarta frase in {$langBName}>
5. <quinta frase in {$langBName}>
6. <sesta frase in {$langBName}>
7. <settima frase in {$langBName}>
8. <ottava frase in {$langBName}>
9. <nona frase in {$langBName}>
10. <decima frase in {$langBName}>

NON aggiungere altre sezioni, NON aggiungere spiegazioni metatestuali.
TXT;

        Log::debug('NextCallCoachAgent.instructions', [
            'locale' => $locale,
            'lang_a' => $this->langA,
            'lang_b' => $this->langB,
            'goal_present' => ! empty($goalPreview),
            'transcript_present' => ! empty($transcriptPreview),
            'prompt_length' => strlen($base),
        ]);

        return $base;
    }
}

<?php

declare(strict_types=1);

namespace App\Neuron;

use Illuminate\Support\Facades\Log;
use NeuronAI\Agent;
use NeuronAI\Providers\AIProviderInterface;
use NeuronAI\Providers\OpenAI\OpenAI;

/**
 * LiveTranslatorAgent
 *
 * Agente Neuron AI dedicato alla traduzione live di frasi brevi riconosciute dal microfono.
 * Regole:
 * - Rileva automaticamente la lingua del testo in ingresso.
 * - Se il testo è in ITALIANO → restituisci SOLO la traduzione in INGLESE.
 * - Se il testo è in INGLESE → restituisci SOLO la traduzione in ITALIANO.
 * - Se il testo è in un\'altra lingua (tra le lingue più parlate: es. spagnolo, francese, tedesco,
 *   portoghese, russo, arabo, cinese, giapponese, hindi, ecc.), restituisci:
 *     - una riga in ITALIANO
 *     - una riga in INGLESE
 *   in questo ordine, senza spiegazioni aggiuntive.
 * - Non aggiungere etichette tipo "Italiano:" o "English:", soltanto il testo tradotto.
 * - Non spiegare cosa stai facendo, non commentare: restituisci solo il testo tradotto.
 */
class LiveTranslatorAgent extends Agent
{
    protected ?string $locale = null;

    protected ?string $targetLang = null;

    public function withLocale(?string $locale): self
    {
        $this->locale = $locale;

        return $this;
    }

    public function withTargetLang(?string $targetLang): self
    {
        $this->targetLang = $targetLang;

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

        $target = $this->targetLang ?: 'it';

        $base = <<<TXT
Sei un traduttore vocale istantaneo per frasi brevi.

Regole FONDAMENTALI:
- Rileva automaticamente la lingua del testo in ingresso.
- La lingua di DESTINAZIONE è fissata a: "{$target}" (codice lingua, es. "it", "en", "es-ES").
- Indipendentemente dalla lingua di origine, restituisci SEMPRE e SOLO la traduzione del testo in questa lingua di destinazione.
- NON restituire la frase originale, solo la traduzione.
- NON aggiungere etichette, prefissi o spiegazioni (niente "Italiano:", "English:", ecc.).
- NON spiegare le tue scelte, non commentare: restituisci SOLO il testo tradotto come dovrà essere mostrato all'utente.
- Mantieni il tono e il registro più possibile fedeli all'originale (formale/informale).
TXT;

        Log::debug('LiveTranslatorAgent.instructions', [
            'locale' => $locale,
            'length' => strlen($base),
        ]);

        return $base;
    }
}

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
 *
 * Nuovo comportamento GENERALE:
 * - Rileva automaticamente la lingua del testo in ingresso (qualunque lingua).
 * - Traduce SEMPRE e SOLO nella lingua di destinazione indicata da target_lang (es. "it", "en", "ja", "es-ES"...),
 *   passata dal frontend in base alla "Lingua B" selezionata dall'utente.
 * - Non fa più nessuna logica speciale solo per italiano / inglese: ogni combinazione A→B è supportata.
 * - Non aggiunge etichette o spiegazioni: restituisce solo il testo già pronto da mostrare all'utente.
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
            model: 'gpt-4o',
        );
    }

    public function instructions(): string
    {
        $locale = $this->locale ?? 'it';

        $target = $this->targetLang ?: 'it';

        $base = <<<TXT
You are a real-time voice translator for short sentences.

CORE RULES:
- Automatically detect the input language (it can be any language).
- The OUTPUT language is fixed to: "{$target}" (language code, e.g. "it", "en", "ja", "es-ES").
- Regardless of the input language, you MUST ALWAYS return ONLY the translation of the text in this output language.
- The output must be natural, fluent text in the target language (no mixed languages).
- The user sentence to translate will be wrapped between the guillemets « and ».
- You MUST translate ONLY the text inside « » and ignore the guillemets themselves.
- IMPORTANT: Your response must NOT include the guillemets « » - return ONLY the translated text without any quotes or guillemets.
- Examples:
  - input: «Japanese sentence», target_lang="it" → output: Traduzione italiana (NO guillemets)
  - input: «Japanese sentence», target_lang="en" → output: English translation (NO guillemets)
  - input: «English sentence», target_lang="it" → output: Traduzione italiana (NO guillemets)
  - input: «Spanish sentence», target_lang="de" → output: Deutsche Übersetzung (NO guillemets)
- DO NOT return the original sentence, only the translation.
- DO NOT add labels, prefixes or explanations (no "Italian:", "English:", etc.).
- DO NOT include guillemets « » or quotes in your response.
- DO NOT explain your choices, do not comment: return ONLY the translated text as it should be shown to the user.
- Preserve the tone and register of the original sentence as much as possible (formal / informal).
TXT;

        Log::debug('LiveTranslatorAgent.instructions', [
            'locale' => $locale,
            'length' => strlen($base),
        ]);

        return $base;
    }
}

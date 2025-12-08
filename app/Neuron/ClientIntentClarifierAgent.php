<?php

declare(strict_types=1);

namespace App\Neuron;

use Illuminate\Support\Facades\Log;
use NeuronAI\Agent;
use NeuronAI\Providers\AIProviderInterface;
use NeuronAI\Providers\OpenAI\OpenAI;

/**
 * ClientIntentClarifierAgent
 *
 * Agente dedicato a chiarire l’intenzione del cliente / interlocutore,
 * partendo da:
 * - una o più frasi focali (focusText)
 * - l’eventuale history del thread di trascrizione (QuoterChatHistory).
 */
class ClientIntentClarifierAgent extends Agent
{
    protected ?string $locale = null;

    protected ?string $focusText = null;

    protected ?string $interlocutorRole = null;

    protected string $langA = 'it';

    protected string $langB = 'en';

    public function withLocale(?string $locale): self
    {
        $this->locale = $locale;

        return $this;
    }

    public function withFocusText(?string $text): self
    {
        $this->focusText = $text;

        return $this;
    }

    public function withInterlocutorRole(?string $role): self
    {
        $this->interlocutorRole = $role;

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
        $focusPreview = $this->focusText ? mb_substr($this->focusText, 0, 2000) : '';
        $roleInfo = $this->interlocutorRole ? "RUOLO DELL'INTERLOCUTORE: {$this->interlocutorRole}\n\n" : '';

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

        // Usa sempre la lingua di traduzione (langB) per la risposta
        $answerLangName = $langBName;

        $base = <<<TXT
Sei un ESPERTO DI COMUNICAZIONE orientato ai colloqui e alle call di lavoro.
Il tuo compito è CHIARIRE cosa intende dire l'interlocutore SPECIFICATO dal ruolo indicato,
quando chi ascolta non è sicuro di aver capito bene.

{$roleInfo}CONTESTO (FRASI FOCALI DELL'INTERLOCUTORE CHE VUOLE ESSERE CHIARITO):
{$focusPreview}

CONTESTO (HISTORY DELLA CALL):
- Hai accesso alla chat history tramite il memory provider (testo della trascrizione già registrato).
- Usa la history solo come SUPPORTO per:
  * ricostruire il tema della call,
  * capire a cosa si riferisce l'interlocutore,
  * intuire obiettivi impliciti o problemi che sta cercando di risolvere.

OBIETTIVO:
- Spiegare in modo CHIARO e STRUTTURATO LE INTENZIONI DELL'INTERLOCUTORE DESCRITTO DAL RUOLO (es. il recruiter, il cliente, il capo):
  1) cosa molto probabilmente intendeva dire QUELLA persona (non l'altra parte della conversazione),
  2) quali ipotesi di contesto stai facendo (es. ruolo dell'interlocutore, scenario, vincoli),
  3) il ragionamento passo‑passo che ti porta a quella interpretazione,
  4) eventuali domande di chiarimento che l'utente potrebbe fare all'interlocutore per confermare.

REGOLE:
- Non inventare dettagli biografici: resta sul piano di obiettivi, problemi, vincoli, desideri.
- Se ci sono più possibili interpretazioni, presentale in modo ordinato (es. "ipotesi A / B / C").
- Sii molto concreto: evita frasi generiche tipo "vuole migliorare il business", ma specifica cosa e come.
- Interpreta SEMPRE e SOLO l'interlocutore descritto nel ruolo (ad esempio: se il ruolo è "recruiter"
  e le frasi mostrano un dialogo tra recruiter e programmatore, devi spiegare cosa vuole il recruiter,
  non cosa vuole il programmatore).
- Tieni conto del ruolo dell'interlocutore indicato per contestualizzare meglio le sue intenzioni.
- Tutto il testo di output deve essere in {$answerLangName}.

FORMATO OUTPUT (SEMPRE LO STESSO, IN {$answerLangName}):
1. Breve RIASSUNTO dell'intenzione: 2‑3 frasi che spiegano "in pratica cosa vuole".
2. Sezione "PERCHE' PROBABILMENTE VUOLE QUESTO": elenco puntato con le ipotesi di contesto principali.
3. Sezione "COME CI ARRIVO": descrivi il ragionamento passo‑passo (anche numerato) che ti porta a questa lettura.
4. Sezione "DOMANDE UTILI DA FARE ALL'INTERLOCUTORE": 3‑5 domande specifiche che aiutano a chiarire eventuali ambiguità.

NON aggiungere testo di servizio (nessun saluto, nessuna spiegazione sul modello).
TXT;

        Log::debug('ClientIntentClarifierAgent.instructions', [
            'locale' => $locale,
            'lang_a' => $this->langA,
            'lang_b' => $this->langB,
            'focus_present' => ! empty($focusPreview),
            'prompt_length' => strlen($base),
        ]);

        return $base;
    }
}

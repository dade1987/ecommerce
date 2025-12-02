<?php

namespace App\Livewire;

use App\Models\Quoter as ModelQuoter;
use App\Models\Thread;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Livewire\Component;
use Livewire\WithFileUploads;
use OpenAI;
use OpenAI\Client;
use function Safe\fopen;

class Quoter extends Component
{
    use WithFileUploads;

    public string $api_key;

    public string $thread_id;

    public string $message;

    public $files = [];

    public array $chat;

    public function mount()
    {
        $this->api_key = config('openapi.key');
        $this->createThread();

        $this->message = 'Intro';
        $this->sendMessage();
    }

    public function getClient()
    {
        return OpenAI::client($this->api_key);
    }

    public function createThread()
    {
        $minutes = 60; // Durata del cookie in minuti
        $thread = $this->getClient()->threads()->create([]);

        Log::info('thread id '.$thread->id);

        $this->thread_id = $thread->id;

        // Registra i metadati del thread (solo alla prima inizializzazione)
        Thread::captureFromRequest($this->thread_id, request());
    }

    public function generateQuote()
    {
        $this->message = 'Genera Preventivo';
        $this->sendMessage();
        $this->message = '';
    }

    public function uploadFile()
    {
        $threadId = $this->thread_id;

        Log::info('thread id '.$threadId);

        // Verifica se è stato caricato un file

        $files = $this->files;

        $path = $files[0]->store('uploads', 'public'); // Salva il file e ottieni la path

        // Carica il file utilizzando la path
        $response = $this->getClient()->files()->upload([
            'purpose' => 'assistants',
            'file' => fopen(storage_path('app/public/'.$path), 'r'), // Usa la path del file caricato
        ]);

        ModelQuoter::create(['thread_id' => $threadId, 'role' => 'user', 'content' => 'Caricamento bolletta']);

        $this->getClient()->threads()->messages()->create($threadId, [
            'role' => 'user',
            'content' => 'Estrai i consumi della fascia F1, della fascia F2 e della fascia F3 dal file caricato. Estrai nome e cognome e indirizzo del cantiere e se l\'utente è privato o azienda. Poi continua con le domande',
            'attachments' => [['file_id' => $response->id, 'tools' => [['type' => 'file_search']]]],
        ]);

        $run = $this->getClient()->threads()->runs()->create(
            threadId: $threadId,
            parameters: [
                'assistant_id' => config('openapi.assistant_id'),
            ]
        );

        $this->retrieveRunResult($threadId, $run->id);

        $messages = $this->getClient()->threads()->messages()->list($threadId)->data;

        $content = $messages[0]->content[0]->text->value;

        ModelQuoter::create(['thread_id' => $threadId, 'role' => 'chatbot', 'content' => $content]);

        $this->chat[] = ['sender' => 'AI-799', 'content' => $content];
    }

    public function sendMessage()
    {
        $threadId = $this->thread_id;
        $userMessage = $this->message;

        $this->chat[] = ['sender' => 'Me', 'content' => $this->message];

        $this->message = '';

        Log::info('thread id '.$threadId);

        $content = $this->generateContentBasedOnMessage($userMessage);

        ModelQuoter::create(['thread_id' => $threadId, 'role' => 'user', 'content' => $userMessage]);

        $this->getClient()->threads()->messages()->create($threadId, [
            'role' => 'user',
            'content' => $content,
        ]);

        //Sei una segretaria che fa preventivi per un\'azienda di sviluppo software. Cerca le informazioni nei file che ti ho passato.
        $run = $this->getClient()->threads()->runs()->create(
            threadId: $threadId,
            parameters: [
                'assistant_id' => config('openapi.assistant_id'),
                'instructions' => 'Devi fare il preventivo per un azienda di fotovoltaico. Ti serve che ti carico il file della bolletta e alcune informazioni aggiuntive.',
                'tools' => [
                    [
                        'type' => 'file_search',
                    ],
                ],
                'model' => 'gpt-4-turbo',
            ]
        );

        $this->retrieveRunResult($threadId, $run->id);

        $messages = $this->getClient()->threads()->messages()->list($threadId)->data;

        $content = $messages[0]->content[0]->text->value;

        ModelQuoter::create(['thread_id' => $threadId, 'role' => 'chatbot', 'content' => $content]);

        if (substr($content, 0, 7) == '```json') {
            dd($content);
        }

        $this->chat[] = ['sender' => 'AI-799', 'content' => $content];
    }

    private function generateContentBasedOnMessage($userMessage)
    {
        //Chiedimi il mio nome, cognome, indirizzo email, numero di telefono, poi fare il preventivo dei servizi richiesti. Scrivi la domanda in modo conciso
        if ($userMessage === 'Intro') {
            return '
            Parti con il chiedermi di caricare la bolletta o la fattura elettrica.
            
            Quando avrò risposto, ponimi le seguenti domande una alla volta:
            
            2. Il tetto è piano o a falda inclinata?
            3. Qual\'è la dimensione della superficie in metri quadrati?
            4. Come è orientato il tetto (Est, Sud, Ovest, Nord)?
            5. Hai un Contatore piccolo o grande?
            6. Sei interessato anche all\'accumulo di energia (per le ore di mancanza di sole) contro i blackout?
            7. Sei interessato anche al Backup di energia (per avere autonomia in caso di blackout o mancanza di energia dalla rete o dal sole per un certo numero di ore)?
            8. Vuoi valutare l\'appartenenza ad una comunità energetica
            9. Confermi che l\'indirizzo per la Proposta è quello indicato in Fattura o è diverso?
                        
            Alla fine crea un breve riassunto dei dati inseriti. Scrivi \'Per generare la Proposta finale in PDF, clicca su "Genera Preventivo"\'. 
            
            NB Ricorda che per fare 5kw (consumo domestico) servono 30mq di pannelli, e che i metri quadri devono essere sempre multipli di 3.';
        } elseif ($userMessage === 'Genera Preventivo') {
            return 'Genera un preventivo per l\'azienda Cavallini Service con sede a Noale in via del Musonetto, 4, partita IVA numero CVLDVD87M23L736P. Il preventivo deve essere in formato JSON Object, con la seguente struttura:\\n\\n- La prima sezione deve essere \'company_info\' con le colonne \'name\' (nome dell\'azienda), \'address\' (indirizzo dell\'azienda) e \'vat_number\' (partita IVA dell\'azienda).\\n- La seconda sezione deve essere \'personal_info\' con le colonne \'first_name\' (nome), \'last_name\' (cognome), \'phone_number\' (numero di telefono), \'email\' (indirizzo email).\\n- La terza sezione deve essere \'products\' che contiene una lista di prodotti e servizi (inclusa la manodopera se c\'è scritto il prezzo nel file), ognuno con le colonne \'name\' (nome del prodotto o servizio), \'quantity\' (quantità del prodotto o servizio) e \'price\' (prezzo del singolo prodotto o servizio).\\n- L\'ultima sezione deve essere \'price_info\' con le colonne \'net_price\' (prezzo netto), \'vat\' (IVA al 22%) e \'total\' (prezzo totale).\\n\\nEsempio di JSON da generare:\\n\\n{\\n  "company_info": {\\n    "name": "Cavallini Service",\\n    "address": "via Tal dei Tali, 15, Milano",\\n    "vat_number": "01234567899"\\n  },\\n  "personal_info": {\\n    "first_name": "Mario",\\n    "last_name": "Rossi",\\n    "phone_number": "1234567890",\\n    "email": "mario.rossi@example.com",\\n    "site_address": "via Esempio, 20",\\n    "city": "Milano",\\n    "province": "MI",\\n    "country": "Italia"\\n  },\\n  "products": [\\n    {\\n      "name": "Finestra in PVC",\\n      "quantity": 10,\\n      "price": 200\\n    },\\n    {\\n      "name": "Porta in legno",\\n      "quantity": 5,\\n      "price": 300\\n    },\\n    {\\n      "name": "Manodopera montaggio finestra",\\n      "quantity": 10,\\n      "price": 150\\n    },\\n    {\\n      "name": "Manodopera montaggio porta",\\n      "quantity": 5,\\n      "price": 150\\n    }\\n  ],\\n  "price_info": {\\n    "net_price": 5500,\\n    "vat": 1210,\\n    "total": 6710\\n  }\\n}. Non rispondere con nessun altra informazioni oltre il JSON. Il JSON non deve contenere commenti e dev\'essere ben formattato.';
        } else {
            //. ' . Se non si tratta della comunicazione dei dati personali, cerca tutte le informazioni richieste nel file, altrimenti chiedimi quali informazioni sui servizi desidero conoscere, cercando tutte le informazioni per le risposte nel file, usando il tool file_search.'
            return $userMessage;
        }
    }

    private function retrieveRunResult($threadId, $runId)
    {
        while (true) {
            $run = $this->getClient()->threads()->runs()->retrieve($threadId, $runId);

            Log::info(var_export($run, true));

            if ($run->status === 'completed') {
                return $run;
            }

            sleep(1); // Attende un secondo prima di fare un'altra richiesta
        }
    }

    public function render()
    {
        return view('livewire.quoter');
    }
}

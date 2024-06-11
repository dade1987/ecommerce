<?php

namespace App\Livewire;

use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Log;
use Livewire\Component;
use OpenAI;
use OpenAI\Client;

class Quoter extends Component
{
    private ?Client $client = null;

    public array $messages = [];

    public string $userInput = '';

    public string $quoteContent = '';

    public string $threadId;

    public function mount()
    {

        $this->createThread();
        $this->messages[] = [
            'sender' => 'Cavallini Service',
            'content' => 'Buongiorno. Benvenuto. Questa è una chat per richiedere informazioni sui nostri prodotti. Attendi un attimo in linea...',
            'type' => 'chatbot',
        ];
        $this->sendMessageToServer('Intro');
    }

    public function initializeClient()
    {
        $apiKey = env('OPENAI_API_KEY_GIULIANO');
        if ($this->client == null) {
            $this->client = OpenAI::client($apiKey);
        }
    }

    public function createThread()
    {
        $this->initializeClient();
        $thread = $this->client->threads()->create([]);
        $this->threadId = $thread->id;
        Cookie::queue('thread_id', $this->threadId, 60);
    }

    public function sendMessage()
    {
        $this->initializeClient();
        $this->messages[] = [
            'sender' => 'Tu',
            'content' => $this->userInput,
            'type' => 'user',
        ];

        $this->sendMessageToServer($this->userInput);

        $this->userInput = '';
    }

    public function generateQuote()
    {
        $this->sendMessageToServer('Genera Preventivo');
    }

    private function sendMessageToServer($userMessage)
    {
        $this->initializeClient();
        $content = $this->getContentBasedOnMessage($userMessage);

        $this->client->threads()->messages()->create($this->threadId, [
            'role' => 'user',
            'content' => $content,
        ]);

        $run = $this->client->threads()->runs()->create(
            threadId: $this->threadId,
            parameters: [
                'assistant_id' => 'asst_58zvAKzBKYjDE6tukTFeyyPZ',
                'instructions' => 'Sei una segretaria di un\'azienda di serramenti e devi fare un preventivo ad un cliente. Cerca per ogni domanda le informazioni dentro al file.',
                'tools' => [
                    [
                        'type' => 'file_search',
                    ],
                ],
                'model' => 'gpt-4-turbo',
            ]
        );

        $this->retrieveRunResult($run->id);

        $messages = $this->client->threads()->messages()->list($this->threadId)->data;

        $response = $messages[0]->content[0]->text->value;

        $this->messages[] = [
            'sender' => 'Cavallini Service',
            'content' => $response,
            'type' => 'chatbot',
        ];

        if ($userMessage === 'Genera Preventivo') {
            $this->quoteContent = $response;
            $this->dispatchBrowserEvent('showQuoteModal');
        }
    }

    private function getContentBasedOnMessage($userMessage)
    {
        if ($userMessage === 'Intro') {
            return 'Chiedimi il mio nome, cognome, indirizzo email, numero di telefono e l\'indirizzo dove bisogna installare i serramenti, poi fare un preventivo. Scrivi la domanda in modo conciso';
        } elseif ($userMessage === 'Genera Preventivo') {
            return 'Genera un preventivo per l\'azienda Cavallini Service con sede a Milano in via Tal dei Tali numero 15, partita IVA numero 01234567899. Il preventivo deve essere in formato JSON Object, con la seguente struttura:\n\n- La prima sezione deve essere \'company_info\' con le colonne \'name\' (nome dell\'azienda), \'address\' (indirizzo dell\'azienda) e \'vat_number\' (partita IVA dell\'azienda).\n- La seconda sezione deve essere \'personal_info\' con le colonne \'first_name\' (nome), \'last_name\' (cognome), \'phone_number\' (numero di telefono), \'email\' (indirizzo email), \'site_address\' (luogo del cantiere con indirizzo e numero civico assieme), \'city\' (città), \'province\' (provincia) e \'country\' (paese).\n- La terza sezione deve essere \'products\' che contiene una lista di prodotti e servizi (inclusa la manodopera se c\'è scritto il prezzo nel file), ognuno con le colonne \'name\' (nome del prodotto o servizio), \'quantity\' (quantità del prodotto o servizio) e \'price\' (prezzo del singolo prodotto o servizio).\n- L\'ultima sezione deve essere \'price_info\' con le colonne \'net_price\' (prezzo netto), \'vat\' (IVA al 22%) e \'total\' (prezzo totale).\n\nEsempio di JSON da generare:\n\n{\n  "company_info": {\n    "name": "Cavallini Service",\n    "address": "via Tal dei Tali, 15, Milano",\n    "vat_number": "01234567899"\n  },\n  "personal_info": {\n    "first_name": "Mario",\n    "last_name": "Rossi",\n    "phone_number": "1234567890",\n    "email": "mario.rossi@example.com",\n    "site_address": "via Esempio, 20",\n    "city": "Milano",\n    "province": "MI",\n    "country": "Italia"\n  },\n  "products": [\n    {\n      "name": "Finestra in PVC",\n      "quantity": 10,\n      "price": 200\n    },\n    {\n      "name": "Porta in legno",\n      "quantity": 5,\n      "price": 300\n    },\n    {\n      "name": "Manodopera montaggio finestra",\n      "quantity": 10,\n      "price": 150\n    },\n    {\n      "name": "Manodopera montaggio porta",\n      "quantity": 5,\n      "price": 150\n    }\n  ],\n  "price_info": {\n    "net_price": 5500,\n    "vat": 1210,\n    "total": 6710\n  }\n}. Non rispondere con nessun altra informazioni oltre il JSON.';
        } else {
            return $userMessage.' . Se non si tratta della comunicazione dei dati personali, cerca tutte le informazioni richieste nel file, altrimenti chiedimi quali informazioni sui serramenti desidero conoscere, cercando tutte le informazioni per le risposte nel file, usando il tool file_search.';
        }
    }

    private function retrieveRunResult($runId)
    {
        $this->initializeClient();
        while (true) {
            $run = $this->client->threads()->runs()->retrieve($this->threadId, $runId);

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

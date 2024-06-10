<?php

namespace App\Http\Controllers;

use App\Models\Quoter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use OpenAI;
use OpenAI\Client;

class QuoterController extends Controller
{
    public Client $client;

    public function __construct()
    {
        $apiKey = env('OPENAI_API_KEY_GIULIANO');
        $this->client = OpenAI::client($apiKey);
    }

    public function createThread()
    {
        $minutes = 60; // Durata del cookie in minuti
        $thread = $this->client->threads()->create([]);

        Log::info('thread id '.$thread->id);

        return response('Thread_id cookie impostato')->cookie(
            'thread_id',
            $thread->id,
            $minutes
        );
    }

    public function sendMessage(Request $request)
    {
        $threadId = $request->cookie('thread_id');
        $userMessage = $request->input('message');

        Log::info('thread id '.$threadId);

        $content = $this->generateContentBasedOnMessage($userMessage);

        Quoter::create(['thread_id'=>$threadId, 'role' => 'user', 'content' => $content]);

        $this->client->threads()->messages()->create($threadId, [
            'role' => 'user',
            'content' => $content,
        ]);

        $run = $this->client->threads()->runs()->create(
            threadId: $threadId,
            parameters: [
                'assistant_id' => 'asst_34SA8ZkwlHiiXxNufoZYddn0',
                'instructions' => 'Sei una segretaria che fa preventivi per un\'azienda di sviluppo software. Cerca le informazioni nei file che ti ho passato.',
                'tools' => [
                    [
                        'type' => 'file_search',
                    ],
                ],
                'model' => 'gpt-4-turbo',
            ]
        );

        $this->retrieveRunResult($threadId, $run->id);

        $messages = $this->client->threads()->messages()->list($threadId)->data;

        $content = $messages[0]->content[0]->text->value;

        Quoter::create(['thread_id'=>$threadId, 'role' => 'chatbot', 'content' => $content]);

        return response()->json([
            'response' => $content,
        ]);
    }

    private function generateContentBasedOnMessage($userMessage)
    {
        if ($userMessage === 'Intro') {
            return 'Chiedimi il mio nome, cognome, indirizzo email, numero di telefono, poi fare il preventivo dei servizi richiesti. Scrivi la domanda in modo conciso';
        } elseif ($userMessage === 'Genera Preventivo') {
            return 'Genera un preventivo per l\'azienda Cavallini Service con sede a Noale in via del Musonetto, 4, partita IVA numero CVLDVD87M23L736P. Il preventivo deve essere in formato JSON Object, con la seguente struttura:\\n\\n- La prima sezione deve essere \'company_info\' con le colonne \'name\' (nome dell\'azienda), \'address\' (indirizzo dell\'azienda) e \'vat_number\' (partita IVA dell\'azienda).\\n- La seconda sezione deve essere \'personal_info\' con le colonne \'first_name\' (nome), \'last_name\' (cognome), \'phone_number\' (numero di telefono), \'email\' (indirizzo email).\\n- La terza sezione deve essere \'products\' che contiene una lista di prodotti e servizi (inclusa la manodopera se c\'è scritto il prezzo nel file), ognuno con le colonne \'name\' (nome del prodotto o servizio), \'quantity\' (quantità del prodotto o servizio) e \'price\' (prezzo del singolo prodotto o servizio).\\n- L\'ultima sezione deve essere \'price_info\' con le colonne \'net_price\' (prezzo netto), \'vat\' (IVA al 22%) e \'total\' (prezzo totale).\\n\\nEsempio di JSON da generare:\\n\\n{\\n  "company_info": {\\n    "name": "Cavallini Service",\\n    "address": "via Tal dei Tali, 15, Milano",\\n    "vat_number": "01234567899"\\n  },\\n  "personal_info": {\\n    "first_name": "Mario",\\n    "last_name": "Rossi",\\n    "phone_number": "1234567890",\\n    "email": "mario.rossi@example.com",\\n    "site_address": "via Esempio, 20",\\n    "city": "Milano",\\n    "province": "MI",\\n    "country": "Italia"\\n  },\\n  "products": [\\n    {\\n      "name": "Finestra in PVC",\\n      "quantity": 10,\\n      "price": 200\\n    },\\n    {\\n      "name": "Porta in legno",\\n      "quantity": 5,\\n      "price": 300\\n    },\\n    {\\n      "name": "Manodopera montaggio finestra",\\n      "quantity": 10,\\n      "price": 150\\n    },\\n    {\\n      "name": "Manodopera montaggio porta",\\n      "quantity": 5,\\n      "price": 150\\n    }\\n  ],\\n  "price_info": {\\n    "net_price": 5500,\\n    "vat": 1210,\\n    "total": 6710\\n  }\\n}. Non rispondere con nessun altra informazioni oltre il JSON. Il JSON non deve contenere commenti e dev\'essere ben formattato.';
        } else {
            return $userMessage.' . Se non si tratta della comunicazione dei dati personali, cerca tutte le informazioni richieste nel file, altrimenti chiedimi quali informazioni sui servizi desidero conoscere, cercando tutte le informazioni per le risposte nel file, usando il tool file_search.';
        }
    }

    private function retrieveRunResult($threadId, $runId)
    {
        while (true) {
            $run = $this->client->threads()->runs()->retrieve($threadId, $runId);

            Log::info(var_export($run, true));

            if ($run->status === 'completed') {
                return $run;
            }

            sleep(1); // Attende un secondo prima di fare un'altra richiesta
        }
    }
}

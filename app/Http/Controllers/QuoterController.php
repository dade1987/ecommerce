<?php

namespace App\Http\Controllers;

use App\Models\Quoter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use OpenAI;
use OpenAI\Client;
use function Safe\fopen;

class QuoterController extends Controller
{
    public Client $client;

    public function __construct()
    {
        $apiKey = config('openapi.key');
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

    public function uploadFile(Request $request)
    {
        $threadId = $request->cookie('thread_id');
        $userMessage = $request->input('message');

        Log::info('thread id '.$threadId);

        // Verifica se è stato caricato un file
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $path = $file->store('uploads', 'public'); // Salva il file e ottieni la path

            // Carica il file utilizzando la path
            $response = $this->client->files()->upload([
                'purpose' => 'assistants',
                'file' => fopen(storage_path('app/public/'.$path), 'r'), // Usa la path del file caricato
            ]);

            Quoter::create(['thread_id' => $threadId, 'role' => 'user', 'content' => 'Caricamento bolletta']);

            $this->client->threads()->messages()->create($threadId, [
                'role' => 'user',
                'content' => 'Estrai i consumi della fascia F1, della fascia F2 e della fascia F3 dal file caricato. Estrai nome e cognome e indirizzo del cantiere e se l\'utente è privato o azienda. Poi continua con le domande',
                'attachments' => [['file_id' => $response->id, 'tools' => [['type' => 'file_search']]]],
            ]);

            $run = $this->client->threads()->runs()->create(
                threadId: $threadId,
                parameters: [
                    'assistant_id' => 'asst_34SA8ZkwlHiiXxNufoZYddn0',
                ]
            );

            $this->retrieveRunResult($threadId, $run->id);

            $messages = $this->client->threads()->messages()->list($threadId)->data;

            $content = $messages[0]->content[0]->text->value;

            Quoter::create(['thread_id' => $threadId, 'role' => 'chatbot', 'content' => $content]);

            return response()->json([
                'response' => $content,
            ]);
        } else {
            return response()->json(['error' => 'No file uploaded'], 400);
        }
    }

    public function sendMessage(Request $request)
    {
        $threadId = $request->cookie('thread_id');
        $userMessage = $request->input('message');

        Log::info('thread id '.$threadId);

        $content = $this->generateContentBasedOnMessage($userMessage);

        Quoter::create(['thread_id' => $threadId, 'role' => 'user', 'content' => $userMessage]);

        $this->client->threads()->messages()->create($threadId, [
            'role' => 'user',
            'content' => $content,
        ]);

        //Sei una segretaria che fa preventivi per un\'azienda di sviluppo software. Cerca le informazioni nei file che ti ho passato.
        $run = $this->client->threads()->runs()->create(
            threadId: $threadId,
            parameters: [
                'assistant_id' => 'asst_34SA8ZkwlHiiXxNufoZYddn0',
                //'instructions' => 'Devi fare il preventivo per un azienda di fotovoltaico. Ti serve che ti carico il file della bolletta e alcune informazioni aggiuntive.',
                'instructions' => 'Sei una segretaria che fa preventivi per un\'azienda di sviluppo software. Cerca le informazioni nei file che ti ho passato.',
                'tools' => [
                    [
                        'type' => 'file_search',
                    ],
                ],
                'model' => 'gpt-4o',
            ]
        );

        $this->retrieveRunResult($threadId, $run->id);

        $messages = $this->client->threads()->messages()->list($threadId)->data;

        $content = $messages[0]->content[0]->text->value;

        Quoter::create(['thread_id' => $threadId, 'role' => 'chatbot', 'content' => $content]);

        return response()->json([
            'response' => $content,
        ]);
    }

    private function generateContentBasedOnMessage($userMessage)
    {
        //Chiedimi il mio nome, cognome, indirizzo email, numero di telefono, poi fare il preventivo dei servizi richiesti. Scrivi la domanda in modo conciso
        if ($userMessage === 'Intro') {
            /*return '
            Esegui i seguenti punti uno alla volta:

            1. SCRIVI QUALI SONO LE ORE DI PIENO SOLE PER ZONA IN ITALIA ANNUALI. CHIEDI SE Ti trovi a nord, centro o sud?
            2. Il tetto è piano, poco inclinato o molto inclinato?
            3. Lo sai che anche i tetti rivolti a nord oggi sono in grado di produrre energia solare? Da che parte è rivolto il tuo?
            4. Quanta energia ti serve? Inserisci i valori annuali di F1, F2 e F3 che trovi nella tua fattura. Se non li trovi puoi caricare la bolletta e sarà l\'AI a determinarli
            5. Conosci le comunità energetiche della tua città, oppure desideri conoscerle?
            6. Che cosa ne pensi delle auto elettriche? Ritieni che sia utile utilizzarle?
            7. Desideri generare energia per un abitazione privata di tua proprietà o per un azienda?
            8. Desideri scaricare un PDF dei dati generati da AI-799?
            9. Indica la tua email o numero di telefono
            10. Vuoi conoscere le aziende vicino a te che hanno le migliori recensioni in materia di realizzazione di impianti di energia solare?
            11. Io sono un intelligenza artificiale. Vuoi parlare con un esperto ai fini di prenotare un sopralluogo senza impegno?
            11a (solo se ha risposto di si alla domanda precedente). In che orari vuoi essere contattato?

            Alla fine crea un breve riassunto dei dati inseriti.

            NB per fare una stima dei costi, l\'impianto consigliato parte da 6kw.
            ogni pannello da 5mq genera 1kw, quindi per fare 6kw servono 30mq.
            la dimensione dell\'impianto si calcola con la formula (CONSUMO ANNUO + 10% / 1200)';
*/

            return 'Chiedimi il mio nome, cognome, indirizzo email, numero di telefono, poi fare il preventivo dei servizi richiesti. Scrivi la domanda in modo conciso';
            //crea un pdf con i dati di riepilogo
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
            $run = $this->client->threads()->runs()->retrieve($threadId, $runId);

            Log::info(var_export($run, true));

            if ($run->status === 'completed') {
                return $run;
            }

            sleep(1); // Attende un secondo prima di fare un'altra richiesta
        }
    }
}

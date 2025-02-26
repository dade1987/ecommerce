<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SommelierApiController extends Controller
{
    /**
     * Restituisce informazioni sui vini.
     */
    public function getWines(Request $request)
    {
        // Parametri opzionali dalla query string
        $name = $request->query('name');
        $region = $request->query('region');
        $vintage = $request->query('vintage');

        // Dati fittizi di esempio
        $wines = [
            [
                'id' => 1,
                'name' => 'Chianti Classico',
                'region' => 'Toscana',
                'vintage' => '2015',
                'description' => 'Vino rosso robusto con note fruttate e speziate.',
            ],
            [
                'id' => 2,
                'name' => 'Barolo',
                'region' => 'Piemonte',
                'vintage' => '2014',
                'description' => 'Vino corposo con tannini intensi e un lungo finale.',
            ],
            [
                'id' => 3,
                'name' => 'Brunello di Montalcino',
                'region' => 'Toscana',
                'vintage' => '2016',
                'description' => 'Vino rosso elegante con note di ciliegia e spezie.',
            ],
            [
                'id' => 4,
                'name' => 'Amarone della Valpolicella',
                'region' => 'Veneto',
                'vintage' => '2013',
                'description' => 'Vino rosso intenso con note di frutta secca e cioccolato.',
            ],
            [
                'id' => 5,
                'name' => 'Prosecco',
                'region' => 'Veneto',
                'vintage' => 'NV',
                'description' => 'Vino spumante leggero e frizzante con note di mela e pera.',
            ],
            [
                'id' => 6,
                'name' => 'Sauvignon Blanc',
                'region' => 'Friuli Venezia Giulia',
                'vintage' => '2018',
                'description' => 'Vino bianco fresco con note di agrumi e erbe aromatiche.',
            ],
            [
                'id' => 7,
                'name' => 'Pinot Grigio',
                'region' => 'Trentino-Alto Adige',
                'vintage' => '2019',
                'description' => 'Vino bianco leggero con note di mela verde e fiori bianchi.',
            ],
            [
                'id' => 8,
                'name' => 'Vermentino',
                'region' => 'Sardegna',
                'vintage' => '2020',
                'description' => 'Vino bianco aromatico con note di pesca e mandorla.',
            ],
            [
                'id' => 9,
                'name' => 'Nero d\'Avola',
                'region' => 'Sicilia',
                'vintage' => '2017',
                'description' => 'Vino rosso intenso con note di frutti di bosco e spezie.',
            ],
            [
                'id' => 10,
                'name' => 'Lambrusco',
                'region' => 'Emilia-Romagna',
                'vintage' => 'NV',
                'description' => 'Vino rosso frizzante con note di frutti rossi e fiori.',
            ],
        ];

        // Filtra in base ai parametri forniti
        if ($name) {
            $wines = array_filter($wines, function ($wine) use ($name) {
                return stripos($wine['name'], $name) !== false;
            });
        }
        if ($region) {
            $wines = array_filter($wines, function ($wine) use ($region) {
                return stripos($wine['region'], $region) !== false;
            });
        }
        if ($vintage) {
            $wines = array_filter($wines, function ($wine) use ($vintage) {
                return $wine['vintage'] === $vintage;
            });
        }

        return response()->json(array_values($wines));
    }

    /**
     * Restituisce un suggerimento per l'abbinamento cibo-vino.
     */
    public function getPairing(Request $request)
    {
        $food = $request->query('food');
        $preferences = $request->query('preferences');

        // Dati fittizi per abbinamenti
        $pairings = [
            'pizza' => [
                'suggestion' => 'Prova un Chianti Classico, ideale per una pizza margherita.',
            ],
            'carne' => [
                'suggestion' => 'Un Barolo si abbina perfettamente a piatti di carne rossa.',
            ],
            'pesce' => [
                'suggestion' => 'Un Vermentino è perfetto per accompagnare piatti di pesce.',
            ],
            'formaggio' => [
                'suggestion' => 'Abbina un Sauvignon Blanc con formaggi freschi.',
            ],
            'cioccolato' => [
                'suggestion' => 'Un Porto si sposa bene con il cioccolato fondente.',
            ],
            'pasta' => [
                'suggestion' => 'Un Pinot Grigio è un ottimo abbinamento per piatti di pasta leggeri.',
            ],
            'insalata' => [
                'suggestion' => 'Prova un Rosé per un abbinamento fresco con l\'insalata.',
            ],
        ];

        $result = [];
        if ($food) {
            foreach ($pairings as $key => $pairing) {
                if (stripos($food, $key) !== false) {
                    $result = $pairing;
                    break;
                }
            }
        }

        if (empty($result)) {
            $result = ['suggestion' => 'Non abbiamo trovato un abbinamento specifico. Prova un vino versatile come il Merlot.'];
        }

        return response()->json($result);
    }

    /**
     * Restituisce curiosità o aneddoti sul mondo del vino.
     */
    public function getTrivia(Request $request)
    {
        $topic = $request->query('topic');

        $trivia = [
            'default' => 'Il vino ha una storia millenaria e ogni regione vanta peculiarità uniche.',
            'chianti'  => 'Il Chianti Classico è principalmente prodotto con uve Sangiovese.',
            'barolo' => 'Il Barolo è conosciuto come il "Re dei Vini" ed è prodotto con uve Nebbiolo.',
            'vermentino' => 'Il Vermentino è un vino bianco aromatico, tipico della Sardegna e della Liguria.',
            'sauvignon' => 'Il Sauvignon Blanc è un vino bianco fresco e aromatico, originario della regione di Bordeaux.',
            'porto' => 'Il Porto è un vino liquoroso prodotto nella regione del Douro, in Portogallo.',
            'pinot' => 'Il Pinot Grigio è un vino bianco leggero e rinfrescante, molto popolare in Italia.',
            'rosé' => 'Il Rosé è un vino che può essere prodotto con diverse varietà di uve rosse, ma con una breve macerazione delle bucce.',
            'merlot' => 'Il Merlot è un vino rosso morbido e fruttato, molto versatile negli abbinamenti.',
        ];

        $result = $trivia['default'];
        if ($topic && isset($trivia[strtolower($topic)])) {
            $result = $trivia[strtolower($topic)];
        }

        return response()->json(['trivia' => $result]);
    }

    /**
     * Restituisce informazioni su eventi, degustazioni o promozioni legate ai vini.
     */
    public function getEvents(Request $request)
    {
        $region = $request->query('region');

        $events = [
            [
                'id' => 1,
                'title' => 'Festival del Vino di Roma',
                'region' => 'Lazio',
                'date' => '2025-05-20',
                'description' => 'Un evento imperdibile per gli amanti del vino, con degustazioni e masterclass.',
            ],
            [
                'id' => 2,
                'title' => 'Notte dei Vini a Venezia',
                'region' => 'Veneto',
                'date' => '2025-06-18',
                'description' => 'Una serata magica tra i canali di Venezia, con degustazioni di vini locali.',
            ],
            [
                'id' => 3,
                'title' => 'Sagra del Vino di Napoli',
                'region' => 'Campania',
                'date' => '2025-07-25',
                'description' => 'Un evento tradizionale con degustazioni di vini campani e piatti tipici.',
            ],
            [
                'id' => 4,
                'title' => 'Cantine Aperte in Piemonte',
                'region' => 'Piemonte',
                'date' => '2025-08-12',
                'description' => 'Visite guidate e degustazioni nelle cantine più rinomate del Piemonte.',
            ],
            [
                'id' => 5,
                'title' => 'Festa del Vino di Palermo',
                'region' => 'Sicilia',
                'date' => '2025-09-05',
                'description' => 'Un evento per scoprire i vini siciliani, con musica e spettacoli dal vivo.',
            ],
            [
                'id' => 6,
                'title' => 'Degustazione di Vini Friulani',
                'region' => 'Friuli Venezia Giulia',
                'date' => '2025-10-10',
                'description' => 'Scopri i sapori unici dei vini friulani in un evento esclusivo.',
            ],
            [
                'id' => 7,
                'title' => 'Vino e Arte a Firenze',
                'region' => 'Toscana',
                'date' => '2025-11-15',
                'description' => 'Un connubio perfetto tra arte e vino nella splendida cornice di Firenze.',
            ],
            [
                'id' => 8,
                'title' => 'Weekend del Vino a Torino',
                'region' => 'Piemonte',
                'date' => '2025-12-01',
                'description' => 'Un fine settimana dedicato ai migliori vini piemontesi.',
            ],
            [
                'id' => 9,
                'title' => 'Vini e Sapori di Sardegna',
                'region' => 'Sardegna',
                'date' => '2026-01-20',
                'description' => 'Un viaggio tra i sapori e i profumi dei vini sardi.',
            ],
            [
                'id' => 10,
                'title' => 'Fiera del Vino di Bari',
                'region' => 'Puglia',
                'date' => '2026-02-14',
                'description' => 'Un evento per scoprire i vini pugliesi, con degustazioni e incontri con i produttori.',
            ],
        ];

        if ($region) {
            $events = array_filter($events, function ($event) use ($region) {
                return stripos($event['region'], $region) !== false;
            });
        }

        return response()->json(array_values($events));
    }
}

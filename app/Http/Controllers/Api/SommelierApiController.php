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

        // Array dei 20 vini più venduti in Veneto
        $wines = [
            [
                'id' => 1,
                'name' => 'Amarone della Valpolicella Classico',
                'region' => 'Veneto',
                'vintage' => '2019',
                'description' => 'Vino rosso robusto ed elegante, con note di ciliegia e spezie.',
            ],
            [
                'id' => 2,
                'name' => 'Prosecco DOC',
                'region' => 'Veneto',
                'vintage' => 'NV',
                'description' => 'Spumante fresco e frizzante, ideale per ogni occasione.',
            ],
            [
                'id' => 3,
                'name' => 'Soave Classico',
                'region' => 'Veneto',
                'vintage' => '2018',
                'description' => 'Vino bianco aromatico, con sentori di frutta e fiori.',
            ],
            [
                'id' => 4,
                'name' => 'Lugana DOC',
                'region' => 'Veneto',
                'vintage' => '2020',
                'description' => 'Vino bianco fresco, con note di agrumi e fiori bianchi.',
            ],
            [
                'id' => 5,
                'name' => 'Bardolino',
                'region' => 'Veneto',
                'vintage' => '2017',
                'description' => 'Vino rosso leggero e fruttato, perfetto per piatti delicati.',
            ],
            [
                'id' => 6,
                'name' => 'Valpolicella Ripasso',
                'region' => 'Veneto',
                'vintage' => '2016',
                'description' => 'Rosso intenso con tannini morbidi e note speziate.',
            ],
            [
                'id' => 7,
                'name' => 'Custoza DOC',
                'region' => 'Veneto',
                'vintage' => '2019',
                'description' => 'Vino bianco secco ed elegante, con sentori floreali.',
            ],
            [
                'id' => 8,
                'name' => 'Lessini Durello Spumante',
                'region' => 'Veneto',
                'vintage' => '2015',
                'description' => 'Spumante metodo classico, fresco e persistente.',
            ],
            [
                'id' => 9,
                'name' => 'Rosso di Verona IGT',
                'region' => 'Veneto',
                'vintage' => '2018',
                'description' => 'Rosso equilibrato con profumi di frutti rossi.',
            ],
            [
                'id' => 10,
                'name' => 'Pinot Grigio Veneto',
                'region' => 'Veneto',
                'vintage' => '2019',
                'description' => 'Bianco leggero, fresco e fruttato.',
            ],
            [
                'id' => 11,
                'name' => 'Merlot Veneto',
                'region' => 'Veneto',
                'vintage' => '2017',
                'description' => 'Rosso morbido con note di prugna e ciliegia.',
            ],
            [
                'id' => 12,
                'name' => 'Cabernet Sauvignon Veneto',
                'region' => 'Veneto',
                'vintage' => '2018',
                'description' => 'Rosso strutturato con tannini decisi e aromi di frutti neri.',
            ],
            [
                'id' => 13,
                'name' => 'Sauvignon Blanc Veneto',
                'region' => 'Veneto',
                'vintage' => '2020',
                'description' => 'Bianco fresco con note erbacee e agrumate.',
            ],
            [
                'id' => 14,
                'name' => 'Glera Prosecco',
                'region' => 'Veneto',
                'vintage' => 'NV',
                'description' => 'Spumante vivace e leggero, perfetto come aperitivo.',
            ],
            [
                'id' => 15,
                'name' => 'Cabernet Franc Veneto',
                'region' => 'Veneto',
                'vintage' => '2016',
                'description' => 'Rosso elegante con aromi di frutti rossi e spezie.',
            ],
            [
                'id' => 16,
                'name' => 'Riesling Veneto',
                'region' => 'Veneto',
                'vintage' => '2018',
                'description' => 'Bianco aromatico con note minerali e fruttate.',
            ],
            [
                'id' => 17,
                'name' => 'Veneto Rosé',
                'region' => 'Veneto',
                'vintage' => '2020',
                'description' => 'Rosé fresco e fruttato, ideale per l’estate.',
            ],
            [
                'id' => 18,
                'name' => 'Valpolicella Classico',
                'region' => 'Veneto',
                'vintage' => '2017',
                'description' => 'Rosso equilibrato con profumi intensi di frutta.',
            ],
            [
                'id' => 19,
                'name' => 'Valpolicella Superiore',
                'region' => 'Veneto',
                'vintage' => '2018',
                'description' => 'Rosso strutturato con note di spezie e vaniglia.',
            ],
            [
                'id' => 20,
                'name' => 'Veneto Rosso IGT',
                'region' => 'Veneto',
                'vintage' => '2019',
                'description' => 'Vino rosso versatile, perfetto con carni e formaggi.',
            ],
            [
                'id' => 21,
                'name' => 'Raboso',
                'region' => 'Veneto',
                'vintage' => '2018',
                'description' => 'Vino rosso robusto e tannico, con note di frutti di bosco e spezie.',
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

        // Suggerimenti di abbinamento basati sui vini veneti
        $pairings = [
            'pizza' => [
                'suggestion' => 'Prova il Prosecco DOC: un abbinamento fresco per una pizza margherita.',
            ],
            'carne' => [
                'suggestion' => 'Un Amarone della Valpolicella Classico si sposa bene con carni rosse.',
            ],
            'pesce' => [
                'suggestion' => 'Il Soave Classico è perfetto per piatti di pesce e crostacei.',
            ],
            'formaggio' => [
                'suggestion' => 'Un Lugana DOC si abbina egregiamente con formaggi stagionati.',
            ],
            'cioccolato' => [
                'suggestion' => 'Prova un Veneto Rosé per un contrasto interessante con il cioccolato fondente.',
            ],
            'pasta' => [
                'suggestion' => 'Il Valpolicella Ripasso è ideale per piatti di pasta ricchi e saporiti.',
            ],
            'insalata' => [
                'suggestion' => 'Il Sauvignon Blanc Veneto dona freschezza ad una buona insalata estiva.',
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
            $result = ['suggestion' => 'Non abbiamo trovato un abbinamento specifico. Prova un vino versatile come il Veneto Rosso IGT.'];
        }

        return response()->json($result);
    }

    /**
     * Restituisce curiosità o aneddoti sul mondo del vino.
     */
    public function getTrivia(Request $request)
    {
        $topic = $request->query('topic');

        // Curiosità relative ai vini veneti
        $trivia = [
            'default'   => 'Il Veneto è una delle regioni vinicole più importanti d’Italia, celebre per Prosecco, Amarone, Soave e Lugana.',
            'amarone'   => 'L\'Amarone della Valpolicella è un vino rosso corposo, noto per il suo processo di appassimento delle uve che ne esalta la complessità aromatica.',
            'prosecco'  => 'Il Prosecco è un vino spumante leggero e frizzante, perfetto per aperitivi e celebrazioni, famoso per le sue note di mela verde e fiori bianchi.',
            'soave'     => 'Il Soave è un vino bianco elegante, caratterizzato da note di mandorla e fiori bianchi, ideale per accompagnare piatti di pesce e antipasti.',
            'lugana'    => 'Il Lugana DOC è un vino bianco fresco e minerale, prodotto tra Veneto e Lombardia, apprezzato per la sua versatilità e capacità di invecchiamento.',
            'valpolicella' => 'I vini Valpolicella, come il Ripasso e il Classico, sono noti per la loro struttura e complessità, con note di ciliegia e spezie, perfetti per piatti di carne.',
            'prosecco_superiore' => 'Il Prosecco Superiore DOCG è una versione premium del Prosecco, prodotto nelle colline di Conegliano e Valdobbiadene, con una maggiore complessità e finezza.',
            'bardolino' => 'Il Bardolino è un vino rosso leggero e fruttato, con note di ciliegia e spezie, ideale per piatti di pasta e carni bianche.',
            'custoza' => 'Il Custoza è un vino bianco aromatico, con sentori di frutta esotica e fiori, perfetto per piatti di pesce e insalate.',
            'recioto' => 'Il Recioto della Valpolicella è un vino dolce e ricco, ottenuto da uve appassite, ideale per dessert e formaggi erborinati.',
            'durello' => 'Il Durello è un vino spumante autoctono del Veneto, noto per la sua acidità vivace e le note di agrumi, perfetto come aperitivo.',
            'garganega' => 'La Garganega è l\'uva principale del Soave, apprezzata per la sua capacità di produrre vini eleganti e longevi, con note di mandorla e fiori.',
            'raboso' => 'Il Raboso è un vino rosso robusto e tannico, con note di frutti di bosco e spezie, tradizionalmente invecchiato in botti di legno.',
            'torcolato' => 'Il Torcolato è un vino dolce ottenuto da uve Vespaiola appassite, noto per le sue note di miele e frutta secca, perfetto per dessert.',
            'pinot_grigio' => 'Il Pinot Grigio del Veneto è un vino bianco fresco e fruttato, con note di pera e mela, ideale per aperitivi e piatti leggeri.',
            'merlot' => 'Il Merlot del Veneto è un vino rosso morbido e vellutato, con note di prugna e cioccolato, perfetto per piatti di carne e formaggi.',
            'cabernet_sauvignon' => 'Il Cabernet Sauvignon del Veneto è un vino rosso strutturato, con note di ribes nero e spezie, ideale per piatti di carne e selvaggina.',
            'chardonnay' => 'Lo Chardonnay del Veneto è un vino bianco elegante, con note di frutta tropicale e vaniglia, perfetto per piatti di pesce e pollame.',
            'moscato' => 'Il Moscato del Veneto è un vino dolce e aromatico, con note di pesca e fiori, ideale per dessert e frutta.',
            'verduzzo' => 'Il Verduzzo è un vino bianco dolce, con note di miele e frutta secca, perfetto per dessert e formaggi stagionati.',
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

        // Eventi esclusivamente in Veneto
        $events = [
            [
                'id' => 1,
                'title' => 'Fiera del Vino di Verona',
                'region' => 'Veneto',
                'date' => '2025-05-20',
                'description' => 'Degustazioni e masterclass dedicate ai vini veneti, tra cui Amarone e Prosecco.',
            ],
            [
                'id' => 2,
                'title' => 'Notte dei Vini a Treviso',
                'region' => 'Veneto',
                'date' => '2025-06-18',
                'description' => 'Evento esclusivo con degustazioni di Soave e Lugana.',
            ],
            [
                'id' => 3,
                'title' => 'Festival del Prosecco',
                'region' => 'Veneto',
                'date' => '2025-07-25',
                'description' => 'Celebrazione del Prosecco e delle tradizioni venete.',
            ],
            [
                'id' => 4,
                'title' => 'Degustazione Valpolicella',
                'region' => 'Veneto',
                'date' => '2025-08-12',
                'description' => 'Scopri i segreti del Valpolicella Classico e Ripasso in degustazione.',
            ],
            [
                'id' => 5,
                'title' => 'Serata di Vini Rossi Veneti',
                'region' => 'Veneto',
                'date' => '2025-09-05',
                'description' => 'Evento dedicato ai vini rossi della regione, perfetto per gli appassionati.',
            ],
            [
                'id' => 6,
                'title' => 'Giornata del Soave',
                'region' => 'Veneto',
                'date' => '2025-10-10',
                'description' => 'Esplora l\'eleganza del Soave Classico con degustazioni guidate.',
            ],
            [
                'id' => 7,
                'title' => 'Mercato del Vino a Vicenza',
                'region' => 'Veneto',
                'date' => '2025-11-15',
                'description' => 'Una giornata tra produttori e degustazioni dei migliori vini veneti.',
            ],
            [
                'id' => 8,
                'title' => 'Festa del Valpolicella',
                'region' => 'Veneto',
                'date' => '2025-12-01',
                'description' => 'Un viaggio tra i sapori del Valpolicella Superiore e Classico.',
            ],
            [
                'id' => 9,
                'title' => 'Weekend dei Vini Bianchi Veneti',
                'region' => 'Veneto',
                'date' => '2026-01-20',
                'description' => 'Degustazioni di Soave, Lugana e Sauvignon Blanc in un evento esclusivo.',
            ],
            [
                'id' => 10,
                'title' => 'Evento Vino e Cucina a Padova',
                'region' => 'Veneto',
                'date' => '2026-02-14',
                'description' => 'Abbinamenti enogastronomici con i migliori vini veneti.',
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

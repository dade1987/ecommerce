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
                'title' => 'Degustazione di vini toscani',
                'region' => 'Toscana',
                'date' => '2025-03-15',
                'description' => 'Un evento per scoprire i migliori vini della Toscana.',
            ],
            [
                'id' => 2,
                'title' => 'Wine & Dine a Milano',
                'region' => 'Lombardia',
                'date' => '2025-04-10',
                'description' => 'Abbinamenti gourmet e degustazione di vini pregiati.',
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

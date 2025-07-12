<?php

namespace Database\Seeders;

use App\Models\OperatorFeedback;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class OperatorFeedbackSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $feedbacks = [
            // Richieste in attesa
            [
                'titolo' => 'Migliorare interfaccia movimenti inventario',
                'descrizione' => 'Sarebbe utile avere un campo di ricerca rapida per i prodotti durante la creazione di movimenti. Attualmente bisogna scorrere tutta la lista.',
                'status' => 'pending',
                'metadata' => [
                    'categoria' => 'UI/UX',
                    'priorita' => 'media',
                    'area' => 'Gestione Inventario'
                ]
            ],
            [
                'titolo' => 'Notifiche automatiche per giacenze basse',
                'descrizione' => 'Implementare un sistema di notifiche automatiche quando la giacenza di un prodotto scende sotto una soglia minima configurabile.',
                'status' => 'pending',
                'metadata' => [
                    'categoria' => 'Automazione',
                    'priorita' => 'alta',
                    'area' => 'Controllo Giacenze'
                ]
            ],
            [
                'titolo' => 'Export Excel delle giacenze',
                'descrizione' => 'Aggiungere la possibilità di esportare in Excel la tabella delle giacenze con filtri applicati.',
                'status' => 'pending',
                'metadata' => [
                    'categoria' => 'Reporting',
                    'priorita' => 'bassa',
                    'area' => 'Giacenze'
                ]
            ],
            
            // Richieste in corso
            [
                'titolo' => 'Integrazione con sistema produzione',
                'descrizione' => 'Collegare automaticamente i movimenti di inventario con il completamento delle fasi di produzione per aggiornare le giacenze in tempo reale.',
                'status' => 'in_progress',
                'metadata' => [
                    'categoria' => 'Integrazione',
                    'priorita' => 'alta',
                    'area' => 'Produzione',
                    'assegnato_a' => 'Team Sviluppo',
                    'data_inizio' => now()->subDays(5)->format('Y-m-d')
                ]
            ],
            [
                'titolo' => 'Codici QR per prodotti',
                'descrizione' => 'Generare automaticamente codici QR per ogni prodotto logistico per facilitare la lettura con scanner durante i movimenti.',
                'status' => 'in_progress',
                'metadata' => [
                    'categoria' => 'Automazione',
                    'priorita' => 'media',
                    'area' => 'Identificazione Prodotti'
                ]
            ],
            
            // Richieste completate
            [
                'titolo' => 'Filtri avanzati per movimenti',
                'descrizione' => 'Aggiungere filtri per data, tipo movimento e magazzino nella lista dei movimenti di inventario.',
                'status' => 'done',
                'metadata' => [
                    'categoria' => 'UI/UX',
                    'priorita' => 'media',
                    'area' => 'Movimenti',
                    'completato_il' => now()->subDays(10)->format('Y-m-d')
                ]
            ],
            [
                'titolo' => 'Validazione quantità negative',
                'descrizione' => 'Impedire la creazione di movimenti che porterebbero a giacenze negative.',
                'status' => 'done',
                'metadata' => [
                    'categoria' => 'Validazione',
                    'priorita' => 'alta',
                    'area' => 'Controllo Qualità'
                ]
            ],
            
            // Richieste rifiutate
            [
                'titolo' => 'Calcolo automatico scadenze',
                'descrizione' => 'Implementare un sistema di calcolo automatico delle date di scadenza basato sulla data di produzione.',
                'status' => 'rejected',
                'metadata' => [
                    'categoria' => 'Automazione',
                    'priorita' => 'bassa',
                    'area' => 'Scadenze',
                    'motivo_rifiuto' => 'Troppo complesso per il ROI attuale',
                    'rifiutato_il' => now()->subDays(15)->format('Y-m-d')
                ]
            ],
            
            // Altre richieste varie
            [
                'titolo' => 'Dashboard KPI logistica',
                'descrizione' => 'Creare una dashboard con KPI principali: rotazione stock, movimenti giornalieri, prodotti più richiesti, efficienza magazzino.',
                'status' => 'pending',
                'metadata' => [
                    'categoria' => 'Analytics',
                    'priorita' => 'media',
                    'area' => 'Reporting'
                ]
            ],
            [
                'titolo' => 'App mobile per operatori',
                'descrizione' => 'Sviluppare un\'app mobile semplificata per permettere agli operatori di magazzino di registrare movimenti direttamente dal pavimento.',
                'status' => 'pending',
                'metadata' => [
                    'categoria' => 'Mobile',
                    'priorita' => 'alta',
                    'area' => 'Operazioni Magazzino'
                ]
            ],
            [
                'titolo' => 'Storico modifiche prodotti',
                'descrizione' => 'Tenere traccia di tutte le modifiche effettuate sui prodotti logistici (descrizione, unità di misura, ecc.)',
                'status' => 'pending',
                'metadata' => [
                    'categoria' => 'Audit',
                    'priorita' => 'bassa',
                    'area' => 'Gestione Prodotti'
                ]
            ],
            [
                'titolo' => 'Integrazione con fornitori',
                'descrizione' => 'API per permettere ai fornitori di aggiornare automaticamente le disponibilità e i prezzi dei loro prodotti.',
                'status' => 'in_progress',
                'metadata' => [
                    'categoria' => 'API',
                    'priorita' => 'media',
                    'area' => 'Fornitori'
                ]
            ],
            [
                'titolo' => 'Backup automatico dati',
                'descrizione' => 'Implementare backup automatici giornalieri dei dati logistici con possibilità di ripristino veloce.',
                'status' => 'done',
                'metadata' => [
                    'categoria' => 'Sicurezza',
                    'priorita' => 'alta',
                    'area' => 'Infrastruttura'
                ]
            ]
        ];

        foreach ($feedbacks as $feedback) {
            OperatorFeedback::create($feedback);
        }

        $this->command->info('✅ Creati ' . count($feedbacks) . ' feedback operatori');
    }
}

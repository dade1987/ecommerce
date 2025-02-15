<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FaqSeeder extends Seeder
{
    public function run()
    {
        DB::table('faqs')->insert([
            [
                'question'   => "Che cos'è un centro olistico?",
                'answer'     => 'Un centro olistico offre trattamenti e terapie che mirano a bilanciare corpo, mente e spirito, integrando approcci tradizionali e alternativi per promuovere il benessere complessivo.',
                'active'     => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'question'   => 'Quali trattamenti offrite?',
                'answer'     => 'I trattamenti possono includere massaggi, agopuntura, aromaterapia, reiki, meditazione, consulenze nutrizionali e percorsi personalizzati per il benessere.',
                'active'     => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'question'   => 'Chi sono i professionisti del centro?',
                'answer'     => 'I nostri terapisti e consulenti sono professionisti qualificati e certificati, specializzati in diverse discipline olistiche, sempre aggiornati e formati per rispondere alle esigenze individuali.',
                'active'     => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'question'   => 'Come posso prenotare una consulenza o un trattamento?',
                'answer'     => 'La prenotazione può avvenire tramite il nostro sito web, telefonicamente o recandoti direttamente al centro. Spesso offriamo anche una prima consulenza gratuita per valutare le tue necessità.',
                'active'     => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'question'   => 'I trattamenti sono personalizzati?',
                'answer'     => 'Sì, ogni percorso viene creato in base alle esigenze specifiche del cliente, a partire da una valutazione iniziale dettagliata.',
                'active'     => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'question'   => 'Quali benefici posso aspettarmi?',
                'answer'     => "I trattamenti olistici mirano a ridurre lo stress, migliorare il benessere fisico e mentale, stimolare la capacità naturale di autoguarigione e promuovere l'equilibrio energetico.",
                'active'     => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'question'   => 'Quali sono i costi e le modalità di pagamento?',
                'answer'     => 'I costi variano a seconda del trattamento e della durata. Sono disponibili diverse modalità di pagamento, pacchetti promozionali e, in alcuni casi, offerte speciali.',
                'active'     => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'question'   => 'Cosa devo aspettarmi durante la prima visita?',
                'answer'     => 'Durante la prima visita, riceverai una valutazione approfondita delle tue esigenze, discuteremo dei tuoi obiettivi e ti proporremo un piano terapeutico personalizzato.',
                'active'     => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'question'   => 'I trattamenti olistici sono supportati da evidenze scientifiche?',
                'answer'     => 'Molti approcci olistici si basano su tradizioni antiche e, dove possibile, sono integrati con conoscenze scientifiche moderne. Il nostro obiettivo è offrire trattamenti sicuri ed efficaci.',
                'active'     => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'question'   => 'Come posso prepararmi per un trattamento?',
                'answer'     => 'Le indicazioni variano a seconda del tipo di trattamento. Ti forniremo tutte le informazioni necessarie al momento della prenotazione per garantire che tu sia a tuo agio e pronto per la seduta.',
                'active'     => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}

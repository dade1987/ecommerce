<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Descrizione della pagina --}}
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-2">Vista Aggregata delle Giacenze</h2>
            <p class="text-sm text-gray-600">
                Questa pagina mostra una <strong>tabella aggregata calcolata in tempo reale</strong> delle quantità correnti 
                per ogni combinazione prodotto-magazzino. I dati sono calcolati sommando tutte le entrate e sottraendo 
                tutte le uscite per ogni prodotto in ogni magazzino.
            </p>
        </div>

        {{-- Statistiche rapide --}}
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="bg-white rounded-lg shadow p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600">Prodotti Logistici</p>
                        <p class="text-2xl font-bold text-gray-900">{{ \App\Models\LogisticProduct::count() }}</p>
                    </div>
                    <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                        <x-heroicon-o-cube class="w-6 h-6 text-blue-600" />
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600">Magazzini</p>
                        <p class="text-2xl font-bold text-gray-900">{{ \App\Models\Warehouse::count() }}</p>
                    </div>
                    <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                        <x-heroicon-o-building-storefront class="w-6 h-6 text-green-600" />
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600">Movimenti Totali</p>
                        <p class="text-2xl font-bold text-gray-900">{{ \App\Models\InventoryMovement::count() }}</p>
                    </div>
                    <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center">
                        <x-heroicon-o-arrows-right-left class="w-6 h-6 text-orange-600" />
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600">Feedback Operatori</p>
                        <p class="text-2xl font-bold text-gray-900">{{ \App\Models\OperatorFeedback::count() }}</p>
                    </div>
                    <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                        <x-heroicon-o-chat-bubble-left-ellipsis class="w-6 h-6 text-purple-600" />
                    </div>
                </div>
            </div>
        </div>

        {{-- Tabella delle giacenze --}}
        <div class="bg-white rounded-lg shadow">
            {{ $this->table }}
        </div>

        {{-- Note tecniche --}}
        <div class="bg-gray-50 rounded-lg p-4">
            <h3 class="font-semibold text-gray-900 mb-2">Note Tecniche</h3>
            <ul class="text-sm text-gray-600 space-y-1">
                <li>• <strong>Vista aggregata:</strong> I dati sono calcolati in tempo reale, non persistiti nel database</li>
                <li>• <strong>Calcolo giacenze:</strong> Somma entrate (to_warehouse) - Somma uscite (from_warehouse)</li>
                <li>• <strong>Integrazione produzione:</strong> Supporto per movimenti automatici tramite production_order_id</li>
                <li>• <strong>Feedback esterni:</strong> OperatorFeedback letto da Cursor per suggerimenti funzionalità</li>
            </ul>
        </div>
    </div>
</x-filament-panels::page> 
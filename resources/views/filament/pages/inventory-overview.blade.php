<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Descrizione della pagina --}}
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-2">Vista Aggregata delle Giacenze</h2>
            <p class="text-sm text-gray-600">
                Questa pagina mostra una <strong>tabella aggregata calcolata in tempo reale</strong> delle quantità di Digital Twin
                per ogni combinazione prodotto-magazzino. I dati sono calcolati contando i Digital Twin presenti in ogni magazzino.
            </p>
        </div>

        @php
            $stats = $this->getHeaderStats();
        @endphp

        {{-- Statistiche rapide --}}
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="bg-white rounded-lg shadow p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600">Prodotti Interni</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $stats['total_products'] }}</p>
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
                        <p class="text-2xl font-bold text-gray-900">{{ $stats['total_warehouses'] }}</p>
                    </div>
                    <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                        <x-heroicon-o-building-storefront class="w-6 h-6 text-green-600" />
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600">Digital Twin Totali</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $stats['total_twins'] }}</p>
                    </div>
                    <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center">
                        <x-heroicon-o-document-duplicate class="w-6 h-6 text-orange-600" />
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600">Emissioni CO2 Totali (kg)</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $stats['total_co2'] }}</p>
                    </div>
                    <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center">
                        <x-heroicon-o-fire class="w-6 h-6 text-red-600" />
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
                <li>• <strong>Vista aggregata:</strong> I dati sono calcolati in tempo reale, non persistiti nel database.</li>
                <li>• <strong>Calcolo giacenze:</strong> Conteggio dei record nella tabella <code>product_twins</code> raggruppati per prodotto e magazzino.</li>
                <li>• <strong>Integrazione produzione:</strong> I Digital Twin vengono creati al completamento di un ordine di produzione.</li>
                <li>• <strong>Tracciabilità CO2:</strong> Le emissioni vengono calcolate e aggregate per ogni fase (produzione e logistica).</li>
            </ul>
        </div>
    </div>
</x-filament-panels::page> 
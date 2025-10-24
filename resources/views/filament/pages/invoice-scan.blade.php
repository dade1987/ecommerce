<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Header con informazioni -->
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">
                Scansione Fattura - Sistema di Tracciabilità
            </h2>
            <p class="text-gray-600">
                Scansiona gli UUID dei ProductTwin per creare fatture con tracciabilità completa.
            </p>
        </div>

        <!-- Form principale -->
        <div class="bg-white rounded-lg shadow">
            {{ $this->form }}
        </div>

        <!-- Bottoni Azioni -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Azioni</h3>
            <div class="flex flex-wrap gap-4">
                @if($this->currentInvoice === null && !empty($this->data['customer_id']))
                    <button wire:click="createInvoice" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:bg-green-700 active:bg-green-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        Crea Fattura
                    </button>
                @endif

                @if($this->currentInvoice !== null && $this->scannedProductTwin !== null)
                    <button wire:click="addProductToInvoice" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        Aggiungi Prodotto
                    </button>
                @endif

                @if($this->currentInvoice !== null)
                    <button wire:click="printInvoice" class="inline-flex items-center px-4 py-2 bg-yellow-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-yellow-700 focus:bg-yellow-700 active:bg-yellow-900 focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                        </svg>
                        Stampa Fattura
                    </button>

                    <button wire:click="sendElectronicInvoice" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                        </svg>
                        Invia Elettronica
                    </button>
                @endif
            </div>
        </div>

        <!-- Informazioni ProductTwin scansionato -->
        @if($this->scannedProductTwin)
        <div class="bg-green-50 border border-green-200 rounded-lg p-6">
            <h3 class="text-lg font-semibold text-green-800 mb-4">
                ✅ Prodotto Scansionato
            </h3>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-green-700">UUID</label>
                    <p class="text-sm text-green-600">{{ $this->scannedProductTwin->uuid }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-green-700">Prodotto</label>
                    <p class="text-sm text-green-600">{{ $this->scannedProductTwin->internalProduct->name }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-green-700">Magazzino</label>
                    <p class="text-sm text-green-600">{{ $this->scannedProductTwin->currentWarehouse->name ?? 'N/A' }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-green-700">Stato</label>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                        {{ $this->scannedProductTwin->lifecycle_status }}
                    </span>
                </div>
            </div>
        </div>
        @endif

        <!-- Fattura corrente -->
        @if($this->currentInvoice)
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-6">
            <h3 class="text-lg font-semibold text-blue-800 mb-4">
                📄 Fattura Corrente: {{ $this->currentInvoice->invoice_number }}
            </h3>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-blue-700">Cliente</label>
                    <p class="text-sm text-blue-600">{{ $this->currentInvoice->customer->name }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-blue-700">Data Fattura</label>
                    <p class="text-sm text-blue-600">{{ $this->currentInvoice->invoice_date->format('d/m/Y') }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-blue-700">Scadenza</label>
                    <p class="text-sm text-blue-600">{{ $this->currentInvoice->due_date->format('d/m/Y') }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-blue-700">Totale</label>
                    <p class="text-sm text-blue-600">€ {{ number_format($this->currentInvoice->total_amount, 2, ',', '.') }}</p>
                </div>
            </div>
        </div>

        <!-- Prodotti nella fattura -->
        <div class="bg-white rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">
                    Prodotti Fatturati ({{ $this->currentInvoice->items->count() }})
                </h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                UUID ProductTwin
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Prodotto
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Prezzo Unitario
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Quantità
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Totale
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($this->currentInvoice->items as $item)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-mono text-gray-900">
                                @foreach($item->productTwins as $twin)
                                    <div>{{ $twin->uuid }}</div>
                                @endforeach
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $item->internalProduct->name }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                € {{ number_format($item->unit_price, 2, ',', '.') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $item->quantity }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                € {{ number_format($item->total_price, 2, ',', '.') }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

        <!-- Istruzioni -->
        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-6">
            <h3 class="text-lg font-semibold text-yellow-800 mb-4">
                📋 Istruzioni
            </h3>
            <ol class="list-decimal list-inside space-y-2 text-sm text-yellow-700">
                <li>Seleziona il cliente e crea la fattura</li>
                <li>Scansiona l'UUID del ProductTwin che vuoi fatturare</li>
                <li>Verifica che il prodotto sia disponibile e non già fatturato</li>
                <li>Aggiungi il prodotto alla fattura</li>
                <li>Ripeti per tutti i prodotti da fatturare</li>
                <li>Stampa la fattura e inviala elettronicamente</li>
            </ol>
        </div>
    </div>
</x-filament-panels::page> 
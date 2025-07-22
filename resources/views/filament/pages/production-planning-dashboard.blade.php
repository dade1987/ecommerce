<x-filament-panels::page>

    <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
        {{-- KPI Section --}}
        <div class="p-6 bg-white rounded-lg shadow-md dark:bg-gray-800">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('filament-production.KPI di Produzione in Tempo Reale') }}</h3>
            <div class="mt-4 space-y-4">
                <div>
                    <div class="flex justify-between">
                        <span class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('filament-production.OEE Medio Impianto') }}</span>
                        <span class="text-lg font-bold text-green-600 dark:text-green-400">{{ $oeeData['avg_oee'] ?? 0 }}%</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2.5 dark:bg-gray-700 mt-1">
                        <div class="bg-green-600 h-2.5 rounded-full" style="width: {{ $oeeData['avg_oee'] ?? 0 }}%"></div>
                    </div>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('filament-production.Lead Time Medio') }}</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">5.2 {{ __('filament-production.giorni') }}</p>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('filament-production.Scostamento Piano (AI vs Manuale)') }}</p>
                    <p class="text-2xl font-bold text-orange-500 dark:text-orange-400">{{ __('filament-production.-15% ritardi') }}</p>
                </div>
            </div>
        </div>

        {{-- Bottleneck Analysis --}}
        <div class="p-6 bg-white rounded-lg shadow-md md:col-span-2 dark:bg-gray-800">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('filament-production.Analisi Colli di Bottiglia') }}</h3>
            {{ $this->form }}
            <div class="mt-6">
                @if (!empty($bottleneckData))
                    <ul class="space-y-4">
                        @foreach ($bottleneckData as $data)
                            <li class="p-4 border rounded-lg dark:border-gray-700">
                                <p class="font-semibold">{{ $data['workstation_name'] }} - <span class="text-sm text-gray-500">{{ $data['production_line'] }}</span></p>
                                <div class="flex items-center justify-between mt-2">
                                    <span class="text-sm">{{ __('filament-production.Utilizzo') }}: {{ $data['utilization'] }}%</span>
                                    <div class="w-full h-4 mx-4 bg-gray-200 rounded-full dark:bg-gray-700">
                                        <div class="h-4 bg-blue-600 rounded-full"
                                             style="width: {{ $data['utilization'] }}%;">
                                        </div>
                                    </div>
                                </div>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ __('filament-production.Carico') }}: {{ $data['workload_hours'] }}h / {{ __('filament-production.Capacità') }}: {{ $data['capacity_hours'] }}h</p>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <p>{{ __('filament-production.Nessun dato sui colli di bottiglia da visualizzare per il periodo selezionato.') }}</p>
                @endif
            </div>
        </div>
    </div>

    {{-- Demand Forecast Section --}}
    @if ($demandForecast)
        <div class="p-6 mt-6 bg-white rounded-lg shadow-md dark:bg-gray-800">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('filament-production.Risultati Previsione Domanda') }}</h3>
            <div class="mt-4">
                <p>
                    <span class="font-medium">{{ __('filament-production.Volume previsto per il prossimo mese:') }}</span>
                    <span class="text-xl font-bold text-primary-600">{{ $demandForecast['next_month_volume'] }}</span> {{ __('filament-production.unità.') }}
                </p>
                <p>
                    <span class="font-medium">{{ __('filament-production.Trend stimato:') }}</span>
                    <span class="font-bold @if($demandForecast['trend_per_month'] > 0) text-green-600 @else text-red-600 @endif">
                        {{ $demandForecast['trend_per_month'] > 0 ? '+' : '' }}{{ $demandForecast['trend_per_month'] }} {{ __('filament-production.unità/mese') }}
                    </span>
                </p>
                <p class="text-sm text-gray-500">
                    {{ __('filament-production.Calcolato con:') }} {{ $demandForecast['calculation_method'] }}.
                </p>
                <div class="p-4 mt-4 text-sm text-blue-700 bg-blue-100 border-l-4 border-blue-500" role="alert">
                    <p class="font-bold">{{ __('filament-production.Azione Suggerita:') }}</p>
                    <p>{{ __('filament-production.Considera di creare ordini di produzione per stock per i prodotti più richiesti per evitare stock-out e soddisfare la domanda prevista.') }}</p>
                </div>
            </div>
        </div>
    @endif

    {{-- Gantt Chart Section --}}
    @if ($ganttChart)
        <div class="p-6 mt-6 bg-white rounded-lg shadow-md dark:bg-gray-800">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('filament-production.Diagramma di Gantt Reale') }}</h3>
            <div class="overflow-x-auto">
                {!! $ganttChart !!}
            </div>
        </div>
    @endif

    {{-- Simulated Gantt Chart Section --}}
    @if ($simulationGantt)
        <div class="p-6 mt-6 border-2 border-dashed rounded-lg border-primary-500 dark:border-primary-400">
            <h3 class="text-lg font-semibold text-primary-600 dark:text-primary-400">{{ __('filament-production.Diagramma di Gantt Simulata (What-If)') }}</h3>
            <div class="overflow-x-auto">
                {!! $simulationGantt !!}
            </div>
        </div>
    @endif

</x-filament-panels::page> 
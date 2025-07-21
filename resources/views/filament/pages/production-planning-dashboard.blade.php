<x-filament-panels::page>
    <div class="space-y-6">
        <form wire:submit.prevent="updateBottleneckData" class="grid grid-cols-1 md:grid-cols-2 gap-4">
            {{ $this->form }}
        </form>

        <x-filament::card>
            <x-slot name="header">
                <h2 class="text-xl font-semibold">Previsione Colli di Bottiglia</h2>
            </x-slot>

            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left text-gray-500">
                    <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3">Postazione</th>
                            <th scope="col" class="px-6 py-3">Linea di Produzione</th>
                            <th scope="col" class="px-6 py-3">Carico (ore)</th>
                            <th scope="col" class="px-6 py-3">Capacità (ore)</th>
                            <th scope="col" class="px-6 py-3">Utilizzo</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($bottleneckData as $data)
                            <tr @class([
                                'bg-white border-b',
                                'bg-red-100' => $data['utilization'] > 90,
                                'bg-yellow-100' => $data['utilization'] > 75 && $data['utilization'] <= 90,
                            ])>
                                <th scope="row" class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap">
                                    {{ $data['workstation_name'] }}
                                </th>
                                <td class="px-6 py-4">{{ $data['production_line'] }}</td>
                                <td class="px-6 py-4">{{ $data['workload_hours'] }}</td>
                                <td class="px-6 py-4">{{ $data['capacity_hours'] }}</td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center">
                                        <span class="mr-2">{{ $data['utilization'] }}%</span>
                                        <div class="w-full bg-gray-200 rounded-full h-2.5">
                                            <div @class([
                                                'h-2.5 rounded-full',
                                                'bg-red-600' => $data['utilization'] > 90,
                                                'bg-yellow-400' => $data['utilization'] > 75 && $data['utilization'] <= 90,
                                                'bg-green-600' => $data['utilization'] <= 75,
                                            ]) style="width: {{ $data['utilization'] }}%"></div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-4 text-center">Nessun dato da visualizzare per il periodo selezionato.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-filament::card>

        <x-filament::card>
            <x-slot name="header">
                <h2 class="text-xl font-semibold">Diagramma di Gantt Schedulazione</h2>
            </x-slot>
            <div id="gantt-container" class="overflow-x-auto">
                @if ($ganttChart)
                    {!! $ganttChart !!}
                @else
                    <div class="flex items-center justify-center p-8 text-gray-500">
                        <span>Clicca su "Genera Schedulazione Avanzata (Gantt)" per visualizzare il diagramma.</span>
                    </div>
                @endif
            </div>
        </x-filament::card>
    </div>
</x-filament-panels::page> 
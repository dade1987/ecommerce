<x-filament-panels::page>
    <div class="space-y-6">
        {{-- HERO + KPI CARDS --}}
        <div
            class="flex flex-col lg:flex-row gap-4 bg-gradient-to-r from-slate-900 via-slate-800 to-emerald-900 rounded-2xl p-6 shadow-xl border border-slate-700">
            {{-- WIDGET 1: Titolo Dashboard --}}
            <div class="flex flex-1 items-center justify-between bg-slate-900/40 rounded-xl px-4 py-3 border border-slate-700/80">
                <div>
                    <h2 class="text-xl font-semibold text-white">Dashboard AI Conversazioni</h2>
                    <p class="mt-1 text-xs text-slate-400">
                        Panoramica in tempo reale delle chat gestite da <span class="font-semibold">EnjoyHen AI</span> sui
                        siti dei clienti.
                    </p>
                </div>
            </div>

            {{-- WIDGET 2: Filtro Date --}}
            <div class="flex items-center justify-between bg-slate-900/40 rounded-xl px-3 py-2 border border-slate-700/80 w-full lg:w-auto lg:max-w-xs">
                <div class="w-full">
                    {{ $this->form }}
                </div>
            </div>
        </div>

        {{-- KPI STATS + WIDGETS --}}
        <div
            class="flex flex-col lg:flex-row gap-4 bg-gradient-to-r from-slate-900 via-slate-800 to-emerald-900 rounded-2xl p-6 shadow-xl border border-slate-700">
            <div class="w-full lg:w-1/2 flex flex-col justify-between">
                <div class="flex flex-row flex-wrap gap-4 text-sm text-slate-200">
                    <div>
                        <div class="text-xs uppercase tracking-wide text-slate-400">Thread totali</div>
                        <div class="mt-1 text-2xl font-bold">
                            {{ number_format($threadStats['total_threads'] ?? 0) }}
                        </div>
                    </div>
                    <div>
                        <div class="text-xs uppercase tracking-wide text-slate-400">Thread nel periodo</div>
                        <div class="mt-1 text-2xl font-bold text-emerald-400">
                            {{ number_format($threadStats['threads_last_7_days'] ?? 0) }}
                        </div>
                    </div>
                    <div>
                        <div class="text-xs uppercase tracking-wide text-slate-400">Msg per thread</div>
                        <div class="mt-1 text-2xl font-bold">
                            {{ number_format($threadStats['avg_messages_per_thread'] ?? 0, 1) }}
                        </div>
                    </div>
                    <div>
                        <div class="text-xs uppercase tracking-wide text-slate-400">IP unici</div>
                        <div class="mt-1 text-2xl font-bold text-indigo-300">
                            {{ number_format($threadStats['unique_ips'] ?? 0) }}
                        </div>
                    </div>
                </div>
            </div>

            <div class="w-full lg:w-1/2 flex flex-row gap-4 text-sm text-slate-100 items-stretch">
                <div class="flex flex-1 items-center justify-between bg-slate-900/40 rounded-xl px-4 py-3 border border-slate-700/80">
                    <div>
                        <div class="text-xs uppercase tracking-wide text-slate-400">Widget EnjoyHen</div>
                        <div class="mt-1 text-lg font-semibold">Streaming attivo</div>
                        <p class="text-xs text-slate-400">
                            Conversazioni generate dall'avatar video + chat testuale.
                        </p>
                    </div>
                    <div class="flex items-center justify-center w-10 h-10 rounded-full bg-emerald-600/80 text-white">
                        <x-heroicon-o-chat-bubble-left-right class="w-5 h-5" />
                    </div>
                </div>

                <div class="flex flex-1 items-center justify-between bg-slate-900/40 rounded-xl px-4 py-3 border border-slate-700/80">
                    <div>
                        <div class="text-xs uppercase tracking-wide text-slate-400">Qualità conversazione</div>
                        <div class="mt-1 text-lg font-semibold">
                            {{ ($threadStats['avg_messages_per_thread'] ?? 0) >= 6 ? 'Alta' : 'Media' }}
                        </div>
                        <p class="text-xs text-slate-400">
                            Più messaggi per thread ⇒ più engagement del visitatore.
                        </p>
                    </div>
                    <div class="flex items-center justify-center w-10 h-10 rounded-full bg-sky-600/80 text-white">
                        <x-heroicon-o-sparkles class="w-5 h-5" />
                    </div>
                </div>
            </div>
        </div>

        {{-- THREADS TABLE (STYLE "ACTIVE USERS") --}}
        <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-lg border border-slate-200/80 dark:border-slate-700/80">
            <div class="px-6 pt-5 pb-3 flex items-center justify-between">
                <div>
                    <h3 class="text-base font-semibold text-gray-900 dark:text-slate-50">
                        Thread recenti
                    </h3>
                    <p class="text-xs text-gray-500 dark:text-slate-400">
                        Ultime conversazioni attivate dai widget EnjoyHen sui vari siti.
                    </p>
                </div>
                <div class="flex items-center gap-2 text-xs">
                    <span class="px-2 py-1 rounded-full bg-emerald-100 text-emerald-700 dark:bg-emerald-900/60 dark:text-emerald-300">
                        LIVE &amp; Recenti
                    </span>
                </div>
            </div>

            <div class="border-t border-slate-200 dark:border-slate-800">
                <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800 text-sm">
                    <thead class="bg-slate-50 dark:bg-slate-900/70">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">
                                #
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">
                                Thread
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">
                                Team / Host
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">
                                IP
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">
                                Stato
                            </th>
                            <th class="px-6 py-3 text-right text-xs font-semibold text-slate-500 uppercase tracking-wider">
                                Azioni
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-slate-900 divide-y divide-slate-200 dark:divide-slate-800">
                        @forelse ($latestThreads as $index => $thread)
                            @php
                                $created = $thread->created_at ?? now();
                                $minutesAgo = $created->diffInMinutes();
                                $status = [
                                    'label' => 'COMPLETATO',
                                    'color' => 'bg-slate-600',
                                ];
                                if ($minutesAgo <= 5) {
                                    $status = ['label' => 'LIVE', 'color' => 'bg-emerald-600'];
                                } elseif ($minutesAgo <= 60) {
                                    $status = ['label' => 'RECENTE', 'color' => 'bg-indigo-600'];
                                }
                            @endphp
                            <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/60 transition-colors">
                                <td class="px-6 py-4 text-xs text-slate-500">
                                    #{{ $thread->messages_count ?? ($index + 1) }}
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="w-10 h-10 rounded-full bg-gradient-to-br from-emerald-500 to-sky-500 flex items-center justify-center text-white text-xs font-semibold shadow-md">
                                            {{ strtoupper(substr($thread->thread_id, -2)) }}
                                        </div>
                                        <div class="min-w-0">
                                            <div class="text-sm font-semibold text-gray-900 dark:text-slate-50 truncate">
                                                {{ substr($thread->thread_id, 0, 24) }}{{ strlen($thread->thread_id) > 24 ? '…' : '' }}
                                            </div>
                                            <div class="text-xs text-slate-500 dark:text-slate-400">
                                                {{ optional($thread->created_at)->format('d/m/Y H:i') ?? 'n/d' }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-sm text-slate-700 dark:text-slate-200">
                                    {{ $thread->team_slug ?? 'Sito generico' }}
                                </td>
                                <td class="px-6 py-4 text-sm text-slate-700 dark:text-slate-200">
                                    {{ $thread->ip_address ?? 'n/d' }}
                                </td>
                                <td class="px-6 py-4">
                                    <span
                                        class="inline-flex items-center px-3 py-1 rounded-full text-[11px] font-semibold text-white {{ $status['color'] }}">
                                        {{ $status['label'] }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right space-x-2">
                                    {{-- Azione 1: Dati thread (view Filament) --}}
                                    <a href="{{ $thread->view_url }}"
                                        class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-semibold bg-slate-700 text-white hover:bg-slate-800 shadow-sm">
                                        Dati thread
                                    </a>

                                    {{-- Azione 2: Visualizza conversazione (modal) --}}
                                    <button wire:click="openConversationModal('{{ $thread->thread_id }}')"
                                        class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-semibold bg-indigo-600 text-white hover:bg-indigo-700 shadow-sm">
                                        Visualizza conversazione
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-6 text-center text-sm text-slate-500">
                                    Nessun thread registrato finora.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- DUE GRAFICI "DELUXE" (BARS + LINE) --}}
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
            {{-- Grafico 1: Messaggi per giorno (line chart) --}}
            <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-lg border border-slate-200/80 dark:border-slate-700/80 p-5">
                <div class="flex items-center justify-between mb-3">
                    <div>
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-slate-50">
                            Volume messaggi (intervallo selezionato)
                        </h3>
                        <p class="text-xs text-slate-500">
                            Andamento dei messaggi salvati in <code>quoters</code> nel periodo.
                        </p>
                    </div>
                </div>
                @php
                    $maxMessages = max(1, collect($messagesPerDay)->max('count') ?? 1);
                    $hasData = collect($messagesPerDay)->sum('count') > 0;
                    $chartHeight = 200;
                    $chartWidth = 100;
                    $padding = 20;
                    $points = [];
                @endphp
                @if($hasData && count($messagesPerDay) > 0 && $maxMessages > 0)
                    <div class="mt-4 relative" style="height: {{ $chartHeight + $padding * 2 }}px;">
                        <svg class="w-full" style="height: {{ $chartHeight + $padding * 2 }}px;" viewBox="0 0 {{ (count($messagesPerDay) - 1) * 100 + $padding * 2 }} {{ $chartHeight + $padding * 2 }}">
                            @php
                                $spacing = (count($messagesPerDay) - 1) > 0 ? (count($messagesPerDay) - 1) : 1;
                                $stepX = ($chartWidth * (count($messagesPerDay) - 1)) / max(1, $spacing);
                            @endphp
                            
                            {{-- Griglia e assi --}}
                            <defs>
                                <linearGradient id="lineGradient" x1="0%" y1="0%" x2="0%" y2="100%">
                                    <stop offset="0%" style="stop-color:rgb(16, 185, 129);stop-opacity:0.8" />
                                    <stop offset="100%" style="stop-color:rgb(14, 165, 233);stop-opacity:0.8" />
                                </linearGradient>
                            </defs>
                            
                            {{-- Linee della griglia (da 0 a max) --}}
                            @for($i = 0; $i <= 4; $i++)
                                @php
                                    $y = $padding + ($chartHeight / 4) * $i;
                                    $value = $maxMessages - ($maxMessages / 4) * $i;
                                @endphp
                                <line x1="{{ $padding }}" y1="{{ $y }}" 
                                      x2="{{ (count($messagesPerDay) - 1) * $stepX + $padding }}" y2="{{ $y }}" 
                                      stroke="currentColor" stroke-width="0.5" 
                                      class="text-slate-200 dark:text-slate-700" />
                                <text x="{{ $padding - 5 }}" y="{{ $y + 4 }}" 
                                      text-anchor="end" 
                                      class="text-[10px] fill-slate-500 dark:fill-slate-400">
                                    {{ round($value) }}
                                </text>
                            @endfor
                            
                            {{-- Calcola punti per curva smooth --}}
                            @foreach ($messagesPerDay as $idx => $day)
                                @php
                                    $x = $padding + ($idx * $stepX);
                                    $normalizedValue = $maxMessages > 0 ? ($day['count'] / $maxMessages) : 0;
                                    $y = $padding + $chartHeight - ($normalizedValue * $chartHeight);
                                    $points[] = ['x' => $x, 'y' => $y, 'count' => $day['count'], 'date' => $day['date']];
                                @endphp
                            @endforeach
                            
                            {{-- Crea path smooth con curve di Bézier --}}
                            @php
                                $smoothPath = '';
                                $areaPath = '';
                                $zeroY = $chartHeight + $padding; // Coordinata Y dello zero
                                
                                if (count($points) > 0) {
                                    $firstPoint = $points[0];
                                    $smoothPath = "M {$firstPoint['x']} {$firstPoint['y']}";
                                    $areaPath = "M {$firstPoint['x']} {$firstPoint['y']}";
                                    
                                    for ($i = 0; $i < count($points) - 1; $i++) {
                                        $p0 = $i > 0 ? $points[$i - 1] : $points[$i];
                                        $p1 = $points[$i];
                                        $p2 = $points[$i + 1];
                                        $p3 = $i < count($points) - 2 ? $points[$i + 2] : $points[$i + 1];
                                        
                                        // Calcola punti di controllo per curva smooth
                                        $cp1x = $p1['x'] + ($p2['x'] - $p0['x']) / 6;
                                        $cp1y = $p1['y'] + ($p2['y'] - $p0['y']) / 6;
                                        $cp2x = $p2['x'] - ($p3['x'] - $p1['x']) / 6;
                                        $cp2y = $p2['y'] - ($p3['y'] - $p1['y']) / 6;
                                        
                                        // Limita i punti di controllo per non andare sotto zero
                                        $cp1y = max($cp1y, min($p1['y'], $p2['y']));
                                        $cp2y = max($cp2y, min($p1['y'], $p2['y']));
                                        
                                        // Assicura che i punti di controllo non vadano mai sotto lo zero
                                        $cp1y = min($cp1y, $zeroY);
                                        $cp2y = min($cp2y, $zeroY);
                                        
                                        $smoothPath .= " C {$cp1x} {$cp1y}, {$cp2x} {$cp2y}, {$p2['x']} {$p2['y']}";
                                        $areaPath .= " C {$cp1x} {$cp1y}, {$cp2x} {$cp2y}, {$p2['x']} {$p2['y']}";
                                    }
                                    
                                    // Chiudi l'area sotto la curva
                                    $lastPoint = end($points);
                                    $firstPoint = reset($points);
                                    $areaPath .= " L {$lastPoint['x']} {$zeroY} L {$firstPoint['x']} {$zeroY} Z";
                                }
                            @endphp
                            
                            {{-- Area sotto la curva --}}
                            @if(!empty($areaPath))
                                <path d="{{ $areaPath }}" fill="url(#lineGradient)" opacity="0.2" />
                            @endif
                            
                            {{-- Linea smooth --}}
                            @if(!empty($smoothPath))
                                <path d="{{ $smoothPath }}" 
                                      fill="none" 
                                      stroke="url(#lineGradient)" 
                                      stroke-width="3" 
                                      stroke-linecap="round" 
                                      stroke-linejoin="round" />
                            @endif
                            
                            {{-- Punti --}}
                            @foreach ($points as $point)
                                <circle cx="{{ $point['x'] }}" 
                                        cy="{{ $point['y'] }}" 
                                        r="4" 
                                        fill="rgb(16, 185, 129)" 
                                        stroke="white" 
                                        stroke-width="2"
                                        class="hover:r-6 transition-all cursor-pointer"
                                        data-count="{{ $point['count'] }}"
                                        data-date="{{ \Carbon\Carbon::parse($point['date'])->format('d/m/Y') }}">
                                    <title>{{ \Carbon\Carbon::parse($point['date'])->format('d/m/Y') }}: {{ $point['count'] }} messaggi</title>
                                </circle>
                            @endforeach
                            
                            {{-- Etichette date --}}
                            @foreach ($messagesPerDay as $idx => $day)
                                @php
                                    $x = $padding + ($idx * $stepX);
                                @endphp
                                <text x="{{ $x }}" 
                                      y="{{ $chartHeight + $padding + 15 }}" 
                                      text-anchor="middle" 
                                      class="text-[10px] fill-slate-500 dark:fill-slate-400">
                                    {{ \Carbon\Carbon::parse($day['date'])->format('d/m') }}
                                </text>
                            @endforeach
                        </svg>
                        
                        {{-- Legenda max --}}
                        <div class="absolute top-2 right-2 text-xs text-slate-500 dark:text-slate-400">
                            <div>Max: <span class="font-semibold text-emerald-600">{{ $maxMessages }}</span></div>
                        </div>
                    </div>
                @else
                    <div class="mt-4 h-48 flex items-center justify-center">
                        <p class="text-sm text-slate-500 dark:text-slate-400">
                            Nessun dato disponibile per il periodo selezionato
                        </p>
                    </div>
                @endif
            </div>

            {{-- Grafico 2: Thread per team (line-like mini chart) --}}
            <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-lg border border-slate-200/80 dark:border-slate-700/80 p-5">
                <div class="flex items-center justify-between mb-3">
                    <div>
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-slate-50">
                            Thread per team (top 5)
                        </h3>
                        <p class="text-xs text-slate-500">
                            Distribuzione dei thread provenienti dai vari team / siti.
                        </p>
                    </div>
                </div>
                @php
                    $maxThreads = max(1, collect($threadsPerTeam)->max('total') ?? 1);
                @endphp
                <div class="mt-4 space-y-3">
                    @forelse ($threadsPerTeam as $row)
                        @php
                            $w = ($row['total'] / $maxThreads) * 100;
                        @endphp
                        <div>
                            <div class="flex items-center justify-between mb-1 text-xs">
                                <span class="font-medium text-slate-700 dark:text-slate-200">
                                    {{ $row['team'] }}
                                </span>
                                <span class="text-slate-500">
                                    {{ $row['total'] }} thread
                                </span>
                            </div>
                            <div class="w-full h-2.5 rounded-full bg-slate-100 dark:bg-slate-800 overflow-hidden">
                                <div class="h-2.5 rounded-full bg-gradient-to-r from-indigo-500 via-sky-500 to-emerald-500"
                                    style="width: {{ $w }}%;">
                                </div>
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-slate-500">
                            Nessun dato ancora disponibile: avvia qualche conversazione con l'avatar EnjoyHen.
                        </p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    {{-- Modal conversazione --}}
    <x-filament::modal id="conversation-modal" width="4xl">
        <x-slot name="heading">
            Conversazione Thread
        </x-slot>

        <x-slot name="description">
            Visualizzazione completa della conversazione
        </x-slot>

        @if($this->selectedThreadId)
            @php
                $messages = \App\Models\Quoter::where('thread_id', $this->selectedThreadId)
                    ->orderBy('created_at', 'asc')
                    ->get();
            @endphp
            @include('filament.widgets.conversation-modal', [
                'messages' => $messages,
                'threadId' => $this->selectedThreadId
            ])
        @else
            <p class="text-sm text-gray-500">Nessun thread selezionato</p>
        @endif
    </x-filament::modal>
</x-filament-panels::page>



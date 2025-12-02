<x-filament-panels::page>
    <div class="space-y-6">
        {{-- HERO + KPI CARDS + FILTRO DATA --}}
        <div
            class="grid grid-cols-1 gap-4 lg:grid-cols-4 bg-gradient-to-r from-slate-900 via-slate-800 to-emerald-900 rounded-2xl p-6 shadow-xl border border-slate-700">
            <div class="col-span-1 lg:col-span-2 flex flex-col justify-between">
                <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                    <div>
                        <h2 class="text-2xl font-semibold text-white">Dashboard AI Conversazioni</h2>
                        <p class="mt-1 text-sm text-slate-300">
                            Panoramica in tempo reale delle chat gestite da <span class="font-semibold">EnjoyHen AI</span> sui
                            siti dei clienti.
                        </p>
                    </div>
                    <div
                        class="inline-flex items-end gap-4 px-4 py-3 bg-white/10 backdrop-blur-sm rounded-xl shadow border border-slate-500/60">
                        {{ $this->form }}
                    </div>
                </div>
                <div class="mt-4 flex flex-wrap gap-4 text-sm text-slate-200">
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

            <div
                class="col-span-1 lg:col-span-2 grid grid-cols-2 gap-4 text-sm text-slate-100 place-content-between">
                <div class="flex items-center justify-between bg-slate-900/40 rounded-xl px-4 py-3 border border-slate-700/80">
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

                <div class="flex items-center justify-between bg-slate-900/40 rounded-xl px-4 py-3 border border-slate-700/80">
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
                                <td class="px-6 py-4 text-right">
                                    <a href="{{ $thread->view_url }}"
                                        class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-semibold bg-indigo-600 text-white hover:bg-indigo-700 shadow-sm">
                                        Visualizza conversazione
                                    </a>
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
            {{-- Grafico 1: Messaggi per ora (bar chart) --}}
            <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-lg border border-slate-200/80 dark:border-slate-700/80 p-5">
                <div class="flex items-center justify-between mb-3">
                    <div>
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-slate-50">
                            Volume messaggi per ora (intervallo selezionato)
                        </h3>
                        <p class="text-xs text-slate-500">
                            Ogni barra rappresenta i messaggi salvati in <code>quoters</code> in quella specifica ora.
                        </p>
                    </div>
                </div>
                @php
                    $maxMessages = max(1, collect($messagesPerDay)->max('count') ?? 1);
                @endphp
                <div class="mt-4 h-48 flex items-end gap-3">
                    @foreach ($messagesPerDay as $day)
                        @php
                            $h = ($day['count'] / $maxMessages) * 100;
                        @endphp
                        <div class="flex flex-col items-center justify-end flex-1 group">
                            <div class="w-full bg-slate-100 dark:bg-slate-800 rounded-t-lg overflow-hidden">
                                <div class="w-full bg-gradient-to-t from-emerald-500 to-sky-500 rounded-t-lg transition-all duration-500 group-hover:opacity-90"
                                    style="height: {{ $h }}%;">
                                </div>
                            </div>
                            <div class="mt-2 text-[11px] text-slate-500">
                                {{ $day['label'] }}
                            </div>
                        </div>
                    @endforeach
                </div>
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
</x-filament-panels::page>



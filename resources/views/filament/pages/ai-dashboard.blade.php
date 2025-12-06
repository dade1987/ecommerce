<x-filament-panels::page>
    <div class="space-y-8">
        {{-- HEADER + FILTRO DATE --}}
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div class="space-y-1.5">
                <div class="inline-flex items-center gap-2 rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700 ring-1 ring-emerald-100">
                    <x-heroicon-o-sparkles class="h-4 w-4" />
                    <span>Dashboard AI Conversazioni</span>
                </div>
                <div>
                    <h2 class="text-2xl font-semibold text-slate-900">
                        Panoramica crediti & chat
                    </h2>
                    {{-- Widget crediti GPT / HeyGen (usa anche endpoint non documentato OpenAI) --}}
                    <div class="mt-3 grid grid-cols-1 gap-3 sm:grid-cols-2">
                        {{-- Credito GPT (OpenAI) --}}
                        <div class="relative overflow-hidden rounded-2xl border border-slate-200 bg-white/90 p-3 shadow-sm">
                            <div class="absolute -right-4 -top-4 h-14 w-14 rounded-full bg-emerald-200/50 blur-xl"></div>
                            <div class="relative flex items-start justify-between gap-3">
                                <div>
                                    <p class="text-[11px] font-semibold uppercase tracking-wide text-emerald-700/80">
                                        Costo GPT (ultimi 30 giorni)
                                    </p>
                                    <p class="mt-1 text-xl font-bold tracking-tight text-slate-900">
                                        @if(!is_null($openAiCreditUsd))
                                            {{ number_format($openAiCreditUsd, 2) }} $
                                        @elseif($openAiCreditError)
                                            <span class="text-xs font-medium text-rose-600">
                                                {{ $openAiCreditError }}
                                            </span>
                                        @else
                                            <span class="text-xs font-medium text-slate-400">
                                                n/d
                                            </span>
                                        @endif
                                    </p>
                                    <p class="mt-1 text-[11px] text-slate-500">
                                        Letto da costs API OpenAI
                                        <span class="font-mono text-[10px] text-slate-400">/v1/organization/costs</span>.
                                    </p>
                                </div>
                                <div class="flex h-9 w-9 items-center justify-center rounded-full bg-emerald-600 text-white ring-2 ring-emerald-300">
                                    <x-heroicon-o-banknotes class="h-4 w-4" />
                                </div>
                            </div>
                        </div>

                        {{-- Credito HeyGen (placeholder, non esiste endpoint API per saldo crediti) --}}
                        <div class="relative overflow-hidden rounded-2xl border border-slate-200 bg-white/90 p-3 shadow-sm">
                            <div class="absolute -right-4 -top-4 h-14 w-14 rounded-full bg-sky-200/50 blur-xl"></div>
                            <div class="relative flex items-start justify-between gap-3">
                                <div>
                                    <p class="text-[11px] font-semibold uppercase tracking-wide text-sky-700/80">
                                        Credito residuo HeyGen
                                    </p>
                                    <p class="mt-1 text-sm font-semibold text-slate-900">
                                        {{ $heygenCreditDisplay ?? 'Verifica dalla dashboard HeyGen' }}
                                    </p>
                                    <p class="mt-1 text-[11px] text-slate-500">
                                        Il saldo dettagliato è disponibile nella sezione billing del tuo account HeyGen.
                                    </p>
                                </div>
                                <div class="flex h-9 w-9 items-center justify-center rounded-full bg-sky-600 text-white ring-2 ring-sky-300">
                                    <x-heroicon-o-video-camera class="h-4 w-4" />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Filtro Date --}}
            <div class="w-full md:w-56">
                <div class="h-full rounded-2xl border border-slate-100 bg-white p-4 shadow-sm">
                    <div class="mb-1 flex items-center justify-between text-xs font-medium text-slate-500">
                        <span>Intervallo date</span>
                        <span class="inline-flex items-center gap-1 rounded-full bg-slate-100 px-2 py-0.5 text-[11px] font-semibold text-slate-700">
                            <x-heroicon-o-calendar-days class="h-3.5 w-3.5" />
                            <span>Live</span>
                        </span>
                    </div>
                    <div class="w-full">
                        {{ $this->form }}
                    </div>
                </div>
            </div>
        </div>

        {{-- STAT CARDS --}}
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
            {{-- THREAD TOTALI --}}
            <div
                class="relative overflow-hidden rounded-2xl border border-emerald-100 bg-gradient-to-br from-emerald-50 via-emerald-100 to-emerald-200 p-4 shadow-sm">
                <div class="absolute -right-6 -top-6 h-20 w-20 rounded-full bg-emerald-300/40 blur-2xl"></div>
                <div class="relative flex items-start justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-emerald-700/80">
                            Thread totali
                        </p>
                        <p class="mt-1 text-3xl font-bold tracking-tight text-emerald-900">
                            {{ number_format($threadStats['total_threads'] ?? 0) }}
                        </p>
                        <p class="mt-1 text-xs text-emerald-800/80">
                            Tutte le conversazioni registrate dai widget EnjoyHen.
                        </p>
                    </div>
                    <div class="flex h-10 w-10 items-center justify-center rounded-full bg-white/70 text-emerald-600 ring-2 ring-emerald-200">
                        <x-heroicon-o-chat-bubble-left-right class="h-5 w-5" />
                    </div>
                </div>
            </div>

            {{-- THREAD NEL PERIODO --}}
            <div
                class="relative overflow-hidden rounded-2xl border border-sky-100 bg-gradient-to-br from-sky-50 via-sky-100 to-sky-200 p-4 shadow-sm">
                <div class="absolute -right-6 -top-6 h-20 w-20 rounded-full bg-sky-300/40 blur-2xl"></div>
                <div class="relative flex items-start justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-sky-700/80">
                            Thread nel periodo
                        </p>
                        <p class="mt-1 text-3xl font-bold tracking-tight text-sky-900">
                            {{ number_format($threadStats['threads_last_7_days'] ?? 0) }}
                        </p>
                        <p class="mt-1 text-xs text-sky-800/80">
                            Conversazioni avviate nell'intervallo selezionato.
                        </p>
                    </div>
                    <div class="flex h-10 w-10 items-center justify-center rounded-full bg-white/70 text-sky-600 ring-2 ring-sky-200">
                        <x-heroicon-o-sparkles class="h-5 w-5" />
                    </div>
                </div>
            </div>

            {{-- MSG PER THREAD --}}
            <div
                class="relative overflow-hidden rounded-2xl border border-amber-100 bg-gradient-to-br from-amber-50 via-amber-100 to-amber-200 p-4 shadow-sm">
                <div class="absolute -right-6 -top-6 h-20 w-20 rounded-full bg-amber-300/40 blur-2xl"></div>
                <div class="relative flex items-start justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-amber-700/80">
                            Msg per thread (media)
                        </p>
                        <p class="mt-1 text-3xl font-bold tracking-tight text-amber-900">
                            {{ number_format($threadStats['avg_messages_per_thread'] ?? 0, 1) }}
                        </p>
                        <p class="mt-1 text-xs text-amber-800/80">
                            Più alto è il valore, più forte è l'engagement del visitatore.
                        </p>
                    </div>
                    <div class="flex h-10 w-10 items-center justify-center rounded-full bg-white/70 text-amber-600 ring-2 ring-amber-200">
                        <x-heroicon-o-bolt class="h-5 w-5" />
                    </div>
                </div>
            </div>

            {{-- IP UNICI --}}
            <div
                class="relative overflow-hidden rounded-2xl border border-violet-100 bg-gradient-to-br from-violet-50 via-violet-100 to-violet-200 p-4 shadow-sm">
                <div class="absolute -right-6 -top-6 h-20 w-20 rounded-full bg-violet-300/40 blur-2xl"></div>
                <div class="relative flex items-start justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-violet-700/80">
                            IP unici
                        </p>
                        <p class="mt-1 text-3xl font-bold tracking-tight text-violet-900">
                            {{ number_format($threadStats['unique_ips'] ?? 0) }}
                        </p>
                        <p class="mt-1 text-xs text-violet-800/80">
                            Visitatori distinti che hanno interagito con l'assistente.
                        </p>
                    </div>
                    <div class="flex h-10 w-10 items-center justify-center rounded-full bg-white/70 text-violet-600 ring-2 ring-violet-200">
                        <x-heroicon-o-globe-alt class="h-5 w-5" />
                    </div>
                </div>
            </div>
        </div>

        {{-- DUE GRAFICI FILAMENT (CHART WIDGETS) --}}
        <x-filament-widgets::widgets
            :columns="[
                'md' => 2,
            ]"
            :widgets="[
                \App\Filament\Widgets\MessagesVolumeChart::class,
                \App\Filament\Widgets\EngagementQualityChart::class,
            ]"
        />

        {{-- SEZIONE CHAT / THREADS --}}
        <div class="rounded-2xl border border-slate-100 bg-white shadow-lg shadow-slate-100/70">
            <div class="flex items-center justify-between px-6 pt-5 pb-3">
                <div>
                    <div class="inline-flex items-center gap-2 rounded-full bg-slate-50 px-3 py-1 text-xs font-semibold text-slate-700">
                        <x-heroicon-o-chat-bubble-left-right class="h-4 w-4" />
                        <span>Chat</span>
                    </div>
                    <h3 class="mt-2 text-base font-semibold text-slate-900">
                        Thread recenti
                    </h3>
                    <p class="text-xs text-slate-500">
                        Ultime conversazioni attivate dai widget EnjoyHen sui vari siti, con stato e azioni rapide.
                    </p>
                </div>
                <div class="flex items-center gap-3 text-xs">
                    <form wire:submit.prevent="searchThreads" class="relative">
                        <input
                            type="search"
                            wire:model.defer="threadSearch"
                            placeholder="Cerca nel testo della chat…"
                            class="w-52 rounded-full border border-slate-200 bg-slate-50 px-3 py-1.5 text-xs text-slate-700 placeholder-slate-400 focus:border-emerald-400 focus:bg-white focus:outline-none focus:ring-1 focus:ring-emerald-400"
                        >
                        <div class="pointer-events-none absolute inset-y-0 right-2 flex items-center text-slate-400">
                            <x-heroicon-o-magnifying-glass class="h-3.5 w-3.5" />
                        </div>
                    </form>

                    {{-- Toggle Tool Mode: RAG vs DATABASE --}}
                    <button
                        type="button"
                        id="toolModeToggle"
                        onclick="toggleToolMode()"
                        class="inline-flex items-center gap-1 rounded-full px-2 py-1 font-semibold ring-1 text-[11px] bg-indigo-50 text-indigo-700 ring-indigo-100"
                    >
                        <span id="toolModeIndicator" class="h-1.5 w-1.5 rounded-full bg-indigo-500"></span>
                        <span id="toolModeLabel">RAG</span>
                    </button>

                    {{-- Switch REALI / FAKE per la sezione in basso --}}
                    <button
                        type="button"
                        wire:click="toggleFakeDataset"
                        class="inline-flex items-center gap-1 rounded-full px-2 py-1 font-semibold ring-1 text-[11px]
                            @if ($showFake)
                                bg-amber-50 text-amber-800 ring-amber-200
                            @else
                                bg-emerald-50 text-emerald-700 ring-emerald-100
                            @endif
                        "
                    >
                        <span
                            class="h-1.5 w-1.5 rounded-full
                                @if ($showFake)
                                    bg-amber-500
                                @else
                                    bg-emerald-500
                                @endif
                            "
                        ></span>
                        <span>
                            @if ($showFake)
                                Demo (fake)
                            @else
                                Dati reali
                            @endif
                        </span>
                    </button>
                </div>
            </div>

            <div class="border-t border-slate-200">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                                Msg
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                                Thread
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                                Team / Host
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                                IP
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                                Stato
                            </th>
                            <th class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-500">
                                Azioni
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
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
                            <tr class="transition-colors odd:bg-slate-50/60 hover:bg-slate-100/70">
                                <td class="px-6 py-4 text-xs font-semibold text-slate-500">
                                    #{{ $thread->messages_count ?? ($index + 1) }}
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="flex h-10 w-10 items-center justify-center rounded-full bg-gradient-to-br from-emerald-400 to-sky-500 text-xs font-semibold text-white shadow-md shadow-emerald-200/80">
                                            {{ strtoupper(substr($thread->thread_id, -2)) }}
                                        </div>
                                        <div class="min-w-0">
                                            <div class="truncate text-sm font-semibold text-slate-900">
                                                {{ substr($thread->thread_id, 0, 24) }}{{ strlen($thread->thread_id) > 24 ? '…' : '' }}
                                            </div>
                                            <div class="flex items-center gap-2 text-xs text-slate-500">
                                                <x-heroicon-o-clock class="h-3.5 w-3.5" />
                                                {{ optional($thread->created_at)->format('d/m/Y H:i') ?? 'n/d' }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-sm text-slate-700">
                                    {{ $thread->team_slug ?? 'Sito generico' }}
                                </td>
                                <td class="px-6 py-4 text-sm text-slate-700">
                                    {{ $thread->ip_address ?? 'n/d' }}
                                </td>
                                <td class="px-6 py-4">
                                    <span
                                        class="inline-flex items-center gap-1 rounded-full px-3 py-1 text-[11px] font-semibold text-white {{ $status['color'] }}">
                                        @if ($status['label'] === 'LIVE')
                                            <span class="h-1.5 w-1.5 rounded-full bg-emerald-300"></span>
                                        @elseif ($status['label'] === 'RECENTE')
                                            <span class="h-1.5 w-1.5 rounded-full bg-indigo-300"></span>
                                        @else
                                            <span class="h-1.5 w-1.5 rounded-full bg-slate-300"></span>
                                        @endif
                                        {{ $status['label'] }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center justify-end gap-2">
                                        {{-- Azione 1: Dati thread (view Filament) --}}
                                        <a href="{{ $thread->view_url }}"
                                            class="inline-flex items-center gap-1 rounded-full bg-slate-900 px-3 py-1.5 text-xs font-semibold text-white shadow-sm shadow-slate-300/60 hover:bg-slate-800 hover:shadow-md">
                                            <x-heroicon-o-rectangle-stack class="h-3.5 w-3.5" />
                                            <span>Dati thread</span>
                                        </a>

                                        {{-- Azione 2: Visualizza conversazione (modal) --}}
                                        <button wire:click="openConversationModal('{{ $thread->thread_id }}')"
                                            class="inline-flex items-center gap-1 rounded-full bg-indigo-600 px-3 py-1.5 text-xs font-semibold text-white shadow-sm shadow-indigo-200/70 hover:bg-indigo-700 hover:shadow-md">
                                            <x-heroicon-o-eye class="h-3.5 w-3.5" />
                                            <span>Visualizza conversazione</span>
                                        </button>
                                    </div>
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

    {{-- Script per gestire il toggle Tool Mode --}}
    <script>
        // Inizializza il tool mode dal localStorage (default: 'rag')
        function initToolMode() {
            const savedMode = localStorage.getItem('aiToolMode') || 'rag';
            updateToolModeUI(savedMode);
        }

        // Aggiorna l'UI del pulsante in base al mode
        function updateToolModeUI(mode) {
            const button = document.getElementById('toolModeToggle');
            const indicator = document.getElementById('toolModeIndicator');
            const label = document.getElementById('toolModeLabel');
            
            if (!button || !indicator || !label) return;
            
            if (mode === 'rag') {
                button.className = 'inline-flex items-center gap-1 rounded-full px-2 py-1 font-semibold ring-1 text-[11px] bg-indigo-50 text-indigo-700 ring-indigo-100';
                indicator.className = 'h-1.5 w-1.5 rounded-full bg-indigo-500';
                label.textContent = 'RAG';
            } else {
                button.className = 'inline-flex items-center gap-1 rounded-full px-2 py-1 font-semibold ring-1 text-[11px] bg-purple-50 text-purple-700 ring-purple-100';
                indicator.className = 'h-1.5 w-1.5 rounded-full bg-purple-500';
                label.textContent = 'DATABASE';
            }
        }

        // Toggle tra RAG e DATABASE
        function toggleToolMode() {
            const currentMode = localStorage.getItem('aiToolMode') || 'rag';
            const newMode = currentMode === 'rag' ? 'database' : 'rag';
            localStorage.setItem('aiToolMode', newMode);
            updateToolModeUI(newMode);
        }

        // Inizializza al caricamento della pagina
        document.addEventListener('DOMContentLoaded', initToolMode);
        
        // Se il DOM è già caricato, inizializza subito
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initToolMode);
        } else {
            initToolMode();
        }
    </script>
</x-filament-panels::page>



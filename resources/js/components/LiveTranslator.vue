<template>
    <div
        class="w-full min-h-screen bg-slate-900 text-slate-100 flex items-stretch justify-center px-2 md:px-6 py-4 md:py-8">
        <div
            class="w-full max-w-6xl bg-slate-800/80 border border-slate-700 rounded-2xl shadow-2xl p-4 md:p-8 flex flex-col">
            <div class="flex items-center justify-between gap-4 mb-4 md:mb-6">
                <div>
                    <h1 class="text-2xl md:text-3xl font-semibold tracking-tight">
                        {{ ui.title }}
                    </h1>
                </div>
            </div>

            <!-- Tabs -->
            <div class="mb-6 border-b border-slate-700 pb-2">
                <div class="text-[11px] uppercase tracking-[0.2em] text-slate-400 mb-2">
                    Modalità
                </div>
                <div class="inline-flex rounded-xl bg-slate-900/70 p-1 shadow-inner shadow-black/40 text-sm">
                    <button type="button"
                        class="relative px-4 py-2 rounded-lg font-semibold transition-all duration-150 flex items-center gap-2"
                        :class="activeTab === 'call'
                            ? 'bg-emerald-500/10 text-emerald-200 shadow-[0_0_0_1px_rgba(16,185,129,0.6)]'
                            : 'text-slate-400 hover:text-slate-100 hover:bg-slate-800/80'"
                        @click="setActiveTab('call')">
                        <span class="inline-flex h-5 w-5 items-center justify-center rounded-full border text-[11px]"
                            :class="activeTab === 'call'
                                ? 'border-emerald-400 bg-emerald-500/20 text-emerald-200'
                                : 'border-slate-500 bg-slate-800 text-slate-300'">
                            A
                        </span>
                        <span class="flex flex-col items-start leading-tight">
                            <span class="text-[11px] uppercase tracking-wide">
                                Interprete &amp; CV
                            </span>
                            <span class="hidden md:inline text-[11px] text-slate-400">
                                Call di lavoro in tempo reale
                            </span>
                        </span>
                    </button>

                    <button type="button"
                        class="relative px-4 py-2 rounded-lg font-semibold transition-all duration-150 flex items-center gap-2"
                        :class="activeTab === 'youtube'
                            ? 'bg-emerald-500/10 text-emerald-200 shadow-[0_0_0_1px_rgba(16,185,129,0.6)]'
                            : 'text-slate-400 hover:text-slate-100 hover:bg-slate-800/80'"
                        @click="setActiveTab('youtube')">
                        <span class="inline-flex h-5 w-5 items-center justify-center rounded-full border text-[11px]"
                            :class="activeTab === 'youtube'
                                ? 'border-emerald-400 bg-emerald-500/20 text-emerald-200'
                                : 'border-slate-500 bg-slate-800 text-slate-300'">
                            ▶
                        </span>
                        <span class="flex flex-col items-start leading-tight">
                            <span class="text-[11px] uppercase tracking-wide">
                                YouTube Interprete
                            </span>
                            <span class="hidden md:inline text-[11px] text-slate-400">
                                Video + traduzione frase per frase
                            </span>
                        </span>
                    </button>
                </div>
            </div>

            <!-- TAB 1: Interprete e Suggeritore Call Lavoro -->
            <div v-if="activeTab === 'call'" class="flex flex-col gap-3 mb-6">
                <p v-if="statusMessage" class="text-xs text-slate-300 text-center">
                    {{ statusMessage }}
                </p>

                <div class="flex flex-col items-center gap-1 text-slate-300">
                    <div class="flex items-center justify-center gap-2 text-[13px]">
                        <input id="useWhisper" type="checkbox" v-model="useWhisper" @change="onRecognitionModeChange"
                            :disabled="!isChromeWithWebSpeech"
                            class="h-3.5 w-3.5 rounded border-slate-500 bg-slate-800 text-emerald-500 focus:ring-emerald-500 disabled:opacity-60 disabled:cursor-not-allowed" />
                        <label for="useWhisper" class="cursor-pointer select-none">
                            {{ ui.whisperLabel }}
                            <span v-if="!isChromeWithWebSpeech" class="ml-1 text-[11px] text-emerald-300">
                                ({{ ui.whisperForcedNote }})
                            </span>
                        </label>
                    </div>

                    <label class="flex items-center gap-2 text-[13px] cursor-pointer select-none">
                        <input type="checkbox" v-model="readTranslationEnabled"
                            class="h-3.5 w-3.5 rounded border-slate-500 bg-slate-800 text-emerald-500 focus:ring-emerald-500" />
                        <span>{{ ui.dubbingLabel }}</span>
                    </label>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <div class="flex flex-col gap-2">
                        <label class="text-xs font-semibold text-emerald-400">
                            {{ ui.langALabel }} <span class="text-red-400">*</span>
                        </label>
                        <select v-model="langA" @change="onLanguagePairChange"
                            class="bg-slate-800 border text-sm rounded-md px-3 py-2 focus:outline-none focus:ring-2"
                            :class="langA ? 'border-slate-600 focus:ring-emerald-500' : 'border-red-500 focus:ring-red-500'">
                            <option value="">-- Seleziona lingua A --</option>
                            <option v-for="opt in availableLanguages" :key="opt.code" :value="opt.code">
                                {{ opt.label }}
                            </option>
                        </select>

                        <!-- Pulsante microfono Lingua A -->
                        <button type="button" @click="toggleListeningForLang('A')" :disabled="!langA || !langB" class="inline-flex items-center justify-center gap-2 px-3 py-2 rounded-lg text-xs font-semibold transition
                            focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-slate-900 border"
                            :class="activeSpeaker === 'A' && isListening
                                ? 'bg-emerald-600 text-white border-emerald-400 shadow-lg shadow-emerald-500/30'
                                : 'bg-slate-700 text-slate-100 border-slate-500 hover:bg-slate-600 disabled:opacity-50 disabled:cursor-not-allowed'">
                            <span
                                class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-black/30 border border-slate-500">
                                <span class="inline-block w-1.5 h-3 rounded-full"
                                    :class="activeSpeaker === 'A' && isListening ? 'bg-red-400 animate-pulse' : 'bg-slate-300'"></span>
                            </span>
                            <span>{{ activeSpeaker === 'A' && isListening ? 'Parlante A attivo' : 'Parla Lingua A'
                            }}</span>
                        </button>
                    </div>

                    <div class="flex flex-col gap-2">
                        <label class="text-xs font-semibold text-emerald-400">
                            {{ ui.langBLabel }} <span class="text-red-400">*</span>
                        </label>
                        <select v-model="langB" @change="onLanguagePairChange"
                            class="bg-slate-800 border text-sm rounded-md px-3 py-2 focus:outline-none focus:ring-2"
                            :class="langB ? 'border-slate-600 focus:ring-emerald-500' : 'border-red-500 focus:ring-red-500'">
                            <option value="">-- Seleziona lingua B --</option>
                            <option v-for="opt in availableLanguages" :key="opt.code" :value="opt.code">
                                {{ opt.label }}
                            </option>
                        </select>

                        <!-- Pulsante microfono Lingua B -->
                        <button type="button" @click="toggleListeningForLang('B')" :disabled="!langA || !langB" class="inline-flex items-center justify-center gap-2 px-3 py-2 rounded-lg text-xs font-semibold transition
                            focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-slate-900 border"
                            :class="activeSpeaker === 'B' && isListening
                                ? 'bg-blue-600 text-white border-blue-400 shadow-lg shadow-blue-500/30'
                                : 'bg-slate-700 text-slate-100 border-slate-500 hover:bg-slate-600 disabled:opacity-50 disabled:cursor-not-allowed'">
                            <span
                                class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-black/30 border border-slate-500">
                                <span class="inline-block w-1.5 h-3 rounded-full"
                                    :class="activeSpeaker === 'B' && isListening ? 'bg-red-400 animate-pulse' : 'bg-slate-300'"></span>
                            </span>
                            <span>{{ activeSpeaker === 'B' && isListening ? 'Parlante B attivo' : 'Parla Lingua B'
                            }}</span>
                        </button>
                    </div>
                </div>
                <div class="mt-4 space-y-6">
                    <!-- Righe principali: originale, traduzione, suggerimenti affiancati -->
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 md:gap-6">
                        <div class="flex flex-col gap-2">
                            <div class="flex items-center justify-between">
                                <span class="text-base md:text-lg font-semibold text-slate-100">
                                    {{ ui.originalTitle }}
                                </span>
                                <span class="text-xs text-slate-400">
                                    {{ ui.originalSubtitle }}
                                </span>
                            </div>
                            <div ref="originalBox"
                                class="h-[100px] md:min-h-[260px] md:max-h-[420px] rounded-xl border border-slate-700 bg-slate-900/60 p-4 text-sm md:text-base lg:text-lg overflow-y-auto leading-relaxed">
                                <p v-if="!displayOriginalText" class="text-slate-500 text-xs md:text-sm">
                                    {{ ui.originalPlaceholder }}
                                </p>
                                <p v-else class="whitespace-pre-wrap">
                                    {{ displayOriginalText }}
                                </p>
                            </div>
                        </div>

                        <div class="flex flex-col gap-2">
                            <div class="flex items-center justify-between">
                                <span class="text-base md:text-lg font-semibold text-slate-100">
                                    {{ ui.translationTitle }}
                                </span>
                                <span class="text-xs text-slate-400">
                                    GPT / Neuron AI
                                </span>
                            </div>
                            <div ref="translationBox"
                                class="h-[100px] md:min-h-[260px] md:max-h-[420px] rounded-xl border border-slate-700 bg-slate-900/60 p-4 text-sm md:text-base lg:text-lg overflow-y-auto leading-relaxed">
                                <div v-if="!hasAnyTranslation" class="text-slate-500 text-xs md:text-sm">
                                    La traduzione apparirà qui man mano che parli.
                                </div>
                                <div v-else class="space-y-2">
                                    <!-- Frasi già tradotte (segmenti fissi) -->
                                    <div v-for="(seg, idx) in translationSegments" :key="'seg-' + idx"
                                        class="whitespace-pre-wrap">
                                        {{ seg }}
                                    </div>
                                    <!-- Frase corrente in streaming, aggiornata token per token con manipolazione diretta DOM -->
                                    <div ref="translationLiveContainer" class="whitespace-pre-wrap"></div>
                                </div>
                            </div>
                        </div>
                        <div class="flex flex-col gap-2">
                            <div class="flex items-center justify-between gap-4">
                                <div>
                                    <h2 class="text-base md:text-lg font-semibold text-slate-100">
                                        {{ ui.suggestionsTitle }}
                                        <span v-if="langA && langB" class="text-sm text-emerald-400">
                                            ({{ langA.toUpperCase() }} + {{ langB.toUpperCase() }})
                                        </span>
                                    </h2>
                                </div>
                            </div>

                            <div ref="suggestionsBox"
                                class="h-[100px] md:min-h-[260px] md:max-h-[420px] rounded-xl border border-slate-700 bg-slate-900/70 p-4 text-xs md:text-sm lg:text-base overflow-y-auto space-y-3 leading-relaxed">
                                <div v-if="!cvText" class="text-xs md:text-sm text-slate-500">
                                    Carica il tuo CV qui sotto per abilitare i suggerimenti basati sul curriculum.
                                </div>

                                <div v-else-if="!langA || !langB" class="text-xs md:text-sm text-slate-500">
                                    Seleziona entrambe le lingue per visualizzare i suggerimenti bilingue.
                                </div>

                                <div v-else>
                                    <p v-if="isLoadingSuggestion" class="text-xs md:text-sm text-emerald-300 mb-2">
                                        Sto preparando un suggerimento basato sul tuo CV...
                                    </p>

                                    <div v-if="suggestions.length === 0 && !isLoadingSuggestion"
                                        class="text-xs md:text-sm text-slate-500">
                                        Quando il sistema riconosce una frase (domanda o tua risposta), qui comparirà un
                                        suggerimento nelle due lingue selezionate coerente con il tuo CV.
                                    </div>

                                    <div v-for="(item, idx) in suggestions" :key="idx"
                                        class="rounded-lg border border-slate-700 bg-slate-900/80 p-3 md:p-4 space-y-2 mb-2">
                                        <div class="text-[11px] md:text-xs text-slate-400">
                                            Riferito alla frase:
                                            <span class="italic text-slate-300">
                                                "{{ item.utterancePreview }}"
                                            </span>
                                        </div>
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                            <div class="space-y-1">
                                                <div class="text-[11px] md:text-xs font-semibold text-slate-200">
                                                    {{ getLangLabel(item.langA) }}
                                                </div>
                                                <div
                                                    class="text-xs md:text-sm text-slate-100 whitespace-pre-wrap leading-relaxed">
                                                    {{ item.suggestionLangA }}
                                                </div>
                                            </div>
                                            <div class="space-y-1">
                                                <div class="text-[11px] md:text-xs font-semibold text-slate-200">
                                                    {{ getLangLabel(item.langB) }}
                                                </div>
                                                <div
                                                    class="text-xs md:text-sm text-slate-100 whitespace-pre-wrap leading-relaxed">
                                                    {{ item.suggestionLangB }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- CV spostato sotto -->
                    <div class="border-t border-slate-700 pt-4 space-y-3">
                        <div>
                            <h2 class="text-sm font-semibold text-slate-100">
                                CV per i suggerimenti
                            </h2>
                            <p class="text-[11px] text-slate-300 mt-1">
                                Carica un file di testo con il tuo CV. Verrà usato solo per generare suggerimenti, non
                                per
                                le traduzioni.
                            </p>
                        </div>
                        <div
                            class="rounded-xl border border-slate-700 bg-slate-900/80 p-3 text-xs space-y-2 max-h-[260px] overflow-y-auto">
                            <label class="block text-[11px] font-medium text-slate-200 mb-1">
                                Carica CV da file (.txt)
                            </label>
                            <input type="file" accept=".txt,.md,.rtf"
                                class="block w-full text-[11px] text-slate-200 file:text-[11px] file:px-2 file:py-1 file:mr-2 file:rounded-md file:border-0 file:bg-emerald-600 file:text-white file:cursor-pointer cursor-pointer"
                                @change="onCvFileChange" />
                            <p class="text-[10px] text-slate-500 mt-2">
                                Suggerimento: salva il tuo CV in formato testo (.txt) e caricalo da qui.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- TAB 2: Traduttore Video Youtube -->
            <div v-else class="flex flex-col gap-4">
                <p v-if="statusMessage" class="text-xs text-slate-300 text-center">
                    {{ statusMessage }}
                </p>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                    <!-- Colonna impostazioni video -->
                    <div class="lg:col-span-1 space-y-3">
                        <div class="space-y-2">
                            <label class="text-xs font-semibold text-emerald-400">
                                URL video YouTube
                            </label>
                            <input v-model="youtubeUrl" type="text" placeholder="https://www.youtube.com/watch?v=..."
                                class="w-full bg-slate-900 border border-slate-700 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500" />
                            <p class="text-[11px] text-slate-400">
                                Incolla qui il link del video che vuoi usare durante la call di lavoro.
                            </p>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            <div class="flex flex-col gap-1">
                                <label class="text-xs font-semibold text-emerald-400">
                                    Lingua del video
                                </label>
                                <select v-model="youtubeLangSource"
                                    class="bg-slate-900 border text-sm rounded-md px-3 py-2 focus:outline-none focus:ring-2"
                                    :class="youtubeLangSource ? 'border-slate-600 focus:ring-emerald-500' : 'border-red-500 focus:ring-red-500'">
                                    <option value="">-- Seleziona --</option>
                                    <option v-for="opt in availableLanguages" :key="'yt-src-' + opt.code"
                                        :value="opt.code">
                                        {{ opt.label }}
                                    </option>
                                </select>
                            </div>
                            <div class="flex flex-col gap-1">
                                <label class="text-xs font-semibold text-emerald-400">
                                    Lingua di traduzione
                                </label>
                                <select v-model="youtubeLangTarget"
                                    class="bg-slate-900 border text-sm rounded-md px-3 py-2 focus:outline-none focus:ring-2"
                                    :class="youtubeLangTarget ? 'border-slate-600 focus:ring-emerald-500' : 'border-red-500 focus:ring-red-500'">
                                    <option value="">-- Seleziona --</option>
                                    <option v-for="opt in availableLanguages" :key="'yt-tgt-' + opt.code"
                                        :value="opt.code">
                                        {{ opt.label }}
                                    </option>
                                </select>
                            </div>
                        </div>

                        <button type="button" @click="onYoutubeTranslateClick"
                            class="mt-2 inline-flex items-center justify-center gap-2 px-4 py-2 rounded-lg text-xs font-semibold border border-emerald-500 text-emerald-100 bg-emerald-700 hover:bg-emerald-600 transition">
                            Avvia modalità interprete sul video
                        </button>

                        <p class="text-[11px] text-slate-400 mt-2">
                            Il video verrà riprodotto qui a fianco. Tu parli ad alta voce quello che senti e il sistema
                            farà da interprete come nella modalità microfono: ad ogni frase tradotta il video si mette
                            in
                            pausa mentre legge la traduzione, poi riprende automaticamente.
                        </p>

                        <!-- Controllo microfono per modalità YouTube (interprete umano) -->
                        <div class="mt-4 space-y-1">
                            <button type="button" @click="toggleListeningForLang('A')" :disabled="!langA || !langB"
                                class="inline-flex items-center justify-center gap-2 px-3 py-2 rounded-lg text-xs font-semibold transition
                                    focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-slate-900 border"
                                :class="activeSpeaker === 'A' && isListening
                                    ? 'bg-emerald-600 text-white border-emerald-400 shadow-lg shadow-emerald-500/30'
                                    : 'bg-slate-700 text-slate-100 border-slate-500 hover:bg-slate-600 disabled:opacity-50 disabled:cursor-not-allowed'">
                                <span
                                    class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-black/30 border border-slate-500">
                                    <span class="inline-block w-1.5 h-3 rounded-full"
                                        :class="activeSpeaker === 'A' && isListening ? 'bg-red-400 animate-pulse' : 'bg-slate-300'">
                                    </span>
                                </span>
                                <span>
                                    {{ activeSpeaker === 'A' && isListening
                                        ? 'Interprete attivo: parla sopra il video'
                                        : 'Parla sopra il video (interprete)' }}
                                </span>
                            </button>
                            <p class="text-[11px] text-slate-400">
                                Riconosco la tua voce in <span class="font-semibold">{{ getLangLabel(langA) }}</span> e
                                traduco in <span class="font-semibold">{{ getLangLabel(langB) }}</span>.
                            </p>
                        </div>
                    </div>

                    <!-- Colonna video + pannelli di traduzione riutilizzati -->
                    <div class="lg:col-span-2 space-y-4">
                        <div
                            class="aspect-video w-full rounded-xl border border-slate-700 bg-black overflow-hidden flex items-center justify-center">
                            <div v-if="!youtubeVideoId" class="text-xs text-slate-400 px-4 text-center">
                                Incolla un URL di YouTube e clicca
                                <span class="font-semibold text-emerald-300">"Avvia modalità interprete sul
                                    video"</span>
                                per caricare il player.
                            </div>
                            <div v-else ref="youtubePlayer" class="w-full h-full"></div>
                        </div>

                        <!-- Riutilizzo pannelli originale/traduzione (solo layout) -->
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                            <div class="flex flex-col gap-2">
                                <div class="flex items-center justify-between">
                                    <span class="text-sm md:text-base font-semibold text-slate-100">
                                        Testo riconosciuto dal microfono
                                    </span>
                                </div>
                                <div ref="originalBox"
                                    class="h-[120px] md:h-[200px] rounded-xl border border-slate-700 bg-slate-900/60 p-3 text-xs md:text-sm overflow-y-auto leading-relaxed">
                                    <p v-if="!displayOriginalText" class="text-slate-500 text-xs md:text-sm">
                                        Inizia a parlare sopra il video per vedere qui le frasi riconosciute.
                                    </p>
                                    <p v-else class="whitespace-pre-wrap">
                                        {{ displayOriginalText }}
                                    </p>
                                </div>
                            </div>
                            <div class="flex flex-col gap-2">
                                <div class="flex items-center justify-between">
                                    <span class="text-sm md:text-base font-semibold text-slate-100">
                                        Traduzione in tempo reale
                                    </span>
                                </div>
                                <div ref="translationBox"
                                    class="h-[120px] md:h-[200px] rounded-xl border border-slate-700 bg-slate-900/60 p-3 text-xs md:text-sm overflow-y-auto leading-relaxed">
                                    <div v-if="!hasAnyTranslation" class="text-slate-500 text-xs md:text-sm">
                                        Le traduzioni delle frasi parlate appariranno qui, mentre il video si mette in
                                        pausa durante il doppiaggio.
                                    </div>
                                    <div v-else class="space-y-2">
                                        <div v-for="(seg, idx) in translationSegments" :key="'yt-seg-' + idx"
                                            class="whitespace-pre-wrap">
                                            {{ seg }}
                                        </div>
                                        <div ref="translationLiveContainer" class="whitespace-pre-wrap"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import WhisperSpeechRecognition from '../utils/WhisperSpeechRecognition';

export default {
    name: 'LiveTranslator',
    props: {
        locale: {
            type: String,
            default: 'it-IT',
        },
    },
    data() {
        return {
            // Tab attiva: 'call' (interprete) o 'youtube' (video)
            activeTab: 'call',

            isListening: false,
            recognition: null,
            originalConfirmed: '',
            originalInterim: '',
            lastFinalOriginalAt: 0,
            translationConfirmed: '',
            translationStreaming: '',
            translationSegments: [],
            translationTokens: [],
            statusMessage: '',
            autoRestart: true,
            currentStream: null,
            cvText: '',
            isLoadingSuggestion: false,
            suggestions: [],
            lastPreviewText: '',
            lastPreviewAt: 0,
            langA: '',
            langB: '',
            currentMicLang: '',
            currentTargetLang: '',
            activeSpeaker: null, // 'A' o 'B' - indica chi sta parlando
            useWhisper: false,
            isChromeWithWebSpeech: true,
            readTranslationEnabled: false,
            ttsQueue: [],
            isTtsPlaying: false,
            wasListeningBeforeTts: false,
            lastSpeakerBeforeTts: null,
            translationThreadId: null,

            // Modalità low-power per mobile (niente streaming token-per-token)
            isMobileLowPower: false,

            // Stato per modalità "Traduttore Video Youtube"
            youtubeUrl: '',
            youtubeVideoId: '',
            youtubePlayer: null,
            youtubeLangSource: '',
            youtubeLangTarget: '',
            isYoutubePlayerReady: false,

            availableLanguages: [
                // Lingue principali europee
                { code: 'it', label: '🇮🇹 Italiano', micCode: 'it-IT' },
                { code: 'en', label: '🇬🇧 English', micCode: 'en-US' },
                { code: 'es', label: '🇪🇸 Español', micCode: 'es-ES' },
                { code: 'fr', label: '🇫🇷 Français', micCode: 'fr-FR' },
                { code: 'de', label: '🇩🇪 Deutsch', micCode: 'de-DE' },
                { code: 'pt', label: '🇵🇹 Português', micCode: 'pt-PT' },
                { code: 'nl', label: '🇳🇱 Nederlands', micCode: 'nl-NL' },
                { code: 'sv', label: '🇸🇪 Svenska', micCode: 'sv-SE' },
                { code: 'no', label: '🇳🇴 Norsk', micCode: 'nb-NO' },
                { code: 'da', label: '🇩🇰 Dansk', micCode: 'da-DK' },
                { code: 'fi', label: '🇫🇮 Suomi', micCode: 'fi-FI' },
                { code: 'pl', label: '🇵🇱 Polski', micCode: 'pl-PL' },
                { code: 'cs', label: '🇨🇿 Čeština', micCode: 'cs-CZ' },
                { code: 'sk', label: '🇸🇰 Slovenčina', micCode: 'sk-SK' },
                { code: 'hu', label: '🇭🇺 Magyar', micCode: 'hu-HU' },
                { code: 'ro', label: '🇷🇴 Română', micCode: 'ro-RO' },
                { code: 'bg', label: '🇧🇬 Български', micCode: 'bg-BG' },
                { code: 'el', label: '🇬🇷 Ελληνικά', micCode: 'el-GR' },
                { code: 'uk', label: '🇺🇦 Українська', micCode: 'uk-UA' },

                // Lingue globali extra-europee
                { code: 'ru', label: '🇷🇺 Русский', micCode: 'ru-RU' },
                { code: 'tr', label: '🇹🇷 Türkçe', micCode: 'tr-TR' },
                { code: 'ar', label: '🇸🇦 العربية', micCode: 'ar-SA' },
                { code: 'he', label: '🇮🇱 עברית', micCode: 'he-IL' },
                { code: 'hi', label: '🇮🇳 हिन्दी', micCode: 'hi-IN' },
                { code: 'zh', label: '🇨🇳 中文 (Mandarin)', micCode: 'zh-CN' },
                { code: 'ja', label: '🇯🇵 日本語', micCode: 'ja-JP' },
                { code: 'ko', label: '🇰🇷 한국어', micCode: 'ko-KR' },
                { code: 'id', label: '🇮🇩 Bahasa Indonesia', micCode: 'id-ID' },
                { code: 'ms', label: '🇲🇾 Bahasa Melayu', micCode: 'ms-MY' },
                { code: 'th', label: '🇹🇭 ไทย', micCode: 'th-TH' },
                { code: 'vi', label: '🇻🇳 Tiếng Việt', micCode: 'vi-VN' },
            ],
            uiLocale: 'it',
        };
    },
    computed: {
        ui() {
            const lang = (this.uiLocale || 'it').toLowerCase();
            const dict = {
                it: {
                    title: 'PolyGlide - Traduttore Istantaneo',
                    subtitle: 'Parla in qualsiasi lingua: vedrai il testo originale e la traduzione live.',
                    langALabel: 'Lingua A',
                    langBLabel: 'Lingua B',
                    whisperLabel: 'Usa Whisper (OpenAI) invece del riconoscimento vocale del browser',
                    whisperForcedNote: 'forzato: non sei su Chrome',
                    dubbingLabel: 'Leggi la traduzione (doppiaggio)',
                    originalTitle: 'Testo originale',
                    originalSubtitle: 'Riconosciuto dal microfono',
                    originalPlaceholder: 'Inizia a parlare per vedere qui la trascrizione in tempo reale.',
                    translationTitle: 'Traduzione',
                    suggestionsTitle: 'Suggerimenti per il colloquio',
                    ttsBusyMessage: 'Sto leggendo la traduzione, attendi che finisca prima di parlare.',
                },
                en: {
                    title: 'PolyGlide - Instant Translator',
                    subtitle: 'Speak in any language: you will see the original text and the live translation.',
                    langALabel: 'Language A',
                    langBLabel: 'Language B',
                    whisperLabel: 'Use Whisper (OpenAI) instead of the browser speech recognition',
                    whisperForcedNote: 'forced: you are not on Chrome',
                    dubbingLabel: 'Read the translation aloud (dubbing)',
                    originalTitle: 'Original text',
                    originalSubtitle: 'Recognised from microphone',
                    originalPlaceholder: 'Start speaking to see the real-time transcription here.',
                    translationTitle: 'Translation',
                    suggestionsTitle: 'Interview suggestions',
                    ttsBusyMessage: 'I am reading the translation, please wait until it finishes before speaking.',
                },
            };

            return dict[lang] || dict.en;
        },
        displayOriginalText() {
            const base = this.originalConfirmed || '';
            const interim = this.originalInterim || '';
            return [base, interim].filter(Boolean).join('\n');
        },
        displayTranslationText() {
            // Usato solo per debug o fallback: unisce segmenti + tokens correnti
            const segmentsText = (this.translationSegments || []).join('\n');
            const streaming = (this.translationTokens || []).join(' ');
            return [segmentsText, streaming].filter(Boolean).join('\n');
        },
        hasAnyTranslation() {
            return (
                (this.translationSegments && this.translationSegments.length > 0) ||
                (this.translationTokens && this.translationTokens.length > 0)
            );
        },
    },
    mounted() {
        this.detectUiLocale();
        this.initDefaultLanguages();
        this.detectEnvAndDefaultMode();
        this.detectMobileLowPower();
    },
    beforeUnmount() {
        this.stopListeningInternal();
        if (this.currentStream) {
            try {
                this.currentStream.close();
            } catch { }
            this.currentStream = null;
        }
    },
    methods: {
        detectMobileLowPower() {
            try {
                const ua = (navigator.userAgent || '').toLowerCase();
                const isMobile =
                    ua.includes('iphone') ||
                    ua.includes('ipad') ||
                    ua.includes('android') ||
                    ua.includes('mobile');

                this.isMobileLowPower = !!isMobile;
            } catch {
                this.isMobileLowPower = false;
            }
        },

        setActiveTab(tab) {
            this.activeTab = tab;
            // In modalità YouTube abilitiamo sempre il doppiaggio,
            // perché serve a fare da interprete sopra al video.
            if (tab === 'youtube') {
                this.readTranslationEnabled = true;
            }
        },

        getLangLabel(langCode) {
            const lang = this.availableLanguages.find(l => l.code === langCode);
            return lang ? lang.label : langCode.toUpperCase();
        },

        scrollToBottom(refName) {
            try {
                const el = this.$refs[refName];
                if (el && el.scrollHeight !== undefined) {
                    el.scrollTop = el.scrollHeight;
                }
            } catch {
                // ignore
            }
        },

        detectRecognitionLang() {
            try {
                const urlParams = new URLSearchParams(window.location.search || '');
                const urlLang = (urlParams.get('lang') || '').trim();
                const navLang = (
                    navigator.language ||
                    (navigator.languages && navigator.languages[0]) ||
                    ''
                ).trim();

                const base = urlLang || this.locale || navLang || 'it-IT';
                const normalized = base.replace('_', '-').trim();
                if (!normalized) return 'it-IT';

                return normalized;
            } catch {
                return 'it-IT';
            }
        },

        initDefaultLanguages() {
            try {
                const nav = (navigator.language || (navigator.languages && navigator.languages[0]) || this.locale || 'it-IT').toString();
                const base = nav.split(/[-_]/)[0].toLowerCase();

                let defaultA = 'it';
                let defaultB = 'en';

                const match = this.availableLanguages.find(l => l.code === base);
                if (match) {
                    defaultA = match.code;
                }

                // Se la lingua A è già inglese, metti italiano come B; altrimenti metti inglese come B
                defaultB = defaultA === 'en' ? 'it' : 'en';

                this.langA = defaultA;
                this.langB = defaultB;
            } catch {
                this.langA = 'it';
                this.langB = 'en';
            }

            this.onLanguagePairChange();
        },

        detectUiLocale() {
            try {
                const nav = (navigator.language || (navigator.languages && navigator.languages[0]) || this.locale || 'it-IT').toString();
                const code = nav.split(/[-_]/)[0].toLowerCase();
                this.uiLocale = ['it', 'en'].includes(code) ? code : 'en';
            } catch {
                this.uiLocale = 'it';
            }
        },

        detectEnvAndDefaultMode() {
            try {
                const hasWebSpeech = !!(window.SpeechRecognition || window.webkitSpeechRecognition);
                const ua = (navigator.userAgent || '').toLowerCase();
                const isChrome = hasWebSpeech && ua.includes('chrome') && !ua.includes('edg') && !ua.includes('opr');

                this.isChromeWithWebSpeech = !!isChrome;

                if (!this.isChromeWithWebSpeech) {
                    // Browser non-Chrome: forza modalità Whisper e non permettere cambio
                    this.useWhisper = true;
                    this.autoRestart = false;
                    this.statusMessage = 'Modalità Whisper attiva automaticamente: il riconoscimento vocale del browser non è pienamente supportato qui.';
                }
            } catch {
                this.isChromeWithWebSpeech = false;
                this.useWhisper = true;
                this.autoRestart = false;
            }
        },

        initSpeechRecognition() {
            try {
                let RecClass = null;

                if (this.useWhisper) {
                    RecClass = WhisperSpeechRecognition;
                } else {
                    RecClass = window.SpeechRecognition || window.webkitSpeechRecognition;
                }

                if (!RecClass) {
                    this.statusMessage = this.useWhisper
                        ? 'Modalità Whisper attiva ma il wrapper non è disponibile in questo browser.'
                        : 'Riconoscimento vocale non disponibile in questo browser. Puoi attivare la modalità Whisper.';
                    return;
                }

                this.recognition = new RecClass();
                this.recognition.lang = this.currentMicLang || this.detectRecognitionLang();
                this.recognition.continuous = true;
                // In modalità Whisper non gestiamo davvero gli interim, arrivano solo final
                this.recognition.interimResults = !this.useWhisper;
                this.recognition.maxAlternatives = 1;

                this.recognition.onstart = () => {
                    // Nessun messaggio di stato
                };

                this.recognition.onerror = (e) => {
                    const err = e && (e.error || e.message) ? String(e.error || e.message) : 'errore sconosciuto';
                    this.statusMessage = `Errore microfono: ${err}`;
                    this.isListening = false;
                };

                this.recognition.onend = () => {
                    // Niente auto-restart in modalità Whisper per evitare loop strani
                    if (this.isListening && this.autoRestart && !this.useWhisper) {
                        try {
                            this.recognition.start();
                        } catch { }
                    } else {
                        // Nessun messaggio di stato
                    }
                };

                this.recognition.onresult = (event) => {
                    try {
                        let interim = '';
                        const results = event.results;

                        for (let i = event.resultIndex; i < results.length; i++) {
                            const res = results[i];
                            const text = (res[0] && res[0].transcript) || '';
                            if (!text) continue;

                            if (res.isFinal) {
                                const clean = text.trim();
                                if (clean) {
                                    // Gestione speciale per mobile: molti browser inviano piu' final progressivi
                                    // (es. \"perche'\", \"perche' nel\", \"perche' nel telefono\"...).
                                    // In questi casi aggiorniamo l'ULTIMA riga invece di crearne una nuova.
                                    const phraseWithDash = `- ${clean}`;
                                    const now = Date.now();
                                    const lines = (this.originalConfirmed || '')
                                        .split('\n')
                                        .filter(Boolean);

                                    let mergedWithPrevious = false;

                                    if (lines.length > 0 && now - this.lastFinalOriginalAt < 2000) {
                                        const lastLine = lines[lines.length - 1];
                                        const prevText = lastLine.startsWith('- ')
                                            ? lastLine.slice(2).trim()
                                            : lastLine.trim();

                                        if (prevText) {
                                            // Se il nuovo testo estende il precedente (o viceversa),
                                            // consideriamolo come la stessa frase aggiornata.
                                            if (
                                                clean.startsWith(prevText) ||
                                                prevText.startsWith(clean)
                                            ) {
                                                lines[lines.length - 1] = phraseWithDash;
                                                this.originalConfirmed = lines.join('\n');
                                                mergedWithPrevious = true;
                                            }
                                        }
                                    }

                                    if (!mergedWithPrevious) {
                                        this.originalConfirmed = this.originalConfirmed
                                            ? `${this.originalConfirmed}\n${phraseWithDash}`
                                            : phraseWithDash;
                                    }

                                    this.lastFinalOriginalAt = now;
                                    this.originalInterim = '';
                                    // Traduci la singola frase appena conclusa
                                    this.startTranslationStream(clean, {
                                        commit: true,
                                        mergeLast: mergedWithPrevious,
                                    });
                                    this.maybeRequestInterviewSuggestion(clean);
                                }
                            } else {
                                interim = [interim, text.trim()].filter(Boolean).join(' ');
                            }
                        }

                        this.originalInterim = interim;

                        this.$nextTick(() => {
                            this.scrollToBottom('originalBox');
                        });
                        // Mentre parli, usa l'interim per una traduzione incrementale
                        // solo su desktop: su mobile low-power saltiamo lo streaming
                        if (interim && !this.isMobileLowPower) {
                            this.maybeStartPreviewTranslation(interim);
                        }
                    } catch (err) {
                        console.warn('Errore gestione risultato speech', err);
                    }
                };
            } catch (e) {
                this.statusMessage = 'Errore inizializzazione microfono.';
            }
        },

        async ensureMicPermission() {
            try {
                if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                    return true;
                }

                const stream = await navigator.mediaDevices.getUserMedia({
                    audio: {
                        echoCancellation: true,
                        noiseSuppression: true,
                        autoGainControl: true,
                        sampleRate: { ideal: 48000 },
                        channelCount: 1,
                        latency: 0,
                        volume: 1.0,
                    },
                });

                try {
                    stream.getTracks().forEach((t) => t.stop());
                } catch { }

                return true;
            } catch {
                return false;
            }
        },

        async toggleListeningForLang(speaker) {
            // Non registrare mentre il TTS sta leggendo
            if (this.isTtsPlaying) {
                this.statusMessage = this.ui.ttsBusyMessage || 'Sto leggendo la traduzione, attendi che finisca prima di parlare.';
                return;
            }
            // Se sta già ascoltando con lo stesso speaker, ferma
            if (this.isListening && this.activeSpeaker === speaker) {
                this.stopListeningInternal();
                return;
            }

            // Se sta ascoltando con un altro speaker, ferma quello prima
            if (this.isListening && this.activeSpeaker !== speaker) {
                this.stopListeningInternal();
                // Attendi un attimo per assicurarsi che il recognition sia fermato
                await new Promise(resolve => setTimeout(resolve, 200));
            }

            // Validazione: entrambe le lingue devono essere selezionate
            if (!this.langA || !this.langB) {
                this.statusMessage = '⚠️ Seleziona entrambe le lingue (A e B) prima di iniziare!';
                return;
            }

            const ok = await this.ensureMicPermission();
            if (!ok) {
                this.statusMessage = 'Permesso microfono negato. Abilitalo nelle impostazioni del browser.';
                return;
            }

            // Imposta lingua e target in base al parlante
            this.activeSpeaker = speaker;
            if (speaker === 'A') {
                const langAObj = this.availableLanguages.find(l => l.code === this.langA);
                if (langAObj) {
                    this.currentMicLang = langAObj.micCode;
                    this.currentTargetLang = this.langB;
                }
            } else {
                const langBObj = this.availableLanguages.find(l => l.code === this.langB);
                if (langBObj) {
                    this.currentMicLang = langBObj.micCode;
                    this.currentTargetLang = this.langA;
                }
            }

            if (!this.recognition) {
                this.initSpeechRecognition();
                if (!this.recognition) {
                    return;
                }
            }

            // Aggiorna lingua del recognition
            if (this.recognition) {
                this.recognition.lang = this.currentMicLang;
            }

            try {
                this.isListening = true;
                this.recognition.start();

                // In modalità YouTube, aspetta 1 secondo dopo l'attivazione del microfono
                // e poi prova a far partire il video (quando il player è pronto).
                if (this.activeTab === 'youtube') {
                    setTimeout(() => {
                        this.playYoutubeAfterMic();
                    }, 1000);
                }
            } catch (e) {
                this.statusMessage = 'Impossibile avviare il microfono.';
                this.isListening = false;
                this.activeSpeaker = null;
            }
        },

        stopListeningInternal() {
            this.isListening = false;
            this.activeSpeaker = null;
            if (this.recognition) {
                try {
                    this.recognition.stop();
                    this.recognition.abort && this.recognition.abort();
                } catch { }
            }
        },

        startTranslationStream(textSegment, options = { commit: true, mergeLast: false }) {
            const safeText = (textSegment || '').trim();
            if (!safeText) return;

            const commit = options && typeof options.commit === 'boolean' ? options.commit : true;
            const mergeLast = options && typeof options.mergeLast === 'boolean' ? options.mergeLast : false;

            // Se è già attivo uno stream e questa è una richiesta finale (commit: true),
            // chiudiamo lo stream precedente per dare priorità alla frase completa
            if (this.currentStream) {
                if (commit) {
                    try {
                        this.currentStream.close();
                    } catch { }
                    this.currentStream = null;
                } else {
                    // Se è solo una preview (commit: false), ignora
                    return;
                }
            }

            // Assicurati che currentTargetLang sia sempre impostato correttamente
            if (!this.currentTargetLang && this.langA && this.langB) {
                // Default: se parli langA, traduci verso langB
                this.currentTargetLang = this.currentMicLang && this.currentMicLang.startsWith(this.langA.split('-')[0])
                    ? this.langB
                    : this.langA;
            }

            const targetLang = this.currentTargetLang || this.langB || 'en';

            // Genera thread_id se non esiste ancora
            if (!this.translationThreadId) {
                this.translationThreadId = 'translation_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
            }

            const params = new URLSearchParams({
                text: safeText,
                source_lang: this.currentMicLang || '',
                locale: this.locale || 'it',
                target_lang: targetLang,
                thread_id: this.translationThreadId,
                ts: String(Date.now()),
            });

            console.log(`📤 Traduzione richiesta: "${safeText.substring(0, 50)}..." → target_lang: ${targetLang}, source_lang: ${this.currentMicLang}`);

            const origin = window.__NEURON_TRANSLATOR_ORIGIN__ || window.location.origin;
            const endpoint = `/api/chatbot/neuron-translator-stream?${params.toString()}`;

            try {
                const es = new EventSource(`${origin}${endpoint}`);
                this.currentStream = es;
                let buffer = '';

                es.addEventListener('message', (e) => {
                    try {
                        const data = JSON.parse(e.data);
                        if (data.token) {
                            buffer += data.token;

                            // Su desktop: aggiorna in streaming token-per-token.
                            // Su mobile low-power: NON aggiornare in streaming, aspetta la frase finale.
                            if (!this.isMobileLowPower) {
                                this.updateTranslationTokens(buffer);
                                this.$nextTick(() => {
                                    this.scrollToBottom('translationBox');
                                });
                            }
                        }
                    } catch { }
                });

                es.addEventListener('done', () => {
                    try {
                        es.close();
                    } catch { }
                    const segment = buffer.trim();
                    if (commit && segment) {
                        // Quando una frase è conclusa:
                        // - se mergeLast è true, aggiorniamo l'ultima riga (caso mobile con final progressivi)
                        // - altrimenti aggiungiamo una nuova riga
                        if (mergeLast && this.translationSegments && this.translationSegments.length > 0) {
                            this.translationSegments.splice(
                                this.translationSegments.length - 1,
                                1,
                                `- ${segment}`
                            );
                        } else {
                            this.translationSegments.push(`- ${segment}`);
                        }

                        // Se il doppiaggio è attivo, metti in coda la traduzione per il TTS
                        if (this.readTranslationEnabled) {
                            this.enqueueTranslationForTts(segment, targetLang);
                        }

                        // Svuotiamo il container DOM della frase corrente per evitare duplicati
                        this.$nextTick(() => {
                            const container = this.$refs.translationLiveContainer;
                            if (container) {
                                container.innerHTML = '';
                            }
                        });
                    }
                    this.translationStreaming = '';
                    this.translationTokens = [];
                    this.currentStream = null;
                    this.$nextTick(() => {
                        this.scrollToBottom('translationBox');
                    });
                });

                es.addEventListener('error', () => {
                    try {
                        es.close();
                    } catch { }
                    this.currentStream = null;
                });
            } catch {
                // In caso di errore, non blocchiamo l'interfaccia
            }
        },

        getLocaleForLangCode(langCode) {
            const code = (langCode || '').toLowerCase();
            if (!code) {
                return this.locale || 'it-IT';
            }

            const langObj = this.availableLanguages.find(l => l.code === code);
            if (langObj && langObj.micCode) {
                return langObj.micCode;
            }

            // Se è già in formato BCP-47, usalo così com'è
            if (code.includes('-')) {
                return code;
            }

            return this.locale || 'it-IT';
        },

        enqueueTranslationForTts(text, langCode) {
            const safe = (text || '').trim();
            if (!safe) return;

            const locale = this.getLocaleForLangCode(langCode || this.currentTargetLang || this.langB || 'en');

            this.ttsQueue.push({
                text: safe,
                locale,
            });

            this.processTtsQueue();
        },

        async processTtsQueue() {
            if (this.isTtsPlaying) {
                return;
            }

            const next = this.ttsQueue.shift();
            if (!next) {
                return;
            }

            this.isTtsPlaying = true;

            // In modalità YouTube, mettiamo in pausa il video mentre parte il TTS
            if (this.activeTab === 'youtube') {
                this.pauseYoutubeIfNeeded();
            }

            // Se il microfono è attivo, mettilo in pausa mentre il TTS parla
            this.wasListeningBeforeTts = this.isListening;
            this.lastSpeakerBeforeTts = this.activeSpeaker;
            if (this.wasListeningBeforeTts) {
                this.stopListeningInternal();
                this.statusMessage = this.ui.ttsBusyMessage || this.statusMessage;
            }

            try {
                const res = await fetch('/api/tts', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify({
                        text: next.text,
                        locale: next.locale,
                        format: 'mp3',
                    }),
                });

                if (!res.ok) {
                    // Se fallisce, passa oltre senza bloccare la coda
                    this.isTtsPlaying = false;
                    this.processTtsQueue();
                    return;
                }

                const blob = await res.blob();
                const url = URL.createObjectURL(blob);
                const audio = new Audio(url);

                audio.onended = () => {
                    URL.revokeObjectURL(url);
                    const shouldResume = this.wasListeningBeforeTts;
                    const speaker = this.lastSpeakerBeforeTts;
                    this.wasListeningBeforeTts = false;
                    this.lastSpeakerBeforeTts = null;
                    this.isTtsPlaying = false;

                    if (shouldResume && speaker) {
                        this.toggleListeningForLang(speaker);
                    }

                    this.processTtsQueue();
                };

                audio.onerror = () => {
                    URL.revokeObjectURL(url);
                    const shouldResume = this.wasListeningBeforeTts;
                    const speaker = this.lastSpeakerBeforeTts;
                    this.wasListeningBeforeTts = false;
                    this.lastSpeakerBeforeTts = null;
                    this.isTtsPlaying = false;

                    if (shouldResume && speaker) {
                        this.toggleListeningForLang(speaker);
                    }

                    this.processTtsQueue();
                };

                try {
                    await audio.play();
                } catch {
                    URL.revokeObjectURL(url);
                    this.isTtsPlaying = false;
                    this.processTtsQueue();
                }
            } catch {
                this.isTtsPlaying = false;
                this.processTtsQueue();
            }
        },

        pauseYoutubeIfNeeded() {
            try {
                if (this.youtubePlayer && typeof this.youtubePlayer.pauseVideo === 'function') {
                    this.youtubePlayer.pauseVideo();
                }
            } catch {
                // ignora errori del player
            }
        },

        resumeYoutubeIfNeeded() {
            try {
                if (this.youtubePlayer && typeof this.youtubePlayer.playVideo === 'function') {
                    this.youtubePlayer.playVideo();
                }
            } catch {
                // ignora errori del player
            }
        },

        playYoutubeAfterMic() {
            // Prova a far partire il video non appena il player è pronto.
            const tryPlay = () => {
                try {
                    if (this.youtubePlayer && typeof this.youtubePlayer.playVideo === 'function') {
                        this.youtubePlayer.playVideo();
                    }
                } catch {
                    // se il browser blocca l'autoplay, l'utente può premere play manualmente
                }
            };

            if (this.isYoutubePlayerReady) {
                tryPlay();
                return;
            }

            // Polling leggero per qualche secondo finché il player non diventa pronto
            const start = Date.now();
            const maxMs = 5000;
            const interval = setInterval(() => {
                if (this.isYoutubePlayerReady || Date.now() - start > maxMs) {
                    clearInterval(interval);
                    if (this.isYoutubePlayerReady) {
                        tryPlay();
                    }
                }
            }, 200);
        },

        maybeStartPreviewTranslation(interimText) {
            const text = (interimText || '').trim();
            if (!text || text.length < 4) {
                return;
            }

            const now = Date.now();
            if (text === this.lastPreviewText && now - this.lastPreviewAt < 800) {
                return;
            }

            this.lastPreviewText = text;
            this.lastPreviewAt = now;

            this.startTranslationStream(text, { commit: false });
        },

        updateTranslationTokens(fullText) {
            const clean = (fullText || '').trim();
            const container = this.$refs.translationLiveContainer;

            if (!container) {
                return;
            }

            if (!clean) {
                container.innerHTML = '';
                this.translationTokens = [];
                return;
            }

            const newTokens = clean.split(/\s+/).filter(Boolean);

            // Se l'array non esiste ancora, creiamo i nodi DOM da zero
            if (!this.translationTokens || this.translationTokens.length === 0) {
                container.innerHTML = '';
                this.translationTokens = [];

                // Aggiungi trattino all'inizio della prima frase
                const dash = document.createTextNode('- ');
                container.appendChild(dash);

                for (let i = 0; i < newTokens.length; i++) {
                    const span = document.createElement('span');
                    span.textContent = newTokens[i];
                    span.dataset.tokenIndex = i;
                    container.appendChild(span);

                    if (i < newTokens.length - 1) {
                        container.appendChild(document.createTextNode(' '));
                    }

                    this.translationTokens.push({
                        text: newTokens[i],
                        node: span
                    });
                }
                return;
            }

            const minLen = Math.min(this.translationTokens.length, newTokens.length);

            // Aggiorna SOLO il textContent dei nodi che cambiano
            for (let i = 0; i < minLen; i++) {
                if (this.translationTokens[i].text !== newTokens[i]) {
                    // Aggiorno SOLO questo nodo DOM specifico
                    this.translationTokens[i].node.textContent = newTokens[i];
                    this.translationTokens[i].text = newTokens[i];
                }
            }

            // Se ci sono token NUOVI in più, aggiungili al DOM
            if (newTokens.length > this.translationTokens.length) {
                for (let i = this.translationTokens.length; i < newTokens.length; i++) {
                    // Aggiungi uno spazio prima della nuova parola
                    container.appendChild(document.createTextNode(' '));

                    const span = document.createElement('span');
                    span.textContent = newTokens[i];
                    span.dataset.tokenIndex = i;
                    container.appendChild(span);

                    this.translationTokens.push({
                        text: newTokens[i],
                        node: span
                    });
                }
            } else if (newTokens.length < this.translationTokens.length) {
                // Se i token diminuiscono, rimuovi i nodi in eccesso dal DOM
                for (let i = this.translationTokens.length - 1; i >= newTokens.length; i--) {
                    const token = this.translationTokens[i];
                    if (token.node && token.node.parentNode) {
                        // Rimuovi anche lo spazio prima
                        if (token.node.previousSibling && token.node.previousSibling.nodeType === 3) {
                            token.node.previousSibling.remove();
                        }
                        token.node.remove();
                    }
                }
                this.translationTokens.splice(newTokens.length);
            }
        },

        onCvFileChange(event) {
            try {
                const file = event.target.files && event.target.files[0];
                if (!file) return;

                const reader = new FileReader();
                reader.onload = (e) => {
                    try {
                        const content = (e.target && e.target.result) || '';
                        if (typeof content === 'string') {
                            this.cvText = content;
                        }
                    } catch {
                        // ignore parse errors
                    }
                };
                reader.readAsText(file);
            } catch {
                // nessun alert per non disturbare l'utente
            }
        },

        onLanguagePairChange() {
            // Validazione: entrambe le lingue devono essere selezionate e diverse
            if (!this.langA || !this.langB) {
                this.statusMessage = 'Seleziona entrambe le lingue (A e B) per iniziare.';
                return;
            }

            if (this.langA === this.langB) {
                this.statusMessage = 'Le due lingue devono essere diverse!';
                this.langB = '';
                return;
            }

            // Imposta la lingua A come predefinita per il microfono
            const langAObj = this.availableLanguages.find(l => l.code === this.langA);
            if (langAObj) {
                this.currentMicLang = langAObj.micCode;
                this.currentTargetLang = this.langB;

                if (this.recognition) {
                    this.recognition.lang = this.currentMicLang;
                }
            }

            this.statusMessage = '';
        },

        onRecognitionModeChange() {
            if (!this.isChromeWithWebSpeech) {
                // In browser non supportati non permettiamo il cambio: resta Whisper
                this.useWhisper = true;
                this.autoRestart = false;
                return;
            }

            // Quando si cambia modalità, fermiamo eventuale ascolto in corso
            if (this.isListening) {
                this.stopListeningInternal();
            }
            this.recognition = null;

            if (this.useWhisper) {
                // In modalità Whisper evitiamo auto-restart lato componente
                this.autoRestart = false;
                this.statusMessage = 'Modalità Whisper attivata: userò OpenAI per il riconoscimento vocale.';
            } else {
                this.autoRestart = true;
                this.statusMessage = 'Modalità browser attivata: userò il riconoscimento vocale del browser.';
            }
        },

        // --- Modalità Traduttore Video Youtube ---
        extractYoutubeVideoId(url) {
            try {
                const trimmed = (url || '').trim();
                if (!trimmed) return '';

                // Formati supportati: https://www.youtube.com/watch?v=ID, youtu.be/ID, short URLs con parametri
                const patterns = [
                    /(?:youtube\.com\/.*v=)([^&#?/]+)/i,
                    /youtu\.be\/([^&#?/]+)/i,
                ];

                for (const re of patterns) {
                    const match = trimmed.match(re);
                    if (match && match[1]) {
                        return match[1];
                    }
                }
                return '';
            } catch {
                return '';
            }
        },

        async onYoutubeTranslateClick() {
            const id = this.extractYoutubeVideoId(this.youtubeUrl);
            if (!id) {
                this.statusMessage = 'URL YouTube non valido. Usa un link completo al video.';
                return;
            }

            if (!this.youtubeLangSource || !this.youtubeLangTarget) {
                this.statusMessage = 'Seleziona sia la lingua del video che la lingua di traduzione.';
                return;
            }

            if (this.youtubeLangSource === this.youtubeLangTarget) {
                this.statusMessage = 'Le due lingue devono essere diverse per la modalità interprete.';
                return;
            }

            this.youtubeVideoId = id;
            this.statusMessage = '';

            // Sincronizza le lingue con la modalità interprete standard
            this.langA = this.youtubeLangSource;
            this.langB = this.youtubeLangTarget;
            this.onLanguagePairChange();

            // In modalità YouTube vogliamo il doppiaggio automatico
            this.readTranslationEnabled = true;

            await this.initYoutubePlayer();

            // Avvia automaticamente il microfono in lingua A (interprete umano sopra al video)
            try {
                await this.toggleListeningForLang('A');
            } catch {
                // Se fallisce (permessi microfono, ecc.), l'utente può usare il pulsante manuale
            }
        },

        async initYoutubePlayer() {
            if (!this.youtubeVideoId) {
                return;
            }

            // Se il player esiste già, aggiorna solo il video
            if (this.youtubePlayer && this.isYoutubePlayerReady) {
                try {
                    this.youtubePlayer.loadVideoById(this.youtubeVideoId);
                } catch {
                    // fallback: ricrea il player
                    this.youtubePlayer = null;
                    this.isYoutubePlayerReady = false;
                }
            }

            if (this.youtubePlayer) {
                return;
            }

            const createPlayer = () => {
                try {
                    this.youtubePlayer = new window.YT.Player(this.$refs.youtubePlayer, {
                        videoId: this.youtubeVideoId,
                        playerVars: {
                            rel: 0,
                            modestbranding: 1,
                        },
                        events: {
                            onReady: () => {
                                this.isYoutubePlayerReady = true;
                            },
                        },
                    });
                } catch {
                    // ignora errori di inizializzazione
                }
            };

            if (window.YT && window.YT.Player) {
                createPlayer();
                return;
            }

            // Carica l'API iframe di YouTube se non è presente
            return new Promise((resolve) => {
                const existing = document.getElementById('youtube-iframe-api');
                if (!existing) {
                    const tag = document.createElement('script');
                    tag.id = 'youtube-iframe-api';
                    tag.src = 'https://www.youtube.com/iframe_api';
                    document.body.appendChild(tag);
                }

                const previous = window.onYouTubeIframeAPIReady;
                window.onYouTubeIframeAPIReady = () => {
                    if (typeof previous === 'function') {
                        try {
                            previous();
                        } catch {
                            // ignore
                        }
                    }
                    createPlayer();
                    resolve();
                };
            });
        },

        detectSpokenLanguage(text) {
            // Rileva la lingua del testo parlato confrontandola con langA e langB
            // Usa euristiche semplici basate su parole comuni
            if (!text || !this.langA || !this.langB) return null;

            const textLower = text.toLowerCase();

            // Pattern per rilevare la lingua (parole molto comuni)
            const patterns = {
                it: /\b(il|la|di|da|in|con|per|un|una|che|sono|è|hai|ho|cosa|come|quando|dove|perché|questo|questa|mi|ti|ci|vi|lo|gli|le|del|della|dei|delle)\b/gi,
                en: /\b(the|a|an|is|are|was|were|have|has|had|do|does|did|will|would|can|could|should|what|when|where|why|how|this|that|you|your|me|my|we|our|they|their)\b/gi,
                es: /\b(el|la|los|las|de|del|en|con|por|para|un|una|que|es|son|hay|tiene|como|cuando|donde|por qué|este|esta|mi|tu|su|nuestro|vuestro)\b/gi,
                fr: /\b(le|la|les|de|du|des|un|une|et|est|sont|a|ont|dans|pour|avec|que|qui|quoi|quand|où|pourquoi|comment|ce|cette|mon|ton|son|notre|votre)\b/gi,
                de: /\b(der|die|das|den|dem|ein|eine|ist|sind|hat|haben|und|oder|in|mit|von|zu|auf|für|was|wann|wo|warum|wie|dieser|diese|mein|dein|sein|unser|ihr)\b/gi,
                pt: /\b(o|a|os|as|de|da|do|em|com|por|para|um|uma|que|é|são|tem|como|quando|onde|por que|este|esta|meu|teu|seu|nosso|vosso)\b/gi,
            };

            const matchCounts = {};

            // Conta i match per langA e langB
            if (patterns[this.langA]) {
                const matches = textLower.match(patterns[this.langA]);
                matchCounts[this.langA] = matches ? matches.length : 0;
            }

            if (patterns[this.langB]) {
                const matches = textLower.match(patterns[this.langB]);
                matchCounts[this.langB] = matches ? matches.length : 0;
            }

            console.log(`🔍 Rilevamento lingua: "${text.substring(0, 50)}..." → ${this.langA}: ${matchCounts[this.langA] || 0}, ${this.langB}: ${matchCounts[this.langB] || 0}`);

            // Determina quale lingua ha più match (soglia minima: 1 match)
            if (matchCounts[this.langA] > matchCounts[this.langB] && matchCounts[this.langA] >= 1) {
                return this.langA;
            } else if (matchCounts[this.langB] > matchCounts[this.langA] && matchCounts[this.langB] >= 1) {
                return this.langB;
            }

            // Fallback: usa la lingua attualmente impostata per il microfono
            return null;
        },

        switchLanguagePair(detectedLang) {
            // Cambia la lingua del microfono e quella target in base alla lingua rilevata
            if (!detectedLang || !this.langA || !this.langB) return;

            const langObj = this.availableLanguages.find(l => l.code === detectedLang);
            if (!langObj) return;

            // Imposta microfono sulla lingua rilevata
            this.currentMicLang = langObj.micCode;

            // Imposta target sull'altra lingua della coppia
            // Se rilevato langA, traduci verso langB; se rilevato langB, traduci verso langA
            this.currentTargetLang = detectedLang === this.langA ? this.langB : this.langA;

            if (this.recognition) {
                this.recognition.lang = this.currentMicLang;
            }

            console.log(`🔄 Lingua rilevata: ${detectedLang.toUpperCase()} → Microfono: ${this.currentMicLang} → Traduzione verso: ${this.currentTargetLang.toUpperCase()}`);
        },

        async maybeRequestInterviewSuggestion(textSegment) {
            const safeText = (textSegment || '').trim();
            if (!safeText) {
                return;
            }

            // Validazioni
            if (!this.cvText || this.cvText.trim() === '') {
                return;
            }

            if (!this.langA || !this.langB) {
                return;
            }

            this.isLoadingSuggestion = true;

            try {
                const res = await fetch('/api/chatbot/interview-suggestion', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify({
                        cv_text: this.cvText,
                        utterance: safeText,
                        locale: this.locale || 'it',
                        lang_a: this.langA,
                        lang_b: this.langB,
                    }),
                });

                const json = await res.json().catch(() => ({}));

                if (!res.ok || json.error) {
                    // Non mostriamo alert per non disturbare durante il colloquio
                    return;
                }

                const langAText = (json.suggestion_lang_a || '').trim();
                const langBText = (json.suggestion_lang_b || '').trim();

                if (!langAText && !langBText) {
                    return;
                }

                const preview = safeText.length > 120 ? `${safeText.slice(0, 117)}...` : safeText;

                this.suggestions = [
                    {
                        utterancePreview: preview,
                        langA: this.langA,
                        langB: this.langB,
                        suggestionLangA: langAText || langBText,
                        suggestionLangB: langBText || langAText,
                    },
                    // manteniamo anche suggerimenti precedenti, ma più in basso
                    ...this.suggestions,
                ].slice(0, 8);
            } catch {
                // Silenzioso: non blocca l'esperienza
            } finally {
                this.isLoadingSuggestion = false;
            }
        },
    },
};
</script>

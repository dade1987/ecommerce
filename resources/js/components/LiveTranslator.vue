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

                <!-- Pannello debug: pulsante + finestra log copiabile -->
                <div class="flex justify-end">
                    <button type="button" @click="showDebugPanel = !showDebugPanel"
                        class="px-2 py-1 rounded-md text-[10px] font-mono border border-slate-600 text-slate-300 bg-slate-900/70 hover:bg-slate-800">
                        {{ showDebugPanel ? ui.debugCloseLabel : ui.debugOpenLabel }}
                    </button>
                </div>
                <div v-if="showDebugPanel" class="border border-slate-700 rounded-lg bg-slate-900/80 p-2 space-y-1">
                    <div class="flex justify-between items-center">
                        <span class="text-[10px] text-slate-400">{{ ui.debugTitle }}</span>
                        <button type="button" @click="copyDebugLogs"
                            class="px-2 py-0.5 rounded-md text-[10px] font-mono border border-slate-600 text-slate-200 bg-slate-800 hover:bg-slate-700">
                            {{ ui.debugCopyLabel }}
                        </button>
                    </div>
                    <textarea readonly
                        class="w-full h-40 text-[10px] md:text-xs font-mono bg-transparent text-slate-200 resize-none outline-none"
                        :value="debugLogs.join('\n')"></textarea>
                    <p v-if="debugCopyStatus" class="text-[10px] text-emerald-300">
                        {{ debugCopyStatus }}
                    </p>
                </div>

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
                            <option value="">{{ ui.selectLangAPlaceholder }}</option>
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
                            <span>
                                {{ activeSpeaker === 'A' && isListening ? ui.speakerAActive : ui.speakerASpeak }}
                            </span>
                        </button>
                    </div>

                    <div class="flex flex-col gap-2">
                        <label class="text-xs font-semibold text-emerald-400">
                            {{ ui.langBLabel }} <span class="text-red-400">*</span>
                        </label>
                        <select v-model="langB" @change="onLanguagePairChange"
                            class="bg-slate-800 border text-sm rounded-md px-3 py-2 focus:outline-none focus:ring-2"
                            :class="langB ? 'border-slate-600 focus:ring-emerald-500' : 'border-red-500 focus:ring-red-500'">
                            <option value="">{{ ui.selectLangBPlaceholder }}</option>
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
                            <span>
                                {{ activeSpeaker === 'B' && isListening ? ui.speakerBActive : ui.speakerBSpeak }}
                            </span>
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
                                <span v-if="isTtsLoading" class="text-[11px] md:text-xs text-emerald-300 italic ml-2">
                                    {{ ui.ttsLoadingMessage }}
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
                                <div v-if="cvText && langA && langB" class="flex items-center gap-2">
                                    <button type="button"
                                        class="inline-flex items-center gap-1 px-2 py-1 rounded-md text-[11px] md:text-xs font-semibold border border-emerald-500 text-emerald-100 bg-emerald-800 hover:bg-emerald-700 transition disabled:opacity-50 disabled:cursor-not-allowed"
                                        :disabled="isLoadingSuggestion" @click="onRequestSuggestionsClick">
                                        <span>
                                            {{ isLoadingSuggestion ? '...' : ui.suggestionsButton }}
                                        </span>
                                    </button>
                                    <button type="button"
                                        class="inline-flex items-center gap-1 px-2 py-1 rounded-md text-[11px] md:text-xs font-semibold border border-sky-500 text-sky-100 bg-sky-800 hover:bg-sky-700 transition disabled:opacity-50 disabled:cursor-not-allowed"
                                        :disabled="isMindMapLoading" @click="onToggleMindMapClick">
                                        <span v-if="!mindMap.raw">
                                            {{ isMindMapLoading ? '...' : ui.mindMapButton }}
                                        </span>
                                        <span v-else>
                                            {{ isMindMapLoading ? '...' : ui.mindMapHideButton }}
                                        </span>
                                    </button>
                                </div>
                            </div>

                            <div ref="suggestionsBox"
                                class="h-[100px] md:min-h-[260px] md:max-h-[420px] rounded-xl border border-slate-700 bg-slate-900/70 p-4 text-xs md:text-sm lg:text-base overflow-y-auto space-y-3 leading-relaxed">
                                <div v-if="!cvText" class="text-xs md:text-sm text-slate-500">
                                    {{ ui.suggestionsNoCv }}
                                </div>

                                <div v-else-if="!langA || !langB" class="text-xs md:text-sm text-slate-500">
                                    {{ ui.suggestionsNoLangs }}
                                </div>

                                <div v-else>
                                    <p v-if="isLoadingSuggestion" class="text-xs md:text-sm text-emerald-300 mb-2">
                                        {{ ui.suggestionsLoading }}
                                    </p>

                                    <div v-if="suggestions.length === 0 && !isLoadingSuggestion"
                                        class="text-xs md:text-sm text-slate-500">
                                        {{ ui.suggestionsEmpty }}
                                    </div>

                                    <div v-for="(item, idx) in suggestions" :key="idx"
                                        class="rounded-lg border border-slate-700 bg-slate-900/80 p-3 md:p-4 space-y-2 mb-2">
                                        <div class="text-[11px] md:text-xs text-slate-400">
                                            {{ ui.suggestionRefersTo }}
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
                                {{ ui.cvSectionTitle }}
                            </h2>
                            <p class="text-[11px] text-slate-300 mt-1">
                                {{ ui.cvSectionDescription }}
                            </p>
                        </div>
                        <div
                            class="rounded-xl border border-slate-700 bg-slate-900/80 p-3 text-xs space-y-2 max-h-[260px] overflow-y-auto">
                            <label class="block text-[11px] font-medium text-slate-200 mb-1">
                                {{ ui.cvUploadLabel }}
                            </label>
                            <input type="file" accept=".txt,.md,.rtf"
                                class="block w-full text-[11px] text-slate-200 file:text-[11px] file:px-2 file:py-1 file:mr-2 file:rounded-md file:border-0 file:bg-emerald-600 file:text-white file:cursor-pointer cursor-pointer"
                                @change="onCvFileChange" />
                            <p class="text-[10px] text-slate-500 mt-2">
                                {{ ui.cvUploadHint }}
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

                <!-- Pannello debug: pulsante + finestra log copiabile (anche in modalità YouTube) -->
                <div class="flex justify-end">
                    <button type="button" @click="showDebugPanel = !showDebugPanel"
                        class="px-2 py-1 rounded-md text-[10px] font-mono border border-slate-600 text-slate-300 bg-slate-900/70 hover:bg-slate-800">
                        {{ showDebugPanel ? ui.debugCloseLabel : ui.debugOpenLabel }}
                    </button>
                </div>
                <div v-if="showDebugPanel" class="border border-slate-700 rounded-lg bg-slate-900/80 p-2 space-y-1">
                    <div class="flex justify-between items-center">
                        <span class="text-[10px] text-slate-400">{{ ui.debugTitle }}</span>
                        <button type="button" @click="copyDebugLogs"
                            class="px-2 py-0.5 rounded-md text-[10px] font-mono border border-slate-600 text-slate-200 bg-slate-800 hover:bg-slate-700">
                            {{ ui.debugCopyLabel }}
                        </button>
                    </div>
                    <textarea readonly
                        class="w-full h-40 text-[10px] md:text-xs font-mono bg-transparent text-slate-200 resize-none outline-none"
                        :value="debugLogs.join('\n')"></textarea>
                    <p v-if="debugCopyStatus" class="text-[10px] text-emerald-300">
                        {{ debugCopyStatus }}
                    </p>
                </div>

                <!-- Controllo modalità riconoscimento (Whisper / browser) anche per YouTube -->
                <div class="flex flex-col items-center gap-1 text-slate-300">
                    <div class="flex items-center justify-center gap-2 text-[13px]">
                        <input id="useWhisperYoutube" type="checkbox" v-model="useWhisper"
                            @change="onRecognitionModeChange" :disabled="!isChromeWithWebSpeech"
                            class="h-3.5 w-3.5 rounded border-slate-500 bg-slate-800 text-emerald-500 focus:ring-emerald-500 disabled:opacity-60 disabled:cursor-not-allowed" />
                        <label for="useWhisperYoutube" class="cursor-pointer select-none">
                            {{ ui.whisperLabel }}
                            <span v-if="!isChromeWithWebSpeech" class="ml-1 text-[11px] text-emerald-300">
                                ({{ ui.whisperForcedNote }})
                            </span>
                        </label>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                    <!-- Colonna impostazioni video -->
                    <div class="lg:col-span-1 space-y-3">
                        <div class="space-y-2">
                            <label class="text-xs font-semibold text-emerald-400">
                                {{ ui.youtubeUrlLabel }}
                            </label>
                            <input v-model="youtubeUrl" type="text" placeholder="https://www.youtube.com/watch?v=..."
                                class="w-full bg-slate-900 border border-slate-700 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500" />
                            <p class="text-[11px] text-slate-400">
                                {{ ui.youtubeUrlHelp }}
                            </p>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            <div class="flex flex-col gap-1">
                                <label class="text-xs font-semibold text-emerald-400">
                                    {{ ui.youtubeLangSourceLabel }}
                                </label>
                                <select v-model="youtubeLangSource"
                                    class="bg-slate-900 border text-sm rounded-md px-3 py-2 focus:outline-none focus:ring-2"
                                    :class="youtubeLangSource ? 'border-slate-600 focus:ring-emerald-500' : 'border-red-500 focus:ring-red-500'">
                                    <option value="">{{ ui.selectOptionPlaceholder }}</option>
                                    <option v-for="opt in availableLanguages" :key="'yt-src-' + opt.code"
                                        :value="opt.code">
                                        {{ opt.label }}
                                    </option>
                                </select>
                            </div>
                            <div class="flex flex-col gap-1">
                                <label class="text-xs font-semibold text-emerald-400">
                                    {{ ui.youtubeLangTargetLabel }}
                                </label>
                                <select v-model="youtubeLangTarget"
                                    class="bg-slate-900 border text-sm rounded-md px-3 py-2 focus:outline-none focus:ring-2"
                                    :class="youtubeLangTarget ? 'border-slate-600 focus:ring-emerald-500' : 'border-red-500 focus:ring-red-500'">
                                    <option value="">{{ ui.selectOptionPlaceholder }}</option>
                                    <option v-for="opt in availableLanguages" :key="'yt-tgt-' + opt.code"
                                        :value="opt.code">
                                        {{ opt.label }}
                                    </option>
                                </select>
                            </div>
                        </div>

                        <button type="button" @click="onYoutubeTranslateClick"
                            class="mt-2 inline-flex items-center justify-center gap-2 px-4 py-2 rounded-lg text-xs font-semibold border border-emerald-500 text-emerald-100 bg-emerald-700 hover:bg-emerald-600 transition">
                            {{ ui.youtubeStartButton }}
                        </button>

                        <p class="text-[11px] text-slate-400 mt-2">
                            {{ ui.youtubeExplain }}
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
                                    {{ activeSpeaker === 'A' && isListening ? ui.youtubeMicAActive : ui.youtubeMicAHelp
                                    }}
                                </span>
                            </button>
                            <p class="text-[11px] text-slate-400">
                                Questo pulsante accende e spegne il microfono. Quando è attivo, ascolto l'audio che
                                entra dal microfono (per esempio il video dalle casse) in
                                <span class="font-semibold">{{ getLangLabel(langA) }}</span> e traduco in
                                <span class="font-semibold">{{ getLangLabel(langB) }}</span>. Per un risultato
                                ottimale usa le <span class="font-semibold">casse</span> e non solo le cuffie chiuse.
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
                                    <span v-if="isTtsLoading"
                                        class="text-[10px] md:text-[11px] text-emerald-300 italic ml-2">
                                        {{ ui.ttsLoadingMessage }}
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

    <!-- Modal mappa mentale (grafi) -->
    <div v-if="showMindMapModal"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 backdrop-blur-sm">
        <div
            class="bg-slate-900 rounded-2xl border border-slate-700 w-[92vw] h-[82vh] max-w-5xl flex flex-col shadow-2xl">
            <div class="flex items-center justify-between px-4 py-2 border-b border-slate-700">
                <div class="flex flex-col">
                    <span class="text-sm md:text-base font-semibold text-slate-100">
                        {{ ui.mindMapTitle }}
                    </span>
                    <span class="text-[11px] text-slate-400">
                        {{ langA && getLangLabel(langA) ? getLangLabel(langA) : '' }}
                    </span>
                </div>
                <div class="flex items-center gap-2">
                    <button type="button"
                        class="inline-flex items-center gap-1 px-2 py-1 rounded-md text-[11px] md:text-xs font-semibold border border-emerald-500 text-emerald-100 bg-emerald-900 hover:bg-emerald-800 transition"
                        @click="exportMindMapAsPrint">
                        <span>PDF</span>
                    </button>
                    <button type="button"
                        class="inline-flex items-center justify-center w-7 h-7 rounded-full border border-slate-600 text-slate-200 hover:bg-slate-700 transition"
                        @click="closeMindMapModal">
                        ✕
                    </button>
                </div>
            </div>
            <div class="flex-1">
                <div ref="mindMapGraphContainer" class="w-full h-full"></div>
            </div>
        </div>
    </div>
</template>

<script>
import WhisperSpeechRecognition from '../utils/WhisperSpeechRecognition';
import { Network } from 'vis-network/standalone';
import 'vis-network/styles/vis-network.css';

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
            // Thread e stato per mappa mentale basata sul CV e sulla history dei suggerimenti
            interviewSuggestionThreadId: null, // Thread ID per mantenere il contesto dei suggerimenti
            isMindMapLoading: false,
            mindMap: {
                langA: '',
                langB: '',
                raw: '',
            },
            mindMapGraph: {
                nodes: [],
                edges: [],
            },
            showMindMapModal: false,
            mindMapNetwork: null,
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
            mobileCurrentTranslationIndex: null,
            isTtsLoading: false,

            // Debug interno: pannello e log testuali copiabili
            showDebugPanel: false,
            debugLogs: [],
            debugCopyStatus: '',
            webSpeechDebugSeq: 0,
            lastWebSpeechEventAt: 0,

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
                { code: 'pt', label: '🇵🇹 Português', micCode: 'pt-BR' },
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
                    suggestionsButton: 'Genera suggerimenti',
                    suggestionsNoCv: 'Carica il tuo CV qui sotto per abilitare i suggerimenti basati sul curriculum.',
                    suggestionsNoLangs: 'Seleziona entrambe le lingue per visualizzare i suggerimenti bilingue.',
                    suggestionsLoading: 'Sto preparando un suggerimento basato sul tuo CV...',
                    suggestionsEmpty: 'Quando il sistema riconosce una frase (domanda o tua risposta), qui comparirà un suggerimento nelle due lingue selezionate coerente con il tuo CV.',
                    suggestionRefersTo: 'Riferito alla frase:',
                    mindMapTitle: 'Mappa mentale dei temi tecnici',
                    mindMapButton: 'Mostra mappa mentale',
                    mindMapHideButton: 'Nascondi mappa mentale',
                    mindMapEmpty: 'La mappa mentale sarà disponibile dopo qualche scambio di suggerimenti.',
                    cvSectionTitle: 'CV per i suggerimenti',
                    cvSectionDescription: 'Carica un file di testo con il tuo CV. Verrà usato solo per generare suggerimenti, non per le traduzioni.',
                    cvUploadLabel: 'Carica CV da file (.txt)',
                    cvUploadHint: 'Suggerimento: salva il tuo CV in formato testo (.txt) e caricalo da qui.',
                    youtubeUrlLabel: 'URL video YouTube',
                    youtubeUrlHelp: 'Incolla qui il link del video che vuoi usare durante la call di lavoro.',
                    youtubeLangSourceLabel: 'Lingua del video',
                    youtubeLangTargetLabel: 'Lingua di traduzione',
                    youtubeStartButton: 'Avvia modalità interprete sul video',
                    youtubeExplain: 'Cliccando su "Avvia modalità interprete sul video" carichiamo il video qui a fianco con la lingua scelta sopra. Il sistema ascolta ciò che entra nel microfono (ad esempio l\'audio delle casse del computer): quando riconosce la fine di una frase mette in pausa il video, traduce la frase (e, se attivo, la legge ad alta voce) e infine fa ripartire automaticamente il video per la frase successiva. In qualsiasi momento puoi anche mettere tu in pausa il video per tradurre o rileggere il testo con più calma.',
                    youtubeMicAActive: 'Interprete attivo: sto ascoltando la tua voce sopra il video',
                    youtubeMicAHelp: 'Avvia / ferma il microfono per parlare sopra il video (interprete)',
                    speakerAActive: 'Parlante A attivo',
                    speakerASpeak: 'Parla Lingua A',
                    speakerBActive: 'Parlante B attivo',
                    speakerBSpeak: 'Parla Lingua B',
                    selectLangAPlaceholder: '-- Seleziona lingua A --',
                    selectLangBPlaceholder: '-- Seleziona lingua B --',
                    selectOptionPlaceholder: '-- Seleziona --',
                    ttsBusyMessage: 'Sto leggendo la traduzione, attendi che finisca prima di parlare.',
                    ttsLoadingMessage: 'Caricamento traduzione in corso...',
                    statusWhisperAutoForced: 'Modalità Whisper attiva automaticamente: il riconoscimento vocale del browser non è pienamente supportato qui.',
                    statusMicInitError: 'Errore inizializzazione microfono.',
                    statusSelectLangAB: '⚠️ Seleziona entrambe le lingue (A e B) prima di iniziare!',
                    statusMicDenied: 'Permesso microfono negato. Abilitalo nelle impostazioni del browser.',
                    statusMicStartError: 'Impossibile avviare il microfono.',
                    statusLangPairMissing: 'Seleziona entrambe le lingue (A e B) per iniziare.',
                    statusLangPairDifferent: 'Le due lingue devono essere diverse!',
                    statusWhisperModeOn: 'Modalità Whisper attivata: userò OpenAI per il riconoscimento vocale.',
                    statusBrowserModeOn: 'Modalità browser attivata: userò il riconoscimento vocale del browser.',
                    statusYoutubeUrlInvalid: 'URL YouTube non valido. Usa un link completo al video.',
                    statusYoutubeLangsMissing: 'Seleziona sia la lingua del video che la lingua di traduzione.',
                    statusYoutubeLangsDifferent: 'Le due lingue devono essere diverse per la modalità interprete.',
                    debugOpenLabel: 'apri debug',
                    debugCloseLabel: 'chiudi debug',
                    debugTitle: 'debug log (mobile + desktop)',
                    debugCopyLabel: 'copia log',
                    debugNoLogsMessage: 'nessun log da copiare',
                    debugCopiedMessage: 'log copiati negli appunti',
                    debugClipboardUnavailableMessage: 'clipboard non disponibile, seleziona il testo manualmente',
                    debugCopyErrorMessage: 'errore copia, seleziona il testo manualmente',
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
                    suggestionsButton: 'Generate suggestions',
                    suggestionsNoCv: 'Upload your CV below to enable CV-based suggestions.',
                    suggestionsNoLangs: 'Select both languages to see bilingual suggestions.',
                    suggestionsLoading: 'Preparing a suggestion based on your CV...',
                    suggestionsEmpty: 'When the system recognises a sentence (question or your answer), a suggestion consistent with your CV will appear here in both selected languages.',
                    suggestionRefersTo: 'Refers to sentence:',
                    mindMapTitle: 'Technical topics mind map',
                    mindMapButton: 'Show mind map',
                    mindMapHideButton: 'Hide mind map',
                    mindMapEmpty: 'Mind map will be available after a few suggestion exchanges.',
                    cvSectionTitle: 'CV for suggestions',
                    cvSectionDescription: 'Upload a text file with your CV. It will be used only to generate suggestions, not for translations.',
                    cvUploadLabel: 'Upload CV from file (.txt)',
                    cvUploadHint: 'Tip: save your CV as a text file (.txt) and upload it here.',
                    youtubeUrlLabel: 'YouTube video URL',
                    youtubeUrlHelp: 'Paste here the link of the video you want to use during the work call.',
                    youtubeLangSourceLabel: 'Video language',
                    youtubeLangTargetLabel: 'Translation language',
                    youtubeStartButton: 'Start interpreter mode on video',
                    youtubeExplain: 'By clicking "Start interpreter mode on video" we load the video on the side with the language selected above. The system listens to what goes into the microphone (for example the audio from your speakers): when it detects the end of a sentence it pauses the video, translates the sentence (and, if enabled, reads it aloud), and then automatically resumes the video for the next sentence. At any moment you can also pause the video yourself to translate or re-read the text more calmly.',
                    youtubeMicAActive: 'Interpreter active: I am listening to your voice over the video',
                    youtubeMicAHelp: 'Start / stop the microphone to speak over the video (interpreter)',
                    speakerAActive: 'Speaker A active',
                    speakerASpeak: 'Speak Language A',
                    speakerBActive: 'Speaker B active',
                    speakerBSpeak: 'Speak Language B',
                    selectLangAPlaceholder: '-- Select language A --',
                    selectLangBPlaceholder: '-- Select language B --',
                    selectOptionPlaceholder: '-- Select --',
                    ttsBusyMessage: 'I am reading the translation, please wait until it finishes before speaking.',
                    ttsLoadingMessage: 'Loading translation...',
                    statusWhisperAutoForced: 'Whisper mode is enabled automatically: browser speech recognition is not fully supported here.',
                    statusMicInitError: 'Microphone initialization error.',
                    statusSelectLangAB: '⚠️ Select both languages (A and B) before starting!',
                    statusMicDenied: 'Microphone permission denied. Enable it in your browser settings.',
                    statusMicStartError: 'Unable to start the microphone.',
                    statusLangPairMissing: 'Select both languages (A and B) to get started.',
                    statusLangPairDifferent: 'The two languages must be different!',
                    statusWhisperModeOn: 'Whisper mode enabled: I will use OpenAI for speech recognition.',
                    statusBrowserModeOn: 'Browser mode enabled: I will use the browser’s built-in speech recognition.',
                    statusYoutubeUrlInvalid: 'Invalid YouTube URL. Please use a full video link.',
                    statusYoutubeLangsMissing: 'Select both the video language and the translation language.',
                    statusYoutubeLangsDifferent: 'The two languages must be different for interpreter mode.',
                    debugOpenLabel: 'open debug',
                    debugCloseLabel: 'close debug',
                    debugTitle: 'debug log (mobile + desktop)',
                    debugCopyLabel: 'copy log',
                    debugNoLogsMessage: 'no logs to copy',
                    debugCopiedMessage: 'logs copied to clipboard',
                    debugClipboardUnavailableMessage: 'clipboard unavailable, select the text manually',
                    debugCopyErrorMessage: 'copy error, select the text manually',
                },
                es: {
                    title: 'PolyGlide - Traductor instantáneo',
                    subtitle: 'Habla en cualquier idioma: verás el texto original y la traducción en directo.',
                    langALabel: 'Idioma A',
                    langBLabel: 'Idioma B',
                    whisperLabel: 'Usar Whisper (OpenAI) en lugar del reconocimiento de voz del navegador',
                    whisperForcedNote: 'forzado: no estás en Chrome',
                    dubbingLabel: 'Leer la traducción en voz alta (doblaje)',
                    originalTitle: 'Texto original',
                    originalSubtitle: 'Reconocido por el micrófono',
                    originalPlaceholder: 'Empieza a hablar para ver aquí la transcripción en tiempo real.',
                    translationTitle: 'Traducción',
                    suggestionsTitle: 'Sugerencias para la entrevista',
                    suggestionsButton: 'Generar sugerencias',
                    suggestionsNoCv: 'Carga tu CV aquí abajo para habilitar sugerencias basadas en el currículum.',
                    suggestionsNoLangs: 'Selecciona ambos idiomas para ver sugerencias bilingües.',
                    suggestionsLoading: 'Estoy preparando una sugerencia basada en tu CV...',
                    suggestionsEmpty: 'Cuando el sistema reconozca una frase (pregunta o respuesta), aquí aparecerá una sugerencia coherente con tu CV en los dos idiomas seleccionados.',
                    suggestionRefersTo: 'Relacionado con la frase:',
                    mindMapTitle: 'Mapa mental de temas técnicos',
                    mindMapButton: 'Mostrar mapa mental',
                    mindMapHideButton: 'Ocultar mapa mental',
                    mindMapEmpty: 'El mapa mental estará disponible después de algunos intercambios de sugerencias.',
                    ttsBusyMessage: 'Estoy leyendo la traducción, espera a que termine antes de volver a hablar.',
                    ttsLoadingMessage: 'Cargando traducción...',
                },
                fr: {
                    title: 'PolyGlide - Traducteur instantané',
                    subtitle: 'Parle dans n’importe quelle langue : tu verras le texte original et la traduction en direct.',
                    langALabel: 'Langue A',
                    langBLabel: 'Langue B',
                    whisperLabel: 'Utiliser Whisper (OpenAI) au lieu de la reconnaissance vocale du navigateur',
                    whisperForcedNote: 'forcé : tu n’es pas sur Chrome',
                    dubbingLabel: 'Lire la traduction à voix haute (doublage)',
                    originalTitle: 'Texte original',
                    originalSubtitle: 'Reconnu par le microphone',
                    originalPlaceholder: 'Commence à parler pour voir ici la transcription en temps réel.',
                    translationTitle: 'Traduction',
                    suggestionsTitle: 'Suggestions pour l’entretien',
                    suggestionsButton: 'Générer des suggestions',
                    suggestionsNoCv: 'Charge ton CV ci-dessous pour activer les suggestions basées sur le CV.',
                    suggestionsNoLangs: 'Sélectionne les deux langues pour afficher les suggestions bilingues.',
                    suggestionsLoading: 'Je prépare une suggestion basée sur ton CV...',
                    suggestionsEmpty: 'Lorsque le système reconnaît une phrase (question ou réponse), une suggestion cohérente avec ton CV apparaîtra ici dans les deux langues sélectionnées.',
                    suggestionRefersTo: 'Référence à la phrase :',
                    mindMapTitle: 'Carte mentale des sujets techniques',
                    mindMapButton: 'Afficher la carte mentale',
                    mindMapHideButton: 'Masquer la carte mentale',
                    mindMapEmpty: 'La carte mentale sera disponible après quelques échanges de suggestions.',
                    ttsBusyMessage: 'Je lis la traduction, attends qu’elle soit terminée avant de reparler.',
                    ttsLoadingMessage: 'Chargement de la traduction...',
                },
                de: {
                    title: 'PolyGlide - Sofortübersetzer',
                    subtitle: 'Sprich in jeder Sprache: Du siehst den Originaltext und die Live-Übersetzung.',
                    langALabel: 'Sprache A',
                    langBLabel: 'Sprache B',
                    whisperLabel: 'Whisper (OpenAI) statt Spracherkennung des Browsers verwenden',
                    whisperForcedNote: 'erzwungen: du verwendest nicht Chrome',
                    dubbingLabel: 'Übersetzung vorlesen (Synchronisation)',
                    originalTitle: 'Originaltext',
                    originalSubtitle: 'Vom Mikrofon erkannt',
                    originalPlaceholder: 'Beginne zu sprechen, um hier die Live-Transkription zu sehen.',
                    translationTitle: 'Übersetzung',
                    suggestionsTitle: 'Vorschläge für das Bewerbungsgespräch',
                    suggestionsButton: 'Vorschläge erzeugen',
                    suggestionsNoCv: 'Lade deinen Lebenslauf hier unten hoch, um CV-basierte Vorschläge zu aktivieren.',
                    suggestionsNoLangs: 'Wähle beide Sprachen aus, um zweisprachige Vorschläge zu sehen.',
                    suggestionsLoading: 'Ich bereite einen Vorschlag auf Basis deines Lebenslaufs vor...',
                    suggestionsEmpty: 'Wenn das System einen Satz (Frage oder Antwort) erkennt, erscheint hier ein Vorschlag, der zu deinem Lebenslauf passt, in beiden ausgewählten Sprachen.',
                    suggestionRefersTo: 'Bezogen auf den Satz:',
                    mindMapTitle: 'Mindmap der technischen Themen',
                    mindMapButton: 'Mindmap anzeigen',
                    mindMapHideButton: 'Mindmap ausblenden',
                    mindMapEmpty: 'Die Mindmap ist nach einigen Suggestionen verfügbar.',
                    ttsBusyMessage: 'Ich lese die Übersetzung, bitte warte, bis ich fertig bin, bevor du weitersprichst.',
                    ttsLoadingMessage: 'Übersetzung wird geladen...',
                },
                pt: {
                    title: 'PolyGlide - Tradutor instantâneo',
                    subtitle: 'Fala em qualquer idioma: vais ver o texto original e a tradução em tempo real.',
                    langALabel: 'Idioma A',
                    langBLabel: 'Idioma B',
                    whisperLabel: 'Usar Whisper (OpenAI) em vez do reconhecimento de voz do navegador',
                    whisperForcedNote: 'forçado: não estás a usar o Chrome',
                    dubbingLabel: 'Ler a tradução em voz alta (dobragem)',
                    originalTitle: 'Texto original',
                    originalSubtitle: 'Reconhecido pelo microfone',
                    originalPlaceholder: 'Começa a falar para veres aqui a transcrição em tempo real.',
                    translationTitle: 'Tradução',
                    suggestionsTitle: 'Sugestões para a entrevista',
                    suggestionsButton: 'Gerar sugestões',
                    suggestionsNoCv: 'Carrega o teu CV abaixo para ativar sugestões baseadas no currículo.',
                    suggestionsNoLangs: 'Seleciona ambos os idiomas para ver sugestões bilingues.',
                    suggestionsLoading: 'Estou a preparar uma sugestão com base no teu CV...',
                    suggestionsEmpty: 'Quando o sistema reconhecer uma frase (pergunta ou resposta), aqui aparecerá uma sugestão coerente com o teu CV nos dois idiomas selecionados.',
                    suggestionRefersTo: 'Referente à frase:',
                    ttsBusyMessage: 'Estou a ler a tradução, espera que termine antes de voltares a falar.',
                    ttsLoadingMessage: 'A carregar a tradução...',
                },
                nl: {
                    title: 'PolyGlide - Directe vertaler',
                    subtitle: 'Spreek in elke taal: je ziet de originele tekst en de livevertaling.',
                    langALabel: 'Taal A',
                    langBLabel: 'Taal B',
                    whisperLabel: 'Whisper (OpenAI) gebruiken in plaats van de spraakherkenning van de browser',
                    whisperForcedNote: 'afgedwongen: je gebruikt geen Chrome',
                    dubbingLabel: 'Vertaling hardop voorlezen (nasynchronisatie)',
                    originalTitle: 'Originele tekst',
                    originalSubtitle: 'Herkenning via microfoon',
                    originalPlaceholder: 'Begin met spreken om hier de realtime transcriptie te zien.',
                    translationTitle: 'Vertaling',
                    suggestionsTitle: 'Sollicitatietips',
                    ttsBusyMessage: 'Ik lees de vertaling voor, wacht tot ik klaar ben voordat je verder praat.',
                    ttsLoadingMessage: 'Vertaling wordt geladen...',
                },
                sv: {
                    title: 'PolyGlide - Omedelbar översättare',
                    subtitle: 'Tala på vilket språk du vill: du ser originaltexten och översättningen i realtid.',
                    langALabel: 'Språk A',
                    langBLabel: 'Språk B',
                    whisperLabel: 'Använd Whisper (OpenAI) istället för webbläsarens röstigenkänning',
                    whisperForcedNote: 'tvingat: du använder inte Chrome',
                    dubbingLabel: 'Läs upp översättningen (dubbning)',
                    originalTitle: 'Originaltext',
                    originalSubtitle: 'Upptäckt av mikrofonen',
                    originalPlaceholder: 'Börja prata för att se transkriberingen i realtid här.',
                    translationTitle: 'Översättning',
                    suggestionsTitle: 'Intervjutips',
                    ttsBusyMessage: 'Jag läser upp översättningen, vänta tills jag är klar innan du pratar igen.',
                    ttsLoadingMessage: 'Laddar översättning...',
                },
                no: {
                    title: 'PolyGlide - Umiddelbar oversetter',
                    subtitle: 'Snakk på hvilket som helst språk: du ser originalteksten og oversettelsen i sanntid.',
                    langALabel: 'Språk A',
                    langBLabel: 'Språk B',
                    whisperLabel: 'Bruk Whisper (OpenAI) i stedet for nettleserens talegjenkjenning',
                    whisperForcedNote: 'tvunget: du bruker ikke Chrome',
                    dubbingLabel: 'Les opp oversettelsen (dubbing)',
                    originalTitle: 'Originaltekst',
                    originalSubtitle: 'Gjenkjent av mikrofonen',
                    originalPlaceholder: 'Begynn å snakke for å se sanntidstranskripsjon her.',
                    translationTitle: 'Oversettelse',
                    suggestionsTitle: 'Intervjutips',
                    ttsBusyMessage: 'Jeg leser opp oversettelsen, vent til jeg er ferdig før du snakker igjen.',
                    ttsLoadingMessage: 'Laster inn oversettelse...',
                },
                da: {
                    title: 'PolyGlide - Instant oversætter',
                    subtitle: 'Tal på hvilket som helst sprog: du ser originalteksten og live-oversættelsen.',
                    langALabel: 'Sprog A',
                    langBLabel: 'Sprog B',
                    whisperLabel: 'Brug Whisper (OpenAI) i stedet for browserens stemmegenkendelse',
                    whisperForcedNote: 'tvunget: du bruger ikke Chrome',
                    dubbingLabel: 'Læs oversættelsen højt (dubbing)',
                    originalTitle: 'Originaltekst',
                    originalSubtitle: 'Genkendt af mikrofonen',
                    originalPlaceholder: 'Begynd at tale for at se realtids-transskriptionen her.',
                    translationTitle: 'Oversættelse',
                    suggestionsTitle: 'Jobsamtale-tips',
                    ttsBusyMessage: 'Jeg læser oversættelsen op, vent til jeg er færdig, før du taler igen.',
                    ttsLoadingMessage: 'Indlæser oversættelse...',
                },
                fi: {
                    title: 'PolyGlide - Välitön kääntäjä',
                    subtitle: 'Puhu millä tahansa kielellä: näet alkuperäisen tekstin ja reaaliaikaisen käännöksen.',
                    langALabel: 'Kieli A',
                    langBLabel: 'Kieli B',
                    whisperLabel: 'Käytä Whisperiä (OpenAI) selaimen puheentunnistuksen sijaan',
                    whisperForcedNote: 'pakotettu: et käytä Chromea',
                    dubbingLabel: 'Lue käännös ääneen (dubbaus)',
                    originalTitle: 'Alkuperäinen teksti',
                    originalSubtitle: 'Mikrofonin tunnistama',
                    originalPlaceholder: 'Ala puhua nähdäksesi reaaliaikaisen transkription täällä.',
                    translationTitle: 'Käännös',
                    suggestionsTitle: 'Haastatteluvinkkejä',
                    ttsBusyMessage: 'Luen käännöstä, odota kunnes olen valmis ennen kuin puhut uudestaan.',
                    ttsLoadingMessage: 'Ladataan käännöstä...',
                },
                pl: {
                    title: 'PolyGlide - Tłumacz natychmiastowy',
                    subtitle: 'Mów w dowolnym języku: zobaczysz tekst oryginalny i tłumaczenie na żywo.',
                    langALabel: 'Język A',
                    langBLabel: 'Język B',
                    whisperLabel: 'Użyj Whisper (OpenAI) zamiast rozpoznawania mowy przeglądarki',
                    whisperForcedNote: 'wymuszone: nie korzystasz z Chrome',
                    dubbingLabel: 'Odczytaj tłumaczenie na głos (dubbing)',
                    originalTitle: 'Tekst oryginalny',
                    originalSubtitle: 'Rozpoznany przez mikrofon',
                    originalPlaceholder: 'Zacznij mówić, aby zobaczyć tutaj transkrypcję w czasie rzeczywistym.',
                    translationTitle: 'Tłumaczenie',
                    suggestionsTitle: 'Wskazówki do rozmowy kwalifikacyjnej',
                    ttsBusyMessage: 'Czytam tłumaczenie, poczekaj, aż skończę, zanim znów zaczniesz mówić.',
                    ttsLoadingMessage: 'Ładowanie tłumaczenia...',
                },
                cs: {
                    title: 'PolyGlide - Okamžitý překladač',
                    subtitle: 'Mluv jakýmkoliv jazykem: uvidíš původní text a překlad v reálném čase.',
                    langALabel: 'Jazyk A',
                    langBLabel: 'Jazyk B',
                    whisperLabel: 'Použít Whisper (OpenAI) místo rozpoznávání řeči prohlížeče',
                    whisperForcedNote: 'vynuceno: nepoužíváš Chrome',
                    dubbingLabel: 'Přečíst překlad nahlas (dubbing)',
                    originalTitle: 'Původní text',
                    originalSubtitle: 'Rozpoznán mikrofonem',
                    originalPlaceholder: 'Začni mluvit, aby ses zde podíval na přepis v reálném čase.',
                    translationTitle: 'Překlad',
                    suggestionsTitle: 'Tipy k pohovoru',
                    ttsBusyMessage: 'Čtu překlad, počkej prosím, než skončím, než znovu promluvíš.',
                    ttsLoadingMessage: 'Načítání překladu...',
                },
                sk: {
                    title: 'PolyGlide - Okamžitý prekladač',
                    subtitle: 'Hovor v akomkoľvek jazyku: uvidíš pôvodný text a preklad v reálnom čase.',
                    langALabel: 'Jazyk A',
                    langBLabel: 'Jazyk B',
                    whisperLabel: 'Použiť Whisper (OpenAI) namiesto rozpoznávania reči v prehliadači',
                    whisperForcedNote: 'vynútené: nepoužívaš Chrome',
                    dubbingLabel: 'Prečítať preklad nahlas (dubbing)',
                    originalTitle: 'Pôvodný text',
                    originalSubtitle: 'Rozpoznaný mikrofónom',
                    originalPlaceholder: 'Začni hovoriť, aby si tu videl prepis v reálnom čase.',
                    translationTitle: 'Preklad',
                    suggestionsTitle: 'Tipy na pohovor',
                    ttsBusyMessage: 'Čítam preklad, počkaj, kým skončím, než znova prehovoríš.',
                    ttsLoadingMessage: 'Načítava sa preklad...',
                },
                hu: {
                    title: 'PolyGlide - Azonnali fordító',
                    subtitle: 'Beszélj bármilyen nyelven: látni fogod az eredeti szöveget és az élő fordítást.',
                    langALabel: 'A nyelv',
                    langBLabel: 'B nyelv',
                    whisperLabel: 'Használd a Whisper-t (OpenAI) a böngésző beszédfelismerése helyett',
                    whisperForcedNote: 'kényszerítve: nem Chrome-ot használsz',
                    dubbingLabel: 'Fordítás felolvasása (szinkron)',
                    originalTitle: 'Eredeti szöveg',
                    originalSubtitle: 'A mikrofon által felismert',
                    originalPlaceholder: 'Kezdj el beszélni, hogy lásd itt a valós idejű átiratot.',
                    translationTitle: 'Fordítás',
                    suggestionsTitle: 'Állásinterjú tippek',
                    ttsBusyMessage: 'Felolvasom a fordítást, várj, amíg befejezem, mielőtt újra beszélsz.',
                    ttsLoadingMessage: 'Fordítás betöltése...',
                },
                ro: {
                    title: 'PolyGlide - Traducător instant',
                    subtitle: 'Vorbește în orice limbă: vei vedea textul original și traducerea în timp real.',
                    langALabel: 'Limba A',
                    langBLabel: 'Limba B',
                    whisperLabel: 'Folosește Whisper (OpenAI) în locul recunoașterii vocale din browser',
                    whisperForcedNote: 'forțat: nu folosești Chrome',
                    dubbingLabel: 'Citește traducerea cu voce tare (dublaj)',
                    originalTitle: 'Text original',
                    originalSubtitle: 'Recunoscut de microfon',
                    originalPlaceholder: 'Începe să vorbești pentru a vedea aici transcrierea în timp real.',
                    translationTitle: 'Traducere',
                    suggestionsTitle: 'Sugestii pentru interviu',
                    ttsBusyMessage: 'Citesc traducerea, te rog așteaptă să termin înainte să vorbești din nou.',
                    ttsLoadingMessage: 'Se încarcă traducerea...',
                },
                bg: {
                    title: 'PolyGlide - Моментален преводач',
                    subtitle: 'Говори на всеки език: ще виждаш оригиналния текст и превода в реално време.',
                    langALabel: 'Език A',
                    langBLabel: 'Език B',
                    whisperLabel: 'Използвай Whisper (OpenAI) вместо разпознаването на реч в браузъра',
                    whisperForcedNote: 'принудително: не използваш Chrome',
                    dubbingLabel: 'Прочитане на превода на глас (дублиране)',
                    originalTitle: 'Оригинален текст',
                    originalSubtitle: 'Разпознат от микрофона',
                    originalPlaceholder: 'Започни да говориш, за да видиш тук транскрипция в реално време.',
                    translationTitle: 'Превод',
                    suggestionsTitle: 'Съвети за интервю',
                    ttsBusyMessage: 'Чета превода, изчакай да приключа, преди да говориш отново.',
                    ttsLoadingMessage: 'Зареждане на превода...',
                },
                el: {
                    title: 'PolyGlide - Άμεσος μεταφραστής',
                    subtitle: 'Μίλησε σε οποιαδήποτε γλώσσα: θα βλέπεις το αρχικό κείμενο και τη ζωντανή μετάφραση.',
                    langALabel: 'Γλώσσα A',
                    langBLabel: 'Γλώσσα B',
                    whisperLabel: 'Χρήση του Whisper (OpenAI) αντί για την αναγνώριση ομιλίας του browser',
                    whisperForcedNote: 'υποχρεωτικά: δεν χρησιμοποιείς Chrome',
                    dubbingLabel: 'Ανάγνωση της μετάφρασης (dubbing)',
                    originalTitle: 'Αρχικό κείμενο',
                    originalSubtitle: 'Αναγνωρισμένο από το μικρόφωνο',
                    originalPlaceholder: 'Ξεκίνα να μιλάς για να δεις εδώ την απομαγνητοφώνηση σε πραγματικό χρόνο.',
                    translationTitle: 'Μετάφραση',
                    suggestionsTitle: 'Συμβουλές για συνέντευξη',
                    ttsBusyMessage: 'Διαβάζω τη μετάφραση, περίμενε να τελειώσω πριν μιλήσεις ξανά.',
                    ttsLoadingMessage: 'Φόρτωση μετάφρασης...',
                },
                uk: {
                    title: 'PolyGlide - Миттєвий перекладач',
                    subtitle: 'Говори будь-якою мовою: ти бачитимеш оригінальний текст і переклад у реальному часі.',
                    langALabel: 'Мова A',
                    langBLabel: 'Мова B',
                    whisperLabel: 'Використовувати Whisper (OpenAI) замість розпізнавання мовлення браузера',
                    whisperForcedNote: 'примусово: ти не використовуєш Chrome',
                    dubbingLabel: 'Читати переклад уголос (дубляж)',
                    originalTitle: 'Оригінальний текст',
                    originalSubtitle: 'Розпізнано мікрофоном',
                    originalPlaceholder: 'Почни говорити, щоб побачити тут транскрипцію в реальному часі.',
                    translationTitle: 'Переклад',
                    suggestionsTitle: 'Поради щодо співбесіди',
                    ttsBusyMessage: 'Я читаю переклад, зачекай, доки я закінчу, перш ніж знову говорити.',
                    ttsLoadingMessage: 'Завантаження перекладу...',
                },
                ru: {
                    title: 'PolyGlide - Мгновенный переводчик',
                    subtitle: 'Говори на любом языке: ты увидишь оригинальный текст и перевод в реальном времени.',
                    langALabel: 'Язык A',
                    langBLabel: 'Язык B',
                    whisperLabel: 'Использовать Whisper (OpenAI) вместо распознавания речи браузером',
                    whisperForcedNote: 'принудительно: ты не используешь Chrome',
                    dubbingLabel: 'Зачитать перевод вслух (дубляж)',
                    originalTitle: 'Исходный текст',
                    originalSubtitle: 'Распознан микрофоном',
                    originalPlaceholder: 'Начни говорить, чтобы здесь увидеть транскрипцию в реальном времени.',
                    translationTitle: 'Перевод',
                    suggestionsTitle: 'Советы по собеседованию',
                    ttsBusyMessage: 'Я зачитываю перевод, подожди, пока я закончу, прежде чем снова говорить.',
                    ttsLoadingMessage: 'Загрузка перевода...',
                },
                tr: {
                    title: 'PolyGlide - Anlık çevirmen',
                    subtitle: 'Herhangi bir dilde konuş: orijinal metni ve canlı çeviriyi göreceksin.',
                    langALabel: 'Dil A',
                    langBLabel: 'Dil B',
                    whisperLabel: 'Tarayıcının ses tanıması yerine Whisper (OpenAI) kullan',
                    whisperForcedNote: 'zorunlu: Chrome kullanmıyorsun',
                    dubbingLabel: 'Çeviriyi sesli oku (dublaj)',
                    originalTitle: 'Orijinal metin',
                    originalSubtitle: 'Mikrofon tarafından algılandı',
                    originalPlaceholder: 'Gerçek zamanlı metin dökümünü görmek için konuşmaya başla.',
                    translationTitle: 'Çeviri',
                    suggestionsTitle: 'Mülakat önerileri',
                    ttsBusyMessage: 'Çeviriyi okuyorum, tekrar konuşmadan önce lütfen bitirmemi bekle.',
                    ttsLoadingMessage: 'Çeviri yükleniyor...',
                },
                ar: {
                    title: 'PolyGlide - مترجم فوري',
                    subtitle: 'تحدّث بأي لغة: سترى النص الأصلي والترجمة مباشرة.',
                    langALabel: 'اللغة أ',
                    langBLabel: 'اللغة ب',
                    whisperLabel: 'استخدم Whisper (OpenAI) بدلاً من التعرف على الصوت في المتصفح',
                    whisperForcedNote: 'إجباري: أنت لا تستخدم كروم',
                    dubbingLabel: 'قراءة الترجمة بصوت عالٍ (دبلجة)',
                    originalTitle: 'النص الأصلي',
                    originalSubtitle: 'يتعرّف عليه الميكروفون',
                    originalPlaceholder: 'ابدأ التحدّث لتظهر هنا الكتابة الفورية للنص.',
                    translationTitle: 'الترجمة',
                    suggestionsTitle: 'نصائح للمقابلة',
                    ttsBusyMessage: 'أقرأ الترجمة الآن، يرجى الانتظار حتى أنتهي قبل أن تتحدّث مجددًا.',
                    ttsLoadingMessage: 'جارٍ تحميل الترجمة...',
                },
                he: {
                    title: 'PolyGlide - מתרגם מיידי',
                    subtitle: 'דבר בכל שפה: תראה את הטקסט המקורי ואת התרגום בזמן אמת.',
                    langALabel: 'שפה A',
                    langBLabel: 'שפה B',
                    whisperLabel: 'השתמש ב‑Whisper (OpenAI) במקום זיהוי הדיבור של הדפדפן',
                    whisperForcedNote: 'חובה: אינך משתמש ב‑Chrome',
                    dubbingLabel: 'קריאת התרגום בקול (דיבוב)',
                    originalTitle: 'טקסט מקורי',
                    originalSubtitle: 'מזוהה על‑ידי המיקרופון',
                    originalPlaceholder: 'התחל לדבר כדי לראות כאן תמלול בזמן אמת.',
                    translationTitle: 'תרגום',
                    suggestionsTitle: 'טיפים לראיון עבודה',
                    ttsBusyMessage: 'אני מקריא את התרגום, המתן עד שאסיים לפני שתחזור לדבר.',
                    ttsLoadingMessage: 'טוען תרגום...',
                },
                hi: {
                    title: 'PolyGlide - त्वरित अनुवादक',
                    subtitle: 'किसी भी भाषा में बोलें: आप मूल पाठ और लाइव अनुवाद देखेंगे।',
                    langALabel: 'भाषा A',
                    langBLabel: 'भाषा B',
                    whisperLabel: 'ब्राउज़र की स्पीच रिकग्निशन की जगह Whisper (OpenAI) का उपयोग करें',
                    whisperForcedNote: 'अनिवार्य: आप Chrome का उपयोग नहीं कर रहे हैं',
                    dubbingLabel: 'अनुवाद को ज़ोर से पढ़ें (डबिंग)',
                    originalTitle: 'मूल पाठ',
                    originalSubtitle: 'माइक्रोफ़ोन द्वारा पहचाना गया',
                    originalPlaceholder: 'रीयल‑टाइम ट्रांसक्रिप्शन देखने के लिए बोलना शुरू करें।',
                    translationTitle: 'अनुवाद',
                    suggestionsTitle: 'इंटरव्यू सुझाव',
                    ttsBusyMessage: 'मैं अनुवाद पढ़ रहा हूँ, कृपया दोबारा बोलने से पहले समाप्त होने तक प्रतीक्षा करें।',
                    ttsLoadingMessage: 'अनुवाद लोड हो रहा है...',
                },
                zh: {
                    title: 'PolyGlide - 即时翻译器',
                    subtitle: '用任何语言说话：你会看到原文和实时翻译。',
                    langALabel: '语言 A',
                    langBLabel: '语言 B',
                    whisperLabel: '使用 Whisper（OpenAI）替代浏览器自带的语音识别',
                    whisperForcedNote: '已强制启用：当前浏览器不是 Chrome',
                    dubbingLabel: '朗读译文（配音）',
                    originalTitle: '原文',
                    originalSubtitle: '由麦克风识别',
                    originalPlaceholder: '开始说话即可在此看到实时转写。',
                    translationTitle: '翻译',
                    suggestionsTitle: '面试建议',
                    ttsBusyMessage: '我正在朗读译文，请等我读完再继续说话。',
                    ttsLoadingMessage: '正在加载翻译…',
                },
                ja: {
                    title: 'PolyGlide - インスタント翻訳',
                    subtitle: 'どんな言語でも話せます。元のテキストとリアルタイム翻訳が表示されます。',
                    langALabel: '言語 A',
                    langBLabel: '言語 B',
                    whisperLabel: 'ブラウザの音声認識の代わりに Whisper (OpenAI) を使用する',
                    whisperForcedNote: '強制: Chrome 以外のブラウザを使用中です',
                    dubbingLabel: '翻訳を音声で読み上げる（吹き替え）',
                    originalTitle: '元のテキスト',
                    originalSubtitle: 'マイクから認識',
                    originalPlaceholder: '話し始めると、ここにリアルタイムの書き起こしが表示されます。',
                    translationTitle: '翻訳',
                    suggestionsTitle: '面接のヒント',
                    ttsBusyMessage: '翻訳を読み上げています。終わるまでお待ちください。',
                    ttsLoadingMessage: '翻訳を読み込み中…',
                },
                ko: {
                    title: 'PolyGlide - 즉시 번역기',
                    subtitle: '어떤 언어로 말해도 원문과 실시간 번역을 볼 수 있습니다.',
                    langALabel: '언어 A',
                    langBLabel: '언어 B',
                    whisperLabel: '브라우저 음성 인식 대신 Whisper(OpenAI) 사용',
                    whisperForcedNote: '강제: Chrome 브라우저가 아님',
                    dubbingLabel: '번역 내용을 소리 내어 읽기 (더빙)',
                    originalTitle: '원문',
                    originalSubtitle: '마이크로 인식됨',
                    originalPlaceholder: '말하기 시작하면 여기에 실시간 전사가 표시됩니다.',
                    translationTitle: '번역',
                    suggestionsTitle: '면접 팁',
                    ttsBusyMessage: '번역을 읽는 중입니다. 끝날 때까지 기다렸다가 다시 말해 주세요.',
                    ttsLoadingMessage: '번역 불러오는 중…',
                },
                id: {
                    title: 'PolyGlide - Penerjemah instan',
                    subtitle: 'Berbicaralah dalam bahasa apa pun: kamu akan melihat teks asli dan terjemahan langsung.',
                    langALabel: 'Bahasa A',
                    langBLabel: 'Bahasa B',
                    whisperLabel: 'Gunakan Whisper (OpenAI) sebagai pengganti pengenalan suara browser',
                    whisperForcedNote: 'dipaksa: kamu tidak menggunakan Chrome',
                    dubbingLabel: 'Bacakan terjemahan (dubbing)',
                    originalTitle: 'Teks asli',
                    originalSubtitle: 'Dikenali oleh mikrofon',
                    originalPlaceholder: 'Mulai berbicara untuk melihat transkripsi waktu nyata di sini.',
                    translationTitle: 'Terjemahan',
                    suggestionsTitle: 'Saran wawancara',
                    ttsBusyMessage: 'Saya sedang membacakan terjemahan, tunggu sampai selesai sebelum berbicara lagi.',
                    ttsLoadingMessage: 'Memuat terjemahan...',
                },
                ms: {
                    title: 'PolyGlide - Penterjemah segera',
                    subtitle: 'Bercakap dalam apa‑apa bahasa: anda akan melihat teks asal dan terjemahan secara langsung.',
                    langALabel: 'Bahasa A',
                    langBLabel: 'Bahasa B',
                    whisperLabel: 'Guna Whisper (OpenAI) menggantikan pengecaman suara pelayar',
                    whisperForcedNote: 'dipaksa: anda tidak menggunakan Chrome',
                    dubbingLabel: 'Baca terjemahan dengan kuat (dubbing)',
                    originalTitle: 'Teks asal',
                    originalSubtitle: 'Dikenal pasti oleh mikrofon',
                    originalPlaceholder: 'Mula bercakap untuk melihat transkripsi masa nyata di sini.',
                    translationTitle: 'Terjemahan',
                    suggestionsTitle: 'Petua temu duga',
                    ttsBusyMessage: 'Saya sedang membaca terjemahan, tunggu sehingga saya selesai sebelum bercakap semula.',
                    ttsLoadingMessage: 'Memuatkan terjemahan...',
                },
                th: {
                    title: 'PolyGlide - ตัวแปลภาษาทันที',
                    subtitle: 'พูดได้ทุกภาษา: คุณจะเห็นข้อความต้นฉบับและคำแปลแบบเรียลไทม์',
                    langALabel: 'ภาษา A',
                    langBLabel: 'ภาษา B',
                    whisperLabel: 'ใช้ Whisper (OpenAI) แทนระบบรู้จำเสียงพูดของเบราว์เซอร์',
                    whisperForcedNote: 'ถูกบังคับใช้: คุณไม่ได้ใช้ Chrome',
                    dubbingLabel: 'อ่านคำแปลออกเสียง (พากย์เสียง)',
                    originalTitle: 'ข้อความต้นฉบับ',
                    originalSubtitle: 'รู้จำโดยไมโครโฟน',
                    originalPlaceholder: 'เริ่มพูดเพื่อดูข้อความถอดเสียงแบบเรียลไทม์ที่นี่',
                    translationTitle: 'คำแปล',
                    suggestionsTitle: 'คำแนะนำสำหรับสัมภาษณ์งาน',
                    ttsBusyMessage: 'กำลังอ่านคำแปลอยู่ กรุณารอให้เสร็จก่อนจึงพูดต่อ',
                    ttsLoadingMessage: 'กำลังโหลดคำแปล...',
                },
                vi: {
                    title: 'PolyGlide - Trình dịch tức thì',
                    subtitle: 'Hãy nói bất kỳ ngôn ngữ nào: bạn sẽ thấy văn bản gốc và bản dịch theo thời gian thực.',
                    langALabel: 'Ngôn ngữ A',
                    langBLabel: 'Ngôn ngữ B',
                    whisperLabel: 'Sử dụng Whisper (OpenAI) thay cho nhận dạng giọng nói của trình duyệt',
                    whisperForcedNote: 'bắt buộc: bạn không dùng Chrome',
                    dubbingLabel: 'Đọc to bản dịch (lồng tiếng)',
                    originalTitle: 'Văn bản gốc',
                    originalSubtitle: 'Được nhận dạng từ micro',
                    originalPlaceholder: 'Bắt đầu nói để xem bản chép lại theo thời gian thực tại đây.',
                    translationTitle: 'Bản dịch',
                    suggestionsTitle: 'Gợi ý phỏng vấn',
                    ttsBusyMessage: 'Tôi đang đọc bản dịch, hãy đợi cho đến khi tôi đọc xong rồi hãy nói tiếp.',
                    ttsLoadingMessage: 'Đang tải bản dịch...',
                },
            };

            const base = dict.en;
            const selected = dict[lang] || dict.en;
            return { ...base, ...selected };
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
        debugLog(...args) {
            try {
                const ts = new Date().toISOString();
                const parts = args.map((a) => {
                    if (typeof a === 'string') return a;
                    try {
                        return JSON.stringify(a);
                    } catch {
                        return String(a);
                    }
                });
                const line = `[${ts}] ${parts.join(' ')}`;
                this.debugLogs.push(line);
                if (this.debugLogs.length > 500) {
                    this.debugLogs.splice(0, this.debugLogs.length - 500);
                }
                // Manteniamo anche il log in console per comodità
                console.log('[LiveTranslator DEBUG]', ...args);
            } catch {
                // ignora errori di logging
            }
        },

        async copyDebugLogs() {
            try {
                const text = (this.debugLogs || []).join('\n');
                if (!text) {
                    this.debugCopyStatus = this.ui.debugNoLogsMessage;
                } else if (navigator.clipboard && navigator.clipboard.writeText) {
                    await navigator.clipboard.writeText(text);
                    this.debugCopyStatus = this.ui.debugCopiedMessage;
                } else {
                    this.debugCopyStatus = this.ui.debugClipboardUnavailableMessage;
                }
            } catch {
                this.debugCopyStatus = this.ui.debugCopyErrorMessage;
            }

            try {
                setTimeout(() => {
                    this.debugCopyStatus = '';
                }, 2000);
            } catch {
                // ignora
            }
        },

        detectMobileLowPower() {
            try {
                const ua = (navigator.userAgent || '').toLowerCase();
                const isMobileUa =
                    ua.includes('iphone') ||
                    ua.includes('ipad') ||
                    ua.includes('android') ||
                    ua.includes('mobile');
                // Considera anche il caso "simulatore mobile" in Chrome:
                // - viewport stretta
                // - pointer coarse (touch) dove disponibile)
                const isSmallViewport =
                    typeof window !== 'undefined' &&
                    window.innerWidth &&
                    window.innerWidth <= 768;
                const isCoarsePointer =
                    typeof window !== 'undefined' &&
                    window.matchMedia &&
                    window.matchMedia('(pointer: coarse)').matches;

                this.isMobileLowPower = !!(isMobileUa || isSmallViewport || isCoarsePointer);

                this.debugLog('detectMobileLowPower', {
                    isMobileLowPower: this.isMobileLowPower,
                    ua,
                    isMobileUa,
                    innerWidth: typeof window !== 'undefined' ? window.innerWidth : null,
                    isSmallViewport,
                    isCoarsePointer,
                });
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
                const supported = [
                    'it', 'en', 'es', 'fr', 'de', 'pt', 'nl', 'sv', 'no', 'da',
                    'fi', 'pl', 'cs', 'sk', 'hu', 'ro', 'bg', 'el', 'uk', 'ru',
                    'tr', 'ar', 'he', 'hi', 'zh', 'ja', 'ko', 'id', 'ms', 'th', 'vi',
                ];
                this.uiLocale = supported.includes(code) ? code : 'en';
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

                console.log('🌐 detectEnvAndDefaultMode', {
                    hasWebSpeech,
                    uaSnippet: ua.slice(0, 160),
                    isChromeWithWebSpeech: this.isChromeWithWebSpeech,
                });

                if (!this.isChromeWithWebSpeech) {
                    // Browser non-Chrome: forza modalità Whisper e non permettere cambio
                    this.useWhisper = true;
                    this.autoRestart = false;
                    this.statusMessage = this.ui.statusWhisperAutoForced;
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
                const detectedLang = this.currentMicLang || this.detectRecognitionLang();
                this.recognition.lang = detectedLang;
                this.recognition.continuous = true;
                // In modalità Whisper non gestiamo davvero gli interim, arrivano solo final
                this.recognition.interimResults = !this.useWhisper;
                this.recognition.maxAlternatives = 1;

                this.debugLog('WebSpeech init', {
                    lang: detectedLang,
                    continuous: true,
                    interimResults: !this.useWhisper,
                    maxAlternatives: 1,
                    useWhisper: this.useWhisper,
                    isChrome: this.isChromeWithWebSpeech,
                });
                console.log('🔧 WebSpeech INITIALIZED', {
                    lang: detectedLang,
                    continuous: true,
                    interimResults: !this.useWhisper,
                    maxAlternatives: 1,
                    useWhisper: this.useWhisper,
                    isChrome: this.isChromeWithWebSpeech,
                });

                this.recognition.onstart = () => {
                    this.webSpeechDebugSeq += 1;
                    this.lastWebSpeechEventAt = Date.now();

                    this.debugLog('WebSpeech onstart', {
                        lang: this.recognition.lang,
                        continuous: this.recognition.continuous,
                        interimResults: this.recognition.interimResults,
                        maxAlternatives: this.recognition.maxAlternatives,
                    });
                    console.log('🎤 WebSpeech STARTED', {
                        seq: this.webSpeechDebugSeq,
                        ts: new Date().toISOString(),
                        lang: this.recognition.lang,
                        continuous: this.recognition.continuous,
                        interimResults: this.recognition.interimResults,
                        currentMicLang: this.currentMicLang,
                        activeSpeaker: this.activeSpeaker,
                        activeTab: this.activeTab,
                    });
                };

                this.recognition.onerror = (e) => {
                    const err = e && (e.error || e.message) ? String(e.error || e.message) : 'errore sconosciuto';
                    const errorCode = e && e.error ? e.error : 'unknown';
                    // Non mostriamo il messaggio di errore all'utente, solo nel debug
                    this.isListening = false;

                    this.webSpeechDebugSeq += 1;
                    this.lastWebSpeechEventAt = Date.now();

                    this.debugLog('WebSpeech onerror', {
                        error: err,
                        errorCode: errorCode,
                        lang: this.recognition?.lang,
                    });
                    console.error('❌ WebSpeech ERROR', {
                        seq: this.webSpeechDebugSeq,
                        ts: new Date().toISOString(),
                        error: err,
                        errorCode: errorCode,
                        lang: this.recognition?.lang,
                        event: e,
                        currentMicLang: this.currentMicLang,
                        activeSpeaker: this.activeSpeaker,
                        activeTab: this.activeTab,
                    });
                };

                this.recognition.onend = () => {
                    this.webSpeechDebugSeq += 1;
                    this.lastWebSpeechEventAt = Date.now();

                    this.debugLog('WebSpeech onend', {
                        isListening: this.isListening,
                        autoRestart: this.autoRestart,
                        useWhisper: this.useWhisper,
                    });
                    console.log('🛑 WebSpeech ENDED', {
                        seq: this.webSpeechDebugSeq,
                        ts: new Date().toISOString(),
                        isListening: this.isListening,
                        autoRestart: this.autoRestart,
                        currentMicLang: this.currentMicLang,
                        activeSpeaker: this.activeSpeaker,
                        activeTab: this.activeTab,
                    });

                    // Niente auto-restart in modalità Whisper per evitare loop strani
                    if (this.isListening && this.autoRestart && !this.useWhisper) {
                        try {
                            this.recognition.start();
                            console.log('🔄 WebSpeech AUTO-RESTART');
                        } catch (err) {
                            console.error('❌ WebSpeech AUTO-RESTART FAILED', err);
                        }
                    } else {
                        // Nessun messaggio di stato
                    }
                };

                this.recognition.onresult = (event) => {
                    try {
                        this.webSpeechDebugSeq += 1;
                        this.lastWebSpeechEventAt = Date.now();

                        this.debugLog('WebSpeech onresult', {
                            resultIndex: event.resultIndex,
                            resultsLength: event.results?.length || 0,
                            lang: this.recognition?.lang,
                        });
                        console.log('📥 WebSpeech RESULT EVENT', {
                            seq: this.webSpeechDebugSeq,
                            ts: new Date().toISOString(),
                            resultIndex: event.resultIndex,
                            resultsLength: event.results && event.results.length,
                            lang: this.recognition && this.recognition.lang,
                            currentMicLang: this.currentMicLang,
                            activeSpeaker: this.activeSpeaker,
                            activeTab: this.activeTab,
                        });

                        let interim = '';
                        const results = event.results;

                        for (let i = event.resultIndex; i < results.length; i++) {
                            const res = results[i];
                            const text = (res[0] && res[0].transcript) || '';
                            if (!text) continue;

                            console.log('   ↳ chunk', {
                                i,
                                isFinal: res.isFinal,
                                transcript: text,
                                confidence: res[0] && typeof res[0].confidence === 'number' ? res[0].confidence : undefined,
                            });

                            if (res.isFinal) {
                                const clean = text.trim().toLowerCase();
                                if (clean) {
                                    const phraseWithDash = `- ${clean}`;

                                    this.debugLog('speechFinal', {
                                        from: 'browser',
                                        isMobileLowPower: this.isMobileLowPower,
                                        text: clean,
                                    });

                                    // MOBILE: niente interim, ma usiamo i final progressivi
                                    // per aggiornare/mergeare l'ULTIMA riga quando è la stessa frase.
                                    if (this.isMobileLowPower) {
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
                                                // Consideriamo "stessa frase" se il nuovo testo è
                                                // uguale o un'estensione del precedente (caso tipico mobile:
                                                // "ciao", "ciao io", "ciao io sono davide"...).
                                                if (
                                                    clean === prevText ||
                                                    (clean.length > prevText.length &&
                                                        clean.startsWith(prevText))
                                                ) {
                                                    lines[lines.length - 1] = phraseWithDash;
                                                    this.originalConfirmed = lines.join('\n');
                                                    mergedWithPrevious = true;
                                                }
                                            }
                                        }

                                        if (!mergedWithPrevious) {
                                            // Nuova frase: aggiungi riga originale e "slot" traduzione
                                            this.originalConfirmed = this.originalConfirmed
                                                ? `${this.originalConfirmed}\n${phraseWithDash}`
                                                : phraseWithDash;

                                            // Crea subito uno slot in translationSegments che verrà
                                            // aggiornato quando arriva la traduzione finale.
                                            this.mobileCurrentTranslationIndex = this.translationSegments.length;
                                            this.translationSegments.push('- ...');
                                        } else {
                                            // Stessa frase: l'ultima riga originale è già stata aggiornata sopra,
                                            // la traduzione userà lo stesso indice mobileCurrentTranslationIndex.
                                        }

                                        this.lastFinalOriginalAt = now;
                                        this.originalInterim = '';

                                        this.startTranslationStream(clean, {
                                            commit: true,
                                            // Usiamo sempre lo stesso indice per tutta la frase mobile
                                            // così la traduzione non sovrascrive mai le frasi precedenti.
                                            mergeLast: false,
                                            mergeIndex: this.mobileCurrentTranslationIndex,
                                        });
                                        continue;
                                    }

                                    this.lastFinalOriginalAt = Date.now();
                                    this.originalConfirmed = this.originalConfirmed
                                        ? `${this.originalConfirmed}\n${phraseWithDash}`
                                        : phraseWithDash;
                                    this.originalInterim = '';
                                    this.startTranslationStream(clean, {
                                        commit: true,
                                        mergeLast: false,
                                    });
                                }
                            } else {
                                // INTERIM solo su desktop / non-low-power
                                if (this.isMobileLowPower) {
                                    continue;
                                }
                                interim = [interim, text.trim().toLowerCase()].filter(Boolean).join(' ');
                            }
                        }

                        this.originalInterim = interim;

                        this.$nextTick(() => {
                            this.scrollToBottom('originalBox');
                        });
                        // Mentre parli, usa l'interim per una traduzione incrementale
                        // solo su desktop e solo nella modalità "call":
                        // - su mobile low-power saltiamo lo streaming
                        // - in modalità YouTube vogliamo traduzione SOLO a fine frase
                        if (interim && !this.isMobileLowPower && this.activeTab === 'call') {
                            this.maybeStartPreviewTranslation(interim);
                        }
                    } catch (err) {
                        console.warn('Errore gestione risultato speech', err);
                    }
                };
            } catch (e) {
                this.statusMessage = this.ui.statusMicInitError;
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
                console.log('⏸️ toggleListeningForLang: TTS is playing, ignore mic toggle', {
                    speaker,
                    langA: this.langA,
                    langB: this.langB,
                });
                return;
            }
            // Se sta già ascoltando con lo stesso speaker, ferma
            if (this.isListening && this.activeSpeaker === speaker) {
                console.log('🛑 toggleListeningForLang: stop same speaker', {
                    speaker,
                    currentMicLang: this.currentMicLang,
                    activeTab: this.activeTab,
                });
                this.stopListeningInternal();
                return;
            }

            // Se sta ascoltando con un altro speaker, ferma quello prima
            if (this.isListening && this.activeSpeaker !== speaker) {
                console.log('🔁 toggleListeningForLang: switching speaker', {
                    from: this.activeSpeaker,
                    to: speaker,
                    currentMicLang: this.currentMicLang,
                });
                this.stopListeningInternal();
                // Attendi un attimo per assicurarsi che il recognition sia fermato
                await new Promise(resolve => setTimeout(resolve, 200));
            }

            // Validazione: entrambe le lingue devono essere selezionate
            if (!this.langA || !this.langB) {
                this.statusMessage = this.ui.statusSelectLangAB;
                console.warn('⚠️ toggleListeningForLang: missing langA/langB', {
                    speaker,
                    langA: this.langA,
                    langB: this.langB,
                });
                return;
            }

            const ok = await this.ensureMicPermission();
            if (!ok) {
                this.statusMessage = this.ui.statusMicDenied;
                console.warn('⚠️ toggleListeningForLang: mic permission denied', {
                    speaker,
                });
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
                    console.error('❌ toggleListeningForLang: initSpeechRecognition failed', {
                        speaker,
                        currentMicLang: this.currentMicLang,
                    });
                    return;
                }
            }

            // Aggiorna lingua del recognition
            // Se il recognition è già in esecuzione, fermalo e riavvialo per applicare il cambio lingua
            const wasRunning = this.isListening && this.recognition;
            if (wasRunning) {
                try {
                    this.recognition.stop();
                    this.recognition.abort && this.recognition.abort();
                } catch { }
                // Attendi che si fermi completamente
                await new Promise(resolve => setTimeout(resolve, 300));
            }

            if (this.recognition) {
                this.recognition.lang = this.currentMicLang;
            }

            try {
                this.isListening = true;
                console.log('▶️ toggleListeningForLang: calling recognition.start()', {
                    speaker,
                    langSetOnRecognition: this.recognition.lang,
                    currentMicLang: this.currentMicLang,
                    activeTab: this.activeTab,
                });
                this.recognition.start();

                // In modalità YouTube, aspetta 1 secondo dopo l'attivazione del microfono
                // e poi prova a far partire il video (quando il player è pronto).
                if (this.activeTab === 'youtube') {
                    setTimeout(() => {
                        this.playYoutubeAfterMic();
                    }, 1000);
                }
            } catch (e) {
                this.statusMessage = this.ui.statusMicStartError;
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

        startTranslationStream(textSegment, options = { commit: true, mergeLast: false, mergeIndex: null }) {
            const safeText = ((textSegment || '').trim()).toLowerCase();
            if (!safeText) return;

            const commit = options && typeof options.commit === 'boolean' ? options.commit : true;
            const mergeLast = options && typeof options.mergeLast === 'boolean' ? options.mergeLast : false;
            const mergeIndex = options && typeof options.mergeIndex === 'number' ? options.mergeIndex : null;

            // In modalità YouTube, se sembra una frase "vera" (non solo una parola)
            // mettiamo SUBITO in pausa il video, senza aspettare che parta il TTS.
            if (commit && this.activeTab === 'youtube') {
                const words = safeText.split(/\s+/).filter(Boolean);
                const hasSentencePunct = /[.!?…]$/.test(safeText);
                const longEnough = safeText.length >= 15 || words.length >= 4;
                const shouldPauseForSentence = hasSentencePunct || longEnough;
                if (shouldPauseForSentence) {
                    this.pauseYoutubeIfNeeded();
                }
            }

            this.debugLog('startTranslationStream', {
                text: safeText,
                commit,
                mergeLast,
                isMobileLowPower: this.isMobileLowPower,
                mergeIndex,
            });

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

            const origin = window.location.origin;
            const endpoint = `/api/chatbot/translator-stream?${params.toString()}`;

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
                    const segment = buffer.trim().toLowerCase();
                    if (commit && segment) {
                        this.debugLog('translationDone', {
                            segment,
                            commit,
                            mergeLast,
                            isMobileLowPower: this.isMobileLowPower,
                            mergeIndex,
                            segmentsCountBefore: (this.translationSegments && this.translationSegments.length) || 0,
                        });
                        // Quando una frase è conclusa:
                        // - se mergeIndex è un indice valido, aggiorniamo quella riga (caso mobile)
                        // - altrimenti, se mergeLast è true, aggiorniamo l'ultima riga
                        // - altrimenti aggiungiamo una nuova riga
                        if (
                            mergeIndex !== null &&
                            this.translationSegments &&
                            this.translationSegments.length > mergeIndex
                        ) {
                            this.translationSegments.splice(
                                mergeIndex,
                                1,
                                `- ${segment}`
                            );
                        } else if (mergeLast && this.translationSegments && this.translationSegments.length > 0) {
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

                    // In modalità YouTube, al termine del TTS facciamo ripartire il video
                    if (this.activeTab === 'youtube') {
                        this.resumeYoutubeIfNeeded();
                    }

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

                    if (this.activeTab === 'youtube') {
                        this.resumeYoutubeIfNeeded();
                    }

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
            const text = ((interimText || '').trim()).toLowerCase();
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
            const clean = ((fullText || '').trim()).toLowerCase();
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
                            // Resetta il thread_id quando viene caricato un nuovo CV
                            this.interviewSuggestionThreadId = null;
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
                this.statusMessage = this.ui.statusLangPairMissing;
                return;
            }

            if (this.langA === this.langB) {
                this.statusMessage = this.ui.statusLangPairDifferent;
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
                this.statusMessage = this.ui.statusWhisperModeOn;
            } else {
                this.autoRestart = true;
                this.statusMessage = this.ui.statusBrowserModeOn;
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
                this.statusMessage = this.ui.statusYoutubeUrlInvalid;
                return;
            }

            if (!this.youtubeLangSource || !this.youtubeLangTarget) {
                this.statusMessage = this.ui.statusYoutubeLangsMissing;
                return;
            }

            if (this.youtubeLangSource === this.youtubeLangTarget) {
                this.statusMessage = this.ui.statusYoutubeLangsDifferent;
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

        onRequestSuggestionsClick() {
            // Prende soprattutto le ultime frasi dette (originali) come contesto per il suggeritore
            const lines = (this.originalConfirmed || '')
                .split('\n')
                .map((l) => l.trim())
                .filter(Boolean);

            if (!lines.length) {
                return;
            }

            // Usa le ultime 3 frasi come contesto compatto
            const lastContext = lines.slice(-3).join(' ');
            this.maybeRequestInterviewSuggestion(lastContext);
        },

        async onToggleMindMapClick() {
            if (this.isMindMapLoading) {
                return;
            }
            await this.requestInterviewMindMap();
        },

        async requestInterviewMindMap() {
            // Richiede la mappa mentale solo se:
            // - c'è un CV
            // - sono selezionate entrambe le lingue
            // - se esiste un thread di suggerimenti, lo usiamo come contesto aggiuntivo
            if (!this.cvText || !this.cvText.trim()) {
                return;
            }
            if (!this.langA || !this.langB) {
                return;
            }

            this.isMindMapLoading = true;

            try {
                const res = await fetch('/api/chatbot/interview-mindmap', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify({
                        cv_text: this.cvText,
                        locale: this.locale || 'it',
                        lang_a: this.langA,
                        lang_b: this.langB,
                        thread_id: this.interviewSuggestionThreadId,
                    }),
                });

                const json = await res.json().catch(() => ({}));

                if (!res.ok || json.error) {
                    // In caso di errore, non mostriamo nulla
                    this.mindMap = {
                        langA: '',
                        langB: '',
                        raw: '',
                    };
                    return;
                }

                const nodes = Array.isArray(json.nodes) ? json.nodes : [];
                const edges = Array.isArray(json.edges) ? json.edges : [];
                const raw = (json.raw || '').trim();

                if ((!nodes.length && !edges.length) && !raw) {
                    this.mindMap = {
                        langA: '',
                        langB: '',
                        raw: '',
                    };
                    return;
                }

                this.mindMap = {
                    langA: '',
                    langB: '',
                    raw: raw || '',
                };
                this.mindMapGraph = {
                    nodes,
                    edges,
                };
                this.showMindMapModal = true;

                this.$nextTick(() => {
                    this.renderMindMapGraph();
                });
            } catch {
                // Silenzioso: non blocca l'esperienza
                this.mindMap = {
                    langA: '',
                    langB: '',
                    raw: '',
                };
            } finally {
                this.isMindMapLoading = false;
            }
        },

        renderMindMapGraph() {
            try {
                const container = this.$refs.mindMapGraphContainer;
                if (!container) {
                    return;
                }

                const data = {
                    nodes: this.mindMapGraph.nodes || [],
                    edges: this.mindMapGraph.edges || [],
                };

                const options = {
                    autoResize: true,
                    nodes: {
                        shape: 'dot',
                        size: 14,
                        font: {
                            color: '#e5e7eb',
                            size: 12,
                        },
                        borderWidth: 1,
                        color: {
                            border: '#22c55e',
                            background: '#065f46',
                            highlight: {
                                border: '#22c55e',
                                background: '#059669',
                            },
                        },
                    },
                    edges: {
                        color: {
                            color: '#64748b',
                            highlight: '#e5e7eb',
                        },
                        width: 1,
                        smooth: true,
                        arrows: {
                            to: { enabled: true, scaleFactor: 0.5 },
                        },
                        font: {
                            color: '#9ca3af',
                            size: 10,
                            strokeWidth: 0,
                            align: 'horizontal',
                        },
                    },
                    layout: {
                        improvedLayout: true,
                    },
                    physics: {
                        enabled: true,
                        stabilization: {
                            iterations: 150,
                            fit: true,
                        },
                    },
                    interaction: {
                        hover: true,
                        dragNodes: true,
                        zoomView: true,
                        dragView: true,
                    },
                };

                if (this.mindMapNetwork) {
                    this.mindMapNetwork.destroy();
                }

                this.mindMapNetwork = new Network(container, data, options);
            } catch (e) {
                // Se la libreria non è disponibile o qualcosa va storto, non blocchiamo l'interfaccia
                console.error('MindMap graph render error', e);
            }
        },

        closeMindMapModal() {
            this.showMindMapModal = false;
        },

        exportMindMapAsPrint() {
            // Prima versione semplice: usa la finestra di stampa del browser.
            // L'utente può scegliere "Salva come PDF".
            window.print();
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

            // Genera thread_id se non esiste ancora
            if (!this.interviewSuggestionThreadId) {
                this.interviewSuggestionThreadId = 'interview_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
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
                        thread_id: this.interviewSuggestionThreadId,
                    }),
                });

                const json = await res.json().catch(() => ({}));

                if (!res.ok || json.error) {
                    // Non mostriamo alert per non disturbare durante il colloquio
                    return;
                }

                // Aggiorna il thread_id se viene restituito uno nuovo
                if (json.thread_id) {
                    this.interviewSuggestionThreadId = json.thread_id;
                }

                const langAText = (json.suggestion_lang_a || '').trim();
                const langBText = (json.suggestion_lang_b || '').trim();

                // Se il suggerimento è vuoto o l'argomento non è cambiato, non aggiungere nulla
                if ((!langAText && !langBText) || json.topic_changed === false) {
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

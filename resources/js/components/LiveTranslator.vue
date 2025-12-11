<template>
    <div
        class="w-full min-h-screen bg-slate-900 text-slate-100 flex items-stretch justify-center px-2 md:px-6 py-4 md:py-8">
        <div class="w-full bg-slate-800/80 border border-slate-700 rounded-2xl shadow-2xl p-4 md:p-8 flex flex-col">
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
                    {{ ui.modeLabel }}
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
                                {{ ui.tabCallTitle }}
                            </span>
                            <span class="hidden md:inline text-[11px] text-slate-400">
                                {{ ui.tabCallSubtitle }}
                            </span>
                        </span>
                    </button>

                    <button type="button"
                        class="relative px-4 py-2 rounded-lg font-semibold transition-all duration-150 flex items-center gap-2 disabled:opacity-40 disabled:cursor-not-allowed"
                        :disabled="isYoutubeTabDisabled" :class="activeTab === 'youtube'
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
                                {{ ui.tabYoutubeTitle }}
                            </span>
                            <span class="hidden md:inline text-[11px] text-slate-400">
                                {{ ui.tabYoutubeSubtitle }}
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

                <!-- Avviso: modalità offline in arrivo -->
                <div
                    class="w-full rounded-lg border border-amber-500/40 bg-amber-900/20 px-3 py-2 text-[11px] md:text-xs text-amber-100 flex items-start gap-2">
                    <span class="mt-0.5 text-sm">⏳</span>
                    <span>{{ ui.offlineNotice }}</span>
                </div>

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
                    <label class="flex items-center gap-2 text-[13px] cursor-pointer select-none">
                        <input type="checkbox" v-model="readTranslationEnabledCall"
                            class="h-3.5 w-3.5 rounded border-slate-500 bg-slate-800 text-emerald-500 focus:ring-emerald-500" />
                        <span>{{ ui.dubbingLabel }}</span>
                    </label>
                    <div class="flex flex-col items-center gap-1 text-[11px] mt-1 w-full px-4">
                        <div class="flex items-center justify-between w-full">
                            <label class="flex items-center gap-1 cursor-pointer select-none">
                                <input type="checkbox" v-model="callAutoPauseEnabled"
                                    class="h-3 w-3 rounded border-slate-500 bg-slate-800 text-emerald-500 focus:ring-emerald-500" />
                                <span>{{ ui.youtubeAutoPauseLabel }}</span>
                            </label>
                            <span class="text-[10px] text-slate-400">
                                {{ whisperSilenceMs }} ms
                            </span>
                        </div>
                        <input type="range" min="400" max="2000" step="100" v-model.number="whisperSilenceMs"
                            class="w-full accent-emerald-500" />
                    </div>
                </div>

                <!-- Selettori lingue: metà pagina + metà pagina (traduzione prima, poi seconda lingua) -->
                <div class="w-full">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        <!-- Lingua di traduzione (langB) -->
                        <div class="flex flex-col gap-2">
                            <label class="text-xs font-semibold text-emerald-400">
                                {{ ui.langBLabel }} <span class="text-red-400">*</span>
                            </label>
                            <select v-model="langB" @change="onLanguagePairChange"
                                class="w-full bg-slate-800 border text-sm rounded-md px-3 py-2 focus:outline-none focus:ring-2"
                                :class="langB ? 'border-slate-600 focus:ring-emerald-500' : 'border-red-500 focus:ring-red-500'">
                                <option value="">{{ ui.selectLangBPlaceholder }}</option>
                                <option v-for="opt in availableLanguages" :key="opt.code" :value="opt.code">
                                    {{ opt.label }}
                                </option>
                            </select>
                        </div>

                        <!-- Seconda lingua consentita (langA) -->
                        <div class="flex flex-col gap-2">
                            <label class="text-xs font-semibold text-slate-200">
                                {{ ui.langALabel }}
                            </label>
                            <select v-model="langA" @change="onLanguagePairChange"
                                class="w-full bg-slate-800 border text-sm rounded-md px-3 py-2 focus:outline-none focus:ring-2 border-slate-600 focus:ring-emerald-500">
                                <option value="">{{ ui.selectLangAPlaceholder }}</option>
                                <option v-for="opt in availableLanguages" :key="'a-' + opt.code" :value="opt.code">
                                    {{ opt.label }}
                                </option>
                            </select>
                        </div>
                    </div>

                    <!-- Pulsante microfono unico (auto-rilevamento lingua sorgente → traduzione in langB) -->
                    <div class="mt-3">
                        <button type="button" @click="toggleListeningForLang('A')" :disabled="!langB" class="w-full inline-flex items-center justify-center gap-2 px-3 py-2 rounded-lg text-xs font-semibold transition
                                focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-slate-900 border"
                            :class="isListening
                                ? 'bg-emerald-600 text-white border-emerald-400 shadow-lg shadow-emerald-500/30'
                                : 'bg-slate-700 text-slate-100 border-slate-500 hover:bg-slate-600 disabled:opacity-50 disabled:cursor-not-allowed'">
                            <span
                                class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-black/30 border border-slate-500">
                                <span class="inline-block w-1.5 h-3 rounded-full"
                                    :class="isListening ? 'bg-red-400 animate-pulse' : 'bg-slate-300'"></span>
                            </span>
                            <span>
                                {{ isListening ? ui.speakerAActive : ui.speakerASpeak }}
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
                                <div class="flex items-center gap-2">
                                    <button type="button"
                                        class="inline-flex items-center gap-1 px-2 py-1 rounded-md text-[10px] md:text-[11px] font-medium border border-slate-600 text-slate-100 bg-slate-800 hover:bg-slate-700 transition"
                                        @click="copyTranscript">
                                        <span>{{ ui.transcriptCopyLabel }}</span>
                                    </button>
                                    <button type="button"
                                        class="inline-flex items-center gap-1 px-2 py-1 rounded-md text-[10px] md:text-[11px] font-medium border border-slate-600 text-slate-100 bg-slate-800 hover:bg-slate-700 transition"
                                        @click="exportTranscriptPdf">
                                        <span>{{ ui.transcriptExportPdfLabel }}</span>
                                    </button>
                                </div>
                            </div>
                            <div ref="originalBox"
                                class="h-[100px] md:min-h-[260px] md:max-h-[420px] rounded-xl border border-slate-700 bg-slate-900/60 p-4 text-sm md:text-base lg:text-lg overflow-y-auto leading-relaxed">
                                <div ref="originalEditable" contenteditable="true"
                                    class="w-full h-full bg-transparent text-sm md:text-base lg:text-lg text-slate-100 outline-none whitespace-pre-wrap"
                                    @focus="onOriginalFocus" @blur="onOriginalBlurInternal"
                                    @input="onOriginalEditableInput">
                                </div>
                            </div>
                        </div>

                        <div class="flex flex-col gap-2">
                            <div class="flex items-center justify-between">
                                <span class="text-base md:text-lg font-semibold text-slate-100">
                                    {{ ui.translationTitle }}
                                </span>
                                <div class="flex items-center gap-2">
                                    <button type="button"
                                        class="inline-flex items-center gap-1 px-2 py-1 rounded-md text-[10px] md:text-[11px] font-medium border border-slate-600 text-slate-100 bg-slate-800 hover:bg-slate-700 transition"
                                        @click="copyTranslation">
                                        <span>{{ ui.translationCopyLabel }}</span>
                                    </button>
                                    <button type="button"
                                        class="inline-flex items-center gap-1 px-2 py-1 rounded-md text-[10px] md:text-[11px] font-medium border border-slate-600 text-slate-100 bg-slate-800 hover:bg-slate-700 transition"
                                        @click="exportTranscriptPdf('translation')">
                                        <span>{{ ui.translationExportPdfLabel }}</span>
                                    </button>
                                    <span v-if="isTtsLoading"
                                        class="text-[11px] md:text-xs text-emerald-300 italic ml-1 hidden md:inline">
                                        {{ ui.ttsLoadingMessage }}
                                    </span>
                                </div>
                            </div>
                            <div ref="translationBox"
                                class="h-[100px] md:min-h-[260px] md:max-h-[420px] rounded-xl border border-slate-700 bg-slate-900/60 p-4 text-sm md:text-base lg:text-lg overflow-y-auto leading-relaxed">
                                <div v-if="!hasAnyTranslation" class="text-slate-500 text-xs md:text-sm">
                                    {{ ui.translationPlaceholder }}
                                </div>
                                <div v-else class="space-y-2">
                                    <!-- Frasi già tradotte (segmenti fissi) -->
                                    <div v-for="(seg, idx) in translationSegments" :key="'seg-' + idx"
                                        class="whitespace-pre-wrap">
                                        {{ seg }}
                                    </div>
                                </div>
                                <!-- Frase corrente in streaming, aggiornata token per token con manipolazione diretta DOM -->
                                <div ref="translationLiveContainer" class="whitespace-pre-wrap"></div>
                            </div>
                        </div>
                        <div class="flex flex-col gap-2" v-if="!isMobileLowPower">
                            <div class="flex items-center justify-between gap-4">
                                <div>
                                    <h2 class="text-base md:text-lg font-semibold text-slate-100">
                                        {{ ui.suggestionsTitle }}
                                        <span v-if="langB" class="text-sm text-emerald-400">
                                            ({{ langB.toUpperCase() }})
                                        </span>
                                    </h2>
                                </div>
                                <div v-if="cvText && langB" class="flex items-center gap-2">
                                    <button type="button"
                                        class="inline-flex items-center gap-1 px-2 py-1 rounded-md text-[11px] md:text-xs font-semibold border border-emerald-500 text-emerald-100 bg-emerald-800 hover:bg-emerald-700 transition disabled:opacity-50 disabled:cursor-not-allowed"
                                        :disabled="isLoadingSuggestion" @click="onRequestSuggestionsClick">
                                        <span>
                                            {{ isLoadingSuggestion ? '...' : ui.suggestionsButton }}
                                        </span>
                                    </button>
                                </div>
                            </div>

                            <div ref="suggestionsBox"
                                class="h-[100px] md:min-h-[260px] md:max-h-[420px] rounded-xl border border-slate-700 bg-slate-900/70 p-4 text-xs md:text-sm lg:text-base overflow-y-auto space-y-3 leading-relaxed">
                                <div v-if="!cvText" class="text-xs md:text-sm text-slate-500">
                                    {{ ui.suggestionsNoCv }}
                                </div>

                                <div v-else-if="!langB" class="text-xs md:text-sm text-slate-500">
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

                    <!-- CV e strumenti call: stessa griglia (2 colonne sotto Testo originale + Traduzione) -->
                    <div class="border-t border-slate-700 pt-4" v-if="!isMobileLowPower">
                        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 md:gap-6 items-start">
                            <!-- Sezione CV: occupa le stesse 2 colonne di Testo originale + Traduzione -->
                            <div class="lg:col-span-2 space-y-3">
                                <div>
                                    <h2 class="text-sm font-semibold text-slate-100">
                                        {{ ui.cvSectionTitle }}
                                    </h2>
                                    <p class="text-[11px] text-slate-300 mt-1">
                                        {{ ui.cvSectionDescription }}
                                    </p>
                                </div>
                                <div class="rounded-xl border border-slate-700 bg-slate-900/80 p-4 text-xs space-y-3">
                                    <label class="block text-sm font-medium text-slate-200 mb-2">
                                        {{ ui.cvUploadLabel }}
                                    </label>
                                    <input type="file" accept=".txt,.md,.rtf,.pdf"
                                        class="block w-full text-sm text-slate-200 file:text-sm file:px-4 file:py-2 file:mr-3 file:rounded-lg file:border-0 file:bg-emerald-600 file:text-white file:cursor-pointer file:font-semibold cursor-pointer hover:file:bg-emerald-500 transition"
                                        @change="onCvFileChange" />
                                    <p class="text-xs text-slate-400 mt-3">
                                        {{ ui.cvUploadHint }}
                                    </p>
                                </div>
                            </div>

                            <!-- Bottoni strumenti call: colonna a destra (come la colonna Suggerimenti) -->
                            <div class="flex flex-col gap-3 items-stretch lg:items-end">
                                <button type="button"
                                    class="inline-flex items-center justify-center gap-2 px-6 py-3 rounded-lg text-sm md:text-base font-semibold border-2 border-sky-400 text-sky-100 bg-gradient-to-r from-sky-700 to-sky-800 hover:from-sky-600 hover:to-sky-700 hover:border-sky-300 shadow-lg hover:shadow-xl transition-all disabled:opacity-50 disabled:cursor-not-allowed w-full md:w-[250px]"
                                    :disabled="isMindMapLoading" @click="onToggleMindMapClick">
                                    <span>
                                        {{ isMindMapLoading ? '...' : ui.mindMapButton }}
                                    </span>
                                </button>
                                <button type="button"
                                    class="inline-flex items-center justify-center gap-2 px-6 py-3 rounded-lg text-sm md:text-base font-semibold border-2 border-purple-400 text-purple-100 bg-gradient-to-r from-purple-700 to-purple-800 hover:from-purple-600 hover:to-purple-700 hover:border-purple-300 shadow-lg hover:shadow-xl transition-all disabled:opacity-50 disabled:cursor-not-allowed w-full md:w-[250px]"
                                    :disabled="isNextCallLoading" @click="openNextCallModal">
                                    <span>
                                        {{ isNextCallLoading ? '...' : ui.nextCallButton }}
                                    </span>
                                </button>
                                <button type="button"
                                    class="inline-flex items-center justify-center gap-2 px-6 py-3 rounded-lg text-sm md:text-base font-semibold border-2 border-amber-400 text-amber-100 bg-gradient-to-r from-amber-700 to-amber-800 hover:from-amber-600 hover:to-amber-700 hover:border-amber-300 shadow-lg hover:shadow-xl transition-all disabled:opacity-50 disabled:cursor-not-allowed w-full md:w-[250px]"
                                    :disabled="isClarifyIntentLoading" @click="onClarifyIntentClick">
                                    <span>
                                        {{ isClarifyIntentLoading ? '...' : ui.clarifyIntentButton }}
                                    </span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <!-- TAB 2: Traduttore Video Youtube -->
            <div v-else class="flex flex-col gap-4">
                <p v-if="statusMessage" class="text-xs text-slate-300 text-center">
                    {{ statusMessage }}
                </p>

                <!-- Avviso mobile-only: YouTube interprete limitato su smartphone -->
                <div v-if="isMobileLowPower"
                    class="w-full rounded-lg border border-amber-500/40 bg-amber-900/25 px-3 py-2 text-[11px] md:text-xs text-amber-100 flex items-start gap-2">
                    <span class="mt-0.5 text-sm">📱</span>
                    <span>{{ ui.youtubeMobileWarning }}</span>
                </div>

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

                <!-- Controllo modalità riconoscimento (Gemini / Whisper / browser) anche per YouTube -->
                <div class="flex flex-col items-center gap-1 text-slate-300">
                    <label class="flex items-center gap-2 text-[13px] cursor-pointer select-none">
                        <input type="checkbox" v-model="readTranslationEnabledYoutube"
                            class="h-3.5 w-3.5 rounded border-slate-500 bg-slate-800 text-emerald-500 focus:ring-emerald-500" />
                        <span>{{ ui.dubbingLabel }}</span>
                    </label>
                    <div class="flex flex-col items-center gap-1 text-[11px] mt-1 w-full px-4">
                        <div class="flex items-center justify-between w-full">
                            <label class="flex items-center gap-1 cursor-pointer select-none">
                                <input type="checkbox" v-model="youtubeAutoPauseEnabled"
                                    class="h-3 w-3 rounded border-slate-500 bg-slate-800 text-emerald-500 focus:ring-emerald-500" />
                                <span>{{ ui.youtubeAutoPauseLabel }}</span>
                            </label>
                            <span class="text-[10px] text-slate-400">
                                {{ whisperSilenceMs }} ms
                            </span>
                        </div>
                        <input type="range" min="400" max="2000" step="100" v-model.number="whisperSilenceMs"
                            class="w-full accent-emerald-500" />
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

                        <!-- Hint desktop: usare PLAY per ascoltare e PAUSA per tradurre -->
                        <div v-if="!isMobileLowPower"
                            class="mt-1 px-3 py-2 rounded-lg border border-emerald-500/70 bg-emerald-900/40 text-[11px] md:text-xs text-emerald-100 font-semibold">
                            {{ ui.youtubePlayPauseHint }}
                        </div>

                        <!-- Pulsante microfono per modalità YouTube SOLO su mobile/low-power.
                             Su desktop il microfono segue automaticamente play/pause del player. -->
                        <div class="mt-4" v-if="isMobileLowPower">
                            <button type="button" @click="toggleListeningForLang('A')"
                                :disabled="!youtubeLangSource || !youtubeLangTarget" class="inline-flex items-center justify-center gap-2 px-3 py-2 rounded-lg text-xs font-semibold border transition
                                    focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-slate-900"
                                :class="isListening
                                    ? 'bg-emerald-600 text-white border-emerald-400 shadow-lg shadow-emerald-500/30'
                                    : 'bg-slate-700 text-slate-100 border-slate-500 hover:bg-slate-600 disabled:opacity-50 disabled:cursor-not-allowed'">
                                <span
                                    class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-black/30 border border-slate-500">
                                    <span class="inline-block w-1.5 h-3 rounded-full"
                                        :class="isListening ? 'bg-red-400 animate-pulse' : 'bg-slate-300'">
                                    </span>
                                </span>
                                <span>
                                    {{ isListening ? ui.youtubeMicAActive : ui.youtubeMicAHelp }}
                                </span>
                            </button>
                        </div>
                    </div>

                    <!-- Colonna video + pannelli di traduzione riutilizzati -->
                    <div class="lg:col-span-2 space-y-4">
                        <div
                            class="aspect-video w-full rounded-xl border border-slate-700 bg-black overflow-hidden flex items-center justify-center">
                            <div v-if="!youtubeVideoId" class="text-xs text-slate-400 px-4 text-center">
                                {{ ui.youtubePlayerPlaceholder }}
                            </div>
                            <div v-else ref="youtubePlayer" class="w-full h-full"></div>
                        </div>

                        <!-- Riutilizzo pannelli originale/traduzione (solo layout) -->
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                            <div class="flex flex-col gap-2">
                                <div class="flex items-center justify-between">
                                    <span class="text-sm md:text-base font-semibold text-slate-100">
                                        {{ ui.youtubeOriginalTitle }}
                                    </span>
                                </div>
                                <div ref="originalBox"
                                    class="h-[120px] md:h-[200px] rounded-xl border border-slate-700 bg-slate-900/60 p-3 text-xs md:text-sm overflow-y-auto leading-relaxed">
                                    <p v-if="!displayOriginalText" class="text-slate-500 text-xs md:text-sm">
                                        {{ ui.youtubeOriginalPlaceholder }}
                                    </p>
                                    <p v-else class="whitespace-pre-wrap">
                                        {{ displayOriginalText }}
                                    </p>
                                </div>
                            </div>
                            <div class="flex flex-col gap-2">
                                <div class="flex items-center justify-between">
                                    <span class="text-sm md:text-base font-semibold text-slate-100">
                                        {{ ui.youtubeTranslationTitle }}
                                    </span>
                                    <span v-if="isTtsLoading"
                                        class="text-[10px] md:text-[11px] text-emerald-300 italic ml-2">
                                        {{ ui.ttsLoadingMessage }}
                                    </span>
                                </div>
                                <div ref="translationBox"
                                    class="h-[120px] md:h-[200px] rounded-xl border border-slate-700 bg-slate-900/60 p-3 text-xs md:text-sm overflow-y-auto leading-relaxed">
                                    <div v-if="!hasAnyTranslation" class="text-slate-500 text-xs md:text-sm">
                                        {{ ui.youtubeTranslationPlaceholder }}
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
                            <div v-if="lastBackendAudioUrl" class="mt-1 text-[11px] text-slate-400 italic break-all">
                                <a :href="lastBackendAudioUrl" download="backend-audio.webm"
                                    class="underline hover:text-emerald-300">
                                    {{ ui.downloadBackendAudioLabel }}
                                </a>
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
                        {{ langB && getLangLabel(langB) ? getLangLabel(langB) : '' }}
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
            <div class="flex-1 flex flex-col">
                <div class="flex-1">
                    <div ref="mindMapGraphContainer" class="w-full h-full"></div>
                </div>
                <!-- Lista testi mappa mentale (sempre visibile e stampabile) -->
                <div
                    class="border-t border-slate-700 px-4 py-3 max-h-48 overflow-y-auto text-xs md:text-sm bg-slate-900/95">
                    <div v-if="mindMapGraph && mindMapGraph.nodes && mindMapGraph.nodes.length">
                        <div v-for="node in mindMapGraph.nodes" :key="node.id || node.label" class="mb-2 last:mb-0">
                            <div class="font-semibold text-slate-100">
                                {{ node.label || node.title || node.id }}
                            </div>
                            <div v-if="node.note || node.description" class="text-slate-300 whitespace-pre-line mt-0.5">
                                {{ node.note || node.description }}
                            </div>
                        </div>
                    </div>
                    <div v-else class="text-slate-500">
                        Nessun nodo disponibile nella mappa mentale.
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Migliora Prossima Call -->
    <div v-if="showNextCallModal"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 backdrop-blur-sm">
        <div
            class="bg-slate-900 rounded-2xl border border-slate-700 w-[92vw] max-w-4xl max-h-[90vh] flex flex-col shadow-2xl">
            <div class="flex items-center justify-between px-4 py-2 border-b border-slate-700">
                <div class="flex flex-col">
                    <span class="text-sm md:text-base font-semibold text-slate-100">
                        {{ ui.nextCallButton }}
                    </span>
                    <span class="text-[11px] text-slate-400">
                        {{ ui.manualTranscriptLabel }}
                    </span>
                </div>
                <button type="button"
                    class="inline-flex items-center justify-center w-7 h-7 rounded-full border border-slate-600 text-slate-200 hover:bg-slate-700 transition"
                    @click="closeNextCallModal">
                    ✕
                </button>
            </div>
            <div class="p-4 space-y-4 overflow-y-auto">
                <div class="space-y-1">
                    <label class="text-xs font-semibold text-slate-200">
                        Obiettivo call
                    </label>
                    <textarea v-model="nextCallGoal" rows="4"
                        class="w-full text-xs md:text-sm rounded-md border border-slate-600 bg-slate-900/80 px-2 py-1 text-slate-100 resize-y focus:outline-none focus:ring-2 focus:ring-emerald-500"></textarea>
                </div>
                <div class="flex justify-end">
                    <button type="button"
                        class="inline-flex items-center gap-1 px-3 py-1.5 rounded-md text-[11px] md:text-xs font-semibold border border-emerald-500 text-emerald-100 bg-emerald-900 hover:bg-emerald-800 transition disabled:opacity-50 disabled:cursor-not-allowed"
                        :disabled="isNextCallLoading || !nextCallGoal.trim()" @click="onNextCallSuggestClick">
                        <span>{{ isNextCallLoading ? '...' : 'Suggerisci' }}</span>
                    </button>
                </div>
                <div v-if="nextCallSuggestionsLangA || nextCallSuggestionsLangB" class="w-full space-y-4">
                    <div v-if="nextCallSuggestionsLangA" class="w-full space-y-1">
                        <div class="text-[11px] md:text-xs font-semibold text-slate-200">
                            {{ getLangLabel(langA || 'it') }}
                        </div>
                        <div class="w-full text-xs md:text-sm text-slate-100 whitespace-pre-wrap leading-relaxed">
                            {{ nextCallSuggestionsLangA }}
                        </div>
                    </div>
                    <div v-if="nextCallSuggestionsLangB" class="w-full space-y-1">
                        <div class="text-[11px] md:text-xs font-semibold text-slate-200">
                            {{ getLangLabel(langB || 'en') }}
                        </div>
                        <div class="w-full text-xs md:text-sm text-slate-100 whitespace-pre-wrap leading-relaxed">
                            {{ nextCallSuggestionsLangB }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Chiarisci Intenzione Interlocutore -->
    <div v-if="showClarifyIntentModal"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 backdrop-blur-sm">
        <div
            class="bg-slate-900 rounded-2xl border border-slate-700 w-[92vw] max-w-4xl max-h-[90vh] flex flex-col shadow-2xl">
            <div class="flex items-center justify-between px-4 py-2 border-b border-slate-700">
                <div class="flex flex-col">
                    <span class="text-sm md:text-base font-semibold text-slate-100">
                        {{ ui.clarifyIntentTitle }}
                    </span>
                    <span class="text-[11px] text-slate-400">
                        {{ langB && getLangLabel(langB) ? getLangLabel(langB) : '' }}
                    </span>
                </div>
                <button type="button"
                    class="inline-flex items-center justify-center w-7 h-7 rounded-full border border-slate-600 text-slate-200 hover:bg-slate-700 transition"
                    @click="closeClarifyIntentModal">
                    ✕
                </button>
            </div>
            <div class="p-4 space-y-4 overflow-y-auto">
                <!-- Fase 1: Descrizione ruolo interlocutore -->
                <div v-if="!clarifyIntentText" class="w-full space-y-4">
                    <div class="space-y-1">
                        <label class="text-xs font-semibold text-slate-200">
                            {{ ui.clarifyIntentSelectSpeaker }}
                        </label>
                        <textarea v-model="clarifyIntentInterlocutorRole" rows="3"
                            :placeholder="ui.clarifyIntentInterlocutorRolePlaceholder"
                            class="w-full text-xs md:text-sm rounded-md border border-slate-600 bg-slate-900/80 px-2 py-1 text-slate-100 resize-y focus:outline-none focus:ring-2 focus:ring-amber-500"></textarea>
                    </div>
                    <div class="flex justify-end">
                        <button type="button"
                            class="inline-flex items-center gap-1 px-3 py-1.5 rounded-md text-[11px] md:text-xs font-semibold border border-amber-500 text-amber-100 bg-amber-900 hover:bg-amber-800 transition disabled:opacity-50 disabled:cursor-not-allowed"
                            :disabled="isClarifyIntentLoading || !clarifyIntentInterlocutorRole.trim()"
                            @click="onClarifyIntentAnalyzeClick">
                            <span>{{ isClarifyIntentLoading ? '...' : ui.clarifyIntentAnalyzeButton }}</span>
                        </button>
                    </div>
                </div>
                <!-- Fase 2: Risultato -->
                <div v-else class="w-full">
                    <div class="w-full text-xs md:text-sm text-slate-100 whitespace-pre-wrap leading-relaxed">
                        {{ clarifyIntentText }}
                    </div>
                </div>
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
            // Per tradurre solo la parte aggiunta a mano nella trascrizione
            originalTextBeforeManualEdit: '',
            // Spiegazione intenzione cliente
            isClarifyIntentLoading: false,
            clarifyIntentText: '',
            isOriginalEditingManually: false,
            langA: '',
            langB: '',
            currentMicLang: '',
            currentTargetLang: '',
            activeSpeaker: null, // 'A' o 'B' - indica chi sta parlando

            isChromeWithWebSpeech: true,

            // Doppiaggio (TTS) indipendente per tab
            readTranslationEnabledCall: false,
            readTranslationEnabledYoutube: true,
            // Auto-pausa basata sul silenzio (sia per modalità call che YouTube)
            callAutoPauseEnabled: true,
            youtubeAutoPauseEnabled: true,
            youtubeAutoResumeEnabled: true,
            // Durata (ms) di silenzio che chiude un segmento dei motori backend (Whisper / Google)
            // e fa scattare l'auto-pausa quando abilitata. Default: 800ms (pausa naturale di conversazione).
            whisperSilenceMs: 700,
            ttsQueue: [],
            isTtsPlaying: false,
            wasListeningBeforeTts: false,
            lastSpeakerBeforeTts: null,
            translationThreadId: null,
            // Coda per traduzioni finali quando uno stream è ancora attivo
            pendingTranslationQueue: [],

            // Speaker da riattivare automaticamente dopo una pausa auto-rilevata
            // (solo modalità call e solo quando il TTS è disattivato).
            pendingAutoResumeSpeaker: null,
            // Speaker da riattivare automaticamente dopo la lettura TTS
            // in modalità call quando l'auto-pausa ha spento il microfono.
            pendingAutoResumeSpeakerAfterTts: null,

            // Modalità low-power per mobile (usata solo per ottimizzare la UI,
            // la logica di traduzione ora è uguale a desktop)
            isMobileLowPower: false,
            isTtsLoading: false,

            // Debug: ultimo audio inviato a un motore backend (Whisper/Gemini)
            lastBackendAudioUrl: '',

            // Debug interno: pannello e log testuali copiabili
            showDebugPanel: false,
            debugLogs: [],
            debugCopyStatus: '',
            // Migliora prossima call
            showNextCallModal: false,
            isNextCallLoading: false,
            nextCallGoal: '',
            nextCallSuggestionsLangA: '',
            nextCallSuggestionsLangB: '',
            // Chiarisci intenzione interlocutore
            showClarifyIntentModal: false,
            clarifyIntentInterlocutorRole: '', // Testo libero che descrive il ruolo dell'interlocutore
            webSpeechDebugSeq: 0,
            lastWebSpeechEventAt: 0,

            // Stato per modalità "Traduttore Video Youtube"
            youtubeUrl: '',
            youtubeVideoId: '',
            youtubePlayer: null,
            youtubeLangSource: '',
            youtubeLangTarget: '',
            isYoutubePlayerReady: false,
            youtubePlayerState: -1,

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
                    title: 'Interpreter – l\'interprete virtuale che ti fa parlare con chiunque',
                    subtitle: 'Parla in qualsiasi lingua: vedrai il testo originale e la traduzione live.',
                    langALabel: 'Lingua dell\'interlocutore',
                    langBLabel: 'Lingua di traduzione',
                    whisperLabel: 'Usa il motore avanzato (cloud)',
                    whisperForcedNote: '',
                    whisperSingleSegmentLabel: 'Invia l’audio solo quando spengo il microfono (meno chiamate, frasi più complete)',
                    googleCloudLabel: 'Usa Gemini (compatibile con tutti i browser)',
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
                    cvUploadLabel: 'Carica CV da file',
                    cvUploadHint: 'Suggerimento: salva il tuo CV e caricalo da qui.',
                    youtubeUrlLabel: 'URL video YouTube',
                    youtubeUrlHelp: 'Incolla qui il link del video che vuoi usare durante la call di lavoro.',
                    youtubeLangSourceLabel: 'Lingua del video',
                    youtubeLangTargetLabel: 'Lingua di traduzione',
                    youtubeMicAActive: 'Stop + Pausa video',
                    youtubeMicAHelp: 'Registra + Play video',
                    youtubeAutoPauseLabel: 'Rileva in automatico le pause',
                    youtubeAutoPauseHint: '',
                    youtubeAutoResumeLabel: 'Riprendi automaticamente dopo traduzione',
                    youtubeAutoResumeHint: '',
                    speakerAActive: 'Registrazione in corso',
                    speakerASpeak: 'Registra',
                    speakerBActive: 'Parlante B attivo',
                    speakerBSpeak: 'Parla Lingua B',
                    selectLangAPlaceholder: '-- Seleziona la lingua dell\'interlocutore --',
                    selectLangBPlaceholder: '-- Seleziona lingua di traduzione --',
                    selectOptionPlaceholder: '-- Seleziona --',
                    ttsBusyMessage: 'Sto leggendo la traduzione, attendi che finisca prima di parlare.',
                    ttsLoadingMessage: 'Caricamento traduzione in corso...',
                    statusWhisperAutoForced: 'Modalità Whisper attiva automaticamente: il riconoscimento vocale del browser non è pienamente supportato qui.',
                    statusMicInitError: 'Errore inizializzazione microfono.',
                    statusSelectLangAB: '⚠️ Seleziona entrambe le lingue (A e B) prima di iniziare!',
                    statusMicDenied: 'Permesso microfono negato. Abilitalo nelle impostazioni del browser.',
                    statusMicStartError: 'Impossibile avviare il microfono.',
                    statusLangPairMissing: 'Seleziona la lingua di traduzione per iniziare.',
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
                    modeLabel: 'Modalità',
                    tabCallTitle: 'Interprete & CV',
                    tabCallSubtitle: 'Call di lavoro in tempo reale',
                    tabYoutubeTitle: 'YouTube Interprete',
                    tabYoutubeSubtitle: 'Video + traduzione frase per frase',
                    translationPlaceholder: 'La traduzione apparirà qui man mano che parli.',
                    youtubePlayerPlaceholder: 'Incolla un URL di YouTube e seleziona le lingue a sinistra: il player si carica automaticamente.',
                    youtubeOriginalTitle: 'Testo riconosciuto dal microfono',
                    youtubeOriginalPlaceholder: 'Inizia a parlare sopra il video per vedere qui le frasi riconosciute.',
                    youtubeTranslationTitle: 'Traduzione in tempo reale',
                    youtubeTranslationPlaceholder: 'Le traduzioni delle frasi parlate appariranno qui, mentre il video si mette in pausa durante il doppiaggio.',
                    youtubeStatusPlaying: 'Riproduzione in corso',
                    youtubeStatusPaused: 'Pausa',
                    youtubeStatusTranscriptionRequested: 'Trascrizione richiesta',
                    youtubeStatusTranscriptionDone: 'Trascrizione arrivata',
                    youtubeStatusTranslationRequested: 'Traduzione richiesta',
                    youtubeStatusReadingTranslation: 'Lettura traduzione',
                    downloadBackendAudioLabel: 'Scarica audio inviato al riconoscimento vocale',
                    youtubePlayPauseHint: 'FAI PLAY per ascoltare il video, metti in PAUSA per tradurre la frase appena detta.',
                    transcriptCopyLabel: 'Copia trascrizione',
                    transcriptExportPdfLabel: 'Esporta PDF trascrizione',
                    translationCopyLabel: 'Copia traduzione',
                    translationExportPdfLabel: 'Esporta PDF traduzione',
                    nextCallButton: 'Migliora prossima call',
                    offlineNotice: 'Tra poco potrai usare Interpreter anche offline, senza connessione: stiamo preparando una modalità locale dedicata.',
                    youtubeMobileWarning: 'Su questo dispositivo mobile il browser non permette di tradurre i video bene come da computer. Per l’esperienza completa di YouTube Interprete usa un PC o Mac (meglio se con Chrome).',
                    clarifyIntentButton: 'Chiarisci intenzione interlocutore',
                    clarifyIntentTitle: 'Cosa intende davvero l\'interlocutore',
                    clarifyIntentEmpty: 'Quando hai dei dubbi su cosa stia chiedendo l\'interlocutore, usa il pulsante qui sopra: qui apparirà una spiegazione ragionata delle sue intenzioni.',
                    clarifyIntentSelectSpeaker: 'Qual è il ruolo dell\'interlocutore di cui vuoi chiarire le intenzioni?',
                    clarifyIntentInterlocutorRolePlaceholder: 'Es: il recruiter, il cliente, il capo, il candidato, ecc.',
                    clarifyIntentAnalyzeButton: 'Chiarisci',
                },
                en: {
                    title: 'Interpreter – the virtual interpreter that lets you talk to anyone',
                    subtitle: 'Speak in any language: you will see the original text and the live translation.',
                    langALabel: 'Interlocutor language',
                    langBLabel: 'Translation language',
                    whisperLabel: 'Use the advanced engine (cloud)',
                    whisperForcedNote: '',
                    whisperSingleSegmentLabel: 'Send audio only when I stop the microphone (fewer calls, more complete sentences)',
                    googleCloudLabel: 'Use Gemini (compatible with all browsers)',
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
                    cvUploadLabel: 'Upload CV from file',
                    cvUploadHint: 'Tip: save your CV as a text file and upload it here.',
                    youtubeUrlLabel: 'YouTube video URL',
                    youtubeUrlHelp: 'Paste here the link of the video you want to use during the work call.',
                    youtubeLangSourceLabel: 'Video language',
                    youtubeLangTargetLabel: 'Translation language',
                    youtubeMicAActive: 'Stop video',
                    youtubeMicAHelp: 'Play video',
                    youtubeAutoPauseLabel: 'Automatically detect pauses',
                    youtubeAutoPauseHint: '',
                    youtubeAutoResumeLabel: 'Auto-resume after translation',
                    youtubeAutoResumeHint: '',
                    speakerAActive: 'Recording…',
                    speakerASpeak: 'Record',
                    speakerBActive: 'Speaker B active',
                    speakerBSpeak: 'Speak Language B',
                    selectLangAPlaceholder: '-- Select interlocutor language --',
                    selectLangBPlaceholder: '-- Select translation language --',
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
                    modeLabel: 'Mode',
                    tabCallTitle: 'Interpreter & CV',
                    tabCallSubtitle: 'Real-time work call',
                    tabYoutubeTitle: 'YouTube Interpreter',
                    tabYoutubeSubtitle: 'Video + phrase-by-phrase translation',
                    offlineNotice: 'Soon you will be able to use Interpreter offline, without an internet connection: we are working on a dedicated local mode.',
                    youtubeMobileWarning: 'On this mobile device the browser cannot handle video translation as well as on desktop. For the full YouTube Interpreter experience, use a PC or Mac (ideally with Chrome).',
                    clarifyIntentButton: 'Clarify interlocutor intent',
                    clarifyIntentTitle: 'What the interlocutor probably means',
                    clarifyIntentEmpty: 'If you are unsure about what the interlocutor is really asking for, use the button above: a step-by-step explanation of their intent will appear here.',
                    clarifyIntentSelectSpeaker: 'What is the role of the interlocutor whose intent you want to clarify?',
                    clarifyIntentInterlocutorRolePlaceholder: 'E.g.: the recruiter, the client, the boss, the candidate, etc.',
                    clarifyIntentAnalyzeButton: 'Clarify',
                    translationPlaceholder: 'The translation will appear here as you speak.',
                    youtubePlayerPlaceholder: 'Paste a YouTube URL and select the languages on the left: the player loads automatically.',
                    youtubeOriginalTitle: 'Text recognized from microphone',
                    youtubeOriginalPlaceholder: 'Start speaking over the video to see recognized phrases here.',
                    youtubeTranslationTitle: 'Real-time translation',
                    youtubeTranslationPlaceholder: 'Translations of spoken phrases will appear here, while the video pauses during dubbing.',
                    youtubeStatusPlaying: 'Playback in progress',
                    youtubeStatusPaused: 'Paused',
                    youtubeStatusTranscriptionRequested: 'Transcription requested',
                    youtubeStatusTranscriptionDone: 'Transcription received',
                    youtubeStatusTranslationRequested: 'Translation requested',
                    youtubeStatusReadingTranslation: 'Reading translation',
                    downloadBackendAudioLabel: 'Download audio sent to speech recognition',
                    youtubePlayPauseHint: 'Press PLAY to listen to the video, press PAUSE to translate the last spoken sentence.',
                    transcriptCopyLabel: 'Copy transcript',
                    transcriptExportPdfLabel: 'Export PDF transcript',
                    translationCopyLabel: 'Copy translation',
                    translationExportPdfLabel: 'Export PDF translation',
                    nextCallButton: 'Improve next call',
                },
                es: {
                    title: 'PolyGlide – el intérprete virtual que te permite hablar con cualquiera',
                    subtitle: 'Habla en cualquier idioma: verás el texto original y la traducción en directo.',
                    langALabel: 'Idioma A',
                    langBLabel: 'Idioma de traducción',
                    whisperLabel: 'Usar el motor avanzado (cloud) en lugar del reconocimiento de voz del navegador',
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
                    modeLabel: 'Modo',
                    tabCallTitle: 'Intérprete y CV',
                    tabCallSubtitle: 'Llamada de trabajo en tiempo real',
                    tabYoutubeTitle: 'Intérprete de YouTube',
                    tabYoutubeSubtitle: 'Video + traducción frase por frase',
                    translationPlaceholder: 'La traducción aparecerá aquí mientras hablas.',
                    youtubePlayerPlaceholder: 'Pega una URL de YouTube y selecciona los idiomas a la izquierda: el reproductor se carga automáticamente.',
                    youtubeOriginalTitle: 'Texto reconocido del micrófono',
                    youtubeOriginalPlaceholder: 'Comienza a hablar sobre el video para ver aquí las frases reconocidas.',
                    youtubeTranslationTitle: 'Traducción en tiempo real',
                    youtubeTranslationPlaceholder: 'Las traducciones de las frases habladas aparecerán aquí, mientras el video se pone en pausa durante el doblaje.',
                    transcriptCopyLabel: 'Copiar transcripción',
                    transcriptExportPdfLabel: 'Exportar PDF transcripción',
                    translationCopyLabel: 'Copiar traducción',
                    translationExportPdfLabel: 'Exportar PDF traducción',
                    nextCallButton: 'Mejorar próxima llamada',
                },
                fr: {
                    title: 'PolyGlide – l\'interprète virtuel qui te permet de parler à n\'importe qui',
                    subtitle: 'Parle dans n’importe quelle langue : tu verras le texte original et la traduction en direct.',
                    langALabel: 'Langue A',
                    langBLabel: 'Langue de traduction',
                    whisperLabel: 'Utiliser le moteur avancé (cloud) au lieu de la reconnaissance vocale du navigateur',
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
                    ttsBusyMessage: 'Je lis la traduction, attends qu\'elle soit terminée avant de reparler.',
                    ttsLoadingMessage: 'Chargement de la traduction...',
                    modeLabel: 'Mode',
                    tabCallTitle: 'Interprète et CV',
                    tabCallSubtitle: 'Appel de travail en temps réel',
                    tabYoutubeTitle: 'Interprète YouTube',
                    tabYoutubeSubtitle: 'Vidéo + traduction phrase par phrase',
                    translationPlaceholder: 'La traduction apparaîtra ici au fur et à mesure que tu parles.',
                    youtubePlayerPlaceholder: 'Colle une URL YouTube et sélectionne les langues à gauche : le lecteur se charge automatiquement.',
                    youtubeOriginalTitle: 'Texte reconnu par le microphone',
                    youtubeOriginalPlaceholder: 'Commence à parler au-dessus de la vidéo pour voir ici les phrases reconnues.',
                    youtubeTranslationTitle: 'Traduction en temps réel',
                    youtubeTranslationPlaceholder: 'Les traductions des phrases parlées apparaîtront ici, pendant que la vidéo se met en pause pendant le doublage.',
                    transcriptCopyLabel: 'Copier la transcription',
                    transcriptExportPdfLabel: 'Exporter PDF transcription',
                    translationCopyLabel: 'Copier la traduction',
                    translationExportPdfLabel: 'Exporter PDF traduction',
                    nextCallButton: 'Améliorer le prochain appel',
                },
                de: {
                    title: 'PolyGlide – der virtuelle Dolmetscher, der dich mit jedem sprechen lässt',
                    subtitle: 'Sprich in jeder Sprache: Du siehst den Originaltext und die Live-Übersetzung.',
                    langALabel: 'Sprache A',
                    langBLabel: 'Übersetzungssprache',
                    whisperLabel: 'Erweiterten Cloud‑Dienst statt Spracherkennung des Browsers verwenden',
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
                    modeLabel: 'Modus',
                    tabCallTitle: 'Dolmetscher & Lebenslauf',
                    tabCallSubtitle: 'Arbeitsgespräch in Echtzeit',
                    tabYoutubeTitle: 'YouTube Dolmetscher',
                    tabYoutubeSubtitle: 'Video + Satz-für-Satz-Übersetzung',
                    translationPlaceholder: 'Die Übersetzung erscheint hier, während du sprichst.',
                    youtubePlayerPlaceholder: 'Füge eine YouTube-URL ein und wähle die Sprachen links aus: Der Player lädt sich automatisch.',
                    youtubeOriginalTitle: 'Vom Mikrofon erkanntes Text',
                    youtubeOriginalPlaceholder: 'Beginne über das Video zu sprechen, um hier die erkannten Sätze zu sehen.',
                    youtubeTranslationTitle: 'Echtzeitübersetzung',
                    youtubeTranslationPlaceholder: 'Die Übersetzungen der gesprochenen Sätze erscheinen hier, während das Video während der Synchronisation pausiert wird.',
                    transcriptCopyLabel: 'Transkript kopieren',
                    transcriptExportPdfLabel: 'PDF Transkript exportieren',
                    translationCopyLabel: 'Übersetzung kopieren',
                    translationExportPdfLabel: 'PDF Übersetzung exportieren',
                    nextCallButton: 'Nächsten Anruf verbessern',
                },
                pt: {
                    title: 'PolyGlide – o intérprete virtual que te permite falar com qualquer pessoa',
                    subtitle: 'Fala em qualquer idioma: vais ver o texto original e a tradução em tempo real.',
                    langALabel: 'Idioma A',
                    langBLabel: 'Idioma de tradução',
                    whisperLabel: 'Usar o motor avançado (cloud) em vez do reconhecimento de voz do navegador',
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
                    title: 'PolyGlide – de virtuele tolk die je met iedereen laat praten',
                    subtitle: 'Spreek in elke taal: je ziet de originele tekst en de livevertaling.',
                    langALabel: 'Taal A',
                    langBLabel: 'Vertalings taal',
                    whisperLabel: 'De geavanceerde cloud‑engine gebruiken in plaats van de spraakherkenning van de browser',
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
                    title: 'PolyGlide – den virtuella tolken som låter dig prata med vem som helst',
                    subtitle: 'Tala på vilket språk du vill: du ser originaltexten och översättningen i realtid.',
                    langALabel: 'Språk A',
                    langBLabel: 'Översättnings språk',
                    whisperLabel: 'Använd den avancerade moln‑motorn i stället för webbläsarens röstigenkänning',
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
                    title: 'PolyGlide – den virtuelle tolken som lar deg snakke med hvem som helst',
                    subtitle: 'Snakk på hvilket som helst språk: du ser originalteksten og oversettelsen i sanntid.',
                    langALabel: 'Språk A',
                    langBLabel: 'Översättnings språk',
                    whisperLabel: 'Bruk den avanserte sky‑motoren i stedet for nettleserens talegjenkjenning',
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
                    title: 'PolyGlide – den virtuelle tolk, der lader dig tale med hvem som helst',
                    subtitle: 'Tal på hvilket som helst sprog: du ser originalteksten og live-oversættelsen.',
                    langALabel: 'Sprog A',
                    langBLabel: 'Oversættelsessprog',
                    whisperLabel: 'Brug den avancerede cloud‑motor i stedet for browserens stemmegenkendelse',
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
                    title: 'PolyGlide – virtuaalinen tulkki, joka antaa sinun puhua kenelle tahansa',
                    subtitle: 'Puhu millä tahansa kielellä: näet alkuperäisen tekstin ja reaaliaikaisen käännöksen.',
                    langALabel: 'Kieli A',
                    langBLabel: 'Käännös kieli',
                    whisperLabel: 'Käytä kehittynyttä pilvipalvelua selaimen puheentunnistuksen sijaan',
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
                    title: 'PolyGlide – wirtualny tłumacz, który pozwala rozmawiać z kimkolwiek',
                    subtitle: 'Mów w dowolnym języku: zobaczysz tekst oryginalny i tłumaczenie na żywo.',
                    langALabel: 'Język A',
                    langBLabel: 'Język tłumaczenia',
                    whisperLabel: 'Użyj zaawansowanego silnika w chmurze zamiast rozpoznawania mowy przeglądarki',
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
                    title: 'PolyGlide – virtuální tlumočník, který vám umožní mluvit s kýmkoli',
                    subtitle: 'Mluv jakýmkoliv jazykem: uvidíš původní text a překlad v reálném čase.',
                    langALabel: 'Jazyk A',
                    langBLabel: 'Jazyk překladu',
                    whisperLabel: 'Použít pokročilý cloudový modul místo rozpoznávání řeči prohlížeče',
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
                    title: 'PolyGlide – virtuálny tlmočník, ktorý vám umožní hovoriť s kýmkoľvek',
                    subtitle: 'Hovor v akomkoľvek jazyku: uvidíš pôvodný text a preklad v reálnom čase.',
                    langALabel: 'Jazyk A',
                    langBLabel: 'Jazyk překladu',
                    whisperLabel: 'Použiť pokročilý cloudový modul namiesto rozpoznávania reči v prehliadači',
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
                    title: 'PolyGlide – a virtuális tolmács, aki bárkivel beszélni enged',
                    subtitle: 'Beszélj bármilyen nyelven: látni fogod az eredeti szöveget és az élő fordítást.',
                    langALabel: 'A nyelv',
                    langBLabel: 'Fordítási nyelv',
                    whisperLabel: 'Használd a fejlett felhőalapú motort a böngésző beszédfelismerése helyett',
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
                    title: 'PolyGlide – interpretul virtual care îți permite să vorbești cu oricine',
                    subtitle: 'Vorbește în orice limbă: vei vedea textul original și traducerea în timp real.',
                    langALabel: 'Limba A',
                    langBLabel: 'Limba traducerii',
                    whisperLabel: 'Folosește motorul avansat din cloud în locul recunoașterii vocale din browser',
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
                    title: 'PolyGlide – виртуалният преводач, който ти позволява да говориш с всеки',
                    subtitle: 'Говори на всеки език: ще виждаш оригиналния текст и превода в реално време.',
                    langALabel: 'Език A',
                    langBLabel: 'Език на превода',
                    whisperLabel: 'Използвай разширения облачен модул вместо разпознаването на реч в браузъра',
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
                    title: 'PolyGlide – ο εικονικός διερμηνέας που σου επιτρέπει να μιλάς με οποιονδήποτε',
                    subtitle: 'Μίλησε σε οποιαδήποτε γλώσσα: θα βλέπεις το αρχικό κείμενο και τη ζωντανή μετάφραση.',
                    langALabel: 'Γλώσσα A',
                    langBLabel: 'Γλώσσα μετάφρασης',
                    whisperLabel: 'Χρήση της προηγμένης μηχανής cloud αντί για την αναγνώριση ομιλίας του browser',
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
                    title: 'PolyGlide – віртуальний перекладач, який дозволяє розмовляти з будь-ким',
                    subtitle: 'Говори будь-якою мовою: ти бачитимеш оригінальний текст і переклад у реальному часі.',
                    langALabel: 'Мова A',
                    langBLabel: 'Мова перекладу',
                    whisperLabel: 'Використовувати розширений хмарний модуль замість розпізнавання мовлення браузера',
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
                    title: 'PolyGlide – виртуальный переводчик, который позволяет говорить с кем угодно',
                    subtitle: 'Говори на любом языке: ты увидишь оригинальный текст и перевод в реальном времени.',
                    langALabel: 'Язык A',
                    langBLabel: 'Язык перевода',
                    whisperLabel: 'Использовать продвинутый облачный модуль вместо распознавания речи браузером',
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
                    title: 'PolyGlide – herkesle konuşmanı sağlayan sanal çevirmen',
                    subtitle: 'Herhangi bir dilde konuş: orijinal metni ve canlı çeviriyi göreceksin.',
                    langALabel: 'Dil A',
                    langBLabel: 'Çeviri dili',
                    whisperLabel: 'Tarayıcının ses tanıması yerine gelişmiş bulut motorunu kullan',
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
                    title: 'PolyGlide – المترجم الافتراضي الذي يتيح لك التحدث مع أي شخص',
                    subtitle: 'تحدّث بأي لغة: سترى النص الأصلي والترجمة مباشرة.',
                    langALabel: 'اللغة أ',
                    langBLabel: 'لغة الترجمة',
                    whisperLabel: 'استخدم المحرك السحابي المتقدم بدلاً من أداة التعرف على الصوت في المتصفح',
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
                    title: 'PolyGlide – המתרגם הווירטואלי שמאפשר לך לדבר עם כל אחד',
                    subtitle: 'דבר בכל שפה: תראה את הטקסט המקורי ואת התרגום בזמן אמת.',
                    langALabel: 'שפה A',
                    langBLabel: 'שפת תרגום',
                    whisperLabel: 'השתמש במנוע ענן מתקדם במקום זיהוי הדיבור של הדפדפן',
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
                    title: 'PolyGlide – वर्चुअल दुभाषिया जो आपको किसी से भी बात करने देता है',
                    subtitle: 'किसी भी भाषा में बोलें: आप मूल पाठ और लाइव अनुवाद देखेंगे।',
                    langALabel: 'भाषा A',
                    langBLabel: 'अनुवाद भाषा',
                    whisperLabel: 'ब्राउज़र की स्पीच रिकग्निशन की जगह उन्नत क्लाउड इंजन का उपयोग करें',
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
                    title: 'PolyGlide – 让您与任何人交谈的虚拟口译员',
                    subtitle: '用任何语言说话：你会看到原文和实时翻译。',
                    langALabel: '语言 A',
                    langBLabel: '翻译语言',
                    whisperLabel: '使用高级云端引擎替代浏览器自带的语音识别',
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
                    title: 'PolyGlide – 誰とでも話せるバーチャル通訳',
                    subtitle: 'どんな言語でも話せます。元のテキストとリアルタイム翻訳が表示されます。',
                    langALabel: '言語 A',
                    langBLabel: '翻訳言語',
                    whisperLabel: 'ブラウザの音声認識の代わりに高度なクラウドエンジンを使用する',
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
                    title: 'PolyGlide – 누구와도 대화할 수 있게 해주는 가상 통역사',
                    subtitle: '어떤 언어로 말해도 원문과 실시간 번역을 볼 수 있습니다.',
                    langALabel: '언어 A',
                    langBLabel: '번역 언어',
                    whisperLabel: '브라우저 음성 인식 대신 고급 클라우드 엔진 사용',
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
                    title: 'PolyGlide – penerjemah virtual yang memungkinkan Anda berbicara dengan siapa pun',
                    subtitle: 'Berbicaralah dalam bahasa apa pun: kamu akan melihat teks asli dan terjemahan langsung.',
                    langALabel: 'Bahasa A',
                    langBLabel: 'Bahasa terjemahan',
                    whisperLabel: 'Gunakan mesin cloud tingkat lanjut sebagai pengganti pengenalan suara browser',
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
                    title: 'PolyGlide – penterjemah maya yang membolehkan anda bercakap dengan sesiapa sahaja',
                    subtitle: 'Bercakap dalam apa‑apa bahasa: anda akan melihat teks asal dan terjemahan secara langsung.',
                    langALabel: 'Bahasa A',
                    langBLabel: 'Bahasa terjemahan',
                    whisperLabel: 'Guna enjin awan lanjutan menggantikan pengecaman suara pelayar',
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
                    title: 'PolyGlide – ล่ามเสมือนที่ให้คุณพูดคุยกับใครก็ได้',
                    subtitle: 'พูดได้ทุกภาษา: คุณจะเห็นข้อความต้นฉบับและคำแปลแบบเรียลไทม์',
                    langALabel: 'ภาษา A',
                    langBLabel: 'ภาษาการแปล',
                    whisperLabel: 'ใช้เอนจินคลาวด์ขั้นสูงแทนระบบรู้จำเสียงพูดของเบราว์เซอร์',
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
                    title: 'PolyGlide – thông dịch viên ảo cho phép bạn nói chuyện với bất kỳ ai',
                    subtitle: 'Hãy nói bất kỳ ngôn ngữ nào: bạn sẽ thấy văn bản gốc và bản dịch theo thời gian thực.',
                    langALabel: 'Ngôn ngữ A',
                    langBLabel: 'Ngôn ngữ dịch',
                    whisperLabel: 'Sử dụng engine đám mây nâng cao thay cho nhận dạng giọng nói của trình duyệt',
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
        // Flag effettivo Whisper in base alla tab attiva
        useWhisperEffective() {
            // Tab "call": sempre Whisper.
            // Tab "youtube": Whisper solo su desktop, WebSpeech su mobile low-power.
            if (this.activeTab === 'youtube' && this.isMobileLowPower) {
                return false;
            }
            return true;
        },
        // Flag effettivo Google Speech in base alla tab attiva
        useGoogleEffective() {
            // Gemini/Google non è più utilizzato.
            return false;
        },
        // Modalità "invia audio solo quando spengo il microfono" effettiva per Whisper
        whisperSendOnStopOnlyEffective() {
            // Manteniamo il comportamento originale di Whisper:
            // registrazione single-segment e invio dell'audio solo quando
            // si spegne esplicitamente il microfono (stopListeningInternal).
            // L'auto-pausa basata sul silenzio simula semplicemente il click
            // sul bottone di pausa, senza cambiare questo flusso.
            return true;
        },
        // Doppiaggio effettivo in base alla tab
        readTranslationEnabledEffective() {
            return this.activeTab === 'youtube'
                ? this.readTranslationEnabledYoutube
                : this.readTranslationEnabledCall;
        },
        displayOriginalText: {
            get() {
                const base = this.originalConfirmed || '';
                const interim = this.originalInterim || '';
                return [base, interim].filter(Boolean).join('\n');
            },
            set(value) {
                const text = (value || '').toString();
                this.originalConfirmed = text;
                this.originalInterim = '';
            },
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
        youtubeStatusLabel() {
            if (this.activeTab !== 'youtube') {
                return '';
            }

            if (this.isTtsPlaying) {
                return this.ui.youtubeStatusReadingTranslation;
            }

            if (this.currentStream) {
                return this.ui.youtubeStatusTranslationRequested;
            }

            if (this.isListening) {
                // Il microfono è attivo ma non stiamo ancora leggendo la traduzione:
                // consideriamo la trascrizione in corso/richiesta.
                return this.ui.youtubeStatusTranscriptionRequested;
            }

            if (this.originalConfirmed) {
                return this.ui.youtubeStatusTranscriptionDone;
            }

            if (this.youtubePlayerState === 1) {
                return this.ui.youtubeStatusPlaying;
            }

            return this.ui.youtubeStatusPaused;
        },
        isYoutubeTabDisabled() {
            // Su mobile/low-power la tab YouTube è disponibile solo se il browser
            // espone la Web Speech API; altrimenti la disabilitiamo del tutto.
            return this.isMobileLowPower && !this.isChromeWithWebSpeech;
        },
    },
    watch: {
        youtubeUrl() {
            this.maybeAutoLoadYoutubePlayer();
        },
        youtubeLangSource() {
            // La scelta delle lingue nella tab YouTube è indipendente dalla tab "call":
            // qui ci limitiamo a caricare / ricaricare il player se la configurazione è valida.
            this.maybeAutoLoadYoutubePlayer();
        },
        youtubeLangTarget() {
            // Anche il cambio della lingua di destinazione YouTube non modifica langA/langB della tab "call".
            this.maybeAutoLoadYoutubePlayer();
        },
        displayOriginalText(newVal) {
            try {
                const el = this.$refs.originalEditable;
                if (!el) {
                    return;
                }
                if (this.isOriginalEditingManually) {
                    // Se l'utente sta digitando non forziamo il contenuto, per non spostare il cursore.
                    return;
                }
                // Evita di riscrivere se il testo è già uguale
                if ((el.innerText || '') === (newVal || '')) {
                    return;
                }
                el.innerText = newVal || '';
                this.$nextTick(() => {
                    this.scrollToBottom('originalBox');
                });
            } catch {
                // silenzioso
            }
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

        async copyTranscript() {
            try {
                const text = (this.displayOriginalText || '').trim();
                if (!text) {
                    return;
                }
                if (navigator.clipboard && navigator.clipboard.writeText) {
                    await navigator.clipboard.writeText(text);
                }
            } catch {
                // silenzioso: non blocca l'esperienza
            }
        },

        onOriginalInput() {
            try {
                this.$nextTick(() => {
                    this.scrollToBottom('originalBox');
                });
            } catch {
                // ignora errori di scroll
            }
        },

        onOriginalFocus() {
            try {
                this.isOriginalEditingManually = true;
                this.originalTextBeforeManualEdit = this.displayOriginalText || '';
                const el = this.$refs.originalEditable;
                if (el && (el.innerText || '') !== (this.displayOriginalText || '')) {
                    el.innerText = this.displayOriginalText || '';
                }
            } catch {
                // silenzioso
            }
        },

        onOriginalEditableInput(event) {
            try {
                const el = event && event.target ? event.target : this.$refs.originalEditable;
                if (!el) {
                    return;
                }
                const text = el.innerText || '';
                // Aggiorna il modello reattivo
                this.displayOriginalText = text;
                this.onOriginalInput();
            } catch {
                // silenzioso
            }
        },

        onOriginalBlurInternal() {
            try {
                this.isOriginalEditingManually = false;
            } catch {
                // silenzioso
            }
            // Usa la logica esistente per capire cosa tradurre
            this.onOriginalBlur();
        },

        onClarifyIntentClick() {
            // Serve un thread di TRASCRIZIONE, altrimenti non abbiamo contesto sufficiente
            if (!this.translationThreadId) {
                return;
            }

            // Reset dello stato quando si apre il modal
            this.clarifyIntentInterlocutorRole = '';
            this.clarifyIntentText = '';
            this.openClarifyIntentModal();
        },

        async onClarifyIntentAnalyzeClick() {
            if (this.isClarifyIntentLoading || !this.clarifyIntentInterlocutorRole.trim()) {
                return;
            }

            // Serve un thread di TRASCRIZIONE, altrimenti non abbiamo contesto sufficiente
            if (!this.translationThreadId) {
                return;
            }

            // Prendiamo le ultime frasi dell'originale come focus principale
            const lines = (this.displayOriginalText || '')
                .split('\n')
                .map((l) => l.trim())
                .filter(Boolean);

            if (!lines.length) {
                return;
            }

            const focusText = lines.slice(-5).join('\n');

            this.isClarifyIntentLoading = true;
            this.clarifyIntentText = ''; // Reset del risultato precedente

            try {
                const res = await fetch('/api/chatbot/interpreter-clarify-intent', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify({
                        focus_text: focusText,
                        interlocutor_role: this.clarifyIntentInterlocutorRole.trim(),
                        locale: this.locale || this.uiLocale || 'it',
                        lang_a: this.langA || 'it',
                        lang_b: this.langB || 'en',
                        thread_id: this.translationThreadId,
                    }),
                });

                const json = await res.json().catch(() => ({}));

                if (!res.ok || json.error) {
                    return;
                }

                this.clarifyIntentText = (json.explanation || '').trim();
            } catch {
                // silenzioso
            } finally {
                this.isClarifyIntentLoading = false;
            }
        },

        openClarifyIntentModal() {
            this.showClarifyIntentModal = true;
        },

        closeClarifyIntentModal() {
            this.showClarifyIntentModal = false;
            this.clarifyIntentInterlocutorRole = '';
            this.clarifyIntentText = '';
        },

        async copyTranslation() {
            try {
                const text = (this.displayTranslationText || '').trim();
                if (!text) {
                    return;
                }
                if (navigator.clipboard && navigator.clipboard.writeText) {
                    await navigator.clipboard.writeText(text);
                }
            } catch {
                // silenzioso
            }
        },

        exportTranscriptPdf(kind) {
            try {
                const original = (this.displayOriginalText || '').trim();
                const translation = (this.displayTranslationText || '').trim();
                const langALabel = this.langA ? this.getLangLabel(this.langA) : '';
                const langBLabel = this.langB ? this.getLangLabel(this.langB) : '';

                const win = window.open('', '_blank');
                if (!win) {
                    return;
                }

                let title = 'Transcript';
                let safeBody = '';

                if (kind === 'translation') {
                    title = 'Traduzione';
                    const safeTranslation = (translation || '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/\n/g, '<br>');
                    safeBody = `
  <h2>Translation ${langBLabel ? '(' + langBLabel + ')' : ''}</h2>
  <div class="box">${safeTranslation || '<em>(vuoto)</em>'}</div>
                    `;
                } else {
                    title = 'Trascrizione';
                    const safeOriginal = (original || '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/\n/g, '<br>');
                    safeBody = `
  <h2>Original ${langALabel ? '(' + langALabel + ')' : ''}</h2>
  <div class="box">${safeOriginal || '<em>(vuoto)</em>'}</div>
                    `;
                }

                win.document.write(`
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>${title}</title>
  <style>
    body { font-family: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; margin: 24px; color: #020617; }
    h1 { font-size: 20px; margin-bottom: 12px; }
    h2 { font-size: 16px; margin-top: 16px; margin-bottom: 8px; }
    .box { border: 1px solid #cbd5f5; padding: 12px; border-radius: 8px; background: #f8fafc; }
  </style>
</head>
<body>
  <h1>${title} call</h1>
  ${safeBody}
</body>
</html>
                `);
                win.document.close();
                win.focus();
                win.print();
            } catch {
                // silenzioso
            }
        },

        onOriginalBlur() {
            const currentText = (this.displayOriginalText || '').trim();
            const beforeText = (this.originalTextBeforeManualEdit || '').trim();

            if (!currentText || currentText === beforeText) {
                return;
            }

            // Se il testo attuale contiene quello precedente, estrai solo la parte nuova
            let newText = '';
            if (beforeText && currentText.startsWith(beforeText)) {
                newText = currentText.substring(beforeText.length).trim();
            } else {
                // Se il testo è stato modificato completamente, prendi tutto
                newText = currentText;
            }

            if (!newText) {
                return;
            }

            // Traduci solo la parte nuova (mantenendo eventuali trattini e punteggiatura)
            this.startTranslationStream(newText, {
                commit: true,
                mergeLast: false,
                shouldEnqueueTts: false,
                // Per testo incollato/scritto a mano NON aggiungiamo "- " noi:
                // se l'utente vuole i trattini, li mette già nel testo originale.
                addDash: false,
            });

            this.originalTextBeforeManualEdit = '';
        },

        onOriginalInput() {
            try {
                const el = this.$refs.originalTextarea;
                if (el) {
                    // auto-resize del textarea per evitare la scrollbar interna
                    el.style.height = 'auto';
                    el.style.height = `${el.scrollHeight}px`;
                }
                this.$nextTick(() => {
                    this.scrollToBottom('originalBox');
                });
            } catch {
                // ignora errori di scroll/resize
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
            // Se la tab YouTube è disabilitata (mobile senza WebSpeech), non permettere il cambio tab.
            if (tab === 'youtube' && this.isYoutubeTabDisabled) {
                this.statusMessage = 'Riconoscimento vocale non disponibile in questo browser.';
                return;
            }

            this.activeTab = tab;

            if (tab === 'call') {
                // Nella tab "call" usiamo sempre Whisper.
                this.statusMessage = '';
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

                // LangB: lingua di traduzione = lingua del browser (se supportata), altrimenti en
                let defaultB = 'en';

                const match = this.availableLanguages.find(l => l.code === base);
                if (match) {
                    defaultB = match.code;
                }

                // LangA: lingua dell'interlocutore.
                // Default: inglese; se la lingua del browser è già inglese,
                // usa italiano come seconda lingua per coprire il caso tipico it<->en.
                let defaultA = 'en';
                if (defaultB === 'en') {
                    defaultA = 'it';
                }

                this.langA = defaultA;
                this.langB = defaultB;
            } catch {
                this.langA = 'en';
                this.langB = 'it';
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
            } catch {
                this.isChromeWithWebSpeech = false;
            }
        },

        initSpeechRecognition() {
            try {
                let RecClass = null;

                // Engine selection:
                // - Tab "call": sempre Whisper
                // - Tab "youtube": mobile → WebSpeech del browser, desktop → Whisper
                if (this.useWhisperEffective) {
                    RecClass = WhisperSpeechRecognition;
                } else {
                    RecClass = window.SpeechRecognition || window.webkitSpeechRecognition;
                }

                if (!RecClass) {
                    this.statusMessage = this.useWhisperEffective
                        ? 'Modalità Whisper attiva ma il wrapper non è disponibile in questo browser.'
                        : 'Riconoscimento vocale non disponibile in questo browser. Puoi attivare la modalità Whisper.';
                    return;
                }

                this.recognition = new RecClass();
                const detectedLang = this.currentMicLang || this.detectRecognitionLang();
                this.recognition.lang = detectedLang;

                // Configurazione base
                const isBackendEngine = this.useWhisperEffective;
                this.recognition.maxAlternatives = 1;

                if (isBackendEngine) {
                    this.recognition.continuous = true;
                    this.recognition.interimResults = false;
                } else {
                    // WebSpeech del browser:
                    //  - desktop: continuous true + interimResults true (streaming classico)
                    //  - mobile: continuous false + interimResults false (solo final, niente interim)
                    if (this.isMobileLowPower) {
                        this.recognition.continuous = false;
                        this.recognition.interimResults = false;
                    } else {
                        this.recognition.continuous = true;
                        this.recognition.interimResults = true;
                    }
                }

                const engine = this.useWhisperEffective ? 'cloud_engine' : 'webspeech';
                this.debugLog('WebSpeech init', {
                    engine,
                    lang: detectedLang,
                    continuous: this.recognition.continuous,
                    interimResults: this.recognition.interimResults,
                    maxAlternatives: this.recognition.maxAlternatives,
                    useWhisper: this.useWhisperEffective,
                    useGoogle: this.useGoogleEffective,
                    isChrome: this.isChromeWithWebSpeech,
                    activeTab: this.activeTab,
                    isMobileLowPower: this.isMobileLowPower,
                });
                console.log('🔧 WebSpeech INITIALIZED', {
                    engine,
                    lang: detectedLang,
                    continuous: true,
                    interimResults: !isBackendEngine,
                    maxAlternatives: 1,
                    useWhisper: this.useWhisperEffective,
                    isChrome: this.isChromeWithWebSpeech,
                    activeTab: this.activeTab,
                    isMobileLowPower: this.isMobileLowPower,
                });

                this.recognition.onstart = () => {
                    this.webSpeechDebugSeq += 1;
                    this.lastWebSpeechEventAt = Date.now();

                    const engine = this.useGoogleEffective ? 'gemini' : (this.useWhisperEffective ? 'whisper' : 'webspeech');
                    this.debugLog('WebSpeech onstart', {
                        engine,
                        lang: this.recognition.lang,
                        continuous: this.recognition.continuous,
                        interimResults: this.recognition.interimResults,
                        maxAlternatives: this.recognition.maxAlternatives,
                    });
                    console.log('🎤 WebSpeech STARTED', {
                        engine,
                        seq: this.webSpeechDebugSeq,
                        ts: new Date().toISOString(),
                        lang: this.recognition.lang,
                        continuous: this.recognition.continuous,
                        interimResults: this.recognition.interimResults,
                        currentMicLang: this.currentMicLang,
                        activeSpeaker: this.activeSpeaker,
                        activeTab: this.activeTab,
                        useGoogle: this.useGoogleEffective,
                        useWhisper: this.useWhisperEffective,
                    });
                };

                this.recognition.onerror = (e) => {
                    const err = e && (e.error || e.message) ? String(e.error || e.message) : 'errore sconosciuto';
                    const errorCode = e && e.error ? e.error : 'unknown';
                    // Non mostriamo il messaggio di errore all'utente, solo nel debug.
                    // In modalità YouTube non spegniamo il microfono in base agli eventi WebSpeech:
                    // lo stato del mic segue solo il player (play/pause).
                    if (this.activeTab === 'call') {
                        this.isListening = false;
                    }

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

                    const isBackendEngine = this.useWhisperEffective;
                    const singleSegmentMode = isBackendEngine && this.recognition && typeof this.recognition === 'object'
                        ? this.recognition.singleSegmentMode
                        : false;

                    const timeSinceLastResult = this.lastWebSpeechEventAt > 0
                        ? Date.now() - this.lastWebSpeechEventAt
                        : null;
                    // In modalità YouTube EVITIAMO l'auto-restart continuo del WebSpeech,
                    // perché genererebbe un loop di onstart/onend che interagisce male
                    // con il player YouTube (soprattutto su mobile). Manteniamo
                    // l'auto-restart solo nella modalità "call".
                    const shouldAutoRestart =
                        this.activeTab === 'call' &&
                        this.isListening &&
                        this.autoRestart &&
                        !this.useWhisperEffective;

                    this.debugLog('WebSpeech onend', {
                        isListening: this.isListening,
                        autoRestart: this.autoRestart,
                        shouldAutoRestart,
                        activeTab: this.activeTab,
                        useWhisper: this.useWhisperEffective,
                        useGoogle: this.useGoogleEffective,
                        isBackendEngine,
                        singleSegmentMode,
                        timeSinceLastResult,
                    });
                    console.log('🛑 WebSpeech ENDED', {
                        seq: this.webSpeechDebugSeq,
                        ts: new Date().toISOString(),
                        isListening: this.isListening,
                        autoRestart: this.autoRestart,
                        shouldAutoRestart,
                        currentMicLang: this.currentMicLang,
                        activeSpeaker: this.activeSpeaker,
                        activeTab: this.activeTab,
                        useWhisper: this.useWhisperEffective,
                        useGoogle: this.useGoogleEffective,
                        isBackendEngine,
                        singleSegmentMode,
                        timeSinceLastResult,
                    });

                    // Niente auto-restart:
                    // - in modalità Whisper / Gemini (backend)
                    // - in modalità YouTube (per evitare loop con il player)
                    if (shouldAutoRestart) {
                        try {
                            this.recognition.start();
                            console.log('🔄 WebSpeech AUTO-RESTART');
                        } catch (err) {
                            console.error('❌ WebSpeech AUTO-RESTART FAILED', err);
                        }
                    } else {
                        // Nessun messaggio di stato
                    }

                    // In modalità "call" con auto-pausa attiva e TTS disattivato:
                    // se la pausa è stata causata dal VAD (onAutoPause), riaccendi
                    // automaticamente il microfono sullo stesso speaker.
                    const shouldAutoResumeCall =
                        isBackendEngine &&
                        this.activeTab === 'call' &&
                        this.callAutoPauseEnabled &&
                        !this.readTranslationEnabledCall &&
                        !!this.pendingAutoResumeSpeaker;

                    if (shouldAutoResumeCall) {
                        const speaker = this.pendingAutoResumeSpeaker;
                        this.pendingAutoResumeSpeaker = null;

                        this.debugLog('WebSpeech onend: auto-resuming mic after VAD pause', {
                            speaker,
                        });
                        console.log('▶️ WebSpeech onend: auto-resuming mic after VAD pause', {
                            ts: new Date().toISOString(),
                            speaker,
                        });

                        try {
                            this.toggleListeningForLang(speaker);
                        } catch (err) {
                            this.debugLog('WebSpeech onend: error auto-resuming mic', {
                                error: String(err),
                            });
                            console.error('❌ WebSpeech onend: error auto-resuming mic', {
                                ts: new Date().toISOString(),
                                error: String(err),
                            });
                        }
                    }
                };

                this.recognition.onresult = (event) => {
                    try {
                        // Debug: link audio inviato al backend (Whisper / Gemini)
                        if (event && event.audioUrl) {
                            try {
                                if (this.lastBackendAudioUrl) {
                                    URL.revokeObjectURL(this.lastBackendAudioUrl);
                                }
                                this.lastBackendAudioUrl = event.audioUrl;
                            } catch {
                                // ignora errori nel revoke
                            }
                        }

                        this.webSpeechDebugSeq += 1;
                        const resultTimestamp = Date.now();
                        this.lastWebSpeechEventAt = resultTimestamp;

                        const engine = this.useWhisperEffective ? 'cloud_engine' : 'webspeech';
                        const isBackendEngine = this.useWhisperEffective;
                        const singleSegmentMode = isBackendEngine && this.recognition && typeof this.recognition === 'object'
                            ? this.recognition.singleSegmentMode
                            : false;

                        this.debugLog('WebSpeech onresult START', {
                            engine,
                            resultIndex: event.resultIndex,
                            resultsLength: event.results?.length || 0,
                            lang: this.recognition?.lang,
                            currentMicLang: this.currentMicLang,
                            activeSpeaker: this.activeSpeaker,
                            activeTab: this.activeTab,
                            useGoogle: this.useGoogleEffective,
                            useWhisper: this.useWhisperEffective,
                            isMobileLowPower: this.isMobileLowPower,
                            isListening: this.isListening,
                            isBackendEngine,
                            singleSegmentMode,
                        });
                        console.log('📥 WebSpeech RESULT EVENT START', {
                            engine,
                            seq: this.webSpeechDebugSeq,
                            ts: new Date().toISOString(),
                            resultIndex: event.resultIndex,
                            resultsLength: event.results && event.results.length,
                            lang: this.recognition && this.recognition.lang,
                            currentMicLang: this.currentMicLang,
                            activeSpeaker: this.activeSpeaker,
                            activeTab: this.activeTab,
                            useGoogle: this.useGoogleEffective,
                            useWhisper: this.useWhisperEffective,
                            isMobileLowPower: this.isMobileLowPower,
                            isListening: this.isListening,
                            isBackendEngine,
                            singleSegmentMode,
                        });

                        let interim = '';
                        // event.results è un SpeechRecognitionResultList (array-like), non un vero array
                        // Su mobile Chrome può essere un oggetto array-like, quindi lo convertiamo in array
                        const results = event.results ? Array.from(event.results) : [];

                        this.debugLog('WebSpeech onresult: results converted', {
                            originalType: typeof event.results,
                            isArrayLike: event.results && typeof event.results.length === 'number',
                            convertedLength: results.length,
                            isArray: Array.isArray(results),
                        });
                        console.log('🔍 WebSpeech onresult: results converted', {
                            ts: new Date().toISOString(),
                            originalType: typeof event.results,
                            isArrayLike: event.results && typeof event.results.length === 'number',
                            convertedLength: results.length,
                            isArray: Array.isArray(results),
                        });

                        if (results.length === 0) {
                            this.debugLog('WebSpeech onresult: empty results, skipping', {});
                            console.warn('⚠️ WebSpeech onresult: empty results, skipping', {
                                ts: new Date().toISOString(),
                            });
                            return;
                        }

                        // Verifica che resultIndex sia valido
                        const resultIndex = typeof event.resultIndex === 'number' && event.resultIndex >= 0
                            ? event.resultIndex
                            : 0;
                        const startIndex = Math.max(0, Math.min(resultIndex, results.length));

                        for (let i = startIndex; i < results.length; i++) {
                            const res = results[i];
                            if (!res || !res[0]) {
                                continue;
                            }
                            const text = res[0].transcript || '';
                            if (!text) {
                                this.debugLog('WebSpeech onresult: empty text in result', {
                                    i,
                                    isFinal: res.isFinal,
                                    hasRes0: !!res[0],
                                });
                                console.log('   ↳ chunk (empty text)', {
                                    ts: new Date().toISOString(),
                                    i,
                                    isFinal: res.isFinal,
                                    hasRes0: !!res[0],
                                });
                                continue;
                            }

                            this.debugLog('WebSpeech onresult: chunk received', {
                                i,
                                isFinal: res.isFinal,
                                transcript: text.substring(0, 100),
                                transcriptLength: text.length,
                                confidence: res[0] && typeof res[0].confidence === 'number' ? res[0].confidence : undefined,
                                isMobileLowPower: this.isMobileLowPower,
                                activeTab: this.activeTab,
                            });
                            console.log('   ↳ chunk', {
                                ts: new Date().toISOString(),
                                i,
                                isFinal: res.isFinal,
                                transcript: text.substring(0, 100),
                                transcriptLength: text.length,
                                confidence: res[0] && typeof res[0].confidence === 'number' ? res[0].confidence : undefined,
                                isMobileLowPower: this.isMobileLowPower,
                                activeTab: this.activeTab,
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

                                    // MOBILE: ora usiamo continuous=false e interimResults=false,
                                    // quindi ogni frase genera un solo final affidabile come su desktop.
                                    // Manteniamo solo la logica di merge sul testo, ma la traduzione
                                    // parte esattamente come su desktop.
                                    if (this.isMobileLowPower) {
                                        this.debugLog('WebSpeech onresult: MOBILE final (no special handling)', {
                                            text: clean.substring(0, 50),
                                            textLength: clean.length,
                                        });
                                        console.log('📱 WebSpeech onresult: MOBILE final (no special handling)', {
                                            ts: new Date().toISOString(),
                                            text: clean.substring(0, 50),
                                            textLength: clean.length,
                                        });
                                        // Lasciamo proseguire nel ramo "DESKTOP processing final"
                                    }

                                    this.debugLog('WebSpeech onresult: DESKTOP processing final', {
                                        text: clean.substring(0, 50),
                                        textLength: clean.length,
                                        activeTab: this.activeTab,
                                        youtubeAutoPauseEnabled: this.youtubeAutoPauseEnabled,
                                        isListening: this.isListening,
                                    });
                                    console.log('💻 WebSpeech onresult: DESKTOP processing final', {
                                        ts: new Date().toISOString(),
                                        text: clean.substring(0, 50),
                                        textLength: clean.length,
                                        activeTab: this.activeTab,
                                        youtubeAutoPauseEnabled: this.youtubeAutoPauseEnabled,
                                        isListening: this.isListening,
                                    });

                                    this.lastFinalOriginalAt = Date.now();
                                    this.originalConfirmed = this.originalConfirmed
                                        ? `${this.originalConfirmed}\n${phraseWithDash}`
                                        : phraseWithDash;
                                    this.originalInterim = '';

                                    // Traduzione sempre immediata a fine frase, in qualunque tab.
                                    this.debugLog('WebSpeech onresult: starting translation', {
                                        text: clean.substring(0, 50),
                                    });
                                    console.log('📤 WebSpeech onresult: starting translation', {
                                        ts: new Date().toISOString(),
                                        text: clean.substring(0, 50),
                                    });
                                    this.startTranslationStream(clean, {
                                        commit: true,
                                        mergeLast: false,
                                    });

                                    // In modalità YouTube, se il doppiaggio è disattivato,
                                    // dopo aver inviato l'audio per la trascrizione possiamo
                                    // riprendere subito il video.
                                    if (this.activeTab === 'youtube' && !this.readTranslationEnabledYoutube) {
                                        this.resumeYoutubeIfNeeded();
                                    }
                                }
                            } else {
                                // INTERIM solo su desktop / non-low-power
                                if (this.isMobileLowPower && this.activeTab === 'call') {
                                    continue;
                                }
                                interim = [interim, text.trim().toLowerCase()].filter(Boolean).join(' ');
                            }
                        }

                        this.originalInterim = interim;

                        this.debugLog('WebSpeech onresult: interim updated', {
                            interim: interim.substring(0, 50),
                            interimLength: interim.length,
                            willStartPreview: interim && !this.isMobileLowPower && this.activeTab === 'call',
                        });
                        console.log('📝 WebSpeech onresult: interim updated', {
                            ts: new Date().toISOString(),
                            interim: interim.substring(0, 50),
                            interimLength: interim.length,
                            willStartPreview: interim && !this.isMobileLowPower && this.activeTab === 'call',
                        });

                        this.$nextTick(() => {
                            this.scrollToBottom('originalBox');
                        });
                        // Mentre parli, usa l'interim per una traduzione incrementale
                        // solo su desktop e solo nella modalità "call":
                        // - su mobile low-power saltiamo lo streaming
                        // - in modalità YouTube vogliamo traduzione SOLO a fine frase
                        if (interim && !this.isMobileLowPower && this.activeTab === 'call') {
                            this.debugLog('WebSpeech onresult: starting preview translation', {
                                interim: interim.substring(0, 50),
                            });
                            console.log('🔍 WebSpeech onresult: starting preview translation', {
                                ts: new Date().toISOString(),
                                interim: interim.substring(0, 50),
                            });
                            this.maybeStartPreviewTranslation(interim);
                        }

                        this.debugLog('WebSpeech onresult END', {
                            processedResults: results.length,
                            finalCount: results.filter(r => r && r.isFinal).length,
                            interimCount: results.filter(r => r && !r.isFinal).length,
                        });
                        console.log('✅ WebSpeech onresult END', {
                            ts: new Date().toISOString(),
                            processedResults: results.length,
                            finalCount: results.filter(r => r && r.isFinal).length,
                            interimCount: results.filter(r => r && !r.isFinal).length,
                        });
                    } catch (err) {
                        this.debugLog('WebSpeech onresult: ERROR', {
                            error: String(err),
                            errorName: err?.name,
                            errorMessage: err?.message,
                        });
                        console.error('❌ WebSpeech onresult: ERROR', {
                            ts: new Date().toISOString(),
                            error: String(err),
                            errorName: err?.name,
                            errorMessage: err?.message,
                            stack: err?.stack,
                        });
                    }
                };
            } catch (e) {
                this.statusMessage = this.ui.statusMicInitError;
            }
        },

        async ensureMicPermission() {
            try {
                this.debugLog('ensureMicPermission START', {
                    hasMediaDevices: !!navigator.mediaDevices,
                    hasGetUserMedia: !!(navigator.mediaDevices && navigator.mediaDevices.getUserMedia),
                    activeTab: this.activeTab,
                    activeSpeaker: this.activeSpeaker,
                });
                console.log('🎤 ensureMicPermission START', {
                    ts: new Date().toISOString(),
                    hasMediaDevices: !!navigator.mediaDevices,
                    hasGetUserMedia: !!(navigator.mediaDevices && navigator.mediaDevices.getUserMedia),
                    activeTab: this.activeTab,
                    activeSpeaker: this.activeSpeaker,
                });

                if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                    this.debugLog('ensureMicPermission: no mediaDevices/getUserMedia', {});
                    console.warn('⚠️ ensureMicPermission: no mediaDevices/getUserMedia');
                    return true;
                }
                // Per il semplice check dei permessi microfono non vogliamo toccare
                // la configurazione audio di Android: usiamo audio:true ovunque.
                const constraints = {
                    audio: true,
                };
                this.debugLog('ensureMicPermission: calling getUserMedia', { constraints });
                console.log('🎤 ensureMicPermission: calling getUserMedia', {
                    ts: new Date().toISOString(),
                    constraints,
                });

                const stream = await navigator.mediaDevices.getUserMedia(constraints);

                this.debugLog('ensureMicPermission: getUserMedia SUCCESS', {
                    streamId: stream?.id,
                    tracksCount: stream?.getTracks()?.length || 0,
                    tracks: stream?.getTracks()?.map(t => ({
                        id: t.id,
                        kind: t.kind,
                        label: t.label,
                        enabled: t.enabled,
                        muted: t.muted,
                        readyState: t.readyState,
                    })) || [],
                });
                console.log('✅ ensureMicPermission: getUserMedia SUCCESS', {
                    ts: new Date().toISOString(),
                    streamId: stream?.id,
                    tracksCount: stream?.getTracks()?.length || 0,
                    tracks: stream?.getTracks()?.map(t => ({
                        id: t.id,
                        kind: t.kind,
                        label: t.label,
                        enabled: t.enabled,
                        muted: t.muted,
                        readyState: t.readyState,
                    })) || [],
                });

                try {
                    stream.getTracks().forEach((t) => {
                        this.debugLog('ensureMicPermission: stopping track', {
                            trackId: t.id,
                            kind: t.kind,
                        });
                        t.stop();
                    });
                } catch (err) {
                    this.debugLog('ensureMicPermission: error stopping tracks', { error: String(err) });
                    console.warn('⚠️ ensureMicPermission: error stopping tracks', err);
                }

                this.debugLog('ensureMicPermission: SUCCESS', {});
                console.log('✅ ensureMicPermission: SUCCESS', { ts: new Date().toISOString() });
                return true;
            } catch (err) {
                this.debugLog('ensureMicPermission: ERROR', {
                    error: String(err),
                    errorName: err?.name,
                    errorMessage: err?.message,
                });
                console.error('❌ ensureMicPermission: ERROR', {
                    ts: new Date().toISOString(),
                    error: String(err),
                    errorName: err?.name,
                    errorMessage: err?.message,
                    stack: err?.stack,
                });
                return false;
            }
        },

        async toggleListeningForLang(speaker) {
            this.debugLog('toggleListeningForLang START', {
                speaker,
                isTtsPlaying: this.isTtsPlaying,
                isListening: this.isListening,
                activeSpeaker: this.activeSpeaker,
                activeTab: this.activeTab,
                langA: this.langA,
                langB: this.langB,
                currentMicLang: this.currentMicLang,
                useWhisperEffective: this.useWhisperEffective,
                useGoogleEffective: this.useGoogleEffective,
            });
            console.log('🎙️ toggleListeningForLang START', {
                ts: new Date().toISOString(),
                speaker,
                isTtsPlaying: this.isTtsPlaying,
                isListening: this.isListening,
                activeSpeaker: this.activeSpeaker,
                activeTab: this.activeTab,
                langA: this.langA,
                langB: this.langB,
                currentMicLang: this.currentMicLang,
                useWhisperEffective: this.useWhisperEffective,
                useGoogleEffective: this.useGoogleEffective,
            });

            // Non registrare mentre il TTS sta leggendo
            if (this.isTtsPlaying) {
                this.debugLog('toggleListeningForLang: TTS is playing, ignore mic toggle', {
                    speaker,
                    langA: this.langA,
                    langB: this.langB,
                });
                console.log('⏸️ toggleListeningForLang: TTS is playing, ignore mic toggle', {
                    speaker,
                    langA: this.langA,
                    langB: this.langB,
                });
                return;
            }
            // Se sta già ascoltando con lo stesso speaker, ferma
            if (this.isListening && this.activeSpeaker === speaker) {
                this.debugLog('toggleListeningForLang: stop same speaker', {
                    speaker,
                    currentMicLang: this.currentMicLang,
                    activeTab: this.activeTab,
                });
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
                this.debugLog('toggleListeningForLang: switching speaker', {
                    from: this.activeSpeaker,
                    to: speaker,
                    currentMicLang: this.currentMicLang,
                });
                console.log('🔁 toggleListeningForLang: switching speaker', {
                    from: this.activeSpeaker,
                    to: speaker,
                    currentMicLang: this.currentMicLang,
                });
                this.stopListeningInternal();
                // Attendi un attimo per assicurarsi che il recognition sia fermato
                await new Promise(resolve => setTimeout(resolve, 200));
            }

            // Validazione lingue in base alla tab attiva:
            // - nella tab "call" usiamo langA/langB
            // - nella tab "youtube" usiamo youtubeLangSource / youtubeLangTarget
            if (this.activeTab === 'youtube') {
                if (!this.youtubeLangSource || !this.youtubeLangTarget) {
                    this.statusMessage = this.ui.statusYoutubeLangsMissing;
                    console.warn('⚠️ toggleListeningForLang: missing youtubeLangSource/youtubeLangTarget', {
                        speaker,
                        youtubeLangSource: this.youtubeLangSource,
                        youtubeLangTarget: this.youtubeLangTarget,
                    });
                    return;
                }
                if (this.youtubeLangSource === this.youtubeLangTarget) {
                    this.statusMessage = this.ui.statusYoutubeLangsDifferent;
                    console.warn('⚠️ toggleListeningForLang: youtubeLangSource === youtubeLangTarget', {
                        speaker,
                        youtubeLangSource: this.youtubeLangSource,
                        youtubeLangTarget: this.youtubeLangTarget,
                    });
                    return;
                }
            } else {
                // Modalità interprete standard: serve solo la lingua dell'utente (langB)
                if (!this.langB) {
                    this.statusMessage = this.ui.statusLangPairMissing;
                    console.warn('⚠️ toggleListeningForLang: missing langB (user language)', {
                        speaker,
                        langB: this.langB,
                    });
                    return;
                }
            }

            const ok = await this.ensureMicPermission();
            if (!ok) {
                this.statusMessage = this.ui.statusMicDenied;
                this.debugLog('toggleListeningForLang: mic permission denied', { speaker });
                console.warn('⚠️ toggleListeningForLang: mic permission denied', {
                    speaker,
                });
                return;
            }

            // Imposta lingua di input e di destinazione in base al parlante
            this.activeSpeaker = speaker;
            if (this.activeTab === 'youtube') {
                // Nella tab YouTube usiamo le lingue specifiche del video
                if (speaker === 'A') {
                    const srcObj = this.availableLanguages.find(l => l.code === this.youtubeLangSource);
                    if (srcObj) {
                        this.currentMicLang = srcObj.micCode;
                        this.currentTargetLang = this.youtubeLangTarget;
                    }
                } else {
                    const tgtObj = this.availableLanguages.find(l => l.code === this.youtubeLangTarget);
                    if (tgtObj) {
                        this.currentMicLang = tgtObj.micCode;
                        this.currentTargetLang = this.youtubeLangSource;
                    }
                }
            } else {
                // Nella tab "call" usiamo un solo microfono:
                // - la lingua sorgente viene auto-rilevata dal motore (Whisper)
                // - la lingua di destinazione è SEMPRE langB (lingua dell'utente)
                const langBObj = this.availableLanguages.find(l => l.code === this.langB);
                if (langBObj) {
                    this.currentTargetLang = this.langB;
                    // currentMicLang lo lasciamo vuoto per indicare "auto"
                    this.currentMicLang = '';
                }
            }

            this.debugLog('toggleListeningForLang: language set', {
                speaker,
                currentMicLang: this.currentMicLang,
                currentTargetLang: this.currentTargetLang,
                activeTab: this.activeTab,
            });
            console.log('🌐 toggleListeningForLang: language set', {
                ts: new Date().toISOString(),
                speaker,
                currentMicLang: this.currentMicLang,
                currentTargetLang: this.currentTargetLang,
                activeTab: this.activeTab,
            });

            if (!this.recognition) {
                this.debugLog('toggleListeningForLang: initializing recognition', {
                    speaker,
                    currentMicLang: this.currentMicLang,
                });
                console.log('🔧 toggleListeningForLang: initializing recognition', {
                    ts: new Date().toISOString(),
                    speaker,
                    currentMicLang: this.currentMicLang,
                });
                this.initSpeechRecognition();
                if (!this.recognition) {
                    this.debugLog('toggleListeningForLang: initSpeechRecognition failed', {
                        speaker,
                        currentMicLang: this.currentMicLang,
                    });
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
                this.debugLog('toggleListeningForLang: stopping running recognition', {
                    speaker,
                    wasRunning,
                });
                console.log('🛑 toggleListeningForLang: stopping running recognition', {
                    ts: new Date().toISOString(),
                    speaker,
                    wasRunning,
                });
                try {
                    this.recognition.stop();
                    this.recognition.abort && this.recognition.abort();
                } catch (err) {
                    this.debugLog('toggleListeningForLang: error stopping recognition', {
                        error: String(err),
                    });
                    console.warn('⚠️ toggleListeningForLang: error stopping recognition', err);
                }
                // Attendi che si fermi completamente
                await new Promise(resolve => setTimeout(resolve, 300));
            }

            if (this.recognition) {
                this.recognition.lang = this.currentMicLang;

                // Comunica al wrapper Whisper quali sono le lingue consentite
                // per il riconoscimento: il backend userà questa whitelist
                // per bloccare trascrizioni in lingue diverse.
                try {
                    const allowed = [];
                    if (this.activeTab === 'youtube') {
                        if (this.youtubeLangSource) {
                            allowed.push(this.youtubeLangSource.toLowerCase());
                        }
                        if (this.youtubeLangTarget && this.youtubeLangTarget !== this.youtubeLangSource) {
                            allowed.push(this.youtubeLangTarget.toLowerCase());
                        }
                    } else {
                        if (this.langA) {
                            allowed.push(this.langA.toLowerCase());
                        }
                        if (this.langB && this.langB !== this.langA) {
                            allowed.push(this.langB.toLowerCase());
                        }
                    }
                    if (this.recognition && typeof this.recognition === 'object') {
                        this.recognition.allowedLangs = allowed;
                    }
                    this.debugLog('toggleListeningForLang: allowedLangs set on recognition', {
                        allowedLangs: allowed,
                        activeTab: this.activeTab,
                    });
                    console.log('🌐 toggleListeningForLang: allowedLangs set on recognition', {
                        ts: new Date().toISOString(),
                        allowedLangs: allowed,
                        activeTab: this.activeTab,
                    });
                } catch {
                    // silenzioso: se fallisce, semplicemente non settiamo la whitelist
                }

                this.debugLog('toggleListeningForLang: recognition.lang set', {
                    lang: this.recognition.lang,
                    currentMicLang: this.currentMicLang,
                });
                console.log('🌐 toggleListeningForLang: recognition.lang set', {
                    ts: new Date().toISOString(),
                    lang: this.recognition.lang,
                    currentMicLang: this.currentMicLang,
                });
            }

            try {
                // Suggerisci al motore di riconoscimento se stai ascoltando
                // la voce diretta al microfono o audio proveniente dalle casse (YouTube).
                if (this.recognition && typeof this.recognition === 'object') {
                    this.recognition.sourceHint = this.activeTab === 'youtube' ? 'speaker' : 'mic';
                    this.debugLog('toggleListeningForLang: sourceHint set', {
                        sourceHint: this.recognition.sourceHint,
                        activeTab: this.activeTab,
                    });
                    console.log('🎯 toggleListeningForLang: sourceHint set', {
                        ts: new Date().toISOString(),
                        sourceHint: this.recognition.sourceHint,
                        activeTab: this.activeTab,
                    });
                }

                this.isListening = true;
                const isBackendEngine = this.useWhisperEffective;
                if (isBackendEngine && this.recognition && typeof this.recognition === 'object') {
                    // Manteniamo sempre la modalità single-segment:
                    // il backend riceve l'audio solo quando si spegne esplicitamente il microfono.
                    this.recognition.singleSegmentMode = !!this.whisperSendOnStopOnlyEffective;

                    // Propaga al wrapper anche la soglia di silenzio (in ms) configurata a livello di UI.
                    // Default leggero: 600ms se non è stato ancora mosso lo slider.
                    let silenceMs = 600;
                    if (typeof this.whisperSilenceMs === 'number' && this.whisperSilenceMs > 0) {
                        silenceMs = this.whisperSilenceMs;
                    }
                    if (Object.prototype.hasOwnProperty.call(this.recognition, '_silenceMs')) {
                        this.recognition._silenceMs = silenceMs;
                    }

                    // Configura la callback di auto-pausa basata sul silenzio: simula il click
                    // sul bottone pausa (stopListeningInternal) senza cambiare il flusso:
                    // lo stop esplicito del mic è sempre ciò che scatena la trascrizione.
                    if ('onAutoPause' in this.recognition) {
                        const self = this;
                        const tab = this.activeTab;
                        const callEnabled = this.callAutoPauseEnabled;
                        const ytEnabled = this.youtubeAutoPauseEnabled;

                        if ((tab === 'call' && callEnabled) || (tab === 'youtube' && ytEnabled)) {
                            this.recognition.onAutoPause = function () {
                                try {
                                    if (!self.isListening) {
                                        return;
                                    }

                                    const speakerBefore = self.activeSpeaker;
                                    const tabBefore = self.activeTab;

                                    // In modalità call distinguiamo due casi:
                                    // - TTS disattivato → auto-riaccendi subito il mic dopo lo stop (gestito in onend)
                                    // - TTS attivo      → riaccendi il mic solo dopo la lettura TTS
                                    if (tabBefore === 'call') {
                                        if (!self.readTranslationEnabledCall) {
                                            self.pendingAutoResumeSpeaker = speakerBefore;
                                        } else {
                                            self.pendingAutoResumeSpeakerAfterTts = speakerBefore;
                                        }
                                    }

                                    self.debugLog('Whisper onAutoPause: auto-stopping listening due to silence', {
                                        activeTab: tabBefore,
                                        activeSpeaker: speakerBefore,
                                        silenceMs,
                                    });
                                    self.stopListeningInternal();
                                } catch {
                                    // ignora errori nel layer superiore
                                }
                            };
                        } else {
                            this.recognition.onAutoPause = null;
                        }
                    }

                    this.debugLog('toggleListeningForLang: singleSegmentMode/silenceMs/onAutoPause set', {
                        singleSegmentMode: this.recognition.singleSegmentMode,
                        whisperSendOnStopOnlyEffective: this.whisperSendOnStopOnlyEffective,
                        isMobileLowPower: this.isMobileLowPower,
                        silenceMs,
                        callAutoPauseEnabled: this.callAutoPauseEnabled,
                        youtubeAutoPauseEnabled: this.youtubeAutoPauseEnabled,
                    });
                    console.log('⚙️ toggleListeningForLang: singleSegmentMode/silenceMs/onAutoPause set', {
                        ts: new Date().toISOString(),
                        singleSegmentMode: this.recognition.singleSegmentMode,
                        whisperSendOnStopOnlyEffective: this.whisperSendOnStopOnlyEffective,
                        isMobileLowPower: this.isMobileLowPower,
                        silenceMs,
                        callAutoPauseEnabled: this.callAutoPauseEnabled,
                        youtubeAutoPauseEnabled: this.youtubeAutoPauseEnabled,
                    });
                }

                this.debugLog('toggleListeningForLang: calling recognition.start()', {
                    speaker,
                    langSetOnRecognition: this.recognition.lang,
                    currentMicLang: this.currentMicLang,
                    activeTab: this.activeTab,
                    isBackendEngine,
                    singleSegmentMode: isBackendEngine && this.recognition && typeof this.recognition === 'object' ? this.recognition.singleSegmentMode : undefined,
                });
                console.log('▶️ toggleListeningForLang: calling recognition.start()', {
                    ts: new Date().toISOString(),
                    speaker,
                    langSetOnRecognition: this.recognition.lang,
                    currentMicLang: this.currentMicLang,
                    activeTab: this.activeTab,
                    isBackendEngine,
                    singleSegmentMode: isBackendEngine && this.recognition && typeof this.recognition === 'object' ? this.recognition.singleSegmentMode : undefined,
                });
                this.recognition.start();
                this.debugLog('toggleListeningForLang: recognition.start() called', {
                    speaker,
                });
                console.log('✅ toggleListeningForLang: recognition.start() called', {
                    ts: new Date().toISOString(),
                    speaker,
                });

                // In modalità YouTube, speaker A: dopo aver acceso il microfono,
                // avvia il video con un leggero delay per rispettare le policy mobile.
                if (this.activeTab === 'youtube' && this.activeSpeaker === 'A') {
                    try {
                        setTimeout(() => {
                            try {
                                if (this.youtubePlayer && typeof this.youtubePlayer.playVideo === 'function') {
                                    this.youtubePlayer.playVideo();
                                }
                            } catch {
                                // ignora errori del player
                            }
                        }, 200);
                    } catch {
                        // ignora errori del timer
                    }
                }
            } catch (e) {
                this.debugLog('toggleListeningForLang: ERROR calling recognition.start()', {
                    error: String(e),
                    errorName: e?.name,
                    errorMessage: e?.message,
                    speaker,
                });
                console.error('❌ toggleListeningForLang: ERROR calling recognition.start()', {
                    ts: new Date().toISOString(),
                    error: String(e),
                    errorName: e?.name,
                    errorMessage: e?.message,
                    speaker,
                    stack: e?.stack,
                });
                this.statusMessage = this.ui.statusMicStartError;
                this.isListening = false;
                this.activeSpeaker = null;
            }
        },

        stopListeningInternal() {
            const wasListening = this.isListening;
            const wasActiveSpeaker = this.activeSpeaker;
            const isBackendEngine = this.useWhisperEffective || this.useGoogleEffective;
            const singleSegmentMode = isBackendEngine && this.recognition && typeof this.recognition === 'object'
                ? this.recognition.singleSegmentMode
                : false;

            this.debugLog('stopListeningInternal START', {
                wasListening,
                activeSpeaker: wasActiveSpeaker,
                activeTab: this.activeTab,
                hasRecognition: !!this.recognition,
                isBackendEngine,
                singleSegmentMode,
                useWhisperEffective: this.useWhisperEffective,
                useGoogleEffective: this.useGoogleEffective,
                whisperSendOnStopOnlyEffective: this.whisperSendOnStopOnlyEffective,
            });
            console.log('🛑 stopListeningInternal START', {
                ts: new Date().toISOString(),
                wasListening,
                activeSpeaker: wasActiveSpeaker,
                activeTab: this.activeTab,
                hasRecognition: !!this.recognition,
                isBackendEngine,
                singleSegmentMode,
                useWhisperEffective: this.useWhisperEffective,
                useGoogleEffective: this.useGoogleEffective,
                whisperSendOnStopOnlyEffective: this.whisperSendOnStopOnlyEffective,
            });

            this.isListening = false;
            this.activeSpeaker = null;

            // Su mobile low-power: se c'è una frase in sospeso (pendingMobileOriginalText),
            // traduciamola una sola volta quando l'utente spegne il microfono.
            if (this.isMobileLowPower && this.pendingMobileOriginalText && this.mobileCurrentTranslationIndex !== null) {
                const pendingText = this.pendingMobileOriginalText;
                const pendingIndex = this.mobileCurrentTranslationIndex;
                this.pendingMobileOriginalText = '';

                this.debugLog('stopListeningInternal: MOBILE translating pending phrase on stop', {
                    pendingText: pendingText.substring(0, 50),
                    mobileCurrentTranslationIndex: pendingIndex,
                });
                console.log('📝 stopListeningInternal: MOBILE translating pending phrase on stop', {
                    ts: new Date().toISOString(),
                    pendingText: pendingText.substring(0, 50),
                    mobileCurrentTranslationIndex: pendingIndex,
                });

                this.startTranslationStream(pendingText, {
                    commit: true,
                    mergeLast: false,
                    mergeIndex: pendingIndex,
                    shouldEnqueueTts: true,
                });
            }

            if (this.recognition) {
                try {
                    const isBackendEngine = this.useWhisperEffective;
                    const singleSegmentMode = isBackendEngine && this.recognition && typeof this.recognition === 'object'
                        ? this.recognition.singleSegmentMode
                        : false;

                    const stopRecognition = () => {
                        try {
                            this.debugLog('stopListeningInternal: calling recognition.stop()', {
                                recognitionLang: this.recognition?.lang,
                                singleSegmentMode,
                                isBackendEngine,
                                wasListening,
                            });
                            console.log('🛑 stopListeningInternal: calling recognition.stop()', {
                                ts: new Date().toISOString(),
                                recognitionLang: this.recognition?.lang,
                                singleSegmentMode,
                                isBackendEngine,
                                wasListening,
                            });
                            if (this.recognition) {
                                this.recognition.stop();
                                if (this.recognition.abort) {
                                    this.recognition.abort();
                                }
                            }
                            this.debugLog('stopListeningInternal: recognition.stop() called successfully', {});
                            console.log('✅ stopListeningInternal: recognition.stop() called successfully', {
                                ts: new Date().toISOString(),
                            });
                        } catch (err) {
                            this.debugLog('stopListeningInternal: error stopping recognition', {
                                error: String(err),
                                errorName: err?.name,
                                errorMessage: err?.message,
                            });
                            console.error('❌ stopListeningInternal: error stopping recognition', {
                                ts: new Date().toISOString(),
                                error: String(err),
                                errorName: err?.name,
                                errorMessage: err?.message,
                                stack: err?.stack,
                            });
                        }
                    };

                    // In modalità YouTube, speaker A: ferma prima il video, poi dopo 200ms spegni il microfono.
                    if (this.activeTab === 'youtube' && wasActiveSpeaker === 'A') {
                        try {
                            if (this.youtubePlayer && typeof this.youtubePlayer.pauseVideo === 'function') {
                                this.youtubePlayer.pauseVideo();
                            }
                        } catch {
                            // ignora errori del player
                        }
                        try {
                            setTimeout(() => {
                                stopRecognition();
                            }, 200);
                        } catch {
                            stopRecognition();
                        }
                    } else {
                        stopRecognition();
                    }
                } catch (err) {
                    this.debugLog('stopListeningInternal: error preparing recognition stop', {
                        error: String(err),
                        errorName: err?.name,
                        errorMessage: err?.message,
                    });
                    console.error('❌ stopListeningInternal: error preparing recognition stop', {
                        ts: new Date().toISOString(),
                        error: String(err),
                        errorName: err?.name,
                        errorMessage: err?.message,
                        stack: err?.stack,
                    });
                }
            }

            this.debugLog('stopListeningInternal: DONE', {
                isListening: this.isListening,
                activeSpeaker: this.activeSpeaker,
            });
            console.log('✅ stopListeningInternal: DONE', {
                ts: new Date().toISOString(),
                isListening: this.isListening,
                activeSpeaker: this.activeSpeaker,
            });
        },

        startTranslationStream(textSegment, options = { commit: true, mergeLast: false, mergeIndex: null, shouldEnqueueTts: true, addDash: true }) {
            const safeText = ((textSegment || '').trim());
            if (!safeText) {
                this.debugLog('startTranslationStream: empty text, skipping', {
                    textSegment: textSegment?.substring(0, 50),
                });
                console.log('⚠️ startTranslationStream: empty text, skipping', {
                    ts: new Date().toISOString(),
                    textSegment: textSegment?.substring(0, 50),
                });
                return;
            }

            const commit = options && typeof options.commit === 'boolean' ? options.commit : true;
            const mergeLast = options && typeof options.mergeLast === 'boolean' ? options.mergeLast : false;
            const mergeIndex = options && typeof options.mergeIndex === 'number' ? options.mergeIndex : null;
            const shouldEnqueueTts = options && typeof options.shouldEnqueueTts === 'boolean' ? options.shouldEnqueueTts : true;
            const addDash = options && typeof options.addDash === 'boolean' ? options.addDash : true;

            this.debugLog('startTranslationStream START', {
                text: safeText.substring(0, 100),
                commit,
                mergeLast,
                mergeIndex,
                shouldEnqueueTts,
                activeTab: this.activeTab,
                isListening: this.isListening,
                youtubeAutoPauseEnabled: this.youtubeAutoPauseEnabled,
                currentTargetLang: this.currentTargetLang,
                currentMicLang: this.currentMicLang,
            });
            console.log('📤 startTranslationStream START', {
                ts: new Date().toISOString(),
                text: safeText.substring(0, 100),
                commit,
                mergeLast,
                mergeIndex,
                activeTab: this.activeTab,
                isListening: this.isListening,
                youtubeAutoPauseEnabled: this.youtubeAutoPauseEnabled,
                currentTargetLang: this.currentTargetLang,
                currentMicLang: this.currentMicLang,
            });

            // NOTA: la pausa del video YouTube è ora gestita SOLO quando il microfono viene spento
            // (stopListeningInternal), per evitare interazioni imprevedibili tra WebSpeech,
            // YouTube player e TTS, soprattutto su mobile. Qui non tocchiamo più il player.

            // Se è già attivo uno stream:
            // - per le preview (commit: false) ignoriamo la nuova richiesta;
            // - per le frasi finali (commit: true) mettiamo in coda la richiesta
            //   così non perdiamo nessuna traduzione, ma le elaboriamo in sequenza.
            if (this.currentStream) {
                if (commit) {
                    this.debugLog('startTranslationStream: queueing commit while another stream is active', {
                        queuedTextPreview: safeText.substring(0, 80),
                        queueLength: (this.pendingTranslationQueue && this.pendingTranslationQueue.length) || 0,
                    });
                    console.log('⏳ startTranslationStream: queueing commit while another stream is active', {
                        ts: new Date().toISOString(),
                        queuedTextPreview: safeText.substring(0, 80),
                        queueLength: (this.pendingTranslationQueue && this.pendingTranslationQueue.length) || 0,
                    });
                    this.pendingTranslationQueue.push({
                        text: safeText,
                        options: {
                            commit,
                            mergeLast,
                            mergeIndex,
                            shouldEnqueueTts,
                            addDash,
                        },
                    });
                    return;
                } else {
                    // Se è solo una preview (commit: false), ignora
                    this.debugLog('startTranslationStream: preview request, ignoring (stream already active)', {});
                    console.log('⏭️ startTranslationStream: preview request, ignoring (stream already active)', {
                        ts: new Date().toISOString(),
                    });
                    return;
                }
            }

            // Assicurati che currentTargetLang sia sempre impostato correttamente
            // Nella modalità call: la lingua sorgente è auto-rilevata, target è sempre langB
            if (!this.currentTargetLang && this.langB) {
                this.currentTargetLang = this.langB;
                this.debugLog('startTranslationStream: auto-set currentTargetLang', {
                    currentTargetLang: this.currentTargetLang,
                    langB: this.langB,
                    currentMicLang: this.currentMicLang,
                });
                console.log('🌐 startTranslationStream: auto-set currentTargetLang', {
                    ts: new Date().toISOString(),
                    currentTargetLang: this.currentTargetLang,
                    langB: this.langB,
                    currentMicLang: this.currentMicLang,
                });
            }

            const targetLang = this.currentTargetLang || this.langB || 'en';

            // Genera thread_id se non esiste ancora
            if (!this.translationThreadId) {
                this.translationThreadId = 'translation_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
                this.debugLog('startTranslationStream: generated new translationThreadId', {
                    translationThreadId: this.translationThreadId,
                });
                console.log('🆔 startTranslationStream: generated new translationThreadId', {
                    ts: new Date().toISOString(),
                    translationThreadId: this.translationThreadId,
                });
            }

            const params = new URLSearchParams({
                text: safeText,
                source_lang: this.currentMicLang || '',
                locale: this.locale || 'it',
                target_lang: targetLang,
                thread_id: this.translationThreadId,
                ts: String(Date.now()),
            });

            const origin = window.location.origin;
            const endpoint = `/api/chatbot/translator-stream?${params.toString()}`;

            this.debugLog('startTranslationStream: creating EventSource', {
                endpoint,
                text: safeText.substring(0, 50),
                targetLang,
                sourceLang: this.currentMicLang,
                threadId: this.translationThreadId,
            });
            console.log(`📤 Traduzione richiesta: "${safeText.substring(0, 50)}..." → target_lang: ${targetLang}, source_lang: ${this.currentMicLang}`, {
                ts: new Date().toISOString(),
                endpoint,
                threadId: this.translationThreadId,
            });

            try {
                const es = new EventSource(`${origin}${endpoint}`);
                this.currentStream = es;
                let buffer = '';

                this.debugLog('startTranslationStream: EventSource created', {
                    readyState: es.readyState,
                });
                console.log('✅ startTranslationStream: EventSource created', {
                    ts: new Date().toISOString(),
                    readyState: es.readyState,
                });

                es.addEventListener('open', () => {
                    this.debugLog('startTranslationStream: EventSource opened', {
                        readyState: es.readyState,
                    });
                    console.log('✅ startTranslationStream: EventSource opened', {
                        ts: new Date().toISOString(),
                        readyState: es.readyState,
                    });
                });

                es.addEventListener('message', (e) => {
                    try {
                        const data = JSON.parse(e.data);
                        if (data.token) {
                            buffer += data.token;

                            this.debugLog('startTranslationStream: token received', {
                                token: data.token,
                                bufferLength: buffer.length,
                                bufferPreview: buffer.substring(0, 50),
                            });
                            console.log('📥 startTranslationStream: token received', {
                                ts: new Date().toISOString(),
                                token: data.token,
                                bufferLength: buffer.length,
                                bufferPreview: buffer.substring(0, 50),
                            });

                            // Su desktop in modalità "call": aggiorna in streaming token-per-token.
                            // In modalità YouTube o mobile low-power: nessuno streaming token-per-token,
                            // usiamo solo l'evento "done" per aggiornare la UI.
                            if (!this.isMobileLowPower && this.activeTab === 'call') {
                                this.updateTranslationTokens(buffer);
                                this.$nextTick(() => {
                                    this.scrollToBottom('translationBox');
                                });
                            }
                        }
                    } catch (err) {
                        this.debugLog('startTranslationStream: error parsing message', {
                            error: String(err),
                            data: e.data?.substring(0, 100),
                        });
                        console.error('❌ startTranslationStream: error parsing message', {
                            ts: new Date().toISOString(),
                            error: String(err),
                            data: e.data?.substring(0, 100),
                        });
                    }
                });

                es.addEventListener('done', () => {
                    try {
                        es.close();
                    } catch (err) {
                        this.debugLog('startTranslationStream: error closing EventSource on done', {
                            error: String(err),
                        });
                        console.warn('⚠️ startTranslationStream: error closing EventSource on done', err);
                    }
                    const segment = buffer.trim();
                    this.debugLog('startTranslationStream: done event', {
                        segment: segment.substring(0, 100),
                        commit,
                        mergeLast,
                        isMobileLowPower: this.isMobileLowPower,
                        mergeIndex,
                        segmentsCountBefore: (this.translationSegments && this.translationSegments.length) || 0,
                        bufferLength: buffer.length,
                    });
                    console.log('✅ startTranslationStream: done event', {
                        ts: new Date().toISOString(),
                        segment: segment.substring(0, 100),
                        commit,
                        mergeLast,
                        isMobileLowPower: this.isMobileLowPower,
                        mergeIndex,
                        segmentsCountBefore: (this.translationSegments && this.translationSegments.length) || 0,
                        bufferLength: buffer.length,
                    });
                    if (commit && segment) {
                        // Decidi come visualizzare il segmento nella lista fissa:
                        // - se addDash è true e il modello NON ha già messo un bullet all'inizio,
                        //   aggiungi un "- " davanti;
                        // - se il testo incollato dall'utente aveva già i trattini, il modello in genere
                        //   li mantiene, quindi NON ne aggiungiamo altri.
                        let displaySegment = segment;
                        if (addDash && !/^\s*[-–•*]/.test(segment)) {
                            displaySegment = `- ${segment}`;
                        }
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
                                displaySegment
                            );
                            this.debugLog('startTranslationStream: merged at index', {
                                mergeIndex,
                                segment: segment.substring(0, 50),
                            });
                            console.log('🔄 startTranslationStream: merged at index', {
                                ts: new Date().toISOString(),
                                mergeIndex,
                                segment: segment.substring(0, 50),
                            });
                        } else if (mergeLast && this.translationSegments && this.translationSegments.length > 0) {
                            this.translationSegments.splice(
                                this.translationSegments.length - 1,
                                1,
                                displaySegment
                            );
                            this.debugLog('startTranslationStream: merged last', {
                                segment: segment.substring(0, 50),
                            });
                            console.log('🔄 startTranslationStream: merged last', {
                                ts: new Date().toISOString(),
                                segment: segment.substring(0, 50),
                            });
                        } else {
                            this.translationSegments.push(displaySegment);
                            this.debugLog('startTranslationStream: added new segment', {
                                segment: segment.substring(0, 50),
                                totalSegments: this.translationSegments.length,
                            });
                            console.log('➕ startTranslationStream: added new segment', {
                                ts: new Date().toISOString(),
                                segment: segment.substring(0, 50),
                                totalSegments: this.translationSegments.length,
                            });
                        }

                        // Se il doppiaggio è attivo nella tab corrente, metti in coda la traduzione per il TTS.
                        // Su mobile low-power possiamo decidere di non leggere le prime versioni brevi della frase
                        // (vedi shouldEnqueueTts negli options).
                        if (this.readTranslationEnabledEffective && shouldEnqueueTts) {
                            this.debugLog('startTranslationStream: enqueueing for TTS', {
                                segment: segment.substring(0, 50),
                                targetLang,
                            });
                            console.log('🔊 startTranslationStream: enqueueing for TTS', {
                                ts: new Date().toISOString(),
                                segment: segment.substring(0, 50),
                                targetLang,
                            });
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

                    // Se ci sono traduzioni finali in coda, avviane la prossima
                    if (this.pendingTranslationQueue && this.pendingTranslationQueue.length > 0) {
                        const next = this.pendingTranslationQueue.shift();
                        if (next && next.text) {
                            this.debugLog('startTranslationStream: starting queued translation', {
                                textPreview: next.text.substring(0, 80),
                                remainingQueue: this.pendingTranslationQueue.length,
                            });
                            console.log('⏭️ startTranslationStream: starting queued translation', {
                                ts: new Date().toISOString(),
                                textPreview: next.text.substring(0, 80),
                                remainingQueue: this.pendingTranslationQueue.length,
                            });
                            this.startTranslationStream(next.text, next.options || {});
                        }
                    }
                });

                es.addEventListener('error', (e) => {
                    this.debugLog('startTranslationStream: EventSource error', {
                        readyState: es.readyState,
                        error: e?.type || 'unknown',
                    });
                    console.error('❌ startTranslationStream: EventSource error', {
                        ts: new Date().toISOString(),
                        readyState: es.readyState,
                        error: e?.type || 'unknown',
                    });
                    try {
                        es.close();
                    } catch (err) {
                        this.debugLog('startTranslationStream: error closing EventSource on error', {
                            error: String(err),
                        });
                        console.warn('⚠️ startTranslationStream: error closing EventSource on error', err);
                    }
                    this.currentStream = null;

                    // In caso di errore, prova comunque a processare gli eventuali elementi in coda
                    if (this.pendingTranslationQueue && this.pendingTranslationQueue.length > 0) {
                        const next = this.pendingTranslationQueue.shift();
                        if (next && next.text) {
                            this.debugLog('startTranslationStream: starting queued translation after error', {
                                textPreview: next.text.substring(0, 80),
                                remainingQueue: this.pendingTranslationQueue.length,
                            });
                            console.log('⏭️ startTranslationStream: starting queued translation after error', {
                                ts: new Date().toISOString(),
                                textPreview: next.text.substring(0, 80),
                                remainingQueue: this.pendingTranslationQueue.length,
                            });
                            this.startTranslationStream(next.text, next.options || {});
                        }
                    }
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
            if (!safe) {
                this.debugLog('enqueueTranslationForTts: empty text, skipping', {});
                console.log('⚠️ enqueueTranslationForTts: empty text, skipping', {
                    ts: new Date().toISOString(),
                });
                return;
            }

            const locale = this.getLocaleForLangCode(langCode || this.currentTargetLang || this.langB || 'en');

            this.debugLog('enqueueTranslationForTts: adding to queue', {
                text: safe.substring(0, 50),
                locale,
                queueLengthBefore: this.ttsQueue.length,
            });
            console.log('🔊 enqueueTranslationForTts: adding to queue', {
                ts: new Date().toISOString(),
                text: safe.substring(0, 50),
                locale,
                queueLengthBefore: this.ttsQueue.length,
            });

            this.ttsQueue.push({
                text: safe,
                locale,
            });

            this.processTtsQueue();
        },

        async processTtsQueue() {
            this.debugLog('processTtsQueue START', {
                isTtsPlaying: this.isTtsPlaying,
                queueLength: this.ttsQueue.length,
                activeTab: this.activeTab,
            });
            console.log('🔊 processTtsQueue START', {
                ts: new Date().toISOString(),
                isTtsPlaying: this.isTtsPlaying,
                queueLength: this.ttsQueue.length,
                activeTab: this.activeTab,
            });

            if (this.isTtsPlaying) {
                this.debugLog('processTtsQueue: TTS already playing, skipping', {});
                console.log('⏸️ processTtsQueue: TTS already playing, skipping', {
                    ts: new Date().toISOString(),
                });
                return;
            }

            const next = this.ttsQueue.shift();
            if (!next) {
                this.debugLog('processTtsQueue: queue empty, exiting', {});
                console.log('✅ processTtsQueue: queue empty, exiting', {
                    ts: new Date().toISOString(),
                });
                return;
            }

            this.isTtsPlaying = true;
            this.debugLog('processTtsQueue: processing item', {
                text: next.text.substring(0, 50),
                locale: next.locale,
                wasListening: this.isListening,
                activeSpeaker: this.activeSpeaker,
            });
            console.log('🔊 processTtsQueue: processing item', {
                ts: new Date().toISOString(),
                text: next.text.substring(0, 50),
                locale: next.locale,
                wasListening: this.isListening,
                activeSpeaker: this.activeSpeaker,
            });

            // In modalità YouTube NON tocchiamo più il player da qui:
            // la pausa del video è gestita solo quando il microfono viene spento
            // (stopListeningInternal). Questo evita doppie pause e comportamenti
            // imprevedibili dopo la prima traduzione, soprattutto su mobile.

            // Se il microfono è attivo, mettilo in pausa mentre il TTS parla
            this.wasListeningBeforeTts = this.isListening;
            this.lastSpeakerBeforeTts = this.activeSpeaker;
            if (this.wasListeningBeforeTts) {
                this.debugLog('processTtsQueue: stopping listening for TTS', {
                    wasListening: this.wasListeningBeforeTts,
                    lastSpeaker: this.lastSpeakerBeforeTts,
                });
                console.log('🛑 processTtsQueue: stopping listening for TTS', {
                    ts: new Date().toISOString(),
                    wasListening: this.wasListeningBeforeTts,
                    lastSpeaker: this.lastSpeakerBeforeTts,
                });
                this.stopListeningInternal();
            }

            try {
                this.debugLog('processTtsQueue: fetching TTS audio', {
                    text: next.text.substring(0, 50),
                    locale: next.locale,
                });
                console.log('📥 processTtsQueue: fetching TTS audio', {
                    ts: new Date().toISOString(),
                    text: next.text.substring(0, 50),
                    locale: next.locale,
                });
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
                    this.debugLog('processTtsQueue: TTS fetch failed', {
                        status: res.status,
                        statusText: res.statusText,
                    });
                    console.error('❌ processTtsQueue: TTS fetch failed', {
                        ts: new Date().toISOString(),
                        status: res.status,
                        statusText: res.statusText,
                    });
                    // Se fallisce, passa oltre senza bloccare la coda
                    this.isTtsPlaying = false;
                    this.processTtsQueue();
                    return;
                }

                this.debugLog('processTtsQueue: TTS fetch success, creating audio', {
                    contentType: res.headers.get('content-type'),
                });
                console.log('✅ processTtsQueue: TTS fetch success, creating audio', {
                    ts: new Date().toISOString(),
                    contentType: res.headers.get('content-type'),
                });
                const blob = await res.blob();
                const url = URL.createObjectURL(blob);
                const audio = new Audio(url);

                audio.onended = () => {
                    this.debugLog('processTtsQueue: audio playback ended', {
                        shouldResume: this.wasListeningBeforeTts,
                        speaker: this.lastSpeakerBeforeTts,
                        activeTab: this.activeTab,
                        youtubeAutoResumeEnabled: this.youtubeAutoResumeEnabled,
                    });
                    console.log('✅ processTtsQueue: audio playback ended', {
                        ts: new Date().toISOString(),
                        shouldResume: this.wasListeningBeforeTts,
                        speaker: this.lastSpeakerBeforeTts,
                        activeTab: this.activeTab,
                        youtubeAutoResumeEnabled: this.youtubeAutoResumeEnabled,
                    });
                    URL.revokeObjectURL(url);
                    const shouldResume = this.wasListeningBeforeTts;
                    const speaker = this.lastSpeakerBeforeTts;
                    this.wasListeningBeforeTts = false;
                    this.lastSpeakerBeforeTts = null;
                    this.isTtsPlaying = false;

                    if (this.activeTab === 'youtube') {
                        if (this.youtubeAutoResumeEnabled) {
                            this.debugLog('processTtsQueue: resuming YouTube listening', {});
                            console.log('▶️ processTtsQueue: resuming YouTube listening', {
                                ts: new Date().toISOString(),
                            });
                            this.toggleListeningForLang('A');
                        }
                    } else if (this.activeTab === 'call' && this.pendingAutoResumeSpeakerAfterTts) {
                        const resumeSpeaker = this.pendingAutoResumeSpeakerAfterTts;
                        this.pendingAutoResumeSpeakerAfterTts = null;

                        this.debugLog('processTtsQueue: resuming CALL listening after TTS (auto-pause)', {
                            speaker: resumeSpeaker,
                        });
                        console.log('▶️ processTtsQueue: resuming CALL listening after TTS (auto-pause)', {
                            ts: new Date().toISOString(),
                            speaker: resumeSpeaker,
                        });
                        this.toggleListeningForLang(resumeSpeaker);
                    } else if (shouldResume && speaker) {
                        this.debugLog('processTtsQueue: resuming listening', {
                            speaker,
                        });
                        console.log('▶️ processTtsQueue: resuming listening', {
                            ts: new Date().toISOString(),
                            speaker,
                        });
                        this.toggleListeningForLang(speaker);
                    }

                    this.processTtsQueue();
                };

                audio.onerror = (err) => {
                    this.debugLog('processTtsQueue: audio playback error', {
                        error: String(err),
                        shouldResume: this.wasListeningBeforeTts,
                        speaker: this.lastSpeakerBeforeTts,
                    });
                    console.error('❌ processTtsQueue: audio playback error', {
                        ts: new Date().toISOString(),
                        error: String(err),
                        shouldResume: this.wasListeningBeforeTts,
                        speaker: this.lastSpeakerBeforeTts,
                    });
                    URL.revokeObjectURL(url);
                    const shouldResume = this.wasListeningBeforeTts;
                    const speaker = this.lastSpeakerBeforeTts;
                    this.wasListeningBeforeTts = false;
                    this.lastSpeakerBeforeTts = null;
                    this.isTtsPlaying = false;

                    if (this.activeTab === 'youtube') {
                        if (this.youtubeAutoResumeEnabled) {
                            this.toggleListeningForLang('A');
                        }
                    } else if (shouldResume && speaker) {
                        this.toggleListeningForLang(speaker);
                    }

                    this.processTtsQueue();
                };

                try {
                    this.debugLog('processTtsQueue: calling audio.play()', {});
                    console.log('▶️ processTtsQueue: calling audio.play()', {
                        ts: new Date().toISOString(),
                    });
                    await audio.play();
                    this.debugLog('processTtsQueue: audio.play() success', {});
                    console.log('✅ processTtsQueue: audio.play() success', {
                        ts: new Date().toISOString(),
                    });
                } catch (err) {
                    this.debugLog('processTtsQueue: audio.play() error', {
                        error: String(err),
                        errorName: err?.name,
                        errorMessage: err?.message,
                    });
                    console.error('❌ processTtsQueue: audio.play() error', {
                        ts: new Date().toISOString(),
                        error: String(err),
                        errorName: err?.name,
                        errorMessage: err?.message,
                        stack: err?.stack,
                    });
                    URL.revokeObjectURL(url);
                    this.isTtsPlaying = false;
                    this.processTtsQueue();
                }
            } catch (err) {
                this.debugLog('processTtsQueue: ERROR', {
                    error: String(err),
                    errorName: err?.name,
                    errorMessage: err?.message,
                });
                console.error('❌ processTtsQueue: ERROR', {
                    ts: new Date().toISOString(),
                    error: String(err),
                    errorName: err?.name,
                    errorMessage: err?.message,
                    stack: err?.stack,
                });
                this.isTtsPlaying = false;
                this.processTtsQueue();
            }
        },

        pauseYoutubeIfNeeded() {
            this.debugLog('pauseYoutubeIfNeeded START', {
                hasPlayer: !!this.youtubePlayer,
                hasPauseVideo: !!(this.youtubePlayer && typeof this.youtubePlayer.pauseVideo === 'function'),
                isYoutubePlayerReady: this.isYoutubePlayerReady,
                activeTab: this.activeTab,
                isListening: this.isListening,
            });
            console.log('⏸️ pauseYoutubeIfNeeded START', {
                ts: new Date().toISOString(),
                hasPlayer: !!this.youtubePlayer,
                hasPauseVideo: !!(this.youtubePlayer && typeof this.youtubePlayer.pauseVideo === 'function'),
                isYoutubePlayerReady: this.isYoutubePlayerReady,
                activeTab: this.activeTab,
                isListening: this.isListening,
            });
            try {
                if (this.youtubePlayer && typeof this.youtubePlayer.pauseVideo === 'function') {
                    this.debugLog('pauseYoutubeIfNeeded: calling pauseVideo()', {});
                    console.log('⏸️ pauseYoutubeIfNeeded: calling pauseVideo()', {
                        ts: new Date().toISOString(),
                    });
                    this.youtubePlayer.pauseVideo();
                    this.debugLog('pauseYoutubeIfNeeded: pauseVideo() called successfully', {});
                    console.log('✅ pauseYoutubeIfNeeded: pauseVideo() called successfully', {
                        ts: new Date().toISOString(),
                    });
                } else {
                    this.debugLog('pauseYoutubeIfNeeded: cannot pause (no player or no pauseVideo)', {
                        hasPlayer: !!this.youtubePlayer,
                        hasPauseVideo: !!(this.youtubePlayer && typeof this.youtubePlayer.pauseVideo === 'function'),
                    });
                    console.warn('⚠️ pauseYoutubeIfNeeded: cannot pause (no player or no pauseVideo)', {
                        ts: new Date().toISOString(),
                        hasPlayer: !!this.youtubePlayer,
                        hasPauseVideo: !!(this.youtubePlayer && typeof this.youtubePlayer.pauseVideo === 'function'),
                    });
                }
            } catch (err) {
                this.debugLog('pauseYoutubeIfNeeded: ERROR', {
                    error: String(err),
                    errorName: err?.name,
                    errorMessage: err?.message,
                });
                console.error('❌ pauseYoutubeIfNeeded: ERROR', {
                    ts: new Date().toISOString(),
                    error: String(err),
                    errorName: err?.name,
                    errorMessage: err?.message,
                    stack: err?.stack,
                });
            }
        },

        resumeYoutubeIfNeeded() {
            this.debugLog('resumeYoutubeIfNeeded START', {
                hasPlayer: !!this.youtubePlayer,
                hasPlayVideo: !!(this.youtubePlayer && typeof this.youtubePlayer.playVideo === 'function'),
                isYoutubePlayerReady: this.isYoutubePlayerReady,
                activeTab: this.activeTab,
            });
            console.log('▶️ resumeYoutubeIfNeeded START', {
                ts: new Date().toISOString(),
                hasPlayer: !!this.youtubePlayer,
                hasPlayVideo: !!(this.youtubePlayer && typeof this.youtubePlayer.playVideo === 'function'),
                isYoutubePlayerReady: this.isYoutubePlayerReady,
                activeTab: this.activeTab,
            });
            try {
                if (this.youtubePlayer && typeof this.youtubePlayer.playVideo === 'function') {
                    this.debugLog('resumeYoutubeIfNeeded: calling playVideo()', {});
                    console.log('▶️ resumeYoutubeIfNeeded: calling playVideo()', {
                        ts: new Date().toISOString(),
                    });
                    this.youtubePlayer.playVideo();
                    this.debugLog('resumeYoutubeIfNeeded: playVideo() called successfully', {});
                    console.log('✅ resumeYoutubeIfNeeded: playVideo() called successfully', {
                        ts: new Date().toISOString(),
                    });
                } else {
                    this.debugLog('resumeYoutubeIfNeeded: cannot play (no player or no playVideo)', {
                        hasPlayer: !!this.youtubePlayer,
                        hasPlayVideo: !!(this.youtubePlayer && typeof this.youtubePlayer.playVideo === 'function'),
                    });
                    console.warn('⚠️ resumeYoutubeIfNeeded: cannot play (no player or no playVideo)', {
                        ts: new Date().toISOString(),
                        hasPlayer: !!this.youtubePlayer,
                        hasPlayVideo: !!(this.youtubePlayer && typeof this.youtubePlayer.playVideo === 'function'),
                    });
                }
            } catch (err) {
                this.debugLog('resumeYoutubeIfNeeded: ERROR', {
                    error: String(err),
                    errorName: err?.name,
                    errorMessage: err?.message,
                });
                console.error('❌ resumeYoutubeIfNeeded: ERROR', {
                    ts: new Date().toISOString(),
                    error: String(err),
                    errorName: err?.name,
                    errorMessage: err?.message,
                    stack: err?.stack,
                });
            }
        },

        playYoutubeAfterMic() {
            this.debugLog('playYoutubeAfterMic START', {
                isYoutubePlayerReady: this.isYoutubePlayerReady,
                hasPlayer: !!this.youtubePlayer,
                hasPlayVideo: !!(this.youtubePlayer && typeof this.youtubePlayer.playVideo === 'function'),
            });
            console.log('▶️ playYoutubeAfterMic START', {
                ts: new Date().toISOString(),
                isYoutubePlayerReady: this.isYoutubePlayerReady,
                hasPlayer: !!this.youtubePlayer,
                hasPlayVideo: !!(this.youtubePlayer && typeof this.youtubePlayer.playVideo === 'function'),
            });

            // Su mobile, per evitare conflitti con le policy audio/video di Chrome (che possono causare
            // pause immediate se il play è avviato da script mentre il mic è attivo),
            // EVITIAMO di far partire il video automaticamente. Lasciamo che sia l'utente
            // a premere Play sul video.
            if (this.isMobileLowPower) {
                this.debugLog('playYoutubeAfterMic: skipping auto-play on mobile', {});
                console.log('📱 playYoutubeAfterMic: skipping auto-play on mobile (user must tap Play)', {
                    ts: new Date().toISOString(),
                });
                return;
            }

            // Prova a far partire il video non appena il player è pronto.
            const tryPlay = () => {
                try {
                    if (this.youtubePlayer && typeof this.youtubePlayer.playVideo === 'function') {
                        // Verifica lo stato del video prima di chiamare playVideo()
                        // Se è già in PLAYING (1) o BUFFERING (3), non chiamare playVideo() di nuovo
                        let currentState = null;
                        if (typeof this.youtubePlayer.getPlayerState === 'function') {
                            try {
                                currentState = this.youtubePlayer.getPlayerState();
                            } catch {
                                // Ignora errori nel recupero dello stato
                            }
                        }

                        this.debugLog('playYoutubeAfterMic: checking video state', {
                            currentState,
                            stateName: currentState === 1 ? 'PLAYING' : currentState === 2 ? 'PAUSED' : currentState === 3 ? 'BUFFERING' : 'OTHER',
                        });
                        console.log('🔍 playYoutubeAfterMic: checking video state', {
                            ts: new Date().toISOString(),
                            currentState,
                            stateName: currentState === 1 ? 'PLAYING' : currentState === 2 ? 'PAUSED' : currentState === 3 ? 'BUFFERING' : 'OTHER',
                        });

                        // Se il video è già in PLAYING o BUFFERING, non chiamare playVideo()
                        if (currentState === 1 || currentState === 3) {
                            this.debugLog('playYoutubeAfterMic: video already playing/buffering, skipping playVideo()', {
                                currentState,
                            });
                            console.log('⏭️ playYoutubeAfterMic: video already playing/buffering, skipping playVideo()', {
                                ts: new Date().toISOString(),
                                currentState,
                            });
                            return;
                        }

                        this.debugLog('playYoutubeAfterMic: calling playVideo()', {
                            currentState,
                        });
                        console.log('▶️ playYoutubeAfterMic: calling playVideo()', {
                            ts: new Date().toISOString(),
                            currentState,
                        });
                        this.youtubePlayer.playVideo();
                        this.debugLog('playYoutubeAfterMic: playVideo() called successfully', {});
                        console.log('✅ playYoutubeAfterMic: playVideo() called successfully', {
                            ts: new Date().toISOString(),
                        });
                    } else {
                        this.debugLog('playYoutubeAfterMic: cannot play (no player or no playVideo)', {
                            hasPlayer: !!this.youtubePlayer,
                            hasPlayVideo: !!(this.youtubePlayer && typeof this.youtubePlayer.playVideo === 'function'),
                        });
                        console.warn('⚠️ playYoutubeAfterMic: cannot play (no player or no playVideo)', {
                            ts: new Date().toISOString(),
                            hasPlayer: !!this.youtubePlayer,
                            hasPlayVideo: !!(this.youtubePlayer && typeof this.youtubePlayer.playVideo === 'function'),
                        });
                    }
                } catch (err) {
                    this.debugLog('playYoutubeAfterMic: ERROR calling playVideo()', {
                        error: String(err),
                        errorName: err?.name,
                        errorMessage: err?.message,
                    });
                    console.error('❌ playYoutubeAfterMic: ERROR calling playVideo()', {
                        ts: new Date().toISOString(),
                        error: String(err),
                        errorName: err?.name,
                        errorMessage: err?.message,
                        stack: err?.stack,
                    });
                }
            };

            if (this.isYoutubePlayerReady) {
                this.debugLog('playYoutubeAfterMic: player ready, trying play immediately', {});
                console.log('✅ playYoutubeAfterMic: player ready, trying play immediately', {
                    ts: new Date().toISOString(),
                });
                tryPlay();
                return;
            }

            this.debugLog('playYoutubeAfterMic: player not ready, starting polling', {
                isYoutubePlayerReady: this.isYoutubePlayerReady,
            });
            console.log('⏳ playYoutubeAfterMic: player not ready, starting polling', {
                ts: new Date().toISOString(),
                isYoutubePlayerReady: this.isYoutubePlayerReady,
            });
            // Polling leggero per qualche secondo finché il player non diventa pronto
            const start = Date.now();
            const maxMs = 5000;
            const interval = setInterval(() => {
                if (this.isYoutubePlayerReady || Date.now() - start > maxMs) {
                    clearInterval(interval);
                    if (this.isYoutubePlayerReady) {
                        this.debugLog('playYoutubeAfterMic: player ready after polling', {
                            elapsedMs: Date.now() - start,
                        });
                        console.log('✅ playYoutubeAfterMic: player ready after polling', {
                            ts: new Date().toISOString(),
                            elapsedMs: Date.now() - start,
                        });
                        tryPlay();
                    } else {
                        this.debugLog('playYoutubeAfterMic: polling timeout, player still not ready', {
                            elapsedMs: Date.now() - start,
                        });
                        console.warn('⚠️ playYoutubeAfterMic: polling timeout, player still not ready', {
                            ts: new Date().toISOString(),
                            elapsedMs: Date.now() - start,
                        });
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
            // Debounce delle traduzioni di preview: usa una soglia proporzionale alla
            // durata di silenzio configurata per Whisper, con un minimo di 300ms.
            const minDelay = (typeof this.whisperSilenceMs === 'number' && this.whisperSilenceMs > 0)
                ? Math.max(300, this.whisperSilenceMs)
                : 600;
            if (text === this.lastPreviewText && now - this.lastPreviewAt < minDelay) {
                return;
            }

            this.lastPreviewText = text;
            this.lastPreviewAt = now;

            this.startTranslationStream(text, { commit: false });
        },

        updateTranslationTokens(fullText) {
            const clean = ((fullText || '').trim());
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
            // Nella modalità "call": serve solo langB (lingua di traduzione)
            // La lingua sorgente viene auto-rilevata da Whisper
            if (!this.langB) {
                this.statusMessage = this.ui.statusLangPairMissing;
                return;
            }

            // Imposta la lingua di destinazione per la traduzione
            // La lingua sorgente viene auto-rilevata, quindi non impostiamo currentMicLang qui
            this.currentTargetLang = this.langB;

            this.statusMessage = '';
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

            await this.initYoutubePlayer();

            // Avvia automaticamente il microfono in lingua A (interprete umano sopra al video)
            try {
                await this.toggleListeningForLang('A');
            } catch {
                // Se fallisce (permessi microfono, ecc.), l'utente può usare il pulsante manuale
            }
        },

        maybeAutoLoadYoutubePlayer() {
            // Carica automaticamente il player YouTube quando:
            // - URL valido
            // - lingua sorgente e di destinazione impostate e diverse
            const id = this.extractYoutubeVideoId(this.youtubeUrl);
            if (!id) {
                return;
            }

            if (!this.youtubeLangSource || !this.youtubeLangTarget) {
                return;
            }

            if (this.youtubeLangSource === this.youtubeLangTarget) {
                return;
            }

            // Se il video è già impostato con questo ID, non facciamo nulla:
            // il bottone "Avvia modalità interprete sul video" gestirà il microfono.
            if (this.youtubeVideoId === id) {
                return;
            }

            this.youtubeVideoId = id;
            this.initYoutubePlayer();
        },

        async initYoutubePlayer() {
            this.debugLog('initYoutubePlayer START', {
                youtubeVideoId: this.youtubeVideoId,
                hasPlayer: !!this.youtubePlayer,
                isYoutubePlayerReady: this.isYoutubePlayerReady,
                hasYT: !!(window.YT && window.YT.Player),
            });
            console.log('🎬 initYoutubePlayer START', {
                ts: new Date().toISOString(),
                youtubeVideoId: this.youtubeVideoId,
                hasPlayer: !!this.youtubePlayer,
                isYoutubePlayerReady: this.isYoutubePlayerReady,
                hasYT: !!(window.YT && window.YT.Player),
            });

            if (!this.youtubeVideoId) {
                this.debugLog('initYoutubePlayer: no videoId, skipping', {});
                console.warn('⚠️ initYoutubePlayer: no videoId, skipping', {
                    ts: new Date().toISOString(),
                });
                return;
            }

            // Se il player esiste già, aggiorna solo il video
            if (this.youtubePlayer && this.isYoutubePlayerReady) {
                try {
                    this.debugLog('initYoutubePlayer: reloading existing player with new videoId', {
                        youtubeVideoId: this.youtubeVideoId,
                    });
                    console.log('🔄 initYoutubePlayer: reloading existing player with new videoId', {
                        ts: new Date().toISOString(),
                        youtubeVideoId: this.youtubeVideoId,
                    });
                    this.youtubePlayer.loadVideoById(this.youtubeVideoId);
                    this.debugLog('initYoutubePlayer: loadVideoById called successfully', {});
                    console.log('✅ initYoutubePlayer: loadVideoById called successfully', {
                        ts: new Date().toISOString(),
                    });
                } catch (err) {
                    this.debugLog('initYoutubePlayer: error loading video, recreating player', {
                        error: String(err),
                    });
                    console.warn('⚠️ initYoutubePlayer: error loading video, recreating player', {
                        ts: new Date().toISOString(),
                        error: String(err),
                    });
                    // fallback: ricrea il player
                    this.youtubePlayer = null;
                    this.isYoutubePlayerReady = false;
                }
            }

            if (this.youtubePlayer) {
                this.debugLog('initYoutubePlayer: player already exists, skipping creation', {});
                console.log('✅ initYoutubePlayer: player already exists, skipping creation', {
                    ts: new Date().toISOString(),
                });
                return;
            }

            const createPlayer = () => {
                try {
                    this.debugLog('initYoutubePlayer: creating new YT.Player', {
                        youtubeVideoId: this.youtubeVideoId,
                        hasRef: !!this.$refs.youtubePlayer,
                    });
                    console.log('🎬 initYoutubePlayer: creating new YT.Player', {
                        ts: new Date().toISOString(),
                        youtubeVideoId: this.youtubeVideoId,
                        hasRef: !!this.$refs.youtubePlayer,
                    });
                    this.youtubePlayer = new window.YT.Player(this.$refs.youtubePlayer, {
                        videoId: this.youtubeVideoId,
                        playerVars: {
                            rel: 0,
                            modestbranding: 1,
                        },
                        events: {
                            onReady: () => {
                                this.isYoutubePlayerReady = true;
                                this.debugLog('initYoutubePlayer: onReady event fired', {
                                    youtubeVideoId: this.youtubeVideoId,
                                });
                                console.log('✅ initYoutubePlayer: onReady event fired', {
                                    ts: new Date().toISOString(),
                                    youtubeVideoId: this.youtubeVideoId,
                                });
                            },
                            onStateChange: (event) => {
                                this.youtubePlayerState = typeof event.data === 'number' ? event.data : -1;

                                this.debugLog('initYoutubePlayer: onStateChange event', {
                                    state: event.data,
                                    stateNames: {
                                        '-1': 'UNSTARTED',
                                        '0': 'ENDED',
                                        '1': 'PLAYING',
                                        '2': 'PAUSED',
                                        '3': 'BUFFERING',
                                        '5': 'CUED',
                                    },
                                });
                                console.log('📺 initYoutubePlayer: onStateChange event', {
                                    ts: new Date().toISOString(),
                                    state: event.data,
                                    stateName: {
                                        '-1': 'UNSTARTED',
                                        '0': 'ENDED',
                                        '1': 'PLAYING',
                                        '2': 'PAUSED',
                                        '3': 'BUFFERING',
                                        '5': 'CUED',
                                    }[String(event.data)] || 'UNKNOWN',
                                });

                                // Solo su desktop / non-mobile facciamo seguire il microfono
                                // allo stato di riproduzione del player YouTube.
                                if (!this.isMobileLowPower && this.activeTab === 'youtube') {
                                    if (event.data === 1) {
                                        // PLAYING → accendi microfono sulla lingua sorgente (A)
                                        if (!this.isListening) {
                                            this.toggleListeningForLang('A');
                                        }
                                    } else if (event.data === 2 || event.data === 0) {
                                        // PAUSED o ENDED → spegni microfono
                                        if (this.isListening) {
                                            this.stopListeningInternal();
                                        }
                                    }
                                }
                            },
                        },
                    });
                    this.debugLog('initYoutubePlayer: YT.Player created successfully', {
                        hasPlayer: !!this.youtubePlayer,
                    });
                    console.log('✅ initYoutubePlayer: YT.Player created successfully', {
                        ts: new Date().toISOString(),
                        hasPlayer: !!this.youtubePlayer,
                    });
                } catch (err) {
                    this.debugLog('initYoutubePlayer: ERROR creating player', {
                        error: String(err),
                        errorName: err?.name,
                        errorMessage: err?.message,
                    });
                    console.error('❌ initYoutubePlayer: ERROR creating player', {
                        ts: new Date().toISOString(),
                        error: String(err),
                        errorName: err?.name,
                        errorMessage: err?.message,
                        stack: err?.stack,
                    });
                }
            };

            if (window.YT && window.YT.Player) {
                this.debugLog('initYoutubePlayer: YT API already loaded, creating player', {});
                console.log('✅ initYoutubePlayer: YT API already loaded, creating player', {
                    ts: new Date().toISOString(),
                });
                createPlayer();
                return;
            }

            this.debugLog('initYoutubePlayer: YT API not loaded, loading script', {});
            console.log('📥 initYoutubePlayer: YT API not loaded, loading script', {
                ts: new Date().toISOString(),
            });
            // Carica l'API iframe di YouTube se non è presente
            return new Promise((resolve) => {
                const existing = document.getElementById('youtube-iframe-api');
                if (!existing) {
                    const tag = document.createElement('script');
                    tag.id = 'youtube-iframe-api';
                    tag.src = 'https://www.youtube.com/iframe_api';
                    document.body.appendChild(tag);
                    this.debugLog('initYoutubePlayer: script tag added', {});
                    console.log('📥 initYoutubePlayer: script tag added', {
                        ts: new Date().toISOString(),
                    });
                } else {
                    this.debugLog('initYoutubePlayer: script tag already exists', {});
                    console.log('✅ initYoutubePlayer: script tag already exists', {
                        ts: new Date().toISOString(),
                    });
                }

                const previous = window.onYouTubeIframeAPIReady;
                window.onYouTubeIframeAPIReady = () => {
                    this.debugLog('initYoutubePlayer: onYouTubeIframeAPIReady callback fired', {});
                    console.log('✅ initYoutubePlayer: onYouTubeIframeAPIReady callback fired', {
                        ts: new Date().toISOString(),
                    });
                    if (typeof previous === 'function') {
                        try {
                            previous();
                        } catch (err) {
                            this.debugLog('initYoutubePlayer: error calling previous callback', {
                                error: String(err),
                            });
                            console.warn('⚠️ initYoutubePlayer: error calling previous callback', err);
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
            // - è selezionata la lingua di traduzione (langB)
            // - esiste un thread di TRASCRIZIONE (translationThreadId) da usare come contesto
            if (!this.langB) {
                return;
            }
            // Se langA non è impostato, usa un default opposto a langB per i suggerimenti bilingue
            const langA = this.langA || (this.langB === 'it' ? 'en' : 'it');

            if (!this.translationThreadId) {
                // Nessuna trascrizione salvata: non ha senso generare la mappa mentale
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
                        // Il CV viene ignorato: la mappa mentale si basa solo sulla history
                        // della TRASCRIZIONE salvata nel thread di traduzione.
                        locale: this.locale || 'it',
                        lang_a: langA,
                        lang_b: this.langB,
                        thread_id: this.translationThreadId,
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

                const rawNodes = Array.isArray(this.mindMapGraph.nodes) ? this.mindMapGraph.nodes : [];
                const rawEdges = Array.isArray(this.mindMapGraph.edges) ? this.mindMapGraph.edges : [];

                const nodes = rawNodes.map((n) => {
                    const importance = typeof n.importance === 'number' ? n.importance : 0.5;
                    const size = 8 + importance * 12; // 8–20

                    return {
                        id: n.id,
                        label: n.label || n.title || '',
                        group: n.group || undefined,
                        value: size,
                        title: n.note || n.description || '',
                    };
                });

                const edges = rawEdges.map((e) => {
                    const strength = typeof e.strength === 'number' ? e.strength : 0.5;
                    const width = 0.5 + strength * 2.5; // 0.5–3

                    return {
                        from: e.from,
                        to: e.to,
                        label: e.label || '',
                        width,
                    };
                });

                const data = { nodes, edges };

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

            if (!this.langB) {
                return;
            }
            // Se langA non è impostato, usa un default opposto a langB per i suggerimenti bilingue
            const langA = this.langA || (this.langB === 'it' ? 'en' : 'it');

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
                        lang_a: langA,
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
                        langA: langA,
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

        openNextCallModal() {
            this.showNextCallModal = true;
        },

        closeNextCallModal() {
            this.showNextCallModal = false;
        },

        async onNextCallSuggestClick() {
            const goal = (this.nextCallGoal || '').trim();
            if (!goal) {
                return;
            }

            const transcript = (this.displayOriginalText || '').trim();

            this.isNextCallLoading = true;

            try {
                const res = await fetch('/api/chatbot/interview-next-call', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify({
                        goal,
                        transcript_text: transcript,
                        locale: this.locale || 'it',
                        lang_a: this.langA || 'it',
                        lang_b: this.langB || 'en',
                        thread_id: this.interviewSuggestionThreadId,
                    }),
                });

                const json = await res.json().catch(() => ({}));

                if (!res.ok || json.error) {
                    return;
                }

                // Per l'UI di Interpreter mostriamo solo la lingua dell'utente (langB).
                this.nextCallSuggestionsLangA = '';
                this.nextCallSuggestionsLangB = (json.tips_lang_b || '').trim();
            } catch {
                // silenzioso
            } finally {
                this.isNextCallLoading = false;
            }
        },
    },
};
</script>

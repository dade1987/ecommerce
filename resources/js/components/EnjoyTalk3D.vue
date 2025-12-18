<template>
  <div ref="rootEl" id="enjoyTalkRoot" :class="[
    'flex flex-col',
    !isWebComponent && 'min-h-[100dvh]',
    isWebComponent ? 'bg-transparent' : 'w-full bg-[#0f172a] pb-[96px] sm:pb-0'
  ]">

    <!-- Floating launcher bubble (snippet mode, visibile solo quando il widget è chiuso) -->
    <button v-if="isWebComponent && !widgetOpen" id="talkLauncherBtn"
      class="enjoytalk-launcher-wow fixed z-[9999] bottom-[calc(1.25rem+env(safe-area-inset-bottom))] right-[calc(1.25rem+env(safe-area-inset-right))] h-16 px-5 rounded-full backdrop-blur text-white shadow-lg border border-white/20 flex items-center gap-3"
      aria-label="Apri Assistente virtuale AI (Ricerche e servizi)" @click="onLauncherClick">
      <!-- Glow + ping (solo CSS) -->
      <span class="pointer-events-none absolute inset-0 rounded-full enjoytalk-launcher-pulse"></span>
      <span class="pointer-events-none absolute inset-0 rounded-full enjoytalk-launcher-ping"></span>

      <!-- Icon (AI assistant) -->
      <span
        class="relative z-10 flex items-center justify-center w-10 h-10 rounded-full bg-white/10 border border-white/15">
        <svg class="w-6 h-6 enjoytalk-launcher-icon" viewBox="0 0 24 24" fill="none" aria-hidden="true">
          <path d="M9 18h6m-8-6V9a5 5 0 0 1 10 0v3m-11 0h12v3a3 3 0 0 1-3 3H9a3 3 0 0 1-3-3v-3Z" stroke="currentColor"
            stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
          <path d="M10 11h.01M14 11h.01" stroke="currentColor" stroke-width="3" stroke-linecap="round" />
          <path d="M12 3v2M6.5 5.5 8 7M17.5 5.5 16 7" stroke="currentColor" stroke-width="2" stroke-linecap="round"
            stroke-linejoin="round" opacity="0.9" />
        </svg>
      </span>

      <!-- Copy (orizzontale) -->
      <span class="relative z-10 text-left leading-tight">
        <span class="flex items-center gap-2">
          <span class="text-[14px] font-extrabold tracking-tight">Assistente virtuale AI</span>
          <span
            class="enjoytalk-ai-badge text-[11px] font-black tracking-widest uppercase px-2 py-0.5 rounded-full border border-white/20">AI</span>
        </span>
        <span class="block text-[12px] font-semibold text-white/85">Ricerche &amp; Servizi</span>
      </span>

      <span class="relative z-10 ml-1 text-white/90">
        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" aria-hidden="true">
          <path d="M9 18 15 12 9 6" stroke="currentColor" stroke-width="2" stroke-linecap="round"
            stroke-linejoin="round" />
        </svg>
      </span>
    </button>

    <!-- Contenuto principale: full layout oppure widget snippet fisso in basso a destra -->
    <div v-show="!isWebComponent || widgetOpen" :class="isWebComponent
      ? 'fixed z-[9999] bottom-24 right-4 w-[320px] max-w-[90vw] pointer-events-none'
      : 'flex flex-col flex-1 w-full'">

      <!-- Header solo in layout full -->
      <div class="px-4 py-4" v-if="!isWebComponent">
        <div class="mx-auto w-full max-w-[520px] flex items-center gap-3">
          <img id="teamLogo" :src="teamLogo" alt="EnjoyTalk 3D"
            class="w-10 h-10 rounded-full object-cover border border-slate-600" />
          <h1 class="font-sans text-2xl text-white">EnjoyTalk 3D</h1>
        </div>
      </div>

      <!-- Canvas Avatar 3D -->
      <div class="flex-1 flex items-center justify-center p-4 overflow-y-auto">
        <div :class="['w-full', isWebComponent ? 'max-w-full' : 'max-w-[520px]']">
          <div :class="[
            'relative rounded-md overflow-hidden pointer-events-auto',
            isWebComponent ? 'bg-transparent border-none' : 'bg-[#111827] border border-slate-700'
          ]">
            <div class="mx-auto w-full px-3 sm:px-0">
              <div id="avatarStage" :class="[
                'rounded-md overflow-hidden w-full h-auto max-h-[calc(100dvh-220px)] aspect-[3/4]',
                isWebComponent ? 'bg-transparent border-none' : ''
              ]">
              </div>
              <!-- Link sorgente (modalità avatar in snippet/webcomponent) -->
              <div v-if="isWebComponent && !snippetTextMode && lastSourceUrl"
                class="mt-3 mb-2 flex justify-center pointer-events-auto">
                <button type="button" @click="openLastSourceUrl"
                  class="inline-flex items-center gap-1 px-4 py-2 rounded-full bg-rose-600 hover:bg-rose-700 text-white text-[11px] font-semibold shadow-md border border-rose-300">
                  <span>Vai alla pagina da cui ho preso queste informazioni</span>
                  <span class="text-xs">↗</span>
                </button>
              </div>

              <!-- Prenotazione (solo snippet/webcomponent): CTA cliccabile per evitare popup block da voce -->
              <div v-if="isWebComponent && !snippetTextMode && lastBookingUrl"
                class="mt-3 mb-2 flex justify-center pointer-events-auto">
                <button type="button" @click="openLastBookingUrl"
                  class="inline-flex items-center gap-1 px-4 py-2 rounded-full bg-sky-500 hover:bg-sky-600 text-white text-[11px] font-semibold shadow-md border border-sky-200">
                  <span>Prenota un appuntamento (Calendly)</span>
                  <span class="text-xs">↗</span>
                </button>
              </div>
              <!-- Chat Panel (solo testo) -->
              <div id="chatPanel"
                class="hidden bg-[#111827] border border-slate-700 rounded-md overflow-hidden w-full h-auto max-h-[calc(100dvh-220px)] aspect-[3/4] flex flex-col">
                <div id="chatMessages" class="flex-1 overflow-auto p-3 space-y-3">
                </div>
              </div>
              <!-- Input chat testuale snippet (come Hen) -->
              <div v-if="isWebComponent && snippetTextMode" class="px-1 pb-3 pt-2">
                <div class="flex items-center gap-2">
                  <input id="talkSnippetInput" v-model="snippetInput" type="text" placeholder="Scrivi qui..."
                    @keyup.enter.prevent="sendSnippetInput"
                    class="flex-1 bg-slate-800/80 text-slate-100 text-sm outline-none border border-slate-700/80 rounded-full px-3 py-2 placeholder-slate-400" />
                  <button @click="sendSnippetInput"
                    class="w-9 h-9 rounded-full bg-emerald-600/90 text-white flex items-center justify-center text-sm shadow border border-emerald-400/80">
                    📤
                  </button>
                </div>
              </div>
            </div>

            <!-- Fumetto di pensiero -->
            <div id="thinkingBubble"
              class="hidden absolute top-4 left-1/2 transform -translate-x-1/2 bg-white rounded-lg px-4 py-2 shadow-lg border border-gray-300">
              <div class="text-gray-700 text-sm font-medium">
                💭 Sto pensando...
              </div>
              <div class="absolute bottom-0 left-1/2 transform translate-y-full -translate-x-1/2">
                <div class="w-0 h-0 border-l-4 border-r-4 border-t-4 border-transparent border-t-white"></div>
              </div>
            </div>

            <!-- Badge ascolto microfono -->
            <div id="listeningBadge"
              class="hidden absolute top-4 left-4 bg-rose-600/90 text-white text-xs font-semibold px-2.5 py-1 rounded-md shadow animate-pulse">
              🎤 Ascolto...
            </div>

            <!-- Loading Overlay -->
            <div id="loadingOverlay"
              class="hidden absolute inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center z-30 rounded-md">
              <div class="flex flex-col items-center gap-6">
                <!-- Spinner animato migliorato -->
                <div class="relative w-16 h-16">
                  <div
                    class="absolute inset-0 bg-gradient-to-r from-indigo-600 to-emerald-600 rounded-full animate-spin"
                    style="border-radius: 50%; -webkit-mask-image: radial-gradient(circle 10px at center, transparent 100%, black 100%); mask-image: radial-gradient(circle 10px at center, transparent 100%, black 100%);">
                  </div>
                  <div class="absolute inset-2 bg-black rounded-full flex items-center justify-center">
                    <div class="w-2 h-2 bg-gradient-to-r from-indigo-400 to-emerald-400 rounded-full animate-pulse">
                    </div>
                  </div>
                </div>
                <!-- Testo e barra di progresso -->
                <div class="text-center">
                  <div class="text-white text-base font-medium mb-3">Caricamento avatar...</div>
                  <div class="w-48 h-1.5 bg-slate-700 rounded-full overflow-hidden">
                    <div class="h-full bg-gradient-to-r from-indigo-600 to-emerald-600 rounded-full animate-pulse"
                      style="width: 65%;"></div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Toggle Chat Mode (solo layout full) -->
            <div v-if="!isWebComponent" class="absolute top-4 right-4 z-30">
              <button id="modeToggleBtn"
                class="px-3 py-2 bg-slate-700/80 hover:bg-slate-600 text-white text-xs rounded-md border border-slate-600 shadow">
                💬 Modalità chat
              </button>
            </div>

            <!-- Close button (snippet mode) -->
            <button v-if="isWebComponent" id="talkCloseBtn" @click="closeWidget"
              class="absolute top-4 right-4 z-30 w-8 h-8 rounded-full bg-black/70 text-white flex items-center justify-center border border-white/40">
              ✕
            </button>

            <!-- Conversa con Me Button (solo layout full) -->
            <div v-if="!isWebComponent" id="conversaBtnContainer"
              class="hidden absolute inset-0 flex items-center justify-center z-25 pointer-events-auto rounded-md bg-black/40 backdrop-blur-sm">
              <button id="conversaBtn"
                class="px-6 py-4 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg transition-colors whitespace-nowrap text-lg sm:text-xl font-semibold shadow-lg hover:shadow-xl transform hover:scale-105">
                🎤 Parla con Me
              </button>
            </div>

            <!-- Modal Trascrizione Email -->
            <div id="emailTranscriptModal"
              class="hidden absolute inset-0 flex items-center justify-center z-40 rounded-md bg-black/60 backdrop-blur-sm">
              <div
                class="w-full max-w-[480px] mx-4 bg-[#0b1220] border border-slate-700 rounded-xl shadow-2xl overflow-hidden">
                <div class="px-4 py-3 border-b border-slate-700 bg-black/50 flex items-center justify-between">
                  <div class="text-slate-100 font-semibold text-base">Invia trascrizione via email</div>
                  <button id="sendTranscriptCancel"
                    class="text-slate-300 hover:text-white px-2 py-1 rounded-md hover:bg-slate-700/60">✕</button>
                </div>
                <div class="p-4 space-y-3">
                  <label class="block text-slate-300 text-sm">Indirizzo email destinatario</label>
                  <input id="emailTranscriptInput" type="email" placeholder="nome@esempio.com"
                    class="w-full px-3 py-2 bg-[#111827] text-white border border-slate-700 rounded-md placeholder-slate-400 focus:border-indigo-500 focus:outline-none" />
                  <div id="emailTranscriptStatus" class="text-xs text-slate-400 min-h-[1rem]"></div>
                </div>
                <div class="px-4 py-3 border-t border-slate-700 bg-black/50 flex items-center justify-end gap-2">
                  <button id="sendTranscriptCancel2"
                    class="px-3 py-2 bg-slate-700 hover:bg-slate-600 text-white rounded-md transition-colors text-sm">Annulla</button>
                  <button id="sendTranscriptConfirm"
                    class="px-3 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-md transition-colors text-sm font-semibold">Invia</button>
                </div>
              </div>
            </div>

            <!-- Debug Overlay (mostrato con ?debug=1) -->
            <div id="debugOverlay"
              class="hidden absolute left-1/2 -translate-x-1/2 top-3 z-10 w-full max-w-[520px] px-3 sm:px-0"
              style="pointer-events: auto">
              <div class="bg-black/70 backdrop-blur-sm border border-slate-600 rounded-md overflow-hidden shadow-lg">
                <div
                  class="flex items-center justify-between px-3 py-2 border-b border-slate-700 bg-black/60 sticky top-0">
                  <div class="text-slate-200 text-xs font-semibold">Debug</div>
                  <div class="flex items-center gap-2">
                    <button id="debugCopy"
                      class="text-[11px] px-2 py-1 bg-slate-700/70 hover:bg-slate-600 text-white rounded">
                      Copia
                    </button>
                    <button id="debugClear"
                      class="text-[11px] px-2 py-1 bg-slate-700/70 hover:bg-slate-600 text-white rounded">
                      Pulisci
                    </button>
                    <button id="debugClose"
                      class="text-[11px] px-2 py-1 bg-slate-700/70 hover:bg-slate-600 text-white rounded">
                      Chiudi
                    </button>
                  </div>
                </div>
                <div
                  class="max-h-[50vh] sm:max-h-[60vh] overflow-auto p-2 text-[11px] font-mono text-slate-200 leading-relaxed"
                  style="margin-bottom: calc(var(--controls-pad, 0px))">
                  <div id="debugContent" class="space-y-1"></div>
                </div>
              </div>
            </div>

          </div> <!-- fine card relativa -->
        </div> <!-- fine wrapper larghezza -->
      </div> <!-- fine blocco avatar/chat -->

      <!-- Controlli (solo layout full, come in EnjoyHen la barra in basso non esiste in snippet) -->
      <div v-if="!isWebComponent" id="controlsBar"
        class="bottom-0 left-0 w-full border-t border-slate-700 bg-[#0f172a] z-20 pb-[env(safe-area-inset-bottom)]">
        <div class="px-3 py-3 sm:px-4 sm:py-4">
          <div class="mx-auto w-full max-w-[520px] px-3 sm:px-0">
            <div class="flex flex-wrap w-full gap-2 items-center min-w-0">
              <input id="textInput" type="text" placeholder="Scrivi la domanda..."
                class="flex-1 min-w-0 px-3 py-3 bg-[#111827] text-white border border-slate-700 rounded-md placeholder-slate-400 focus:border-indigo-500 focus:outline-none text-[15px] sm:text-base" />
              <button id="sendBtn"
                class="px-3 py-3 sm:px-4 sm:py-3 bg-indigo-600 hover:bg-indigo-700 text-white rounded-md transition-colors whitespace-nowrap text-sm sm:text-base">
                📤
              </button>
              <button id="micBtn"
                class="px-3 py-3 sm:px-4 sm:py-3 bg-rose-600 hover:bg-rose-700 text-white rounded-md transition-colors whitespace-nowrap text-sm sm:text-base">
                🎤
              </button>
              <button id="emailTranscriptBtn"
                class="px-3 py-3 sm:px-4 sm:py-3 bg-emerald-600 hover:bg-emerald-700 text-white rounded-md transition-colors whitespace-nowrap text-sm sm:text-base">
                📧 Trascrizione
              </button>
            </div>
            <div class="mt-2 flex items-center gap-3 text-slate-300 text-xs sm:text-sm">
              <label class="inline-flex items-center gap-2 cursor-pointer select-none">
                <input id="useBrowserTts" type="checkbox" class="accent-indigo-600" />
                <span>Usa TTS del browser (italiano)</span>
              </label>
              <span id="browserTtsStatus" class="opacity-70"></span>
              <label class="inline-flex items-center gap-2 cursor-pointer select-none ml-auto">
                <input id="useAdvancedLipsync" type="checkbox" class="accent-emerald-600" />
                <span>LipSync avanzato (WebAudio)</span>
              </label>
            </div>
            <div id="liveText" class="hidden mt-3 text-slate-300 min-h-[1.5rem]"></div>
            <!-- Link sorgente (avatar / layout full) -->
            <div v-if="lastSourceUrl" class="mt-3 flex justify-center">
              <button type="button" @click="openLastSourceUrl"
                class="inline-flex items-center gap-1 px-4 py-2 rounded-full bg-rose-600 hover:bg-rose-700 text-white text-xs sm:text-[13px] font-semibold shadow-md border border-rose-300">
                <span>Vai alla pagina da cui ho preso queste informazioni</span>
                <span class="text-xs">↗</span>
              </button>
            </div>
          </div>
        </div>
      </div>

      <audio id="ttsPlayer" class="hidden" playsinline></audio>
    </div> <!-- fine wrapper contenuto principale -->

    <!-- Floating controls bar (snippet mode) -->
    <div v-if="isWebComponent && widgetOpen" id="talkFloatingControls"
      class="fixed z-[9999] bottom-4 right-4 flex items-center gap-2 pointer-events-auto">
      <!-- Menu button -->
      <button id="talkMenuBtn" @click="toggleSnippetMenu"
        class="w-11 h-11 rounded-full bg-slate-900/60 backdrop-blur text-white/90 flex items-center justify-center shadow-lg border border-slate-500/60">
        ⋯
      </button>
      <!-- Email transcript button (snippet) -->
      <button id="talkEmailTranscriptFloatingBtn" @click="openSnippetEmailModal"
        class="w-11 h-11 rounded-full bg-emerald-600/90 backdrop-blur text-white flex items-center justify-center shadow-lg border border-emerald-400/80">
        📧
      </button>
      <!-- Mic button (snippet) - richiama la stessa logica del mic globale -->
      <button id="talkMicFloatingBtn" @click="onSnippetMicClick"
        class="w-11 h-11 rounded-full bg-rose-600/90 backdrop-blur text-white flex items-center justify-center shadow-lg border border-rose-400/80">
        🎤
      </button>
      <!-- Keyboard button: apre/chiude la modalità chat testuale -->
      <button id="talkKeyboardBtn" @click="onSnippetTextClick"
        class="w-11 h-11 rounded-full bg-indigo-600/90 backdrop-blur text-white flex items-center justify-center shadow-lg border border-indigo-400/80">
        ⌨️
      </button>
    </div>

    <!-- Floating options menu (snippet mode) -->
    <div v-if="isWebComponent && widgetOpen && snippetMenuOpen" id="talkOptionsPanel"
      class="fixed z-[10000] bottom-28 right-4 w-72 rounded-2xl bg-slate-900/90 backdrop-blur text-slate-100 shadow-2xl border border-slate-700/80 pointer-events-auto">
      <div class="px-4 py-3 border-b border-slate-800 text-[11px] font-semibold text-slate-400">
        AZIONI
      </div>
      <div class="px-4 py-2 space-y-2 text-sm">
        <button @click="toggleTextInterface"
          class="w-full flex items-center justify-between px-3 py-2 rounded-lg bg-slate-800/70 hover:bg-slate-700">
          <span>{{ snippetTextMode ? 'Modalità avatar' : 'Interfaccia testuale' }}</span>
        </button>
        <button @click="toggleMessages"
          class="w-full flex items-center justify-between px-3 py-2 rounded-lg bg-slate-800/70 hover:bg-slate-700">
          <span>Messaggi</span>
          <span
            :class="['px-2 py-0.5 text-[11px] rounded-full', snippetMessagesOn ? 'bg-emerald-600 text-white' : 'bg-rose-600 text-white']">
            {{ snippetMessagesOn ? 'ON' : 'OFF' }}
          </span>
        </button>
        <button @click="toggleAudio"
          class="w-full flex items-center justify-between px-3 py-2 rounded-lg bg-slate-800/70 hover:bg-slate-700">
          <span>Audio</span>
          <span
            :class="['px-2 py-0.5 text-[11px] rounded-full', snippetAudioOn ? 'bg-emerald-600 text-white' : 'bg-rose-600 text-white']">
            {{ snippetAudioOn ? 'ON' : 'OFF' }}
          </span>
        </button>
      </div>
    </div>

  </div> <!-- fine root -->
</template>

<script>
import { onMounted, defineComponent, getCurrentInstance, ref } from "vue";
import { injectStylesIfNeeded } from '../utils/inject-styles.js'
import WhisperSpeechRecognition from "../utils/WhisperSpeechRecognition";

export default defineComponent({
  name: "EnjoyTalk3D",
  props: {
    heygenApiKey: {
      type: String,
      default: "",
    },
    heygenServerUrl: {
      type: String,
      default: "https://api.heygen.com",
    },
    locale: {
      type: String,
      default: "it-IT",
    },
    teamLogo: {
      type: String,
      default: "/images/logoai.jpeg",
    },
    teamSlug: {
      type: String,
      default: () => {
        // Fallback: leggi dal pathname se non fornito via prop
        return window.location.pathname.split("/").pop()
      }
    },
    glbUrl: {
      type: String,
      default: "/images/68f78ddb4530fb061a1349d5.glb",
    },
    calendlyUrl: {
      type: String,
      default: "",
    },
  },
  data() {
    return {
      // stato minimale esposto secondo Options API
      isListening: false,
      advancedLipsyncOn: false,
      // stato snippet/webcomponent
      widgetOpen: true,
      introPlayed: false,
      snippetMenuOpen: false,
      snippetTextMode: false,
      snippetMessagesOn: true,
      snippetAudioOn: true,
      snippetInput: "",
      // URL della fonte principale (RAG sito) dell'ultima risposta
      lastSourceUrl: "",
      // URL prenotazione (Calendly) pronto per click (evita popup block da voice)
      lastBookingUrl: "",
    };
  },
  mounted() {
    try {
      // In modalità webcomponent il widget parte chiuso, mostrato solo come bubble
      if (import.meta.env.VITE_IS_WEB_COMPONENT) {
        this.widgetOpen = false;
      }
      // Inietta gli stili CSS dopo che il componente è montato
      // injectStylesIfNeeded()
      // Mostra l'overlay di caricamento
      const loadingOverlay = document.getElementById("loadingOverlay");
      if (loadingOverlay) {
        loadingOverlay.classList.remove("hidden");
      }
      // Priorità: props ricevuti > dataset dell'elemento > valori di default
      // I props sono già gestiti in setup(), niente da fare qui
      this.initLibraries().then(() => {
        try {
          this.setupScene && this.setupScene();
        } catch { }
        try {
          setTimeout(() => {
            try {
              this.loadHumanoid && this.loadHumanoid();
            } catch { }
          }, 0);
        } catch { }
      });
    } catch { }
  },
  beforeUnmount() {
    try {
      if (typeof window !== "undefined" && window.speechSynthesis)
        window.speechSynthesis.cancel();
    } catch { }
    try {
      if (typeof this._cleanup === "function") this._cleanup();
    } catch { }
    try {
      if (window.__enjoyTalkResizeObserver) {
        window.__enjoyTalkResizeObserver.disconnect?.();
        window.__enjoyTalkResizeObserver = null;
      }
    } catch { }
  },
  methods: {
    // Wrappers Options API per funzioni operative
    startStream(message) {
      try {
        return (
          this._startStream ||
          this.startStreamFacade ||
          this.startStreamImpl
        )?.(message);
      } catch { }
    },
    stopAllSpeechOutput() {
      try {
        return this._stopAllSpeechOutput?.();
      } catch { }
    },
    ensureMicPermission() {
      try {
        return this._ensureMicPermission?.();
      } catch { }
    },
    setListeningUI(active) {
      try {
        return this._setListeningUI?.(active);
      } catch { }
    },

    getGreetingMessage() {
      try {
        const raw = this.locale || "it-IT";
        const low = String(raw).toLowerCase();
        if (low === "it" || low.indexOf("it-") === 0) {
          return "Buongiorno";
        }
        if (low === "en" || low.indexOf("en-") === 0) {
          return "Good morning";
        }
      } catch { }
      return "Buongiorno";
    },
    debugSnapshot(extra) {
      try {
        const e = extra && typeof extra === "object" ? extra : {};
        const w = typeof window !== "undefined" ? window : {};
        const ua = (typeof navigator !== "undefined" && navigator.userAgent) ? navigator.userAgent : "";
        return {
          widgetOpen: !!this.widgetOpen,
          introPlayed: !!this.introPlayed,
          snippetAudioOn: !!this.snippetAudioOn,
          teamSlug: this.teamSlug,
          calendlyUrl: this.calendlyUrl,
          hasSendToTts: typeof this._sendToTts === "function",
          hasLivewire: !!w.Livewire,
          hasLivewireDispatch: !!(w.Livewire && typeof w.Livewire.dispatch === "function"),
          hasLivewireEmit: !!(w.Livewire && typeof w.Livewire.emit === "function"),
          hasCalendly: !!w.Calendly,
          ua,
          ...e,
        };
      } catch {
        return { error: "debugSnapshot_failed" };
      }
    },
    // Toggle widget snippet (bubble ↔ widget)
    onLauncherClick() {
      try {
        const isSnippet = import.meta.env.VITE_IS_WEB_COMPONENT || false;
        if (isSnippet) {
          // In modalità snippet la bubble apre/chiude il widget
          if (this.widgetOpen) {
            this.closeWidget && this.closeWidget();
          } else {
            this.widgetOpen = true;
            try {
              console.log("[EnjoyTalk3D] launcher_open", this.debugSnapshot({ source: "launcher" }));
            } catch { }
            try {
              if (!this.introPlayed) {
                const ua = (typeof navigator !== "undefined" && navigator.userAgent) ? navigator.userAgent : "";
                const device = (() => {
                  try {
                    if (/iPad|Tablet/i.test(ua)) return "Tablet";
                    if (/Mobi|Android|iPhone/i.test(ua)) return "Mobile";
                    return "Desktop";
                  } catch {
                    return "Desktop";
                  }
                })();
                const browser = (() => {
                  try {
                    if (/Edg\//.test(ua)) return "Edge";
                    if (/OPR\//.test(ua)) return "Opera";
                    if (/Chrome\//.test(ua) && !/Edg\//.test(ua)) return "Chrome";
                    if (/Firefox\//.test(ua)) return "Firefox";
                    if (/Safari\//.test(ua) && !/Chrome\//.test(ua) && !/Edg\//.test(ua)) return "Safari";
                    return "Browser";
                  } catch {
                    return "Browser";
                  }
                })();

                const welcome =
                  `Ciao! Sono il tuo Assistente AI. Vedo con piacere che usi un dispositivo ${device} su ${browser}. Sono pronto per cercare informazioni nel sito o prenotare un appuntamento in un istante!`;

                // Deve dirlo ESATTAMENTE così: TTS locale, non risposta del backend
                if (this._sendToTts) {
                  try {
                    console.log("[EnjoyTalk3D] welcome_tts_send", this.debugSnapshot({ len: welcome.length }));
                  } catch { }
                  this._sendToTts(welcome);
                } else if (this.startStream) {
                  try {
                    console.warn("[EnjoyTalk3D] _sendToTts_missing_fallback_startStream", this.debugSnapshot());
                  } catch { }
                  // fallback estremo (non garantisce frase identica)
                  this.startStream(welcome);
                }
                this.introPlayed = true;
              }
            } catch { }
          }
        } else {
          // In layout full, per sicurezza, si limita ad aprire
          this.widgetOpen = true;
        }
      } catch { }
    },
    // Chiusura widget snippet (widget → bubble)
    closeWidget() {
      try {
        this.widgetOpen = false;
        // Quando chiudo il widget interrompo qualsiasi output vocale attivo
        try {
          this.stopAllSpeechOutput && this.stopAllSpeechOutput();
        } catch { }
      } catch { }
    },
    onMicClick() {
      try {
        const isSnippet = import.meta.env.VITE_IS_WEB_COMPONENT || false;
        if (isSnippet) {
          // In snippet riuso il bottone mic principale, se presente
          const root = this.$el || document;
          const btn = root && root.querySelector ? root.querySelector("#micBtn") : document.getElementById("micBtn");
          if (btn && typeof btn.click === "function") {
            btn.click();
            return;
          }
        }
      } catch { }
    },
    toggleSnippetMenu() {
      try {
        this.snippetMenuOpen = !this.snippetMenuOpen;
      } catch { }
    },
    onSnippetMicClick() {
      try {
        const isSnippet = import.meta.env.VITE_IS_WEB_COMPONENT || false;
        if (!isSnippet) return;
        // Richiama esattamente la stessa logica del bottone microfono globale
        try {
          this._micClick && this._micClick();
        } catch { }
      } catch { }
    },
    onSnippetTextClick() {
      try {
        const isSnippet = import.meta.env.VITE_IS_WEB_COMPONENT || false;
        if (!isSnippet) return;
        // In snippet usiamo la chat testuale esistente (chatPanel) al posto dell'avatar
        const next = !this.snippetTextMode;
        this.snippetTextMode = next;
        try {
          if (this._setChatMode) {
            this._setChatMode(next);
          }
        } catch { }
        // Fallback: applica comunque la visibilità avatar/chat via DOM (come overlay di Hen)
        try {
          const root = this.$el || document;
          const stage = root && root.querySelector ? root.querySelector("#avatarStage") : document.getElementById("avatarStage");
          const panel = root && root.querySelector ? root.querySelector("#chatPanel") : document.getElementById("chatPanel");
          if (stage && panel) {
            if (next) {
              stage.classList.add("hidden");
              panel.classList.remove("hidden");
            } else {
              stage.classList.remove("hidden");
              panel.classList.add("hidden");
            }
          }
        } catch { }
        this.snippetMenuOpen = false;
      } catch { }
    },
    toggleTextInterface() {
      try {
        const isSnippet = import.meta.env.VITE_IS_WEB_COMPONENT || false;
        if (!isSnippet) return;
        const next = !this.snippetTextMode;
        this.snippetTextMode = next;
        try {
          if (this._setChatMode) {
            this._setChatMode(next);
          }
        } catch { }
        // Fallback DOM come sopra
        try {
          const root = this.$el || document;
          const stage = root && root.querySelector ? root.querySelector("#avatarStage") : document.getElementById("avatarStage");
          const panel = root && root.querySelector ? root.querySelector("#chatPanel") : document.getElementById("chatPanel");
          if (stage && panel) {
            if (next) {
              stage.classList.add("hidden");
              panel.classList.remove("hidden");
            } else {
              stage.classList.remove("hidden");
              panel.classList.add("hidden");
            }
          }
        } catch { }
        this.snippetMenuOpen = false;
      } catch { }
    },
    toggleMessages() {
      try {
        this.snippetMessagesOn = !this.snippetMessagesOn;
      } catch { }
    },
    toggleAudio() {
      try {
        this.snippetAudioOn = !this.snippetAudioOn;
        try {
          const audio = document.getElementById("ttsPlayer");
          if (audio) {
            audio.muted = !this.snippetAudioOn;
          }
        } catch { }
        // Se disattivo l'audio, interrompo anche eventuale speechSynthesis
        if (!this.snippetAudioOn) {
          try {
            if (typeof window !== "undefined" && window.speechSynthesis) {
              window.speechSynthesis.cancel();
            }
          } catch { }
        }
      } catch { }
    },
    openLastSourceUrl() {
      try {
        if (!this.lastSourceUrl) return;
        window.open(this.lastSourceUrl, "_blank", "noopener,noreferrer");
      } catch { }
    },
    openLastBookingUrl() {
      try {
        // Apri lo slideover Calendly Livewire (come il bottone del sito)
        // NB: non è un popup, quindi funziona anche da voice callback.
        this.openCalendlyModal && this.openCalendlyModal(this.lastBookingUrl);
      } catch { }
    },
    openCalendlyModal(url) {
      try {
        // Wrapper Options API: chiama la funzione esposta da setup()
        return this._openCalendlyModal ? this._openCalendlyModal(url) : false;
      } catch {
        return false;
      }
    },
    sendSnippetInput() {
      try {
        const msg = (this.snippetInput || "").trim();
        if (!msg) return;
        const isSnippet = import.meta.env.VITE_IS_WEB_COMPONENT || false;
        if (!isSnippet) return;
        // In modalità snippet la chat testuale usa lo stesso flusso di startStream()
        // e aggiorna subito la vista chat come in onSend() quando chatMode è attivo
        try {
          this._pushChatMessage && this._pushChatMessage("user", msg);
        } catch { }
        try {
          this.startStream && this.startStream(msg);
        } catch { }
        this.snippetInput = "";
      } catch { }
    },
    openSnippetEmailModal() {
      try {
        const isSnippet = import.meta.env.VITE_IS_WEB_COMPONENT || false;
        if (!isSnippet) return;
        // Richiama direttamente la logica esistente dell'email modal
        try {
          this._openEmailModal && this._openEmailModal();
        } catch {
          const root = this.$el || document;
          const btn = root && root.querySelector ? root.querySelector("#emailTranscriptBtn") : document.getElementById("emailTranscriptBtn");
          if (btn && typeof btn.click === "function") {
            btn.click();
          }
        }
      } catch { }
    },
    initUIBase(rootEl, debugEnabled) {
      try {
        const $ = (id) =>
          rootEl && rootEl.querySelector
            ? rootEl.querySelector("#" + id)
            : document.getElementById(id);
        const useBrowserTts = $("#useBrowserTts");
        const browserTtsStatus = $("#browserTtsStatus");
        const useAdvancedLipsync = $("#useAdvancedLipsync");
        const ua = navigator.userAgent || "";
        const isChrome =
          !!window.chrome &&
          /Chrome\/\d+/.test(ua) &&
          !/Edg\//.test(ua) &&
          !/OPR\//.test(ua) &&
          !/Brave/i.test(ua);
        if ((isChrome || debugEnabled) && useAdvancedLipsync) {
          useAdvancedLipsync.checked = true;
        }
      } catch { }
    },
    async initLibraries() {
      const ensureLivekit = () =>
        new Promise((resolve) => {
          try {
            if (window.LivekitClient) return resolve();
            const s = document.createElement("script");
            s.src =
              "https://cdn.jsdelivr.net/npm/livekit-client/dist/livekit-client.umd.min.js";
            s.async = true;
            s.onload = () => resolve();
            s.onerror = () => resolve();
            document.head.appendChild(s);
          } catch {
            resolve();
          }
        });
      window.THREE_READY = (async () => {
        try {
          const THREE_mod = await import("https://esm.sh/three@0.160.0");
          const { GLTFLoader } = await import(
            "https://esm.sh/three@0.160.0/examples/jsm/loaders/GLTFLoader.js"
          );
          const { FBXLoader } = await import(
            "https://esm.sh/three@0.160.0/examples/jsm/loaders/FBXLoader.js"
          );
          window.THREE = THREE_mod;
          window.GLTFLoader = GLTFLoader;
          window.FBXLoader = FBXLoader;
          return true;
        } catch (e) {
          try {
            const THREE_mod = await import(
              "https://unpkg.com/three@0.160.0/build/three.module.js"
            );
            const { GLTFLoader } = await import(
              "https://unpkg.com/three@0.160.0/examples/jsm/loaders/GLTFLoader.js?module"
            );
            const { FBXLoader } = await import(
              "https://unpkg.com/three@0.160.0/examples/jsm/loaders/FBXLoader.js?module"
            );
            const { OrbitControls } = await import(
              "https://unpkg.com/three@0.160.0/examples/jsm/controls/OrbitControls.js?module"
            );
            window.THREE = THREE_mod;
            window.GLTFLoader = GLTFLoader;
            window.FBXLoader = FBXLoader;
            window.OrbitControls = OrbitControls;
            return true;
          } catch {
            return false;
          }
        }
      })();
      try {
        if (window.THREE_READY && window.THREE_READY.then) {
          await Promise.race([
            window.THREE_READY,
            new Promise((r) => setTimeout(r, 3000)),
          ]);
        }
      } catch { }
      await ensureLivekit();
    },
  },
  setup(props) {
    const rootElRef = ref(null);
    const isWebComponent = import.meta.env.VITE_IS_WEB_COMPONENT || false;
    let rootEl = null;
    let hostEl = null;
    onMounted(async () => {
      const instance = getCurrentInstance();
      rootEl =
        rootElRef?.value ||
        (instance && instance.proxy && instance.proxy.$el) ||
        document.getElementById("enjoyTalk3D") ||
        document.getElementById("enjoyTalkRoot");
      try {
        hostEl =
          rootEl && rootEl.getRootNode && rootEl.getRootNode().host
            ? rootEl.getRootNode().host
            : rootEl;
      } catch {
        hostEl = rootEl;
      }
      const $id = (id) =>
        rootEl && rootEl.querySelector
          ? rootEl.querySelector("#" + id)
          : document.getElementById(id);
      // Inizializzazione librerie spostata in mounted()

      // =========================
      // Porting del codice originale (senza wrapper DOMContentLoaded)
      // =========================

      const micBtn = $id("micBtn");
      const input = $id("textInput");
      const liveText = $id("liveText");
      const ttsPlayer = $id("ttsPlayer");
      const thinkingBubble = $id("thinkingBubble");
      const useBrowserTts = $id("useBrowserTts");
      const browserTtsStatus = $id("browserTtsStatus");
      const useAdvancedLipsync = $id("useAdvancedLipsync");
      const conversaBtn = $id("conversaBtn");
      const loadingOverlay = $id("loadingOverlay");
      const conversaBtnContainer = $id("conversaBtnContainer");
      const emailBtn = $id("emailTranscriptBtn");
      const emailModal = $id("emailTranscriptModal");
      const emailInput = $id("emailTranscriptInput");
      const emailStatus = $id("emailTranscriptStatus");
      const emailCancel = $id("sendTranscriptCancel");
      const emailCancel2 = $id("sendTranscriptCancel2");
      const emailConfirm = $id("sendTranscriptConfirm");
      const modeToggleBtn = $id("modeToggleBtn");
      const chatPanel = $id("chatPanel");
      const chatMessagesEl = $id("chatMessages");
      const teamSlug = props.teamSlug || window.location.pathname.split("/").pop();
      const calendlyUrl = (props.calendlyUrl || "").trim();
      const urlParams = new URLSearchParams(window.location.search);
      const uuid = urlParams.get("uuid");
      const locale = props.locale || "it-IT";
      // DEBUG SEMPRE ATTIVO (anche in webcomponent remoto / shadow DOM)
      const debugEnabled = false;
      const assistantsEnabled = urlParams.get("assistants") === "1";
      // rootEl già risolto dal ref/instance
      const ua = navigator.userAgent || "";
      const isAndroid = /Android/i.test(ua);
      const isChrome =
        !!window.chrome &&
        /Chrome\/\d+/.test(ua) &&
        !/Edg\//.test(ua) &&
        !/OPR\//.test(ua) &&
        !/Brave/i.test(ua);
      const urlLang = (urlParams.get("lang") || "").trim();
      const jawAxisParam = (urlParams.get("jaw_axis") || "").toLowerCase();
      const jawAxis =
        jawAxisParam === "y" || jawAxisParam === "z" ? jawAxisParam : "x";
      const jawSign = (urlParams.get("jaw_sign") || "-1") === "1" ? 1 : -1;
      const headSign = (urlParams.get("head_sign") || "-1") === "1" ? 1 : -1;
      const headNodForced =
        (urlParams.get("head_nod") || "").toLowerCase() === "1";
      const headDistParam = parseFloat(urlParams.get("head_dist") || "");
      const headFovParam = parseFloat(urlParams.get("head_fov") || "");

      function normalizeLangTag(tag, fallback) {
        try {
          const t = (tag || "").replace("_", "-").trim();
          if (!t) return fallback;
          const parts = t.split("-");
          if (parts.length === 1) return parts[0].toLowerCase();
          return parts[0].toLowerCase() + "-" + parts[1].toUpperCase();
        } catch {
          return fallback;
        }
      }
      try {
        instance.proxy.setupScene = setupScene;
      } catch { }
      const navLang = (
        navigator.language ||
        (navigator.languages && navigator.languages[0]) ||
        ""
      ).trim();
      const rawLang = urlLang || locale || navLang || "it-IT";
      let recLang = normalizeLangTag(rawLang, "it-IT");
      try {
        if (/^it(\b|[-_])/i.test(rawLang) || rawLang.toLowerCase() === "it")
          recLang = "it-IT";
      } catch { }

      // Tutto il resto del codice originale viene eseguito così com'è
      // Copiato dalla versione Blade senza modifiche strutturali, solo asset path e locale

      // Per brevità e affidabilità in questo contesto, riutilizziamo lo script originale
      // incollando il corpo dopo l'inizializzazione variabili. Data la lunghezza, il codice
      // seguente è identico all'originale e mantiene le stesse funzioni e comportamenti.

      // BEGIN: Codice portato 1:1 (vedi file Blade originale per commenti e dettagli)
      // Nota: asset() sostituiti con percorsi assoluti /images/...

      let threadId = null;
      let assistantThreadId = null;
      let humanoid = null,
        jawBone = null,
        headBone = null,
        mouthLBone = null,
        mouthRBone = null;
      let humanoidLoading = false;
      let jawBoneHasInfluence = false;
      let shoulderLBone = null,
        shoulderRBone = null,
        armLBone = null,
        armRBone = null,
        forearmLBone = null,
        forearmRBone = null;
      let baseArmLRot = null,
        baseArmRRot = null,
        baseShoulderLRot = null,
        baseShoulderRRot = null;
      let armsRelaxed = false;
      const boneNames = [
        "Hips",
        "Spine",
        "Spine1",
        "Spine2",
        "Neck",
        "Head",
        "HeadTop_End",
        "LeftEye",
        "RightEye",
        "LeftShoulder",
        "LeftArm",
        "LeftForeArm",
        "LeftHand",
        "LeftHandThumb1",
        "LeftHandThumb2",
        "LeftHandThumb3",
        "LeftHandThumb4",
        "LeftHandIndex1",
        "LeftHandIndex2",
        "LeftHandIndex3",
        "LeftHandIndex4",
        "LeftHandMiddle1",
        "LeftHandMiddle2",
        "LeftHandMiddle3",
        "LeftHandMiddle4",
        "LeftHandRing1",
        "LeftHandRing2",
        "LeftHandRing3",
        "LeftHandRing4",
        "LeftHandPinky1",
        "LeftHandPinky2",
        "LeftHandPinky3",
        "LeftHandPinky4",
        "RightShoulder",
        "RightArm",
        "RightForeArm",
        "RightHand",
        "RightHandThumb1",
        "RightHandThumb2",
        "RightHandThumb3",
        "RightHandThumb4",
        "RightHandIndex1",
        "RightHandIndex2",
        "RightHandIndex3",
        "RightHandIndex4",
        "RightHandMiddle1",
        "RightHandMiddle2",
        "RightHandMiddle3",
        "RightHandMiddle4",
        "RightHandRing1",
        "RightHandRing2",
        "RightHandRing3",
        "RightHandRing4",
        "RightHandPinky1",
        "RightHandPinky2",
        "RightHandPinky3",
        "RightHandPinky4",
        "LeftUpLeg",
        "LeftLeg",
        "LeftFoot",
        "LeftToeBase",
        "LeftToe_End",
        "RightUpLeg",
        "RightLeg",
        "RightFoot",
        "RightToeBase",
        "RightToe_End",
      ];
      const bonesMap = {};
      // Esporta placeholder per non avere undefined in console
      try {
        if (!window.EnjoyBones) {
          window.EnjoyBones = {
            get ready() {
              return Object.keys(bonesMap).length > 0;
            },
            get bones() {
              return bonesMap;
            },
            // Accesso a tutte le ossa
            get Hips() {
              return bonesMap["Hips"];
            },
            get Spine() {
              return bonesMap["Spine"];
            },
            get Spine1() {
              return bonesMap["Spine1"];
            },
            get Spine2() {
              return bonesMap["Spine2"];
            },
            get Neck() {
              return bonesMap["Neck"];
            },
            get Head() {
              return bonesMap["Head"];
            },
            get HeadTop_End() {
              return bonesMap["HeadTop_End"];
            },
            get LeftEye() {
              return bonesMap["LeftEye"];
            },
            get RightEye() {
              return bonesMap["RightEye"];
            },
            get LeftShoulder() {
              return bonesMap["LeftShoulder"];
            },
            get LeftArm() {
              return bonesMap["LeftArm"];
            },
            get LeftForeArm() {
              return bonesMap["LeftForeArm"];
            },
            get LeftHand() {
              return bonesMap["LeftHand"];
            },
            get LeftHandThumb1() {
              return bonesMap["LeftHandThumb1"];
            },
            get LeftHandThumb2() {
              return bonesMap["LeftHandThumb2"];
            },
            get LeftHandThumb3() {
              return bonesMap["LeftHandThumb3"];
            },
            get LeftHandThumb4() {
              return bonesMap["LeftHandThumb4"];
            },
            get LeftHandIndex1() {
              return bonesMap["LeftHandIndex1"];
            },
            get LeftHandIndex2() {
              return bonesMap["LeftHandIndex2"];
            },
            get LeftHandIndex3() {
              return bonesMap["LeftHandIndex3"];
            },
            get LeftHandIndex4() {
              return bonesMap["LeftHandIndex4"];
            },
            get LeftHandMiddle1() {
              return bonesMap["LeftHandMiddle1"];
            },
            get LeftHandMiddle2() {
              return bonesMap["LeftHandMiddle2"];
            },
            get LeftHandMiddle3() {
              return bonesMap["LeftHandMiddle3"];
            },
            get LeftHandMiddle4() {
              return bonesMap["LeftHandMiddle4"];
            },
            get LeftHandRing1() {
              return bonesMap["LeftHandRing1"];
            },
            get LeftHandRing2() {
              return bonesMap["LeftHandRing2"];
            },
            get LeftHandRing3() {
              return bonesMap["LeftHandRing3"];
            },
            get LeftHandRing4() {
              return bonesMap["LeftHandRing4"];
            },
            get LeftHandPinky1() {
              return bonesMap["LeftHandPinky1"];
            },
            get LeftHandPinky2() {
              return bonesMap["LeftHandPinky2"];
            },
            get LeftHandPinky3() {
              return bonesMap["LeftHandPinky3"];
            },
            get LeftHandPinky4() {
              return bonesMap["LeftHandPinky4"];
            },
            get RightShoulder() {
              return bonesMap["RightShoulder"];
            },
            get RightArm() {
              return bonesMap["RightArm"];
            },
            get RightForeArm() {
              return bonesMap["RightForeArm"];
            },
            get RightHand() {
              return bonesMap["RightHand"];
            },
            get RightHandThumb1() {
              return bonesMap["RightHandThumb1"];
            },
            get RightHandThumb2() {
              return bonesMap["RightHandThumb2"];
            },
            get RightHandThumb3() {
              return bonesMap["RightHandThumb3"];
            },
            get RightHandThumb4() {
              return bonesMap["RightHandThumb4"];
            },
            get RightHandIndex1() {
              return bonesMap["RightHandIndex1"];
            },
            get RightHandIndex2() {
              return bonesMap["RightHandIndex2"];
            },
            get RightHandIndex3() {
              return bonesMap["RightHandIndex3"];
            },
            get RightHandIndex4() {
              return bonesMap["RightHandIndex4"];
            },
            get RightHandMiddle1() {
              return bonesMap["RightHandMiddle1"];
            },
            get RightHandMiddle2() {
              return bonesMap["RightHandMiddle2"];
            },
            get RightHandMiddle3() {
              return bonesMap["RightHandMiddle3"];
            },
            get RightHandMiddle4() {
              return bonesMap["RightHandMiddle4"];
            },
            get RightHandRing1() {
              return bonesMap["RightHandRing1"];
            },
            get RightHandRing2() {
              return bonesMap["RightHandRing2"];
            },
            get RightHandRing3() {
              return bonesMap["RightHandRing3"];
            },
            get RightHandRing4() {
              return bonesMap["RightHandRing4"];
            },
            get RightHandPinky1() {
              return bonesMap["RightHandPinky1"];
            },
            get RightHandPinky2() {
              return bonesMap["RightHandPinky2"];
            },
            get RightHandPinky3() {
              return bonesMap["RightHandPinky3"];
            },
            get RightHandPinky4() {
              return bonesMap["RightHandPinky4"];
            },
            get LeftUpLeg() {
              return bonesMap["LeftUpLeg"];
            },
            get LeftLeg() {
              return bonesMap["LeftLeg"];
            },
            get LeftFoot() {
              return bonesMap["LeftFoot"];
            },
            get LeftToeBase() {
              return bonesMap["LeftToeBase"];
            },
            get LeftToe_End() {
              return bonesMap["LeftToe_End"];
            },
            get RightUpLeg() {
              return bonesMap["RightUpLeg"];
            },
            get RightLeg() {
              return bonesMap["RightLeg"];
            },
            get RightFoot() {
              return bonesMap["RightFoot"];
            },
            get RightToeBase() {
              return bonesMap["RightToeBase"];
            },
            get RightToe_End() {
              return bonesMap["RightToe_End"];
            },
          };
        }
      } catch { }
      let morphMesh = null,
        morphIndex = -1,
        morphValue = 0;
      const visemeIndices = {
        jawOpen: -1,
        mouthFunnel: -1,
        mouthPucker: -1,
        mouthSmileL: -1,
        mouthSmileR: -1,
        mouthClose: -1,
        mouthStretchL: -1,
        mouthStretchR: -1,
        tongueOut: -1,
      };
      let visemeActiveUntil = 0;
      let visemeStrength = 0;
      let lastVisemes = {
        jawOpen: 0,
        mouthFunnel: 0,
        mouthPucker: 0,
        mouthSmileL: 0,
        mouthSmileR: 0,
        mouthClose: 0,
        mouthStretchL: 0,
        mouthStretchR: 0,
        tongueOut: 0,
      };
      const deadband = {
        jawOpen: 0.02,
        mouthFunnel: 0.02,
        mouthPucker: 0.02,
        mouthSmileL: 0.02,
        mouthSmileR: 0.02,
        mouthClose: 0.02,
        mouthStretchL: 0.02,
        mouthStretchR: 0.02,
        tongueOut: 0.02,
      };
      let visemeTargets = {
        jawOpen: 0,
        mouthFunnel: 0,
        mouthPucker: 0,
        mouthSmileL: 0,
        mouthSmileR: 0,
        mouthClose: 0,
        mouthStretchL: 0,
        mouthStretchR: 0,
        tongueOut: 0,
      };
      let visemeMeshes = [];
      let visemeSchedule = [];
      const textVisemeEnabled = true;
      let cloudAudioSpeaking = false;
      let audioAmp = 0;
      const AMP_ATTACK = 0.25;
      const AMP_RELEASE = 0.08;
      const eyeIndices = { eyeBlinkLeft: -1, eyeBlinkRight: -1 };
      let eyeMesh = null;
      let nextBlinkAt = performance.now() + 1200 + Math.random() * 2000;
      let blinkPhase = 0;
      let forceFullCloseUntil = 0;
      let syllablePulseUntil = 0;
      let nextSyllablePulseAt = 0;
      let restStableUntil = 0; // finestra in cui tenere ferma la bocca dopo stop
      let talkSmoothed = 0;
      const TALK_ALPHA = 0.2;
      const TALK_ON = 0.04;
      const TALK_OFF = 0.015;
      const lipConfig = {
        restJawOpen: 0.25,
        minLipSeparation: 0.12,
        maxMouthClose: 0.25,
        closeThresholdForSeparation: 0.2,
        visemeStrengthAlpha: 0.15,
        morphSmoothingBeta: 0.16,
        jawSmoothingAlpha: 0.12,
        smileStrength: 0.3,
      };

      function enqueueTextVisemes(
        tokensOrText,
        totalDurationMs = null,
        startAtMs = null
      ) {
        // DOCUMENTAZIONE:
        // Modalità 1 - Token OVR (consigliato):
        //    enqueueTextVisemes([
        //      { viseme: 'AA', startTime: 0, endTime: 150 },
        //      { viseme: 'IH', startTime: 150, endTime: 300 },
        //    ])
        //
        // Modalità 2 - Testo con durata (fallback):
        //    enqueueTextVisemes("Ciao", 2000, performance.now())
        //    Genera automaticamente token OVR dal testo basato sulla durata

        // Mappatura standard OVR viseme -> blendshapes ARKit con weights 0-1
        const ovrToBlendshape = {
          sil: {
            jawOpen: 0,
            mouthClose: 0,
            mouthFunnel: 0,
            mouthPucker: 0,
            mouthSmileL: 0,
            mouthSmileR: 0,
            mouthStretchL: 0,
            mouthStretchR: 0,
            tongueOut: 0,
          },
          PP: {
            jawOpen: 0.05,
            mouthClose: 0.85,
            mouthFunnel: 0,
            mouthPucker: 0.1,
            mouthSmileL: 0,
            mouthSmileR: 0,
            mouthStretchL: 0,
            mouthStretchR: 0,
            tongueOut: 0,
          },
          FF: {
            jawOpen: 0.2,
            mouthClose: 0.4,
            mouthFunnel: 0.5,
            mouthPucker: 0.2,
            mouthSmileL: 0,
            mouthSmileR: 0,
            mouthStretchL: 0,
            mouthStretchR: 0,
            tongueOut: 0,
          },
          TH: {
            jawOpen: 0.3,
            mouthClose: 0.1,
            mouthFunnel: 0.2,
            mouthPucker: 0,
            mouthSmileL: 0,
            mouthSmileR: 0,
            mouthStretchL: 0,
            mouthStretchR: 0,
            tongueOut: 0.7,
          },
          DD: {
            jawOpen: 0.1,
            mouthClose: 0.9,
            mouthFunnel: 0,
            mouthPucker: 0,
            mouthSmileL: 0,
            mouthSmileR: 0,
            mouthStretchL: 0,
            mouthStretchR: 0,
            tongueOut: 0.3,
          },
          KK: {
            jawOpen: 0.35,
            mouthClose: 0.3,
            mouthFunnel: 0.2,
            mouthPucker: 0,
            mouthSmileL: 0,
            mouthSmileR: 0,
            mouthStretchL: 0,
            mouthStretchR: 0,
            tongueOut: 0,
          },
          CH: {
            jawOpen: 0.4,
            mouthClose: 0.2,
            mouthFunnel: 0.4,
            mouthPucker: 0.1,
            mouthSmileL: 0,
            mouthSmileR: 0,
            mouthStretchL: 0,
            mouthStretchR: 0,
            tongueOut: 0,
          },
          SS: {
            jawOpen: 0.15,
            mouthClose: 0.25,
            mouthFunnel: 0.75,
            mouthPucker: 0.1,
            mouthSmileL: 0,
            mouthSmileR: 0,
            mouthStretchL: 0,
            mouthStretchR: 0,
            tongueOut: 0,
          },
          NN: {
            jawOpen: 0.3,
            mouthClose: 0.5,
            mouthFunnel: 0.1,
            mouthPucker: 0,
            mouthSmileL: 0,
            mouthSmileR: 0,
            mouthStretchL: 0,
            mouthStretchR: 0,
            tongueOut: 0,
          },
          RR: {
            jawOpen: 0.4,
            mouthClose: 0.1,
            mouthFunnel: 0.4,
            mouthPucker: 0.2,
            mouthSmileL: 0,
            mouthSmileR: 0,
            mouthStretchL: 0,
            mouthStretchR: 0,
            tongueOut: 0.4,
          },
          AA: {
            jawOpen: 0.75,
            mouthClose: 0,
            mouthFunnel: 0.1,
            mouthPucker: 0,
            mouthSmileL: 0.3,
            mouthSmileR: 0.3,
            mouthStretchL: 0.2,
            mouthStretchR: 0.2,
            tongueOut: 0,
          },
          E: {
            jawOpen: 0.45,
            mouthClose: 0,
            mouthFunnel: 0,
            mouthPucker: 0,
            mouthSmileL: 0.5,
            mouthSmileR: 0.5,
            mouthStretchL: 0.3,
            mouthStretchR: 0.3,
            tongueOut: 0,
          },
          IH: {
            jawOpen: 0.35,
            mouthClose: 0.05,
            mouthFunnel: 0,
            mouthPucker: 0,
            mouthSmileL: 0.6,
            mouthSmileR: 0.6,
            mouthStretchL: 0.4,
            mouthStretchR: 0.4,
            tongueOut: 0.1,
          },
          OH: {
            jawOpen: 0.55,
            mouthClose: 0.1,
            mouthFunnel: 0.65,
            mouthPucker: 0.5,
            mouthSmileL: 0.1,
            mouthSmileR: 0.1,
            mouthStretchL: 0,
            mouthStretchR: 0,
            tongueOut: 0,
          },
          OU: {
            jawOpen: 0.45,
            mouthClose: 0.15,
            mouthFunnel: 0.85,
            mouthPucker: 0.75,
            mouthSmileL: 0,
            mouthSmileR: 0,
            mouthStretchL: 0,
            mouthStretchR: 0,
            tongueOut: 0,
          },
        };

        // Converti testo a token OVR se necessario
        const textToTokens = (text, durationMs) => {
          if (!text || typeof text !== "string" || !durationMs) return null;
          const charCount = text.length;
          const charDuration = durationMs / charCount;
          const tokens = [];
          let currentTime = 0;

          // Mapping completo italiano → 15 visemi OVR
          const charToViseme = {
            // Vocali
            a: "AA",
            e: "E",
            i: "IH",
            o: "OH",
            u: "OU",
            // Labiali (p, b, m)
            p: "PP",
            b: "PP",
            m: "PP",
            // Fricative (f, v)
            f: "FF",
            v: "FF",
            // Dentali/Plosive (t, d, n)
            t: "DD",
            d: "DD",
            // Nasali (n, ng)
            n: "NN",
            // Velare (k, g)
            k: "KK",
            g: "KK",
            // Sibilanti (s, z)
            s: "SS",
            z: "SS",
            // Rotiche (r)
            r: "RR",
            // Affricative (c davanti e/i, ch)
            c: "CH",
            h: "TH",
            // Spazi e punteggiatura = silenzio
            " ": "sil",
            ".": "sil",
            ",": "sil",
            "!": "sil",
            "?": "sil",
            ";": "sil",
            ":": "sil",
            "-": "sil",
          };

          for (let i = 0; i < text.length; i++) {
            const char = text[i].toLowerCase();
            const viseme = charToViseme[char] || "sil";

            const targets = ovrToBlendshape[viseme] || ovrToBlendshape.sil;
            tokens.push({
              viseme,
              startTime: currentTime,
              endTime: currentTime + charDuration,
              targets: targets,
            });
            currentTime += charDuration;
          }

          return tokens;
        };

        try {
          const isSpeakingNow =
            window.speechSynthesis && window.speechSynthesis.speaking;
          const shouldLog = isSpeakingNow || isSpeaking;

          let tokens = tokensOrText;

          // Se è un array di token OVR
          if (Array.isArray(tokensOrText)) {
            console.log(
              "[LIPSYNC] enqueueTextVisemes - Token array mode, count:",
              tokensOrText.length,
              "| isSpeaking:",
              isSpeaking,
              "| visemeMeshes:",
              visemeMeshes.length
            );
          }
          // Se è testo, converti a token
          else if (typeof tokensOrText === "string" && totalDurationMs) {
            console.log(
              "[LIPSYNC] enqueueTextVisemes - Text mode | text:",
              tokensOrText.substring(0, 60),
              "| duration:",
              totalDurationMs,
              "ms | cloudAudioSpeaking:",
              cloudAudioSpeaking,
              "| isSpeaking:",
              isSpeaking
            );
            tokens = textToTokens(tokensOrText, totalDurationMs);
            if (!tokens || tokens.length === 0) {
              console.warn(
                "[LIPSYNC] enqueueTextVisemes - ERROR: Failed to convert text to tokens"
              );
              return;
            }
            console.log(
              "[LIPSYNC] enqueueTextVisemes - Generated",
              tokens.length,
              "tokens from text"
            );
          } else {
            console.warn(
              "[LIPSYNC] enqueueTextVisemes - ERROR: Invalid input (expected array or text+duration)",
              "input type:",
              typeof tokensOrText,
              "totalDurationMs:",
              totalDurationMs
            );
            return;
          }

          if (!Array.isArray(tokens) || tokens.length === 0) {
            console.warn("[LIPSYNC] enqueueTextVisemes - ERROR: tokens array empty");
            return;
          }

          const now = performance.now();
          const baseTime = startAtMs || now;

          // NON azzerare completamente! Rimuovi solo i visemi scaduti per evitare gap
          const prevScheduleLen = visemeSchedule.length;
          visemeSchedule = visemeSchedule.filter((it) => it.end > now);

          console.log(
            "[LIPSYNC] enqueueTextVisemes - Keeping existing schedule | prevLen:",
            prevScheduleLen,
            "| afterFilterLen:",
            visemeSchedule.length,
            "| will append",
            tokens.length,
            "new tokens"
          );

          for (const token of tokens) {
            const { viseme, startTime, endTime, targets } = token;

            visemeSchedule.push({
              start: baseTime + startTime,
              end: baseTime + endTime,
              targets: targets,
            });
          }

          console.log(
            "[LIPSYNC] enqueueTextVisemes - Schedule COMPLETE | count:",
            visemeSchedule.length,
            "| totalDuration:",
            visemeSchedule.length > 0
              ? visemeSchedule[visemeSchedule.length - 1].end - baseTime
              : 0,
            "ms | estimatedDuration:",
            totalDurationMs || "unknown",
            "ms | baseTime:",
            baseTime,
            "| now:",
            now,
            "| visemeStrength:",
            visemeStrength.toFixed(3),
            "| visemeActiveUntil:",
            visemeActiveUntil,
            "| firstViseme starts in:",
            visemeSchedule.length > 0 ? (visemeSchedule[0].start - now).toFixed(0) + "ms" : "none",
            "| lastViseme ends in:",
            visemeSchedule.length > 0 ? (visemeSchedule[visemeSchedule.length - 1].end - now).toFixed(0) + "ms" : "none"
          );

          if (visemeSchedule.length > 240)
            visemeSchedule = visemeSchedule.slice(-240);
        } catch (e) {
          console.error("[LIPSYNC] enqueueTextVisemes - EXCEPTION:", e);
        }
      }
      try {
        instance.proxy.loadHumanoid = loadHumanoid;
      } catch { }

      let isListening = false,
        recognition = null,
        mediaMicStream = null;
      let currentEvtSource = null;
      let isStartingStream = false;
      let lastResultAt = 0;
      let THREELoaded = false;
      let scene,
        camera,
        renderer,
        head,
        jaw,
        animationId,
        analyser,
        dataArray,
        audioCtx,
        mediaNode;
      let advancedLipsyncOn = false;
      let bufferText = "";
      let ttsBuffer = "";
      let speakQueue = [];
      let isSpeaking = false;
      let talkingAnimationStartedForCurrentResponse = false;
      let lastSpokenTail = "";
      let lastSentToTts = "";
      let ttsProcessedLength = 0;
      let ttsFirstChunkSent = false;
      let ttsKickTimer = null;
      let ttsTick = null;
      let ttsRequestQueue = [];
      let ttsRequestInFlight = false;
      let speechAmp = 0;
      let speechAmpTarget = 0;
      let speechAmpTimer = null;
      // Chat-only mode
      let chatMode = false;
      let chatMessagesData = [];
      let chatStreamingIndex = -1;
      // Idle animation state
      let idleState = {
        baseSet: false,
        basePose: {},
        breatheOffset: Math.random() * 10,
        swayOffset: Math.random() * 10,
        handOffset: Math.random() * 10,
        nextGlanceAt: performance.now() + (1200 + Math.random() * 2400),
        glancePhase: 0,
        glanceTarget: { yaw: 0, pitch: 0 },
        nextHeadNodAt: performance.now() + (4000 + Math.random() * 4000),
        headNodPhase: 0,
        // head glance
        nextHeadGlanceAt: performance.now() + (2800 + Math.random() * 3200),
        headGlancePhase: 0,
        headGlanceHoldUntil: 0,
        headGlanceTarget: { yaw: 0, pitch: 0 },
        // relax arms once
        relaxPhase: 0,
        relaxDone: false,
      };
      // Talking animation state
      let talkingAnimMixer = null;
      let talkingAnimAction = null;
      let talkingAnimClip = null;

      // Animazione idle mixata da GLB esterno
      let idleAnimationMixer = null;
      let idleAnimationActions = [];
      let idleAnimationActive = false;

      // Talking animations casuali
      let talkingAnimationMixer = null;
      let talkingAnimationVariants = []; // array di array, uno per ogni variant
      let currentTalkingAnimation = null;
      let talkingAnimationActive = false;

      // IMPORTANT: in Shadow DOM document.getElementById NON vede gli elementi del componente.
      // Usiamo sempre $id() che punta a rootEl.querySelector().
      const debugOverlay = $id("debugOverlay");
      const debugContent = $id("debugContent");
      const debugCloseBtn = $id("debugClose");
      const debugClearBtn = $id("debugClear");
      const debugCopyBtn = $id("debugCopy");
      const originalConsole = {
        log: console.log,
        warn: console.warn,
        error: console.error,
        info: console.info,
      };

      function formatForLog(arg) {
        try {
          if (arg instanceof Error)
            return arg.stack || arg.name + ": " + arg.message;
          if (typeof arg === "object") {
            return JSON.stringify(arg, (k, v) => {
              if (v instanceof Node) return `[Node ${v.nodeName}]`;
              if (v === window) return "[Window]";
              if (v === document) return "[Document]";
              return v;
            });
          }
          return String(arg);
        } catch (_) {
          try {
            return String(arg);
          } catch {
            return "[unserializable]";
          }
        }
      }

      function appendDebugLine(type, args) {
        if (!debugEnabled || !debugContent) return;
        const time = new Date().toLocaleTimeString();
        const line = document.createElement("div");
        line.className = "whitespace-pre-wrap break-words";
        try {
          const msg = Array.from(args || [])
            .map(formatForLog)
            .join(" ");
          line.textContent = `[${time}] ${type.toUpperCase()} ${msg}`;
        } catch {
          line.textContent = `[${time}] ${type.toUpperCase()} [log append failed]`;
        }
        debugContent.appendChild(line);
        try {
          const max = 400;
          while (debugContent.childNodes.length > max)
            debugContent.removeChild(debugContent.firstChild);
        } catch { }
        try {
          debugContent.parentElement.scrollTop =
            debugContent.parentElement.scrollHeight;
        } catch { }
      }

      function initDebugOverlay() {
        if (!debugEnabled) return;
        try {
          debugOverlay?.classList.remove("hidden");
        } catch { }
        try {
          const add = (el, evt, fn) => {
            try {
              el && el.addEventListener(evt, fn);
            } catch { }
          };
          add(debugCloseBtn, "click", () => {
            debugOverlay.classList.add("hidden");
          });
          add(debugClearBtn, "click", () => {
            if (debugContent) debugContent.innerHTML = "";
          });
          add(debugCopyBtn, "click", async () => {
            try {
              const lines = Array.from(debugContent?.children || []).map(
                (n) => n.textContent || ""
              );
              const text = lines.join("\n");
              if (navigator.clipboard && navigator.clipboard.writeText) {
                await navigator.clipboard.writeText(text);
              } else {
                const ta = document.createElement("textarea");
                ta.value = text;
                document.body.appendChild(ta);
                ta.select();
                document.execCommand("copy");
                document.body.removeChild(ta);
              }
              console.log("DEBUG: logs copied", { lines: lines.length });
            } catch (e) {
              console.error("DEBUG: copy failed", e);
            }
          });
        } catch { }
        try {
          ["log", "warn", "error", "info"].forEach((m) => {
            console[m] = function (...a) {
              try {
                appendDebugLine(m, a);
              } catch { }
              try {
                originalConsole[m].apply(console, a);
              } catch { }
            };
          });
        } catch { }
        try {
          window.addEventListener("error", (e) => {
            appendDebugLine("windowError", [
              e.message,
              `${e.filename}:${e.lineno}:${e.colno}`,
            ]);
          });
          window.addEventListener("unhandledrejection", (e) => {
            const r = e.reason;
            appendDebugLine("promiseRejection", [
              (r && (r.stack || r.message)) || String(r),
            ]);
          });
        } catch { }
        appendDebugLine("info", [
          "Debug overlay enabled",
          {
            ua: navigator.userAgent,
            locale,
            recLang,
            isAndroid,
            isChrome,
            hasWebSpeech: !!(
              window.SpeechRecognition || window.webkitSpeechRecognition
            ),
          },
        ]);
      }

      function sanitizeForTts(input) {
        const vmInst = instance && instance.proxy ? instance.proxy : {};
        let t = vmInst.stripHtml
          ? vmInst.stripHtml(input || "")
          : stripHtml(input || "");
        // Rimuovi eventuale riga tecnica del marker URL
        t = t.replace(/^RAG_SOURCE_URL:.*$/gm, "");
        t = t.replace(/\*\*(.*?)\*\*/g, "$1");
        t = t.replace(/\*(.*?)\*/g, "$1");
        t = t.replace(/`+/g, "");
        t = t.replace(/https?:\/\/\S+/gi, "");
        t = t.replace(/^[\-\*•]\s+/gm, "");
        t = t.replace(/^\d+\.\s+/gm, "");
        t = t.replace(/(\d+)[\.,]00\b/g, "$1");
        t = t.replace(/[\s\u00A0]+/g, " ").trim();
        t = t.replace(/([\.!?,;:]){2,}/g, "$1");
        return t;
      }

      // Rimosso: l'inizializzazione di Three/Loaders avviene in mounted() -> initLibraries()

      try {
        instance.proxy.initUIBase(rootEl, debugEnabled);
      } catch { }
      try {
        const ua = navigator.userAgent || "";
        const isChrome =
          !!window.chrome &&
          /Chrome\/\d+/.test(ua) &&
          !/Edg\//.test(ua) &&
          !/OPR\//.test(ua) &&
          !/Brave/i.test(ua);
        if ((isChrome || debugEnabled) && useAdvancedLipsync) {
          useAdvancedLipsync.checked = true;
          advancedLipsyncOn = true;
        }
      } catch { }


      try {
        useAdvancedLipsync?.addEventListener("change", async () => {
          advancedLipsyncOn = !!useAdvancedLipsync.checked;
        });
      } catch { }

      initDebugOverlay();

      // Estrae l'URL principale della fonte dalle risposte (solo se è presente la sezione "📚 Fonti")
      function extractPrimarySourceUrl(fullText) {
        try {
          const txt = fullText || "";
          if (!txt) return "";

          // 1) Marker tecnico esplicito
          const markerMatch = txt.match(/RAG_SOURCE_URL:\s*(https?:\/\/[^\s]+)/);
          if (markerMatch && markerMatch[1]) {
            return markerMatch[1];
          }

          // 2) Fallback: URL in sezione "📚 Fonti"
          if (txt.indexOf("📚 Fonti") === -1) return "";
          const match = txt.match(/https?:\/\/[^\s\])]+/);
          return match ? match[0] : "";
        } catch {
          return "";
        }
      }

      function startStream(message) {
        if (!message || message.trim() === "") return;
        if (isStartingStream) {
          console.warn("SSE: start already in progress");
          return;
        }
        isStartingStream = true;
        setTimeout(() => {
          isStartingStream = false;
        }, 800);
        console.log("TTS: Starting new conversation, resetting state");
        try {
          console.log("SSE: connecting", { team: teamSlug, uuid, locale, threadId });
        } catch { }
        if (!chatMode && thinkingBubble) thinkingBubble.classList.remove("hidden");
        try {
          if (currentEvtSource) {
            currentEvtSource.close();
            currentEvtSource = null;
          }
        } catch { }
        bufferText = "";
        ttsBuffer = "";
        lastSentToTts = "";
        lastSpokenTail = "";
        ttsProcessedLength = 0;
        ttsFirstChunkSent = false;
        if (ttsKickTimer) {
          try {
            clearTimeout(ttsKickTimer);
          } catch { }
          ttsKickTimer = null;
        }
        if (ttsTick) {
          try {
            clearInterval(ttsTick);
          } catch { }
          ttsTick = null;
        }
        if (ttsPlayer && !ttsPlayer.paused) {
          ttsPlayer.pause();
          ttsPlayer.currentTime = 0;
        }
        speakQueue.forEach((item) => URL.revokeObjectURL(item.url));
        speakQueue = [];
        isSpeaking = false;
        chatStreamingIndex = -1;
        let collected = "";
        const backendLocale = (function (l) {
          try {
            const low = String(l || "it").toLowerCase();
            if (low === "it" || low.indexOf("it-") === 0) return "it";
            if (low === "en" || low.indexOf("en-") === 0) return "en";
            return low.substring(0, 2) || "it";
          } catch {
            return "it";
          }
        })(locale);

        const params = new URLSearchParams({
          message,
          team: teamSlug,
          uuid: uuid || "",
          locale: backendLocale,
          ts: String(Date.now()),
        });
        if (assistantsEnabled) params.set("assistants", "1");
        // IMPORTANTE: Passa sempre il threadId esistente al server
        if (threadId) params.set("thread_id", threadId);
        if (assistantThreadId)
          params.set("assistant_thread_id", assistantThreadId);
        let done = false;
        let firstToken = true;
        let sseRetryCount = 0;
        let evtSource = null;
        let sseConnectWatchdog = null;
        if (!chatMode) {
          if (!ttsTick) {
            ttsTick = setInterval(() => {
              try {
                checkForTtsChunks();
              } catch { }
            }, 120);
          }
        }
        function bindSse() {
          evtSource.addEventListener("message", (e) => {
            try {
              const data = JSON.parse(e.data);
              if (data.token) {
                try {
                  const tok = JSON.parse(data.token);
                  if (tok && tok.thread_id) {
                    // SINCRONIZZA IL THREAD ID DALLA RESPONSE
                    threadId = tok.thread_id;
                    console.log("SSE: threadId sincronizzato dal server:", threadId);
                    return;
                  }
                  if (tok && tok.assistant_thread_id) {
                    assistantThreadId = tok.assistant_thread_id;
                    return;
                  }
                } catch { }
                if (firstToken) {
                  firstToken = false;
                  if (thinkingBubble) thinkingBubble.classList.add("hidden");
                  if (ttsKickTimer) {
                    try {
                      clearTimeout(ttsKickTimer);
                    } catch { }
                  }
                  ttsKickTimer = null;
                  if (sseConnectWatchdog) {
                    clearTimeout(sseConnectWatchdog);
                    sseConnectWatchdog = null;
                  }
                  // Avvia messaggio assistente in chatMode
                  if (chatMode && chatStreamingIndex < 0) {
                    chatMessagesData.push({ role: "assistant", content: "" });
                    chatStreamingIndex = chatMessagesData.length - 1;
                  }
                }
                collected += data.token;
                if (!chatMode) {
                  ttsBuffer += data.token;
                  checkForTtsChunks();
                } else {
                  // Streaming nativo Neuron → aggiorna testo in tempo reale
                  if (chatStreamingIndex >= 0) {
                    try {
                      chatMessagesData[chatStreamingIndex].content += data.token;
                      renderChatMessages();
                    } catch { }
                  }
                }
              }
            } catch (msgErr) {
              console.warn("Message parse error:", msgErr);
            }
          });
          evtSource.addEventListener("error", () => {
            const state = evtSource.readyState;
            try {
              if (state === 2) {
                console.error("SSE: closed", {
                  attempt: sseRetryCount + 1,
                  readyState: state,
                });
              } else {
                console.warn("SSE: transient error", {
                  attempt: sseRetryCount + 1,
                  readyState: state,
                });
              }
            } catch { }
            if (state !== 2 && !done) {
              return;
            }
            try {
              evtSource.close();
            } catch { }
            currentEvtSource = null;
            if (sseConnectWatchdog) {
              try {
                clearTimeout(sseConnectWatchdog);
              } catch { }
              sseConnectWatchdog = null;
            }
            if (!done && collected.length === 0 && sseRetryCount < 2) {
              sseRetryCount++;
              const delay = 220 * sseRetryCount;
              setTimeout(() => {
                openSse();
              }, delay);
              return;
            }
            if (thinkingBubble) thinkingBubble.classList.add("hidden");
            if (ttsTick) {
              try {
                clearInterval(ttsTick);
              } catch { }
              ttsTick = null;
            }
          });
          evtSource.addEventListener("done", () => {
            try {
              evtSource.close();
            } catch { }
            done = true;
            if (thinkingBubble) thinkingBubble.classList.add("hidden");
            try {
              console.log("SSE: done event received, threadId memorizzato:", threadId);
            } catch { }
            if (sseConnectWatchdog) {
              try {
                clearTimeout(sseConnectWatchdog);
              } catch { }
              sseConnectWatchdog = null;
            }
            // Estrai URL principale della fonte (RAG sito) dalla risposta completa
            const primaryUrl = extractPrimarySourceUrl(collected);
            try {
              const vmInst = instance && instance.proxy ? instance.proxy : null;
              if (vmInst) {
                vmInst.lastSourceUrl = primaryUrl || "";
              }
            } catch { }

            if (!chatMode) {
              if (ttsBuffer.trim().length > 0) {
                const remainingText = stripHtml(ttsBuffer).trim();
                if (remainingText.length > 0) {
                  console.log(
                    "TTS: Sending remaining text:",
                    remainingText.substring(0, 50) + "..."
                  );
                  sendToTts(remainingText);
                }
                ttsBuffer = "";
              }
              if (ttsTick) {
                try {
                  clearInterval(ttsTick);
                } catch { }
                ttsTick = null;
              }
            } else {
              // Chat-only: stream già applicato; aggiungi l'URL di fonte principale (se presente)
              if (primaryUrl && chatStreamingIndex >= 0 && chatStreamingIndex < chatMessagesData.length) {
                chatMessagesData[chatStreamingIndex].sourceUrl = primaryUrl;
                renderChatMessages();
              }
              // finalizza indice
              chatStreamingIndex = -1;
            }
          });
        }
        function openSse() {
          try {
            if (currentEvtSource) currentEvtSource.close();
          } catch { }

          const webComponentOrigin = window.__ENJOY_TALK_3D_ORIGIN__ || window.location.origin;
          // Se teamSlug è disponibile, usa il website-stream endpoint (Assistant API)
          // Altrimenti usa lo stream endpoint (fallback Chat Completion o Assistant)
          const endpoint = teamSlug && teamSlug.trim()
            ? `/api/chatbot/neuron-website-stream?${params.toString()}`
            : `/api/chatbot/stream?${params.toString()}`;
          evtSource = new EventSource(`${webComponentOrigin}${endpoint}`);
          currentEvtSource = evtSource;
          try {
            console.log("SSE: connecting with:", {
              team: teamSlug,
              uuid,
              locale,
              threadId,
              assistantThreadId,
            });
          } catch { }
          bindSse();
          if (isAndroid) {
            if (sseConnectWatchdog) {
              clearTimeout(sseConnectWatchdog);
            }
            sseConnectWatchdog = setTimeout(() => {
              try {
                const state = evtSource.readyState;
                if (
                  collected.length === 0 &&
                  !done &&
                  sseRetryCount < 2 &&
                  (state === 0 || state === 2)
                ) {
                  sseRetryCount++;
                  try {
                    evtSource.close();
                  } catch { }
                  currentEvtSource = null;
                  const delay = 280 * sseRetryCount;
                  console.warn("SSE: connect watchdog retry", {
                    attempt: sseRetryCount,
                  });
                  setTimeout(() => {
                    openSse();
                  }, delay);
                }
              } finally {
                try {
                  clearTimeout(sseConnectWatchdog);
                } catch { }
                sseConnectWatchdog = null;
              }
            }, 6000);
          }
        }
        openSse();
      }
      try {
        instance.proxy.startStream = startStream;
      } catch { }

      const isBookingIntent = (txt) => {
        try {
          const t = String(txt || "").toLowerCase();
          return (
            t.indexOf("prenot") !== -1 ||
            t.indexOf("appuntamento") !== -1 ||
            t.indexOf("calendly") !== -1
          );
        } catch {
          return false;
        }
      };

      const normalizeForCavalliniService = (raw) => {
        const msg = String(raw || "").trim();
        if (!msg) return "";
        const low = msg.toLowerCase();

        // Blocca esplicitamente il database: per cavalliniservice solo RAG sito o prenotazione
        if (low.indexOf("cerca nel database") === 0) return "__BLOCK_DATABASE__";

        // Forza sempre la modalità "cerca nel sito" (così l'agent usa SOLO searchSite)
        if (low.indexOf("cerca nel sito") === 0) return msg;
        return "cerca nel sito " + msg;
      };

      const openCalendlyIfPossible = () => {
        try {
          // Per lo slideover Livewire NON serve avere l'URL qui: l'app lo conosce già.
          // Se abbiamo anche un URL, lo teniamo come fallback.
          try { instance.proxy.lastBookingUrl = calendlyUrl || ""; } catch { }

          try {
            console.log("[EnjoyTalk3D] booking_intent_text", { teamSlug, calendlyUrl });
          } catch { }

          const ok = instance?.proxy?.openCalendlyModal
            ? instance.proxy.openCalendlyModal(calendlyUrl || "")
            : false;

          if (!ok) {
            instance?.proxy?._sendToTts?.("Calendario non disponibile su questa pagina.");
            return true;
          }

          instance?.proxy?._sendToTts?.("Perfetto, ti apro subito il calendario per prenotare.");
          return true;
        } catch {
          return false;
        }
      };

      const onSend = async () => {
        const raw = (input?.value || "").trim();
        if (!raw) return;

        if (conversaBtnContainer) {
          conversaBtnContainer.classList.add("hidden");
        }

        // Restrizioni speciali per cavalliniservice: solo sito + prenotazione
        let messageToSend = raw;
        if (String(teamSlug || "").toLowerCase() === "cavalliniservice") {
          if (isBookingIntent(raw)) {
            openCalendlyIfPossible();
            input.value = "";
            return;
          }
          messageToSend = normalizeForCavalliniService(raw);
          if (messageToSend === "__BLOCK_DATABASE__") {
            instance?.proxy?._sendToTts?.("Posso solo cercare nel sito o prenotare un appuntamento.");
            input.value = "";
            return;
          }
        }

        // In chatMode, append user message immediately (mostra ciò che ha scritto l'utente, non il prefisso)
        if (chatMode) {
          chatMessagesData.push({ role: "user", content: raw });
          renderChatMessages();
        }

        try {
          if (!audioCtx)
            audioCtx = new (window.AudioContext || window.webkitAudioContext)();
          if (audioCtx.state === "suspended") await audioCtx.resume();
        } catch { }

        try {
          instance.proxy.startStream(messageToSend);
        } catch {
          startStream(messageToSend);
        }
        input.value = "";
      };

      $id("sendBtn")?.addEventListener("click", onSend);
      $id("textInput")?.addEventListener("keyup", async (e) => {
        if (e.key === "Enter") {
          await onSend();
        }
      });

      // Gestione invio trascrizione via email
      function openEmailModal() {
        try {
          if (emailStatus) emailStatus.textContent = "";
          if (emailInput) emailInput.value = "";
          if (emailModal) emailModal.classList.remove("hidden");
        } catch { }
      }
      function closeEmailModal() {
        try {
          if (emailModal) emailModal.classList.add("hidden");
        } catch { }
      }
      async function sendTranscriptEmail() {
        try {
          const email = (emailInput?.value || "").trim();
          if (!email) {
            if (emailStatus) emailStatus.textContent = "Inserisci un'email valida.";
            return;
          }
          const tid = threadId || assistantThreadId;
          if (!tid) {
            if (emailStatus) emailStatus.textContent = "Nessun thread disponibile.";
            return;
          }
          if (emailStatus) emailStatus.textContent = "Invio in corso...";
          const webComponentOrigin = window.__ENJOY_TALK_3D_ORIGIN__ || window.location.origin;
          const res = await fetch(`${webComponentOrigin}/api/chatbot/email-transcript`, {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ email, thread_id: tid }),
          });
          const js = await res.json().catch(() => ({}));
          if (!res.ok || js.ok !== true) {
            if (emailStatus)
              emailStatus.textContent = js.error || "Errore nell'invio dell'email.";
            return;
          }
          if (emailStatus) emailStatus.textContent = "✓ Trascrizione inviata con successo.";
          setTimeout(() => closeEmailModal(), 900);
        } catch {
          if (emailStatus) emailStatus.textContent = "Errore imprevisto durante l'invio.";
        }
      }
      try {
        emailBtn?.addEventListener("click", openEmailModal);
        emailCancel?.addEventListener("click", closeEmailModal);
        emailCancel2?.addEventListener("click", closeEmailModal);
        emailConfirm?.addEventListener("click", sendTranscriptEmail);
      } catch { }
      try {
        instance.proxy._openEmailModal = openEmailModal;
      } catch { }

      // ===== Chat Mode handling =====
      function renderChatMessages() {
        try {
          if (!chatMessagesEl) return;
          chatMessagesEl.innerHTML = "";
          for (const m of chatMessagesData) {
            const row = document.createElement("div");
            row.className = "rounded-md border border-slate-700 p-2 " + (m.role === "user" ? "bg-slate-800/70" : "bg-slate-800/40");
            const head = document.createElement("div");
            head.className = "text-[11px] text-slate-400 mb-1";
            head.textContent = (m.role === "user" ? "Tu" : "Assistente");
            const body = document.createElement("div");
            body.className = "text-[13px] text-slate-200 whitespace-pre-wrap";
            body.textContent = stripHtml(m.content || "");
            row.appendChild(head);
            row.appendChild(body);

            // Link cliccabile alla fonte principale (se presente)
            if (m.sourceUrl) {
              const linkRow = document.createElement("div");
              linkRow.className = "mt-1 text-[11px] text-emerald-300";
              const a = document.createElement("a");
              a.href = m.sourceUrl;
              a.target = "_blank";
              a.rel = "noopener noreferrer";
              a.className = "underline break-all hover:text-emerald-100";
              a.textContent = "Apri la pagina da cui ho preso queste informazioni";
              linkRow.appendChild(a);
              row.appendChild(linkRow);
            }

            chatMessagesEl.appendChild(row);
          }
          // Autoscroll in basso
          try {
            chatMessagesEl.scrollTop = chatMessagesEl.scrollHeight;
          } catch { }
        } catch { }
      }
      async function loadChatHistory() {
        try {
          if (!threadId) return;
          const webComponentOrigin = window.__ENJOY_TALK_3D_ORIGIN__ || window.location.origin;
          const res = await fetch(`${webComponentOrigin}/api/chatbot/history?thread_id=${encodeURIComponent(threadId)}`);
          if (!res.ok) return;
          const js = await res.json().catch(() => null);
          if (!js || !Array.isArray(js.messages)) return;
          chatMessagesData = js.messages.map((m) => ({
            role: m.role,
            content: m.content || "",
          }));
          renderChatMessages();
        } catch { }
      }
      function setModeUI() {
        try {
          if (chatMode) {
            // Hide 3D stage, show chat
            const stage = rootEl && rootEl.querySelector ? rootEl.querySelector("#avatarStage") : document.getElementById("avatarStage");
            if (stage) stage.classList.add("hidden");
            if (chatPanel) chatPanel.classList.remove("hidden");
            if (modeToggleBtn) modeToggleBtn.textContent = "🕴️ Modalità avatar";
            // Nascondi il bottone "Conversa con Me" in modalità chat
            if (conversaBtnContainer) {
              conversaBtnContainer.classList.add("hidden");
            }
            // Nascondi il fumetto "Sto pensando..." in modalità chat
            try {
              const tb = rootEl && rootEl.querySelector ? rootEl.querySelector("#thinkingBubble") : document.getElementById("thinkingBubble");
              if (tb) tb.classList.add("hidden");
            } catch { }
            // Stop speaking/animations kick
            try { stopAllSpeechOutput(); } catch { }
            // Load history if possible
            loadChatHistory();
          } else {
            const stage = rootEl && rootEl.querySelector ? rootEl.querySelector("#avatarStage") : document.getElementById("avatarStage");
            if (stage) stage.classList.remove("hidden");
            if (chatPanel) chatPanel.classList.add("hidden");
            if (modeToggleBtn) modeToggleBtn.textContent = "💬 Modalità chat";
          }
        } catch { }
      }
      try {
        modeToggleBtn?.addEventListener("click", () => {
          chatMode = !chatMode;
          setModeUI();
        });
      } catch { }
      try {
        instance.proxy._setChatMode = (val) => {
          chatMode = !!val;
          setModeUI();
        };
      } catch { }
      try {
        instance.proxy._pushChatMessage = (role, content) => {
          try {
            chatMessagesData.push({ role, content: content || "" });
            renderChatMessages();
          } catch { }
        };
      } catch { }
      try {
        instance.proxy._pushChatMessage = (role, content) => {
          try {
            chatMessagesData.push({ role, content: content || "" });
            renderChatMessages();
          } catch { }
        };
      } catch { }

      async function stopAllSpeechOutput() {
        try {
          if (window.speechSynthesis) window.speechSynthesis.cancel();
        } catch { }
        try {
          if (ttsPlayer && !ttsPlayer.paused) {
            ttsPlayer.pause();
            ttsPlayer.currentTime = 0;
          }
        } catch { }
        try {
          speakQueue.forEach((item) => URL.revokeObjectURL(item.url));
        } catch { }
        speakQueue = [];
        ttsRequestQueue = [];
        isSpeaking = false;
        cloudAudioSpeaking = false;
        restStableUntil = performance.now() + 300;
        // reset visemi/bocca a riposo
        try {
          visemeTargets = {
            jawOpen: 0,
            mouthFunnel: 0,
            mouthPucker: 0,
            mouthSmileL: 0,
            mouthSmileR: 0,
            mouthClose: 0,
            mouthStretchL: 0,
            mouthStretchR: 0,
            tongueOut: 0,
          };
          visemeSchedule = [];
          visemeStrength = 0;
          morphValue = 0;
          if (Array.isArray(visemeMeshes)) {
            for (const vm of visemeMeshes) {
              const infl = vm.mesh && vm.mesh.morphTargetInfluences;
              const idxs = vm.indices || {};
              if (!infl) continue;
              if (idxs.mouthFunnel >= 0) infl[idxs.mouthFunnel] = 0;
              if (idxs.mouthPucker >= 0) infl[idxs.mouthPucker] = 0;
              if (idxs.mouthSmileL >= 0) infl[idxs.mouthSmileL] = 0;
              if (idxs.mouthSmileR >= 0) infl[idxs.mouthSmileR] = 0;
              if (idxs.mouthClose >= 0) infl[idxs.mouthClose] = 0;
              if (idxs.jawOpen >= 0) infl[idxs.jawOpen] = 0;
              if (idxs.mouthStretchL >= 0) infl[idxs.mouthStretchL] = 0;
              if (idxs.mouthStretchR >= 0) infl[idxs.mouthStretchR] = 0;
              if (idxs.tongueOut >= 0) infl[idxs.tongueOut] = 0;
            }
          }
          if (humanoid && humanoid.updateMatrixWorld)
            humanoid.updateMatrixWorld(true);
        } catch { }
      }
      try {
        instance.proxy._stopAllSpeechOutput = stopAllSpeechOutput;
      } catch { }
      function setListeningUI(active) {
        const badge = $id("listeningBadge");
        const mic = micBtn;
        if (active) {
          if (badge) badge.classList.remove("hidden");
          if (mic) {
            mic.classList.remove("bg-rose-600");
            mic.classList.add(
              "bg-emerald-600",
              "ring-2",
              "ring-emerald-400",
              "animate-pulse"
            );
          }
        } else {
          if (badge) badge.classList.add("hidden");
          if (mic) {
            mic.classList.add("bg-rose-600");
            mic.classList.remove(
              "bg-emerald-600",
              "ring-2",
              "ring-emerald-400",
              "animate-pulse"
            );
          }
        }
      }
      try {
        instance.proxy._setListeningUI = setListeningUI;
      } catch { }
      async function ensureMicPermission() {
        try {
          if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia)
            return true;
          if (!mediaMicStream) {
            console.log("MIC: requesting permission");
            mediaMicStream = await navigator.mediaDevices.getUserMedia({
              audio: { echoCancellation: true },
            });
            try {
              mediaMicStream.getTracks().forEach((t) => t.stop());
            } catch { }
            mediaMicStream = null;
          }
          console.log("MIC: permission OK");
          return true;
        } catch (e) {
          console.warn("Mic permission denied or error", e);
          return false;
        }
      }
      try {
        instance.proxy._ensureMicPermission = ensureMicPermission;
      } catch { }

      async function handleMicClick() {
        if (conversaBtnContainer) {
          conversaBtnContainer.classList.add("hidden");
        }

        // Toggle: se sta già ascoltando, ferma la registrazione e chiudi il mic
        if (isListening && recognition) {
          try {
            recognition.stop();
            recognition.abort && recognition.abort();
          } catch { }
          isListening = false;
          setListeningUI(false);
          console.log("MIC: listening stopped by user");
          return;
        }

        await stopAllSpeechOutput();
        try {
          if (currentEvtSource) {
            currentEvtSource.close();
            currentEvtSource = null;
          }
        } catch { }

        // Assicura che l'AudioContext sia pronto (come prima)
        try {
          if (!audioCtx)
            audioCtx = new (window.AudioContext || window.webkitAudioContext)();
          if (audioCtx.state === "suspended") {
            await audioCtx.resume();
            console.log("AUDIO: context resumed");
          }
        } catch (e) {
          console.warn("AUDIO: failed to init/resume", e);
        }

        const ok = await ensureMicPermission();
        if (!ok) {
          alert(
            "Permesso microfono negato. Abilitalo nelle impostazioni del browser."
          );
          return;
        }

        try {
          const vm = instance && instance.proxy ? instance.proxy : {};

          // Nuova richiesta vocale in modalità avatar: nascondi il bottone della fonte precedente
          try {
            if (!chatMode && vm && vm.lastSourceUrl) {
              vm.lastSourceUrl = "";
            }
          } catch { }

          // Usa sempre WhisperSpeechRecognition al posto della Web Speech API
          recognition = new WhisperSpeechRecognition();

          // Lingua: riusa recLang calcolato in precedenza
          recognition.lang = recLang || "it-IT";

          // Modalità single-segment + pausa automatica basata sul silenzio (1s)
          try {
            recognition.singleSegmentMode = true;
            if (Object.prototype.hasOwnProperty.call(recognition, "_silenceMs")) {
              recognition._silenceMs = 1000;
            }
          } catch { }

          recognition.onstart = async () => {
            isListening = true;
            setListeningUI(true);
            console.log("WHISPER: onstart");
            try {
              if (audioCtx && audioCtx.state === "running") {
                await audioCtx.suspend();
                console.log("AUDIO: context suspended for recognition");
              }
            } catch (e) {
              console.warn("AUDIO: suspend failed", e);
            }
          };

          recognition.onerror = async (e) => {
            console.error(
              "WHISPER: onerror",
              (e && (e.error || e.message)) || e
            );
            isListening = false;
            setListeningUI(false);
            try {
              if (audioCtx && audioCtx.state === "suspended") {
                await audioCtx.resume();
                console.log("AUDIO: context resumed after error");
              }
            } catch (er) {
              console.warn("AUDIO: resume after error failed", er);
            }
          };

          recognition.onend = async () => {
            isListening = false;
            setListeningUI(false);
            console.log("WHISPER: onend");
            try {
              if (audioCtx && audioCtx.state === "suspended") {
                await audioCtx.resume();
                console.log("AUDIO: context resumed after end");
              }
            } catch (er) {
              console.warn("AUDIO: resume after end failed", er);
            }
          };

          // Auto-pausa dopo 1 secondo di silenzio: ferma il mic (triggerando la trascrizione)
          if ("onAutoPause" in recognition) {
            recognition.onAutoPause = function () {
              try {
                if (!isListening) return;
                console.log("WHISPER: onAutoPause → stopping mic after silence");
                isListening = false;
                setListeningUI(false);
                recognition.stop();
              } catch { }
            };
          }

          recognition.onresult = (event) => {
            try {
              lastResultAt = Date.now();

              const results = event && event.results ? event.results : [];
              if (!results || !results.length) return;

              const last = results[results.length - 1];
              if (!last || !last[0]) return;

              const transcript = last[0].transcript || "";
              const safe = (transcript || "").trim();

              console.log("WHISPER: onresult", { transcript: safe });

              if (!safe) {
                console.warn(
                  "WHISPER: final transcript empty, not starting stream"
                );
                return;
              }

              // In modalità avatar (non chat), se esiste una fonte RAG con URL,
              // riconosci una conferma vocale semplice per aprire direttamente la pagina.
              try {
                if (!chatMode && vm && vm.lastSourceUrl) {
                  const lower = safe.toLowerCase();
                  if (
                    lower === "sì" ||
                    lower === "si" ||
                    lower === "ok" ||
                    lower === "va bene" ||
                    lower.indexOf("apri la pagina") !== -1 ||
                    lower.indexOf("apri il link") !== -1 ||
                    lower.indexOf("portami") !== -1
                  ) {
                    if (typeof vm.openLastSourceUrl === "function") {
                      vm.openLastSourceUrl();
                      return;
                    }
                  }
                }
              } catch { }

              // In chatMode, mostra subito il messaggio dell'utente
              if (chatMode) {
                chatMessagesData.push({ role: "user", content: safe });
                renderChatMessages();
              }

              if (debugEnabled && liveText) {
                liveText.classList.remove("hidden");
                liveText.textContent = safe;
                setTimeout(() => {
                  try {
                    liveText.classList.add("hidden");
                    liveText.textContent = "";
                  } catch { }
                }, 800);
              }

              // Invia la frase trascritta nel flusso di conversazione Neuron,
              // come faceva prima il risultato di WebSpeech.
              try {
                // Restrizioni speciali per cavalliniservice: solo sito + prenotazione
                const slugLow = String(teamSlug || "").toLowerCase();
                if (slugLow === "cavalliniservice") {
                  const low = safe.toLowerCase();
                  const isBooking =
                    low.indexOf("prenot") !== -1 ||
                    low.indexOf("appuntamento") !== -1 ||
                    low.indexOf("calendly") !== -1;
                  if (isBooking) {
                    try { vm.lastBookingUrl = calendlyUrl || ""; } catch { }
                    // Apri direttamente lo slideover Livewire (non serve URL)
                    try {
                      try {
                        console.log("[EnjoyTalk3D] booking_intent_voice", { teamSlug, calendlyUrl, transcript: safe });
                      } catch { }
                      const ok = vm.openCalendlyModal ? vm.openCalendlyModal(calendlyUrl || "") : false;
                      if (ok) {
                        vm?._sendToTts?.("Perfetto, ti apro subito il calendario per prenotare.");
                      } else {
                        vm?._sendToTts?.("Calendario non disponibile su questa pagina.");
                      }
                    } catch { }
                    return;
                  }
                  if (low.indexOf("cerca nel database") === 0) {
                    try {
                      vm?._sendToTts?.("Posso solo cercare nel sito o prenotare un appuntamento.");
                    } catch { }
                    return;
                  }
                  const normalized = low.indexOf("cerca nel sito") === 0 ? safe : ("cerca nel sito " + safe);
                  vm.startStream
                    ? vm.startStream(normalized)
                    : startStream(normalized);
                  return;
                }

                vm.startStream
                  ? vm.startStream(safe)
                  : startStream(safe);
              } catch {
                startStream(safe);
              }
            } catch (err) {
              console.error("WHISPER: onresult handler failed", err);
            }
          };

          console.log("WHISPER: start()", { lang: recognition.lang });
          recognition.start();
        } catch (err) {
          console.warn("Riconoscimento vocale (Whisper) non disponibile o errore", err);
          alert("Riconoscimento vocale non disponibile in questo browser.");
        }
      }

      micBtn?.addEventListener("click", handleMicClick);
      try {
        instance.proxy._micClick = handleMicClick;
      } catch { }

      // Listener per bottone "Conversa con Me"
      conversaBtn?.addEventListener("click", async () => {
        const prompt = "salutami e spiegami che cosa sai fare";
        // Nascondi il container del bottone
        if (conversaBtnContainer) {
          conversaBtnContainer.classList.add("hidden");
        }
        try {
          if (!audioCtx)
            audioCtx = new (window.AudioContext || window.webkitAudioContext)();
          if (audioCtx.state === "suspended") await audioCtx.resume();
        } catch { }
        try {
          instance.proxy.startStream(prompt);
        } catch {
          startStream(prompt);
        }
      });

      // Cleanup orchestrator esposto per beforeUnmount()
      try {
        instance.proxy._cleanup = () => {
          try {
            if (currentEvtSource) {
              currentEvtSource.close();
              currentEvtSource = null;
            }
          } catch { }
          try {
            if (recognition && isListening) {
              recognition.stop();
              recognition.abort && recognition.abort();
            }
          } catch { }
          try {
            if (renderer && renderer.dispose) renderer.dispose();
          } catch { }
          try {
            if (scene) {
              scene.traverse((o) => {
                try {
                  o.geometry?.dispose?.();
                } catch { }
                try {
                  if (o.material) {
                    if (Array.isArray(o.material))
                      o.material.forEach((m) => m.dispose?.());
                    else o.material.dispose?.();
                  }
                } catch { }
              });
            }
          } catch { }
        };
      } catch { }

      // initThree rimosso per evitare doppie istanze UMD: usiamo solo import ESM da initLibraries()

      function setupScene() {
        const stage =
          rootEl && rootEl.querySelector
            ? rootEl.querySelector("#avatarStage")
            : document.getElementById("avatarStage");
        const rect = stage.getBoundingClientRect();
        let width = Math.floor(
          rect.width && rect.width > 0
            ? rect.width
            : Math.min(window.innerWidth || 360, 520)
        );
        let height = Math.floor(
          rect.height && rect.height > 0
            ? rect.height
            : Math.round((width * 4) / 3)
        );
        if (!width || width < 10 || !height || height < 10) {
          width = 800;
          height = 450;
          stage.style.width = width + "px";
          stage.style.height = height + "px";
        }
        scene = new THREE.Scene();
        // Use CSS background on stage: centered and fit by height (no stretch)
        try {
          const setStageBg = (url) => {
            try {
              stage.style.backgroundImage = `url('${url}')`;
              stage.style.backgroundPosition = "center center";
              stage.style.backgroundRepeat = "no-repeat";
              stage.style.backgroundSize = "auto 100%"; // fit height
            } catch { }
          };
          const img = new Image();
          img.onload = () => setStageBg("/images/office.webp");
          img.onerror = () => {
            try {
              const img2 = new Image();
              img2.onload = () => setStageBg("/images/office.webp");
              img2.onerror = () => {
                try {
                  stage.style.backgroundImage = "";
                } catch { }
              };
              img2.src = "/images/office.webp";
            } catch { }
          };
          img.src = "/images/office.webp";
        } catch { }
        camera = new THREE.PerspectiveCamera(2, width / height, 0.1, 100);
        camera.position.set(0, 0.5, 2);
        try {
          console.log("CAM init", {
            pos: camera.position.toArray(),
            fov: camera.fov,
          });
        } catch { }
        renderer = new THREE.WebGLRenderer({ antialias: true, alpha: true });
        try {
          renderer.setClearColor(0x000000, 0);
        } catch { }
        renderer.setPixelRatio(Math.min(window.devicePixelRatio || 1, 1.75));
        renderer.setSize(width, height);
        stage.innerHTML = "";
        stage.appendChild(renderer.domElement);
        renderer.domElement.style.width = "100%";
        renderer.domElement.style.height = "100%";
        const light = new THREE.DirectionalLight(0xffffff, 1.0);
        light.position.set(1, 2, 3);
        scene.add(light);
        scene.add(new THREE.AmbientLight(0xffffff, 0.4));
        const headGeom = new THREE.SphereGeometry(0.6, 32, 32);
        const headMat = new THREE.MeshStandardMaterial({
          color: 0x8fa7ff,
          roughness: 0.6,
          metalness: 0.0,
        });
        head = new THREE.Mesh(headGeom, headMat);
        head.position.y = 0.2;
        scene.add(head);
        const jawGeom = new THREE.BoxGeometry(0.8, 0.25, 0.6);
        const jawMat = new THREE.MeshStandardMaterial({
          color: 0x9bb0ff,
          roughness: 0.6,
        });
        jaw = new THREE.Mesh(jawGeom, jawMat);
        jaw.position.y = -0.25;
        jaw.position.z = 0.0;
        jaw.geometry.translate(0, 0.12, 0);
        scene.add(jaw);
        console.log("setupScene: start, THREE present =", !!window.THREE);
        try {
          // OrbitControls removed
        } catch (e) {
          /* noop */
        }
        animate();
        window.addEventListener("resize", onResize);
        if ("ResizeObserver" in window) {
          try {
            window.__enjoyTalkResizeObserver?.disconnect?.();
          } catch { }
          try {
            window.__enjoyTalkResizeObserver = new ResizeObserver(onResize);
            window.__enjoyTalkResizeObserver.observe(stage);
          } catch { }
        }
        // Caricamento avatar orchestrato in mounted(); evita doppi trigger qui
      }

      function onResize() {
        if (!renderer || !camera) return;
        const stage =
          rootEl && rootEl.querySelector
            ? rootEl.querySelector("#avatarStage")
            : document.getElementById("avatarStage");
        const controls =
          rootEl && rootEl.querySelector
            ? rootEl.querySelector("#controlsBar")
            : document.getElementById("controlsBar");
        const rect = stage.getBoundingClientRect();
        let width = Math.floor(
          rect.width && rect.width > 0
            ? rect.width
            : Math.min(window.innerWidth || 360, 520)
        );
        let height = Math.floor(
          rect.height && rect.height > 0
            ? rect.height
            : Math.round((width * 4) / 3)
        );
        if (!width || width < 10 || !height || height < 10) {
          width = 800;
          height = 450;
          stage.style.width = width + "px";
          stage.style.height = height + "px";
        }
        renderer.setSize(width, height);
        camera.aspect = width / height;
        camera.updateProjectionMatrix();
        try {
          const controlsRect = controls
            ? controls.getBoundingClientRect()
            : null;
          const pad = controlsRect ? Math.ceil(controlsRect.height) : 0;
          document.body.style.setProperty("--controls-pad", pad + "px");
        } catch { }
        try {
          const vh = window.visualViewport
            ? window.visualViewport.height
            : window.innerHeight;
          const maxH = Math.max(
            320,
            Math.floor(vh - (controls?.offsetHeight || 0) - 180)
          );
          stage.style.maxHeight = maxH + "px";
        } catch { }
      }



      function animate() {
        const forcedClosing = performance.now() < (forceFullCloseUntil || 0);
        animationId = requestAnimationFrame(animate);
        head.position.y = 0.2 + Math.sin(performance.now() / 1200) * 0.01;

        // Aggiorna il mixer dell'animazione idle SEMPRE, per mantenerlo sincronizzato
        if (idleAnimationMixer) {
          idleAnimationMixer.update(0.016); // ~60fps - sempre aggiornato!
        }

        // Aggiorna il mixer dell'animazione talking se attivo
        if (talkingAnimationMixer && talkingAnimationActive) {
          talkingAnimationMixer.update(0.016); // ~60fps
        }

        // Controllo automatico per riavviare idle quando talking finisce
        checkTalkingAnimationFinished();

        // Solo mixer delle animazioni - niente funzioni procedurali

        // Audio analysis and lipsync
        let amp = 0;
        if (
          useBrowserTts &&
          useBrowserTts.checked &&
          "speechSynthesis" in window &&
          window.speechSynthesis.speaking
        ) {
          if (analyser && dataArray) {
            analyser.getByteFrequencyData(dataArray);
            const avgFreq =
              dataArray.reduce((a, b) => a + b) / dataArray.length;
            amp = avgFreq / 256;
          } else {
            amp = 0;
          }
        }
        if (analyser && dataArray) {
          analyser.getByteTimeDomainData(dataArray);
          let sum = 0,
            zc = 0,
            prev = 0;
          for (let i = 0; i < dataArray.length; i++) {
            const v = (dataArray[i] - 128) / 128.0;
            sum += v * v;
            if (i > 0 && ((v >= 0 && prev < 0) || (v < 0 && prev >= 0))) zc++;
            prev = v;
          }
          const rms = Math.sqrt(sum / dataArray.length);
          const targetAmp = Math.min(1, rms * 5.5);
          const a = targetAmp > audioAmp ? AMP_ATTACK : AMP_RELEASE;
          audioAmp = audioAmp * (1 - a) + targetAmp * a;
          amp = audioAmp;
        }

        // Viseme and lipsync
        if (textVisemeEnabled) {
          const nowT = performance.now();
          const prevScheduleLen = visemeSchedule.length;
          const prevLastEnd = visemeSchedule.length > 0 ? visemeSchedule[visemeSchedule.length - 1].end : 0;
          visemeSchedule = visemeSchedule.filter((it) => it.end > nowT);
          const active = visemeSchedule.find(
            (it) => it.start <= nowT && it.end > nowT
          );

          if (active) {
            const blend = Math.max(
              0,
              Math.min(
                1,
                (nowT - active.start) / Math.max(1, active.end - active.start)
              )
            );
            visemeTargets = {
              jawOpen: active.targets.jawOpen * blend,
              mouthFunnel: active.targets.mouthFunnel * blend,
              mouthPucker: active.targets.mouthPucker * blend,
              mouthSmileL: active.targets.mouthSmileL * blend,
              mouthSmileR: active.targets.mouthSmileR * blend,
              mouthClose: active.targets.mouthClose * blend,
            };
            visemeActiveUntil = nowT + 120;
            console.log(
              "[LIPSYNC] frame animate - ACTIVE VISEME | blend:",
              blend.toFixed(3),
              "| jawOpen:",
              visemeTargets.jawOpen.toFixed(3),
              "| mouthFunnel:",
              visemeTargets.mouthFunnel.toFixed(3),
              "| cloudAudioSpeaking:",
              cloudAudioSpeaking,
              "| isSpeaking:",
              isSpeaking,
              "| meshCount:",
              visemeMeshes.length,
              "| viseme endTime in",
              (active.end - nowT).toFixed(0),
              "ms"
            );
          } else if (prevScheduleLen > 0) {
            const timeToNextEnd = prevLastEnd > nowT ? prevLastEnd - nowT : 0;
            const nextViseme = visemeSchedule.length > 0 ? visemeSchedule[0] : null;
            const timeToNextStart = nextViseme ? nextViseme.start - nowT : -1;
            console.log(
              "[LIPSYNC] frame animate - NO ACTIVE VISEME | schedule filtered from",
              prevScheduleLen,
              "to",
              visemeSchedule.length,
              "| prevLastEnd was",
              timeToNextEnd.toFixed(0),
              "ms ago | nextVisemeStartsIn:",
              timeToNextStart >= 0 ? timeToNextStart.toFixed(0) + "ms" : "none",
              "| now:",
              nowT.toFixed(0),
              "| prevLastEnd:",
              prevLastEnd.toFixed(0),
              "| cloudAudioSpeaking:",
              cloudAudioSpeaking,
              "| isSpeaking:",
              isSpeaking
            );
          }
        }

        // Apply lipsync to meshes
        const now = performance.now();
        const restJawOpen = lipConfig.restJawOpen;
        let appliedJaw = null;

        let lipsyncApplied = false;
        if (
          Array.isArray(visemeMeshes) &&
          visemeMeshes.length > 0 &&
          visemeActiveUntil > now
        ) {
          lipsyncApplied = true;
          // Ramp-up più veloce per cloud TTS
          const alphaRampUp = cloudAudioSpeaking ? 0.4 : lipConfig.visemeStrengthAlpha;
          visemeStrength =
            visemeStrength * (1 - alphaRampUp) +
            alphaRampUp;
          console.log(
            "[LIPSYNC] applying to meshes | visemeStrength ramp:",
            visemeStrength.toFixed(3),
            "| alphaRampUp:",
            alphaRampUp.toFixed(3),
            "| meshes:",
            visemeMeshes.length,
            "| activeUntil ms:",
            (visemeActiveUntil - now).toFixed(0)
          );

          for (const vm of visemeMeshes) {
            const infl = vm.mesh.morphTargetInfluences;
            if (!infl) continue;
            const smooth = (key, target) => {
              const prev = lastVisemes[key] || 0;
              const diff = target - prev;
              if (Math.abs(diff) < (deadband[key] || 0.02)) return prev;
              const alpha = lipConfig.morphSmoothingBeta;
              const v = prev * (1 - alpha) + target * alpha;
              lastVisemes[key] = v;
              return v;
            };
            const setIdx = (idx, val, key) => {
              if (idx >= 0) {
                const smoothed = smooth(key, val);
                infl[idx] =
                  infl[idx] * 0.7 +
                  Math.max(0, Math.min(1, smoothed * visemeStrength)) *
                  0.3;
              }
            };
            let jawv = Math.min(
              1,
              Math.max(
                0,
                visemeTargets.jawOpen + (cloudAudioSpeaking ? 0 : restJawOpen)
              )
            );
            const roundness = Math.min(
              1,
              visemeTargets.mouthFunnel + visemeTargets.mouthPucker
            );
            const closeSuppression = Math.max(
              0,
              1 - jawv * 1.5 - roundness * 0.9
            );
            let constrainedClose = Math.min(
              lipConfig.maxMouthClose,
              (visemeTargets.mouthClose || 0) * closeSuppression
            );
            appliedJaw = jawv;
            setIdx(vm.indices.jawOpen, jawv * 0.9, "jawOpen");
            setIdx(
              vm.indices.mouthFunnel,
              visemeTargets.mouthFunnel * 1.05,
              "mouthFunnel"
            );
            setIdx(
              vm.indices.mouthPucker,
              visemeTargets.mouthPucker * 1.0,
              "mouthPucker"
            );
            setIdx(
              vm.indices.mouthSmileL,
              visemeTargets.mouthSmileL * 1.05,
              "mouthSmileL"
            );
            setIdx(
              vm.indices.mouthSmileR,
              visemeTargets.mouthSmileR * 1.05,
              "mouthSmileR"
            );
            setIdx(vm.indices.mouthClose, constrainedClose, "mouthClose");
          }
        } else if (
          morphMesh &&
          morphIndex >= 0 &&
          Array.isArray(morphMesh.morphTargetInfluences)
        ) {
          lipsyncApplied = true;
          visemeStrength = 0;
          const target = Math.min(
            1,
            (cloudAudioSpeaking ? 0 : restJawOpen * 0.6)
          );
          morphValue = morphValue * 0.82 + target * 0.18;
          morphMesh.morphTargetInfluences[morphIndex] = morphValue;
          /*console.log(
            "[LIPSYNC] applying fallback morph target | meshName:",
            morphMesh.name,
            "| morphValue:",
            morphValue.toFixed(3),
            "| target:",
            target.toFixed(3),
            "| cloudAudio:",
            cloudAudioSpeaking
          );*/
        } else if (visemeActiveUntil > now && !lipsyncApplied) {
          console.log(
            "[LIPSYNC] NOT applied - conditions not met | visemeActiveUntil > now:",
            visemeActiveUntil > now,
            "| meshes.length:",
            visemeMeshes.length,
            "| morphMesh exists:",
            !!morphMesh,
            "| morphIndex:",
            morphIndex,
            "| now:",
            now
          );
        }

        // Jaw bone animation
        if (jawBone) {
          if (jawBone.type === "Bone") {
            window.__jawBonePrev = window.__jawBonePrev ?? 0;
            let jawFromVisemes = Math.max(
              Math.min(
                1,
                Math.max(
                  0,
                  visemeTargets.jawOpen + (cloudAudioSpeaking ? 0 : restJawOpen)
                )
              )
            );
            const jawForBone = Math.max(
              lipConfig.minLipSeparation,
              appliedJaw !== null ? appliedJaw : jawFromVisemes
            );
            const a = lipConfig.jawSmoothingAlpha;
            const jb = window.__jawBonePrev * (1 - a) + jawForBone * a;
            window.__jawBonePrev = jb;
            const angle = jawSign * (jb * 0.65);
            if (jawAxis === "x") jawBone.rotation.x = angle;
            else if (jawAxis === "y") jawBone.rotation.y = angle;
            else jawBone.rotation.z = angle;
            try {
              jawBone.updateMatrixWorld(true);
            } catch { }
            try {
              humanoid.updateMatrixWorld(true);
            } catch { }
          }
        } else if (jaw) {
          window.__jawGeomPrev = window.__jawGeomPrev ?? 0;
          const jawForBone = Math.max(
            lipConfig.minLipSeparation,
            appliedJaw !== null
              ? appliedJaw
              : (cloudAudioSpeaking ? 0 : restJawOpen * 0.2)
          );
          const a = lipConfig.jawSmoothingAlpha;
          const jb = window.__jawGeomPrev * (1 - a) + jawForBone * a;
          window.__jawGeomPrev = jb;
          jaw.rotation.x = -(jb * 0.5);
        }

        renderer.render(scene, camera);
      }

      function checkForTtsChunks() {
        const vmInst = instance && instance.proxy ? instance.proxy : {};
        const clean = vmInst.stripHtml
          ? vmInst.stripHtml(ttsBuffer)
          : stripHtml(ttsBuffer);
        if (!clean || clean.length < 2) return;
        const boundaryIndex = vmInst.findSentenceBoundary
          ? vmInst.findSentenceBoundary(clean)
          : findSentenceBoundary(clean);
        if (boundaryIndex <= 0) return;
        const chunk = clean.slice(0, boundaryIndex).trim();
        if (!chunk || chunk.length < 4) return;
        if (speakQueue.some((item) => item.text === chunk)) {
          ttsBuffer = clean.slice(boundaryIndex).trim();
          return;
        }
        console.log("TTS: Sending sentence:", chunk.substring(0, 80) + "...");
        sendToTts(chunk);
        lastSentToTts = chunk;
        ttsBuffer = clean.slice(boundaryIndex).trim();
      }

      function findSentenceBoundary(text) {
        const abbreviations = [
          "es",
          "ecc",
          "etc",
          "sig",
          "sigg",
          "sigra",
          "sig.na",
          "sig.ra",
          "dott",
          "ing",
          "avv",
          "prof",
          "dr",
          "dottssa",
          "srl",
          "spa",
          "s.p.a",
          "s.r.l",
          "p.es",
          "nr",
          "n",
          "art",
          "cap",
          "ca",
          "vs",
          "no",
        ];
        let i = 0;
        let lastSafe = -1;
        while (i < text.length) {
          const ch = text[i];
          let isBoundary = false;
          let endIndex = i + 1;
          if (ch === "." || ch === "!" || ch === "?" || ch === "…") {
            if (ch === "." && text.slice(i, i + 3) === "...") {
              endIndex = i + 3;
              isBoundary = true;
              i = i + 3;
            } else {
              const nextNonSpaceIdx = findNextNonSpace(text, i + 1);
              const prevNonSpaceIdx = findPrevNonSpace(text, i - 1);
              const nextCh = nextNonSpaceIdx >= 0 ? text[nextNonSpaceIdx] : "";
              const prevCh = prevNonSpaceIdx >= 0 ? text[prevNonSpaceIdx] : "";
              const decimalLike =
                ch === "." && /[0-9]/.test(prevCh) && /[0-9]/.test(nextCh);
              let abbrevLike = false;
              if (ch === ".") {
                const startTok = findTokenStart(text, i - 1);
                const token = text
                  .slice(startTok, i)
                  .toLowerCase()
                  .replace(/\./g, "");
                if (token.length > 0 && abbreviations.includes(token)) {
                  abbrevLike = true;
                }
              }
              if (!decimalLike && !abbrevLike) {
                isBoundary = true;
                i = i + 1;
              } else {
                i = i + 1;
              }
            }
            if (isBoundary) {
              const afterIdx = findNextNonSpace(text, endIndex);
              const nextIsUpper =
                afterIdx >= 0
                  ? /[A-ZÀ-Ý\(\["'“"']/.test(text[afterIdx])
                  : true;
              if (afterIdx < 0 || nextIsUpper || text[afterIdx - 1] === "\n") {
                lastSafe = endIndex;
              }
            }
          } else {
            i++;
          }
        }
        return lastSafe;
      }
      function findNextNonSpace(s, start) {
        for (let k = start; k < s.length; k++) {
          if (!/\s/.test(s[k])) return k;
        }
        return -1;
      }
      function findPrevNonSpace(s, start) {
        for (let k = start; k >= 0; k--) {
          if (!/\s/.test(s[k])) return k;
        }
        return -1;
      }
      function findTokenStart(s, idx) {
        let k = idx;
        while (k >= 0 && /[\p{L}\p{N}\.]/u.test(s[k])) {
          k--;
        }
        return k + 1;
      }

      function sendToTts(text) {
        const vmInst = instance && instance.proxy ? instance.proxy : {};
        const norm = vmInst.sanitizeForTts
          ? vmInst.sanitizeForTts(text)
          : sanitizeForTts(text);
        if (!norm || norm.length < 3) return;
        if (speakQueue.some((item) => item.text === norm)) return;

        if (
          useBrowserTts &&
          useBrowserTts.checked &&
          "speechSynthesis" in window
        ) {
          speakQueue.push({ url: null, text: norm });
          if (!isSpeaking) playNextInQueue();
          return;
        }
        ttsRequestQueue.push(norm);
        processTtsQueue();
        return;
      }
      try {
        instance.proxy._sendToTts = sendToTts;
      } catch { }

      // Funzione openCalendlyModal esposta da setup() (per Composition API)
      function openCalendlyModal(url) {
        try {
          const calendlyUrl = (url || "").trim();
          if (!calendlyUrl) {
            try {
              console.warn("[EnjoyTalk3D] calendly_open_no_url");
            } catch { }
            return false;
          }

          const w = typeof window !== "undefined" ? window : {};
          if (!w.Calendly || typeof w.Calendly.initPopupWidget !== "function") {
            try {
              console.warn("[EnjoyTalk3D] calendly_open_not_available", { hasCalendly: !!w.Calendly });
            } catch { }
            return false;
          }

          w.Calendly.initPopupWidget({ url: calendlyUrl });
          try {
            console.log("[EnjoyTalk3D] calendly_open_ok", { url: calendlyUrl });
          } catch { }
          return true;
        } catch (e) {
          try {
            console.error("[EnjoyTalk3D] calendly_open_error", e);
          } catch { }
          return false;
        }
      }
      try {
        instance.proxy.openCalendlyModal = openCalendlyModal;
        instance.proxy._openCalendlyModal = openCalendlyModal;
      } catch { }

      async function processTtsQueue() {
        if (ttsRequestInFlight) return;
        const next = ttsRequestQueue.shift();
        if (!next) return;
        ttsRequestInFlight = true;
        try {
          console.log("TTS: Requesting audio for:", next.substring(0, 80));
          const webComponentOrigin = window.__ENJOY_TALK_3D_ORIGIN__ || window.location.origin;
          const res = await fetch(`${webComponentOrigin}/api/tts`, {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({
              text: next,
              locale: "it-IT",
              format: "mp3",
            }),
          });
          if (!res.ok) throw new Error(`TTS API ${res.status}`);
          const blob = await res.blob();
          const url = URL.createObjectURL(blob);
          speakQueue.push({ url, text: next });
          if (!isSpeaking) playNextInQueue();
        } catch (err) {
          console.error("TTS request failed:", err);
        } finally {
          ttsRequestInFlight = false;
          if (ttsRequestQueue.length > 0) processTtsQueue();
        }
      }
      try {
        instance.proxy._processTtsQueue = processTtsQueue;
      } catch { }

      function sendToTtsIfNew() {
        const vmInst = instance && instance.proxy ? instance.proxy : {};
        const clean = vmInst.stripHtml
          ? vmInst.stripHtml(bufferText)
          : stripHtml(bufferText);
        if (!clean) return;
        const norm = clean.replace(/\s+/g, " ").trim();
        if (!norm || norm.length < 3) return;
        if (lastSpokenTail.includes(norm)) {
          console.log(
            "TTS: Skipping already spoken text:",
            norm.substring(0, 50)
          );
          return;
        }
        if (speakQueue.some((item) => item.text === norm)) {
          console.log(
            "TTS: Skipping text already in queue:",
            norm.substring(0, 50)
          );
          return;
        }
        console.log("TTS: Sending remaining text:", norm.substring(0, 100));
        sendToTts(norm);
      }
      function enqueueSpeak(text) {
        sendToTts(text);
      }

      function playNextInQueue() {
        if (!speakQueue.length) {
          isSpeaking = false;
          talkingAnimationStartedForCurrentResponse = false;
          console.log("TTS: Queue empty, stopping");
          return;
        }
        isSpeaking = true;
        const item = speakQueue.shift();
        console.log(
          "TTS: Playing:",
          item.text.substring(0, 50),
          "... Queue remaining:",
          speakQueue.length
        );
        if (
          useBrowserTts &&
          useBrowserTts.checked &&
          "speechSynthesis" in window
        ) {
          try {
            const utter = new SpeechSynthesisUtterance(item.text);
            utter.lang = "it-IT";
            utter.rate = 1.0;
            utter.pitch = 1.0;
            utter.volume = 1.0;
            const voices = window.speechSynthesis.getVoices();
            const prefNames = [
              "Google italiano",
              "Microsoft",
              "Elsa",
              "Lucia",
              "Carla",
              "Silvia",
              "Alice",
            ];
            const itVoices = voices.filter((v) => /it[-_]/i.test(v.lang));
            const femaleVoices = itVoices.filter((v) =>
              /female|donna|feminine/i.test(v.name + " " + (v.voiceURI || ""))
            );
            const chosen =
              femaleVoices[0] ||
              itVoices.find((v) =>
                prefNames.some((n) => (v.name || "").includes(n))
              ) ||
              itVoices[0] ||
              voices.find((v) => /Italian/i.test(v.name)) ||
              voices[0];
            if (chosen) utter.voice = chosen;
            if (browserTtsStatus)
              browserTtsStatus.textContent = chosen
                ? `Voce: ${chosen.name}`
                : "Voce IT non trovata (usa default)";
            utter.onstart = () => {
              try {
                // Solo avvia animazione al primo chunk della risposta
                if (!talkingAnimationStartedForCurrentResponse) {
                  talkingAnimationStartedForCurrentResponse = true;
                  playRandomTalkingAnimation();
                }
                const nowT = performance.now();
                const textClean = (item.text || "").replace(/\s+/g, " ").trim();
                const words = textClean.split(/\s+/).filter(Boolean);
                const charCount = textClean.length;
                const wordCount =
                  words.length || Math.max(1, Math.round(charCount / 5));
                const periods = (textClean.match(/[.!?…]/g) || []).length;
                const commas = (textClean.match(/[,:;]/g) || []).length;
                const parens = (textClean.match(/[()\-\u2013\u2014]/g) || [])
                  .length;
                const rate =
                  typeof utter.rate === "number" && utter.rate > 0
                    ? utter.rate
                    : 1;
                let estSec =
                  wordCount * 0.55 * 0.5 +
                  (charCount / 13.5) * 0.5 +
                  (periods * 0.55 + commas * 0.32 + parens * 0.22) +
                  0.4;
                estSec = estSec / rate;
                estSec = Math.max(2.2, estSec);
                visemeSchedule = [];
                console.log(
                  "[LIPSYNC] speechSynthesis onstart | text:",
                  textClean.substring(0, 60),
                  "| estDuration:",
                  estSec.toFixed(2),
                  "s | charCount:",
                  charCount,
                  "| wordCount:",
                  wordCount,
                  "| rate:",
                  rate
                );
                enqueueTextVisemes(
                  item.text || "",
                  Math.floor(estSec * 1000),
                  nowT
                );
              } catch { }
            };
            utter.onend = () => {
              try {
                //stopTalkingAnimation();
                console.log(
                  "[LIPSYNC] speechSynthesis onend | closing mouth | text:",
                  item.text.substring(0, 50),
                  "| forceClosing for 220ms"
                );
                visemeSchedule = [];
                visemeTargets = {
                  jawOpen: 0,
                  mouthFunnel: 0,
                  mouthPucker: 0,
                  mouthSmileL: 0,
                  mouthSmileR: 0,
                  mouthClose: 0,
                };
                forceFullCloseUntil = performance.now() + 220;
                visemeActiveUntil = performance.now() + 180;
                visemeStrength = 0;
                if (Array.isArray(visemeMeshes)) {
                  for (const vm of visemeMeshes) {
                    const infl = vm.mesh && vm.mesh.morphTargetInfluences;
                    const idxs = vm.indices || {};
                    if (!infl) continue;
                    try {
                      if (idxs.mouthFunnel >= 0) infl[idxs.mouthFunnel] = 0;
                    } catch { }
                    try {
                      if (idxs.mouthPucker >= 0) infl[idxs.mouthPucker] = 0;
                    } catch { }
                    try {
                      if (idxs.mouthSmileL >= 0) infl[idxs.mouthSmileL] = 0;
                    } catch { }
                    try {
                      if (idxs.mouthSmileR >= 0) infl[idxs.mouthSmileR] = 0;
                    } catch { }
                    try {
                      if (idxs.mouthClose >= 0) infl[idxs.mouthClose] = 0;
                    } catch { }
                    try {
                      if (idxs.jawOpen >= 0) infl[idxs.jawOpen] = 0;
                    } catch { }
                  }
                }
                if (humanoid && humanoid.updateMatrixWorld)
                  humanoid.updateMatrixWorld(true);
              } catch { }
              visemeActiveUntil = 0;
              visemeStrength = 0;
              lastSpokenTail = (lastSpokenTail + " " + item.text).slice(-400);
              lastSentToTts = "";
              playNextInQueue();
            };
            utter.onerror = () => {
              if (item.url) {
                ttsPlayer.src = item.url;
                ttsPlayer.play().catch(() => {
                  isSpeaking = false;
                });
              } else {
                isSpeaking = false;
                playNextInQueue();
              }
            };
            window.speechSynthesis.speak(utter);
            return;
          } catch (e) {
            console.warn("speechSynthesis error, fallback to audio element", e);
          }
        }
        ttsPlayer.src = item.url;
        if (!audioCtx) {
          audioCtx = new (window.AudioContext || window.webkitAudioContext)();
        }
        if (audioCtx.state === "suspended") {
          audioCtx.resume().catch(() => { });
        }
        const onEnded = () => {
          URL.revokeObjectURL(item.url);
          lastSpokenTail = (lastSpokenTail + " " + item.text).slice(-400);
          console.log(
            "[LIPSYNC] cloud TTS onEnded | closing mouth | text:",
            item.text.substring(0, 50),
            "| cloudAudioSpeaking: false"
          );
          cloudAudioSpeaking = false;
          visemeSchedule = [];
          visemeTargets = {
            jawOpen: 0,
            mouthFunnel: 0,
            mouthPucker: 0,
            mouthSmileL: 0,
            mouthSmileR: 0,
            mouthClose: 0,
            mouthStretchL: 0,
            mouthStretchR: 0,
            tongueOut: 0,
          };
          forceFullCloseUntil = performance.now() + 220;
          visemeActiveUntil = performance.now() + 180;
          try {
            if (Array.isArray(visemeMeshes)) {
              for (const vm of visemeMeshes) {
                const infl = vm.mesh && vm.mesh.morphTargetInfluences;
                const idxs = vm.indices || {};
                if (!infl) continue;
                if (idxs.mouthFunnel >= 0) infl[idxs.mouthFunnel] = 0;
                if (idxs.mouthPucker >= 0) infl[idxs.mouthPucker] = 0;
                if (idxs.mouthSmileL >= 0) infl[idxs.mouthSmileL] = 0;
                if (idxs.mouthSmileR >= 0) infl[idxs.mouthSmileR] = 0;
                if (idxs.mouthClose >= 0) infl[idxs.mouthClose] = 0;
                if (idxs.jawOpen >= 0) infl[idxs.jawOpen] = 0;
              }
            }
            if (humanoid && humanoid.updateMatrixWorld)
              humanoid.updateMatrixWorld(true);
          } catch { }
          //stopTalkingAnimation();
          ttsPlayer.removeEventListener("ended", onEnded);
          playNextInQueue();
        };
        const onError = () => {
          console.error("TTS: Playback error for:", item.text.substring(0, 50));
          URL.revokeObjectURL(item.url);
          ttsPlayer.removeEventListener("ended", onEnded);
          ttsPlayer.removeEventListener("error", onError);
          isSpeaking = false;
          cloudAudioSpeaking = false;
          playNextInQueue();
        };
        ttsPlayer.addEventListener("ended", onEnded);
        ttsPlayer.addEventListener("error", onError);
        const onPlaying = () => {
          try {
            // Solo avvia animazione al primo chunk della risposta
            if (!talkingAnimationStartedForCurrentResponse) {
              talkingAnimationStartedForCurrentResponse = true;
              playRandomTalkingAnimation();
            }
            // Popola visemeSchedule in base alla durata dell'audio
            if (ttsPlayer.duration && item.text) {
              const durationMs = ttsPlayer.duration * 1000;
              console.log(
                "[LIPSYNC] cloud TTS onPlaying | text:",
                item.text.substring(0, 60),
                "| audioDuration:",
                durationMs.toFixed(0),
                "ms | cloudAudioSpeaking: true"
              );
              enqueueTextVisemes(item.text, durationMs, performance.now());
            } else {
              console.log(
                "[LIPSYNC] cloud TTS onPlaying - WARNING | no duration or text | duration:",
                ttsPlayer.duration,
                "| text:",
                item.text.substring(0, 30)
              );
              visemeSchedule = [];
            }
          } catch { }
          try {
            ttsPlayer.removeEventListener("playing", onPlaying);
          } catch { }
        };
        ttsPlayer.addEventListener("playing", onPlaying);
        ttsPlayer
          .play()
          .then(() => {
            console.log(
              "[LIPSYNC] cloud TTS play() started | text:",
              item.text.substring(0, 50),
              "| cloudAudioSpeaking: true"
            );
            cloudAudioSpeaking = true;
          })
          .catch((err) => {
            console.error(
              "[LIPSYNC] cloud TTS play() failed:",
              err,
              "| text:",
              item.text.substring(0, 50)
            );
            isSpeaking = false;
            onError();
          });
      }
      try {
        instance.proxy._playNextInQueue = playNextInQueue;
      } catch { }

      async function heygenEnsureSession() {
        if (heygen.started || heygen.connecting) return;
        heygen.connecting = true;
        try {
          if (!HEYGEN_CONFIG.apiKey || !HEYGEN_CONFIG.serverUrl) {
            videoAvatarStatus &&
              (videoAvatarStatus.textContent = "Config mancante");
            throw new Error("HEYGEN config missing");
          }
          const tokRes = await fetch(
            `${HEYGEN_CONFIG.serverUrl}/v1/streaming.create_token`,
            {
              method: "POST",
              headers: {
                "Content-Type": "application/json",
                "X-Api-Key": HEYGEN_CONFIG.apiKey,
              },
            }
          );
          const tokJson = await tokRes.json();
          heygen.sessionToken = tokJson?.data?.token;
          if (!heygen.sessionToken) throw new Error("No session token");
          videoAvatarStatus && (videoAvatarStatus.textContent = "Token OK");
          const body = {
            quality: "high",
            version: "v2",
            // Usa VP8 per garantire compatibilità con Firefox (specialmente su Linux).
            video_encoding: "VP8",
          };
          if (heygenAvatar) body.avatar_name = heygenAvatar;
          if (heygenVoice) body.voice = { voice_id: heygenVoice, rate: 1.0 };
          const newRes = await fetch(
            `${HEYGEN_CONFIG.serverUrl}/v1/streaming.new`,
            {
              method: "POST",
              headers: {
                "Content-Type": "application/json",
                Authorization: `Bearer ${heygen.sessionToken}`,
              },
              body: JSON.stringify(body),
            }
          );
          const newJson = await newRes.json();
          heygen.sessionInfo = newJson?.data;
          if (!heygen.sessionInfo?.session_id)
            throw new Error("No session info");
          heygen.room = new LivekitClient.Room({
            adaptiveStream: false,
            dynacast: true,
            videoCaptureDefaults: {
              resolution: LivekitClient.VideoPresets.h720.resolution,
            },
          });
          heygen.mediaStream = new MediaStream();
          heygen.room.on(
            LivekitClient.RoomEvent.TrackSubscribed,
            async (track) => {
              try {
                if (track.kind === "video") {
                  heygen.mediaStream.addTrack(track.mediaStreamTrack);
                  if (heygenVideo) {
                    heygenVideo.srcObject = heygen.mediaStream;
                    await heygenVideo.play().catch(() => { });
                  }
                  videoAvatarStatus &&
                    (videoAvatarStatus.textContent = "Video connesso");
                }
                if (track.kind === "audio") {
                  heygen.mediaStream.addTrack(track.mediaStreamTrack);
                  videoAvatarStatus &&
                    (videoAvatarStatus.textContent = "Audio connesso");
                }
              } catch (e) {
                console.warn("HEYGEN: TrackSubscribed handler failed", e);
              }
            }
          );
          heygen.room.on(LivekitClient.RoomEvent.TrackUnsubscribed, (track) => {
            const mt = track.mediaStreamTrack;
            if (mt) heygen.mediaStream.removeTrack(mt);
          });
          await heygen.room.prepareConnection(
            heygen.sessionInfo.url,
            heygen.sessionInfo.access_token
          );
          await fetch(`${HEYGEN_CONFIG.serverUrl}/v1/streaming.start`, {
            method: "POST",
            headers: {
              "Content-Type": "application/json",
              Authorization: `Bearer ${heygen.sessionToken}`,
            },
            body: JSON.stringify({ session_id: heygen.sessionInfo.session_id }),
          });
          try {
            const params = new URLSearchParams({
              session_id: heygen.sessionInfo.session_id,
              session_token: heygen.sessionToken,
              silence_response: "true",
              stt_language: "en",
            });
            const wsUrl = `wss://${new URL(HEYGEN_CONFIG.serverUrl).hostname
              }/v1/ws/streaming.chat?${params}`;
            heygen.ws = new WebSocket(wsUrl);
            heygen.ws.addEventListener("message", () => { });
          } catch { }
          await heygen.room.connect(
            heygen.sessionInfo.url,
            heygen.sessionInfo.access_token
          );
          try {
            if (heygenVideo?.srcObject && heygenVideo.paused) {
              await heygenVideo.play();
            }
          } catch { }
          heygen.started = true;
          videoAvatarStatus && (videoAvatarStatus.textContent = "Connesso");
        } catch (e) {
          console.error("HEYGEN: init failed", e);
        } finally {
          heygen.connecting = false;
        }
      }

      async function heygenSendRepeat(text) {
        try {
          await heygenEnsureSession();
          if (!heygen.sessionInfo?.session_id) return;
          await fetch(`${HEYGEN_CONFIG.serverUrl}/v1/streaming.task`, {
            method: "POST",
            headers: {
              "Content-Type": "application/json",
              Authorization: `Bearer ${heygen.sessionToken}`,
            },
            body: JSON.stringify({
              session_id: heygen.sessionInfo.session_id,
              text,
              task_type: "repeat",
            }),
          });
        } catch (e) {
          console.error("HEYGEN: repeat failed", e);
        }
      }

      async function heygenClose() {
        try {
          if (heygen.sessionInfo?.session_id) {
            await fetch(`${HEYGEN_CONFIG.serverUrl}/v1/streaming.stop`, {
              method: "POST",
              headers: {
                "Content-Type": "application/json",
                Authorization: `Bearer ${heygen.sessionToken}`,
              },
              body: JSON.stringify({
                session_id: heygen.sessionInfo.session_id,
              }),
            });
          }
        } catch { }
        try {
          if (heygen.ws && heygen.ws.readyState < 2) heygen.ws.close();
        } catch { }
        try {
          if (heygen.room) heygen.room.disconnect();
        } catch { }
        if (heygenVideo) {
          try {
            heygenVideo.pause();
          } catch { }
          heygenVideo.srcObject = null;
        }
        heygen = {
          sessionInfo: null,
          room: null,
          mediaStream: null,
          sessionToken: null,
          connecting: false,
          started: false,
        };
        videoAvatarStatus && (videoAvatarStatus.textContent = "");
      }

      function stripHtml(html) {
        const tmp = document.createElement("div");
        tmp.innerHTML = html;
        return (tmp.textContent || tmp.innerText || "")
          .replace(/\s+/g, " ")
          .trim();
      }

      function loadHumanoid() {
        if (humanoid || humanoidLoading) return;
        humanoidLoading = true;
        // Mostra l'overlay di caricamento
        if (loadingOverlay) {
          loadingOverlay.classList.remove("hidden");
        }
        try {
          const FBXLoaderCtor = window.FBXLoader;
          const GLTFLoaderCtor = window.GLTFLoader;
          if (!window.THREE || (!FBXLoaderCtor && !GLTFLoaderCtor)) {
            console.warn(
              "Loader non presente. THREE:",
              !!window.THREE,
              "FBXLoader:",
              !!FBXLoaderCtor,
              "GLTFLoader:",
              !!GLTFLoaderCtor
            );
            return;
          }
          const fbxUrl = "";

          // Costruisci l'URL del GLB
          let finalGlbUrl = "/api/static/images/68f78ddb4530fb061a1349d5.glb"; // default

          // Se glbUrl è fornito dal prop
          if (instance.props.glbUrl && instance.props.glbUrl.trim()) {
            const glbUrlProp = instance.props.glbUrl;
            // URL esterno: caricalo direttamente
            if (glbUrlProp.startsWith('http')) {
              finalGlbUrl = glbUrlProp;
            } else {
              // Se è un path relativo, usalo via API
              const webComponentOrigin = window.__ENJOY_TALK_3D_ORIGIN__ || window.location.origin;
              finalGlbUrl = `${webComponentOrigin}/api/static/${glbUrlProp.replace(/^\//, '')}`;
            }
          } else {
            // Usa il default dal web component origin
            const webComponentOrigin = window.__ENJOY_TALK_3D_ORIGIN__ || window.location.origin;
            finalGlbUrl = `${webComponentOrigin}/api/static/images/68f78ddb4530fb061a1349d5.glb`;
          }

          const glbUrl = finalGlbUrl + "?v=" + Date.now();
          function attachHumanoid(root) {
            try {
              humanoid = root;
              humanoid.position.set(0, -0.3, 0);
              humanoid.scale.set(1.2, 1.2, 1.2);
              try {
                console.log("HUM attach", {
                  pos: humanoid.position.toArray(),
                  scale: humanoid.scale.toArray(),
                });
              } catch { }
              scene.add(humanoid);
              fitCameraToObject(camera, humanoid, 1.4);
              visemeMeshes = [];
              const glassesRegex =
                /(glass|occhiali|spectacle|eyewear|sunglass)/i;
              const keptGlasses = new Set();
              const topGlassesRoot = (o) => {
                let r = o;
                try {
                  while (
                    r &&
                    r.parent &&
                    r.parent !== humanoid &&
                    glassesRegex.test(String(r.parent.name || ""))
                  ) {
                    r = r.parent;
                  }
                } catch { }
                return r || o;
              };
              humanoid.traverse((obj) => {
                const name = (obj.name || "").toLowerCase();
                // Nascondi eventuale secondo paio di occhiali
                try {
                  if (obj.isMesh && glassesRegex.test(name)) {
                    const root = topGlassesRoot(obj);
                    const id = root && root.uuid ? root.uuid : obj.uuid;
                    if (!keptGlasses.has(id) && keptGlasses.size === 0) {
                      keptGlasses.add(id);
                    } else if (!keptGlasses.has(id)) {
                      root.visible = false;
                      return; // salta ulteriori registrazioni su questo ramo
                    }
                  }
                } catch { }
                // Mappa esatta di tutte le ossa usando i nomi precisi
                if (obj.type === "Bone") {
                  for (const boneName of boneNames) {
                    if (obj.name === boneName && !bonesMap[boneName]) {
                      bonesMap[boneName] = obj;
                    }
                  }
                  // Imposta le ossa speciali per compatibilità
                  if (obj.name === "Head" && !headBone) headBone = obj;
                  if (obj.name === "LeftShoulder" && !shoulderLBone)
                    shoulderLBone = obj;
                  if (obj.name === "RightShoulder" && !shoulderRBone)
                    shoulderRBone = obj;
                  if (obj.name === "LeftArm" && !armLBone) armLBone = obj;
                  if (obj.name === "RightArm" && !armRBone) armRBone = obj;
                  if (obj.name === "LeftForeArm" && !forearmLBone)
                    forearmLBone = obj;
                  if (obj.name === "RightForeArm" && !forearmRBone)
                    forearmRBone = obj;
                }

                // Ricerca di jawBone per compatibilità (fallback)
                if (
                  !jawBone &&
                  obj.type === "Bone" &&
                  (name.includes("jaw") ||
                    name.includes("lowerjaw") ||
                    name.includes("mandible") ||
                    name.includes("mixamorigjaw"))
                ) {
                  jawBone = obj;
                }

                if (obj.isMesh) {
                  try {
                    const keys = obj.morphTargetDictionary
                      ? Object.keys(obj.morphTargetDictionary)
                      : [];
                    console.log(
                      "Mesh:",
                      obj.name,
                      "hasMorph:",
                      !!obj.morphTargetDictionary,
                      "keys:",
                      keys
                    );
                  } catch { }
                }
                if (
                  !jawBone &&
                  obj.isSkinnedMesh &&
                  obj.skeleton &&
                  Array.isArray(obj.skeleton.bones)
                ) {
                  try {
                    const bones = obj.skeleton.bones;
                    try {
                      console.log(
                        "Skeleton bones:",
                        bones.map((b) => b.name)
                      );
                    } catch { }
                    const toLower = (s) => String(s || "").toLowerCase();
                    const hasAny = (s, tokens) => {
                      const n = toLower(s);
                      return tokens.some((t) => n.includes(t));
                    };
                    const jawTokens = [
                      "jaw",
                      "lowerjaw",
                      "mandible",
                      "chin",
                      "facial_jaw",
                      "bip_c_jaw",
                      "_jaw",
                      "jawbone",
                      "ctr_jaw",
                      "bn_jaw",
                      "j_jaw",
                      "mixamorig:jaw",
                      "mixamorigjaw",
                    ];
                    const mouthTokens = [
                      "mouth",
                      "lowerlip",
                      "upperlip",
                      "lip",
                    ];
                    let candidate = bones.find((b) =>
                      hasAny(b.name, jawTokens)
                    );
                    if (!candidate)
                      candidate = bones.find(
                        (b) =>
                          hasAny(b.name, mouthTokens) &&
                          (b.children?.length || 0) <= 2
                      );
                    if (!candidate) {
                      const head = bones.find((b) =>
                        /head|neck|face/i.test(b.name)
                      );
                      if (head && head.children && head.children.length) {
                        candidate =
                          head.children.find((c) =>
                            /jaw|mouth|chin|mandible/i.test(c.name)
                          ) || head.children[0];
                      }
                    }
                    if (candidate) {
                      jawBone = candidate;
                      console.log(
                        "Jaw bone trovato via Skeleton:",
                        candidate.name
                      );
                    }
                    const headCand = bones.find((b) =>
                      /\bhead\b/i.test(b.name)
                    );
                    if (headCand && !headBone) headBone = headCand;
                  } catch { }
                }
                if (
                  obj.isMesh &&
                  obj.morphTargetDictionary &&
                  obj.morphTargetInfluences
                ) {
                  const dict = obj.morphTargetDictionary;
                  const lowerMap = {};
                  try {
                    Object.keys(dict).forEach((k) => {
                      lowerMap[String(k).toLowerCase()] = dict[k];
                    });
                  } catch { }
                  const findKeyCI = (cands) => {
                    for (const name of cands) {
                      const idx = lowerMap[String(name).toLowerCase()];
                      if (idx !== undefined) return idx;
                    }
                    return undefined;
                  };
                  const jawIdx = findKeyCI([
                    "jawopen",
                    "jaw_open",
                    "viseme_aa",
                    "aa",
                    "base_jaw",
                    "j_open",
                  ]);
                  const funnelIdx = findKeyCI([
                    "mouthfunnel",
                    "lipsfunnel",
                    "viseme_ou",
                    "ou",
                    "uw",
                    "ow",
                    "oh",
                  ]);
                  const puckerIdx = findKeyCI([
                    "mouthpucker",
                    "lipspucker",
                    "pucker",
                  ]);
                  const smileLIdx = findKeyCI([
                    "mouthsmile_l",
                    "smileleft",
                    "mouthsmileleft",
                  ]);
                  const smileRIdx = findKeyCI([
                    "mouthsmile_r",
                    "smileright",
                    "mouthsmileright",
                  ]);
                  const closeIdx = findKeyCI([
                    "mouthclose",
                    "lipsupperclose",
                    "lipslowerclose",
                    "viseme_mbp",
                    "mbp",
                  ]);
                  const eyeBlinkLIdx = findKeyCI([
                    "eyeblink_l",
                    "eyeBlink_L",
                    "eyelidclose_l",
                    "eyelid_l",
                    "eyeblinkleft",
                  ]);
                  const eyeBlinkRIdx = findKeyCI([
                    "eyeblink_r",
                    "eyeBlink_R",
                    "eyelidclose_r",
                    "eyelid_r",
                    "eyeblinkright",
                  ]);
                  if (
                    jawIdx !== undefined &&
                    (/wolf3d_head/i.test(obj.name) || !morphMesh)
                  ) {
                    morphMesh = obj;
                    morphIndex = jawIdx;
                    console.log(
                      "Morph target (jaw) trovato su",
                      obj.name,
                      "index:",
                      morphIndex
                    );
                    try {
                      if (jawIdx !== undefined) visemeIndices.jawOpen = jawIdx;
                      if (funnelIdx !== undefined)
                        visemeIndices.mouthFunnel = funnelIdx;
                      if (puckerIdx !== undefined)
                        visemeIndices.mouthPucker = puckerIdx;
                      if (smileLIdx !== undefined)
                        visemeIndices.mouthSmileL = smileLIdx;
                      if (smileRIdx !== undefined)
                        visemeIndices.mouthSmileR = smileRIdx;
                      if (closeIdx !== undefined)
                        visemeIndices.mouthClose = closeIdx;
                      if (
                        visemeIndices.mouthFunnel < 0 &&
                        lowerMap["viseme_ou"] !== undefined
                      )
                        visemeIndices.mouthFunnel = lowerMap["viseme_ou"];
                      if (
                        visemeIndices.jawOpen < 0 &&
                        lowerMap["viseme_aa"] !== undefined
                      )
                        visemeIndices.jawOpen = lowerMap["viseme_aa"];
                      if (
                        eyeBlinkLIdx !== undefined ||
                        eyeBlinkRIdx !== undefined
                      ) {
                        eyeMesh = obj;
                        if (eyeBlinkLIdx !== undefined)
                          eyeIndices.eyeBlinkLeft = eyeBlinkLIdx;
                        if (eyeBlinkRIdx !== undefined)
                          eyeIndices.eyeBlinkRight = eyeBlinkRIdx;
                      }
                    } catch { }
                  } else if (!morphMesh) {
                    const altIdx = findKeyCI([
                      "jawopen",
                      "mouthopen",
                      "viseme_aa",
                      "aa",
                    ]);
                    if (altIdx !== undefined) {
                      morphMesh = obj;
                      morphIndex = altIdx;
                      console.log(
                        "Morph target (alt jaw) su",
                        obj.name,
                        "index:",
                        morphIndex
                      );
                    }
                  }
                  try {
                    const addIdx = (n) => {
                      const k = String(n).toLowerCase();
                      return lowerMap[k] !== undefined ? lowerMap[k] : -1;
                    };
                    const indices = {
                      // core visemes
                      jawOpen: jawIdx !== undefined ? jawIdx : -1,
                      mouthFunnel: funnelIdx !== undefined ? funnelIdx : -1,
                      mouthPucker: puckerIdx !== undefined ? puckerIdx : -1,
                      mouthSmileL: smileLIdx !== undefined ? smileLIdx : -1,
                      mouthSmileR: smileRIdx !== undefined ? smileRIdx : -1,
                      mouthClose: closeIdx !== undefined ? closeIdx : -1,
                      eyeBlinkLeft:
                        eyeBlinkLIdx !== undefined ? eyeBlinkLIdx : -1,
                      eyeBlinkRight:
                        eyeBlinkRIdx !== undefined ? eyeBlinkRIdx : -1,
                      // extended ARKit set
                      browDownLeft: addIdx("browDownLeft"),
                      browDownRight: addIdx("browDownRight"),
                      browInnerUp: addIdx("browInnerUp"),
                      browOuterUpLeft: addIdx("browOuterUpLeft"),
                      browOuterUpRight: addIdx("browOuterUpRight"),
                      eyeSquintLeft: addIdx("eyeSquintLeft"),
                      eyeSquintRight: addIdx("eyeSquintRight"),
                      eyeWideLeft: addIdx("eyeWideLeft"),
                      eyeWideRight: addIdx("eyeWideRight"),
                      jawForward: addIdx("jawForward"),
                      jawLeft: addIdx("jawLeft"),
                      jawRight: addIdx("jawRight"),
                      mouthFrownLeft: addIdx("mouthFrownLeft"),
                      mouthFrownRight: addIdx("mouthFrownRight"),
                      mouthShrugLower: addIdx("mouthShrugLower"),
                      mouthShrugUpper: addIdx("mouthShrugUpper"),
                      noseSneerLeft: addIdx("noseSneerLeft"),
                      noseSneerRight: addIdx("noseSneerRight"),
                      mouthLowerDownLeft: addIdx("mouthLowerDownLeft"),
                      mouthLowerDownRight: addIdx("mouthLowerDownRight"),
                      mouthLeft: addIdx("mouthLeft"),
                      mouthRight: addIdx("mouthRight"),
                      eyeLookDownLeft: addIdx("eyeLookDownLeft"),
                      eyeLookDownRight: addIdx("eyeLookDownRight"),
                      eyeLookUpLeft: addIdx("eyeLookUpLeft"),
                      eyeLookUpRight: addIdx("eyeLookUpRight"),
                      eyeLookInLeft: addIdx("eyeLookInLeft"),
                      eyeLookInRight: addIdx("eyeLookInRight"),
                      eyeLookOutLeft: addIdx("eyeLookOutLeft"),
                      eyeLookOutRight: addIdx("eyeLookOutRight"),
                      cheekPuff: addIdx("cheekPuff"),
                      cheekSquintLeft: addIdx("cheekSquintLeft"),
                      cheekSquintRight: addIdx("cheekSquintRight"),
                      mouthDimpleLeft: addIdx("mouthDimpleLeft"),
                      mouthDimpleRight: addIdx("mouthDimpleRight"),
                      mouthStretchLeft: addIdx("mouthStretchLeft"),
                      mouthStretchRight: addIdx("mouthStretchRight"),
                      mouthRollLower: addIdx("mouthRollLower"),
                      mouthRollUpper: addIdx("mouthRollUpper"),
                      mouthPressLeft: addIdx("mouthPressLeft"),
                      mouthPressRight: addIdx("mouthPressRight"),
                      mouthUpperUpLeft: addIdx("mouthUpperUpLeft"),
                      mouthUpperUpRight: addIdx("mouthUpperUpRight"),
                      mouthSmileLeft: addIdx("mouthSmileLeft"),
                      mouthSmileRight: addIdx("mouthSmileRight"),
                      tongueOut: addIdx("tongueOut"),
                    };
                    if (eyeMesh === null) {
                      if (
                        dict["eyeBlinkLeft"] !== undefined ||
                        dict["eyeBlinkRight"] !== undefined ||
                        eyeBlinkLIdx !== undefined ||
                        eyeBlinkRIdx !== undefined
                      ) {
                        eyeMesh = obj;
                        if (dict["eyeBlinkLeft"] !== undefined)
                          eyeIndices.eyeBlinkLeft = dict["eyeBlinkLeft"];
                        if (dict["eyeBlinkRight"] !== undefined)
                          eyeIndices.eyeBlinkRight = dict["eyeBlinkRight"];
                        if (eyeBlinkLIdx !== undefined)
                          eyeIndices.eyeBlinkLeft = eyeBlinkLIdx;
                        if (eyeBlinkRIdx !== undefined)
                          eyeIndices.eyeBlinkRight = eyeBlinkRIdx;
                      }
                    }
                    if (Object.values(indices).some((v) => v !== -1)) {
                      visemeMeshes.push({ mesh: obj, indices });
                      console.log("Viseme mesh registered:", obj.name, indices);
                    }
                    // Esponi le variabili sulla window per il debug da console
                    window.debugEnjoyTalk = {
                      get visemeMeshes() {
                        return visemeMeshes;
                      },
                      get lipConfig() {
                        return lipConfig;
                      },
                      get visemeTargets() {
                        return visemeTargets;
                      },
                      get lastVisemes() {
                        return lastVisemes;
                      },
                      get visemeIndices() {
                        return visemeIndices;
                      },
                      setSmileStrength(val) {
                        lipConfig.smileStrength = val;
                        console.log("smileStrength impostato a:", val);
                      },
                      testSmile() {
                        console.log(
                          "Test smile - imposto mouthSmileL e mouthSmileR a 0.5"
                        );
                        visemeMeshes.forEach((vm) => {
                          if (vm.mesh && vm.mesh.morphTargetInfluences) {
                            const infl = vm.mesh.morphTargetInfluences;
                            const idxs = vm.indices || {};
                            if (idxs.mouthSmileL >= 0)
                              infl[idxs.mouthSmileL] = 0.5;
                            if (idxs.mouthSmileR >= 0)
                              infl[idxs.mouthSmileR] = 0.5;
                          }
                        });
                      },
                    };
                  } catch { }
                }
              });
              try {
                if (headBone) {
                  const p = new THREE.Vector3();
                  headBone.getWorldPosition(p);
                  const t = p.clone();
                  t.y -= 0.06;
                  const defaultDist = 1.45;
                  const dist =
                    isFinite(headDistParam) && headDistParam > 0.2
                      ? headDistParam
                      : defaultDist;
                  const fov =
                    isFinite(headFovParam) &&
                      headFovParam >= 20 &&
                      headFovParam <= 70
                      ? headFovParam
                      : 38;
                  camera.position.set(p.x, t.y + 0.02, p.z + dist);
                  camera.fov = fov;
                  camera.lookAt(t);
                  camera.updateProjectionMatrix();
                  try {
                    console.log("CAM headBone target", {
                      pos: camera.position.toArray(),
                      fov: camera.fov,
                      target: t.toArray(),
                      dist,
                    });
                  } catch { }
                  try {
                    if (jawBone) {
                      const prev = jawBone.rotation.x;
                      jawBone.rotation.x += 0.02;
                      humanoid.updateMatrixWorld(true);
                      const before = new THREE.Box3()
                        .setFromObject(humanoid)
                        .getSize(new THREE.Vector3());
                      jawBone.rotation.x = prev;
                      humanoid.updateMatrixWorld(true);
                      const after = new THREE.Box3()
                        .setFromObject(humanoid)
                        .getSize(new THREE.Vector3());
                      jawBoneHasInfluence =
                        Math.abs(before.y - after.y) > 1e-4 ||
                        Math.abs(before.x - after.x) > 1e-4;
                      if (debugEnabled)
                        console.log("jawBoneHasInfluence", jawBoneHasInfluence);
                    }
                  } catch { }
                } else {
                  let headMesh = null;
                  humanoid.traverse((obj) => {
                    if (
                      !headMesh &&
                      (/wolf3d_head/i.test(obj.name) || /head$/i.test(obj.name))
                    )
                      headMesh = obj;
                  });
                  if (headMesh) {
                    fitCameraToObject(camera, headMesh, 1.2);
                  } else {
                    fitCameraToObject(camera, humanoid, 1.35);
                  }
                  try {
                    console.log("CAM fallback fit");
                  } catch { }
                }
              } catch { }
              head.visible = false;
              jaw.visible = false;
            } catch { }
          }
          function loadWithFBX() {
            return new Promise((resolve, reject) => {
              try {
                const loader = new FBXLoaderCtor();
                console.log("Carico humanoid FBX da", fbxUrl);
                loader.load(
                  fbxUrl,
                  (obj) => {
                    try {
                      obj.updateMatrixWorld(true);
                    } catch { }
                    attachHumanoid(obj);
                    resolve(true);
                  },
                  undefined,
                  (err) => {
                    console.warn("Impossibile caricare FBX", err);
                    reject(err);
                  }
                );
              } catch (e) {
                reject(e);
              }
            });
          }
          function loadWithGLTF() {
            return new Promise((resolve, reject) => {
              try {
                if (!GLTFLoaderCtor) {
                  reject(new Error("GLTFLoader non disponibile"));
                  return;
                }
                const loader = new GLTFLoaderCtor();
                console.log("Carico humanoid GLB da", glbUrl);
                loader.load(
                  glbUrl,
                  (gltf) => {
                    attachHumanoid(gltf.scene);
                    resolve(true);
                  },
                  undefined,
                  (err) => {
                    console.warn("Impossibile caricare GLB", err);
                    reject(err);
                  }
                );
              } catch (e) {
                reject(e);
              }
            });
          }
          function loadIdleAnimation() {
            return new Promise((resolve) => {
              try {
                if (!GLTFLoaderCtor) {
                  resolve(false);
                  return;
                }
                idleAnimationActions = [];
                idleAnimationMixer = null;
                idleAnimationActive = false;
                const webComponentOrigin = window.__ENJOY_TALK_3D_ORIGIN__ || window.location.origin;
                const idleAnimPathRelative = "/images/animation-library-master/feminine/glb/idle/F_Standing_Idle_Variations_003.glb";
                const idleAnimUrl =
                  webComponentOrigin + `/api/static${idleAnimPathRelative}` +
                  "?v=" +
                  Date.now();
                const loader = new GLTFLoaderCtor();
                loader.load(
                  idleAnimUrl,
                  (gltf) => {
                    try {
                      if (
                        humanoid &&
                        gltf.animations &&
                        gltf.animations.length > 0
                      ) {
                        idleAnimationMixer = new window.THREE.AnimationMixer(
                          humanoid
                        );
                        gltf.animations.forEach((clip, idx) => {
                          try {
                            const action = idleAnimationMixer.clipAction(clip);
                            if (action) {
                              action.loop = window.THREE.LoopRepeat;
                              idleAnimationActions.push(action);
                              console.log(
                                `ANIMATION [IDLE] Clip ${idx} caricata: ${clip.name}`
                              );
                            }
                          } catch (e) {
                            console.warn(
                              "Errore caricamento azione animazione:",
                              e
                            );
                          }
                        });
                        console.log(
                          `ANIMATION [IDLE] Animazione idle caricata: ${gltf.animations.length} clips totali`
                        );
                        if (idleAnimationActions.length > 0) {
                          idleAnimationActive = true;
                          idleAnimationActions.forEach((a, idx) => {
                            console.log(
                              `ANIMATION [IDLE] Avvio azione ${idx}: ${a.getClip().name
                              }`
                            );
                            a.play();
                          });
                          console.log(
                            `ANIMATION [IDLE] ✓ Idle animations avviate (${idleAnimationActions.length})`
                          );
                        }
                        resolve(true);
                      } else {
                        resolve(false);
                      }
                    } catch (e) {
                      console.warn("Errore caricamento idle animation:", e);
                      resolve(false);
                    }
                  },
                  undefined,
                  (err) => {
                    console.warn("Impossibile caricare idle animation", err);
                    resolve(false);
                  }
                );
              } catch (e) {
                console.warn("Errore loadIdleAnimation:", e);
                resolve(false);
              }
            });
          }
          function loadTalkingAnimations() {
            return new Promise((resolve) => {
              try {
                if (!GLTFLoaderCtor) {
                  resolve(false);
                  return;
                }
                const loader = new GLTFLoaderCtor();
                let loadedCount = 0;
                talkingAnimationVariants = [];
                talkingAnimationMixer = null;
                currentTalkingAnimation = null;
                talkingAnimationActive = false;
                const webComponentOrigin = window.__ENJOY_TALK_3D_ORIGIN__ || window.location.origin;
                for (let i = 1; i <= 6; i++) {
                  const variant = String(i).padStart(3, "0");
                  const talkPathRelative = `/images/animation-library-master/feminine/glb/expression/F_Talking_Variations_${variant}.glb`;
                  const talkUrl =
                    webComponentOrigin + `/api/static${talkPathRelative}` +
                    "?v=" +
                    Date.now();
                  loader.load(
                    talkUrl,
                    (gltf) => {
                      try {
                        if (gltf.animations && gltf.animations.length > 0) {
                          const variantClips = gltf.animations.map(
                            (clip, idx) => {
                              console.log(
                                `ANIMATION [TALKING] Variante ${variant} - Clip ${idx} caricata: ${clip.name}`
                              );
                              return { clip, name: clip.name };
                            }
                          );
                          talkingAnimationVariants.push(variantClips);
                          console.log(
                            `ANIMATION [TALKING] Variante ${variant} ✓ caricata: ${gltf.animations.length} clips`
                          );
                        }
                        loadedCount++;
                        if (loadedCount === 6) {
                          if (
                            talkingAnimationVariants.length > 0 &&
                            humanoid &&
                            window.THREE
                          ) {
                            talkingAnimationMixer =
                              new window.THREE.AnimationMixer(humanoid);
                            console.log(
                              `ANIMATION [TALKING] ✓ Tutte le talking animations caricate: ${talkingAnimationVariants.length
                              } varianti, ${talkingAnimationVariants.reduce(
                                (sum, v) => sum + v.length,
                                0
                              )} clips totali`
                            );
                            resolve(true);
                          } else {
                            console.warn(
                              "Errore: variants=",
                              talkingAnimationVariants.length,
                              "humanoid=",
                              !!humanoid,
                              "THREE=",
                              !!window.THREE
                            );
                            resolve(false);
                          }
                        }
                      } catch (e) {
                        console.warn(
                          `Errore caricamento talking ${variant}:`,
                          e
                        );
                        loadedCount++;
                        if (loadedCount === 6)
                          resolve(talkingAnimationVariants.length > 0);
                      }
                    },
                    undefined,
                    (err) => {
                      console.warn(
                        `Impossibile caricare talking variant ${variant}`,
                        err
                      );
                      loadedCount++;
                      if (loadedCount === 6)
                        resolve(talkingAnimationVariants.length > 0);
                    }
                  );
                }
              } catch (e) {
                console.warn("Errore loadTalkingAnimations:", e);
                resolve(false);
              }
            });
          }
          (async () => {
            try {
              if (humanoid) return; // già caricato
              console.log(
                "ANIMATION [LOADING] Inizio caricamento modello e animazioni..."
              );
              if (GLTFLoaderCtor) {
                console.log("ANIMATION [LOADING] Caricamento con GLTFLoader");
                await loadWithGLTF();
                console.log("ANIMATION [LOADING] ✓ Humanoid caricato (GLTF)");
                await loadIdleAnimation();
                console.log("ANIMATION [LOADING] ✓ Idle animation caricate");
                await loadTalkingAnimations();
                console.log(
                  "ANIMATION [LOADING] ✓ Talking animations caricate"
                );
                return;
              }
              if (FBXLoaderCtor && fbxUrl) {
                console.log("ANIMATION [LOADING] Caricamento con FBXLoader");
                await loadWithFBX();
                console.log("ANIMATION [LOADING] ✓ Humanoid caricato (FBX)");
                await loadIdleAnimation();
                console.log("ANIMATION [LOADING] ✓ Idle animation caricate");
                await loadTalkingAnimations();
                console.log(
                  "ANIMATION [LOADING] ✓ Talking animations caricate"
                );
                return;
              }
            } catch (e) {
              console.warn("ANIMATION [LOADING] ❌ Nessun modello caricato", e);
            } finally {
              humanoidLoading = false;
              // Nascondi l'overlay di caricamento
              if (loadingOverlay) {
                loadingOverlay.classList.add("hidden");
              }
              // Mostra il bottone "Conversa con Me" solo in layout full
              if (!isWebComponent && conversaBtnContainer) {
                conversaBtnContainer.classList.remove("hidden");
              }

              // In modalità snippet: frase di benvenuto presa dal welcome_message del team
              try {
                const vmInst = instance && instance.proxy ? instance.proxy : null;
                const isSnippet = import.meta.env.VITE_IS_WEB_COMPONENT || false;
                // IMPORTANTISSIMO: non far partire il benvenuto mentre il widget è chiuso (altrimenti l'utente non lo sente)
                if (isSnippet && vmInst && vmInst.widgetOpen && !vmInst.introPlayed) {
                  try {
                    const greeting = vmInst.getGreetingMessage
                      ? vmInst.getGreetingMessage()
                      : "Buongiorno";
                    vmInst.startStream && vmInst.startStream(greeting);
                    vmInst.introPlayed = true;
                  } catch { }
                }
              } catch { }

              console.log(
                "ANIMATION [LOADING] Completato (humanoidLoading = false)"
              );
            }
          })();
        } catch (e) {
          console.warn("Errore loadHumanoid()", e);
          humanoidLoading = false;
          // Nascondi l'overlay di caricamento in caso di errore
          if (loadingOverlay) {
            loadingOverlay.classList.add("hidden");
          }
          // Mostra il bottone anche in caso di errore
          if (conversaBtnContainer) {
            conversaBtnContainer.classList.remove("hidden");
          }
        }
      }

      function fitCameraToObject(camera, object, offset = 1.25) {
        try {
          const box = new THREE.Box3().setFromObject(object);
          const size = box.getSize(new THREE.Vector3());
          const center = box.getCenter(new THREE.Vector3());
          const aspect =
            camera.aspect && isFinite(camera.aspect) ? camera.aspect : 1;
          const vFOV = THREE.MathUtils.degToRad(camera.fov);
          const hFOV = 2 * Math.atan(Math.tan(vFOV / 2) * aspect);
          const distV = size.y / 2 / Math.tan(vFOV / 2);
          const distH = size.x / 2 / Math.tan(hFOV / 2);
          let dist = Math.max(distV, distH) * (offset || 1);
          const dir = new THREE.Vector3()
            .subVectors(camera.position, center)
            .normalize();
          if (!isFinite(dir.lengthSq()) || dir.lengthSq() === 0)
            dir.set(0, 0, 1);
          camera.position.copy(center.clone().add(dir.multiplyScalar(dist)));
          camera.near = Math.max(0.01, dist / 100);
          camera.far = Math.max(camera.near + 1, dist * 10 + size.length());
          camera.lookAt(center);
          camera.updateProjectionMatrix();
          try {
            console.log("CAM fitCameraToObject", {
              pos: camera.position.toArray(),
              fov: camera.fov,
              center: center.toArray(),
              size: size.toArray(),
              dist,
              aspect,
            });
          } catch { }
        } catch (e) {
          console.warn("fitCameraToObject error", e);
        }
      }

      function playRandomTalkingAnimation() {
        try {
          console.log(
            "ANIMATION [PLAY_TALKING] Chiamata. mixer=",
            !!talkingAnimationMixer,
            "humanoid=",
            !!humanoid,
            "variants=",
            talkingAnimationVariants.length
          );
          if (
            !talkingAnimationMixer ||
            !humanoid ||
            talkingAnimationVariants.length === 0
          ) {
            console.warn(
              "ANIMATION [PLAY_TALKING] ❌ Condizioni non soddisfatte per talking animation"
            );
            return;
          }
          // Ferma la talking animation precedente
          if (currentTalkingAnimation) {
            try {
              console.log(
                "ANIMATION [PLAY_TALKING] Fermando talking animation precedente"
              );
              currentTalkingAnimation.stop();
            } catch { }
          }
          // Sceglie una variante casuale (0-6)
          const variantIdx = Math.floor(
            Math.random() * talkingAnimationVariants.length
          );
          const variant = talkingAnimationVariants[variantIdx];
          if (!variant || variant.length === 0) {
            console.warn(
              "ANIMATION [PLAY_TALKING] ❌ Variante non trovata o vuota:",
              variantIdx
            );
            return;
          }
          // Sceglie una clip casuale della variante
          const clipIdx = Math.floor(Math.random() * variant.length);
          const clipData = variant[clipIdx];
          if (!clipData || !clipData.clip) {
            console.warn(
              "ANIMATION [PLAY_TALKING] ❌ Clip non trovata:",
              variantIdx,
              clipIdx
            );
            return;
          }
          console.log(
            `ANIMATION [PLAY_TALKING] Avvio: variante ${variantIdx}, clip ${clipIdx} (${clipData.name})`
          );
          // Crea una nuova azione per questa clip
          currentTalkingAnimation = talkingAnimationMixer.clipAction(
            clipData.clip
          );
          if (currentTalkingAnimation) {
            currentTalkingAnimation.clampWhenFinished = false;
            currentTalkingAnimation.loop = window.THREE.LoopOnce;

            // Calcola il timeout basato sulla durata dell'animazione
            const clipDuration = currentTalkingAnimation.getClip().duration;
            const timeoutMs = clipDuration * 1000; // durata

            currentTalkingAnimation.play();
            talkingAnimationActive = true;
            console.log(
              `✓ Talking animation avviata: ${clipData.name
              } (durata: ${clipDuration.toFixed(2)}s)`
            );

            // Dopo che finisce l'animazione, riaccendi le idle
            if (window.__talkingTimeoutId)
              clearTimeout(window.__talkingTimeoutId);
            window.__talkingTimeoutId = setTimeout(() => {
              console.log(
                "ANIMATION [TIMEOUT] Talking animation finita per timeout, riaccendo idle..."
              );
              //restoreIdleAnimations();
              window.__talkingTimeoutId = null;
            }, timeoutMs);
          } else {
            console.warn("ANIMATION [PLAY_TALKING] ❌ Azione non creata");
          }
        } catch (e) {
          console.warn("ANIMATION [PLAY_TALKING] ❌ Errore:", e);
        }
      }

      function restoreIdleAnimations() {
        try {
          if (talkingAnimationActive && idleAnimationMixer) {
            talkingAnimationActive = false;
            idleAnimationActive = true;
            if (currentTalkingAnimation) currentTalkingAnimation.stop();
            if (idleAnimationActions && idleAnimationActions.length > 0) {
              idleAnimationActions.forEach((action) => {
                try {
                  action.weight = 1.0;
                  action.loop = window.THREE.LoopRepeat;
                  if (!action.isRunning()) action.play();
                } catch (e) { }
              });
            }
          }
        } catch (e) { }
      }
      function stopTalkingAnimation() {
        try {
          if (currentTalkingAnimation) {
            console.log(
              `ANIMATION [STOP_TALKING] Fermando talking animation: ${currentTalkingAnimation.getClip().name
              }`
            );
            // CrossFade di ritorno alle idle animations per una transizione fluida
            const fadeDuration = 0.3; // 300ms di transizione
            if (idleAnimationActions && idleAnimationActions.length > 0) {
              console.log(
                `ANIMATION [STOP_TALKING] CrossFade indietro a ${idleAnimationActions.length} idle actions, durata: ${fadeDuration}s`
              );
              idleAnimationActions.forEach((action, idx) => {
                console.log(
                  `ANIMATION [STOP_TALKING]   ↳ Talking → Idle action ${idx}: ${action.getClip().name
                  }`
                );
                currentTalkingAnimation.crossFadeTo(action, fadeDuration, true);
                action.play(); // Riavvia le idle animations
              });
            }
            // Riabilita il mixer idle
            idleAnimationActive = true;
            currentTalkingAnimation.stop();
            currentTalkingAnimation = null;
            talkingAnimationActive = false;
            console.log(
              "ANIMATION [STOP_TALKING] ✓ Animation fermata e idle ripristinate"
            );
          }
        } catch (e) {
          console.warn("ANIMATION [STOP_TALKING] ❌ Errore:", e);
        }
      }

      // Monitoraggio automatico per riavviare le idle quando la talking finisce
      let lastTalkingState = false;
      function checkTalkingAnimationFinished() {
        try {
          const isTalkingNow =
            currentTalkingAnimation && talkingAnimationActive;

          // Se era in talking e ora non lo è più, torna a idle
          if (lastTalkingState && !isTalkingNow && talkingAnimationMixer) {
            // Controlla se l'azione è finita
            if (
              currentTalkingAnimation === null ||
              !currentTalkingAnimation.isRunning?.()
            ) {
              console.log(
                "ANIMATION [AUTO_STOP_TALKING] Talking animation finita, ritorno a idle..."
              );
              //stopTalkingAnimation();
            }
          }
          lastTalkingState = isTalkingNow;
        } catch (e) {
          /* silenzio */
        }
      }

      // ==================== CONSOLE API ====================
      // Espone un'interfaccia completa per controllare le animazioni da console
      window.enjoyTalkConsole = {
        talk: (msg) => {
          conversaBtnContainer.classList.add("hidden");
          sendToTts(msg);
        },
        // === TALKING ANIMATIONS ===
        playTalking: () => {
          console.log("🎬 Avvio talking animation...");
          playRandomTalkingAnimation();
        },
        stopTalking: () => {
          console.log("🛑 Fermo talking animation...");
          stopTalkingAnimation();
        },
        playTalkingVariant: (variantIdx, clipIdx) => {
          try {
            console.log(
              `🎬 Avvio talking variant ${variantIdx}, clip ${clipIdx}...`
            );
            if (
              !talkingAnimationMixer ||
              !humanoid ||
              !talkingAnimationVariants.length
            ) {
              console.warn("❌ Talking animations non caricate");
              return;
            }
            const variant =
              talkingAnimationVariants[
              variantIdx % talkingAnimationVariants.length
              ];
            if (!variant || !variant.length) {
              console.warn(`❌ Variante ${variantIdx} non trovata`);
              return;
            }
            const clip = variant[clipIdx % variant.length];
            if (!clip || !clip.clip) {
              console.warn(
                `❌ Clip ${clipIdx} non trovata in variante ${variantIdx}`
              );
              return;
            }
            if (currentTalkingAnimation) currentTalkingAnimation.stop();
            currentTalkingAnimation = talkingAnimationMixer.clipAction(
              clip.clip
            );
            currentTalkingAnimation.clampWhenFinished = false;
            currentTalkingAnimation.loop = window.THREE.LoopOnce;

            currentTalkingAnimation.play();
            const dur = currentTalkingAnimation.getClip().duration;
            if (window.__talkingTimeoutId)
              clearTimeout(window.__talkingTimeoutId);
            window.__talkingTimeoutId = setTimeout(() => {
              //restoreIdleAnimations();
              window.__talkingTimeoutId = null;
            }, dur * 1000);
            talkingAnimationActive = true;
            console.log(`✓ Talking animation avviata: ${clip.name}`);
          } catch (e) {
            console.warn("❌ Errore:", e);
          }
        },

        // === IDLE ANIMATIONS ===
        playIdle: () => {
          try {
            console.log("🎬 Avvio idle animations...");
            if (!idleAnimationMixer || !idleAnimationActions.length) {
              console.warn("❌ Idle animations non caricate");
              return;
            }
            idleAnimationActive = true;
            idleAnimationActions.forEach((action, idx) => {
              action.play();
              console.log(`  ↳ Idle action ${idx}: ${action.getClip().name}`);
            });
            if (currentTalkingAnimation) currentTalkingAnimation.stop();
            currentTalkingAnimation = null;
            talkingAnimationActive = false;
            console.log("✓ Idle animations avviate");
          } catch (e) {
            console.warn("❌ Errore:", e);
          }
        },
        stopIdle: () => {
          try {
            console.log("🛑 Fermo idle animations...");
            if (idleAnimationActions && idleAnimationActions.length) {
              idleAnimationActions.forEach((action, idx) => {
                action.stop();
              });
            }
            idleAnimationActive = false;
            console.log("✓ Idle animations fermate");
          } catch (e) {
            console.warn("❌ Errore:", e);
          }
        },

        // === INFO ===
        listVariants: () => {
          console.group("📋 Talking Animation Variants");
          talkingAnimationVariants.forEach((variant, vidx) => {
            console.group(`Variant ${vidx} (${variant.length} clips)`);
            variant.forEach((clip, cidx) => {
              console.log(
                `  Clip ${cidx}: ${clip.name} (${clip.clip.duration.toFixed(
                  2
                )}s)`
              );
            });
            console.groupEnd();
          });
          console.groupEnd();
        },
        listIdle: () => {
          console.group("📋 Idle Animations");
          if (idleAnimationActions && idleAnimationActions.length) {
            idleAnimationActions.forEach((action, idx) => {
              console.log(
                `Idle ${idx}: ${action.getClip().name} (${action
                  .getClip()
                  .duration.toFixed(2)}s)`
              );
            });
          } else {
            console.log("❌ Nessuna idle animation caricata");
          }
          console.groupEnd();
        },

        status: () => {
          console.group("📊 Animation Status");
          console.log("Humanoid loaded:", !!humanoid);
          console.log(
            "Idle animations loaded:",
            idleAnimationActions?.length || 0
          );
          console.log(
            "Talking variants loaded:",
            talkingAnimationVariants?.length || 0
          );
          console.log("Idle active:", idleAnimationActive);
          console.log("Talking active:", talkingAnimationActive);
          console.log(
            "Current talking animation:",
            currentTalkingAnimation?.getClip?.()?.name || "None"
          );
          console.groupEnd();
        },

        // === HELP ===
        help: () => {
          console.log(
            `%c🎬 EnjoyTalk 3D - Animation Console API`,
            "color: #6366f1; font-size: 14px; font-weight: bold;"
          );
          console.log(
            `
%c--- TALKING ANIMATIONS ---
enjoyTalkConsole.playTalking()           // Avvia una talking animation casuale
enjoyTalkConsole.stopTalking()           // Ferma la talking animation
enjoyTalkConsole.playTalkingVariant(v, c) // Avvia variante v, clip c (es: playTalkingVariant(0, 1))

%c--- IDLE ANIMATIONS ---
enjoyTalkConsole.playIdle()              // Riavvia le idle animations
enjoyTalkConsole.stopIdle()              // Ferma le idle animations

%c--- INFO & DEBUG ---
enjoyTalkConsole.listVariants()         // Elenca tutte le talking animation variants
enjoyTalkConsole.listIdle()             // Elenca tutte le idle animations
enjoyTalkConsole.status()               // Mostra lo stato corrente
enjoyTalkConsole.help()                 // Mostra questa guida
          `,
            "color: #10b981; font-size: 12px;",
            "color: #f59e0b; font-size: 12px;",
            "color: #8b5cf6; font-size: 12px;"
          );
        },
      };

      // Mostra il messaggio di benvenuto
      console.log(
        "%c✅ EnjoyTalk 3D Console API pronta! Digita: enjoyTalkConsole.help()",
        "color: #10b981; font-weight: bold; font-size: 13px;"
      );

      // Callback per l'evento 'finished' del mixer

      function onTalkingFinished(event) {
        try {
          console.log(
            "ANIMATION [FINISHED] Talking animation finita, ritorno a idle..."
          );
          if (
            talkingAnimationActive &&
            idleAnimationMixer &&
            currentTalkingAnimation
          ) {
            talkingAnimationActive = false;
            idleAnimationActive = true;

            // Ferma la talking animation
            currentTalkingAnimation.stop();

            // Riaccendi le idle animations visibili
            if (idleAnimationActions && idleAnimationActions.length > 0) {
              idleAnimationActions.forEach((action, idx) => {
                try {
                  // Assicura che l'azione sia visibile e in playing
                  action.weight = 1.0;
                  action.loop = window.THREE.LoopRepeat;

                  // Se non sta girando, falla partire
                  if (!action.isRunning()) {
                    action.play();
                  }

                  console.log(
                    `ANIMATION [FINISHED]   ↳ Idle action ${idx}: ${action.getClip().name
                    }, weight: ${action.weight}, running: ${action.isRunning()}`
                  );
                } catch (e) {
                  console.warn(
                    `ANIMATION [FINISHED] Errore riavvio idle action ${idx}:`,
                    e
                  );
                }
              });
              console.log(
                `ANIMATION [FINISHED] ✓ Idle animations ripristinate (${idleAnimationActions.length} actions)`
              );
            }
          }
        } catch (e) {
          console.warn("ANIMATION [FINISHED] ❌ Errore:", e);
        }
      }
    });

    return { isWebComponent };
  },
});

</script>

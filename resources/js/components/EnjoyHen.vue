<template>
  <div ref="rootEl" id="enjoyHenRoot" :class="[
    'flex flex-col',
    !isWebComponent && 'min-h-[100dvh] max-h-[100dvh]',
    isWebComponent ? 'bg-transparent' : 'w-full bg-[#0f172a] overflow-hidden'
  ]">

    <!-- Floating launcher bubble (snippet mode) -->
    <button v-if="isWebComponent && !widgetOpen" id="henLauncherBtn"
      class="fixed z-[9999] bottom-4 right-4 w-14 h-14 rounded-full bg-emerald-600/90 backdrop-blur text-white shadow-lg border border-emerald-300/80 flex items-center justify-center"
      @click="onLauncherClick">
      💬
    </button>

    <!-- Widget content -->
    <div v-show="!isWebComponent || widgetOpen" :class="isWebComponent
      ? 'fixed z-[9998] bottom-24 right-4 w-[320px] max-w-[90vw] pointer-events-none'
      : 'flex flex-col flex-1'">
      <!-- Header -->
      <div class="px-4 py-4 border-b border-slate-700" v-if="!isWebComponent">
        <div class="mx-auto w-full max-w-2xl flex items-center gap-3">
          <img id="teamLogo" :src="teamLogo" alt="EnjoyHen"
            class="w-10 h-10 rounded-full object-cover border border-slate-600" />
          <h1 class="font-sans text-2xl text-white">EnjoyHen AI</h1>
        </div>
      </div>

      <!-- Main Content -->
      <div class="flex-1 flex items-center justify-center p-4 overflow-y-auto">
        <div :class="['w-full', isWebComponent ? 'max-w-full' : 'max-w-2xl']">
          <!-- Video Avatar / Text Chat -->
          <div :class="[
            'relative mb-6 rounded-lg overflow-hidden pointer-events-auto',
            isWebComponent ? 'bg-transparent border-none' : 'bg-black border border-slate-700'
          ]">
            <!-- Close button (snippet mode) -->
            <button v-if="isWebComponent" id="henCloseBtn" @click="closeWidget"
              class="absolute top-3 right-3 z-30 w-8 h-8 rounded-full bg-black/70 text-white flex items-center justify-center border border-white/40">
              ✕
            </button>
            <!-- SNIPPET: se snippetTextMode è attivo mostro solo la chat testuale -->
            <template v-if="isWebComponent && snippetTextMode">
              <div
                class="flex flex-col w-full h-full rounded-3xl bg-slate-900/95 backdrop-blur border border-slate-700/80 shadow-2xl">
                <!-- Header -->
                <div class="flex items-center justify-between px-4 py-3 border-b border-slate-800">
                  <div class="text-[11px] uppercase tracking-wide text-slate-400">
                    Assistente digitale
                  </div>
                  <button @click="closeSnippetTextMode"
                    class="w-7 h-7 rounded-full bg-slate-800/80 text-slate-200 flex items-center justify-center text-xs border border-slate-600/70">
                    ✕
                  </button>
                </div>

                <!-- Messages -->
                <div id="henTextMessages"
                  class="flex-1 max-h-[260px] min-h-[200px] overflow-y-auto px-4 py-3 space-y-2 text-sm scrollbar-thin scrollbar-thumb-slate-700 scrollbar-track-transparent">
                  <div v-if="snippetMessages.length === 0" class="text-xs text-slate-400">
                    Scrivi un messaggio per iniziare la conversazione con l’assistente.
                  </div>
                  <div v-for="(m, idx) in snippetMessages" :key="idx" :class="[
                    'max-w-[90%] px-3 py-2 rounded-2xl',
                    m.role === 'user'
                      ? 'ml-auto bg-emerald-600 text-white'
                      : 'mr-auto bg-slate-800 text-slate-100'
                  ]">
                    <div class="text-[10px] opacity-70 mb-0.5">
                      {{ m.role === 'user' ? 'Tu' : 'Assistente' }}
                    </div>
                    <div class="whitespace-pre-line">
                      {{ m.content }}
                    </div>
                  </div>
                </div>

                <!-- Input -->
                <div class="px-4 py-3 border-t border-slate-800">
                  <div class="flex items-center gap-2">
                    <input id="henSnippetInput" v-model="snippetInput" type="text" placeholder="Scrivi qui..."
                      @keyup.enter.prevent="sendSnippetInput"
                      class="flex-1 bg-slate-800/80 text-slate-100 text-sm outline-none border border-slate-700/80 rounded-full px-3 py-2 placeholder-slate-400" />
                    <button @click="sendSnippetInput"
                      class="w-9 h-9 rounded-full bg-emerald-600/90 text-white flex items-center justify-center text-sm shadow border border-emerald-400/80">
                      📤
                    </button>
                  </div>
                </div>
              </div>
            </template>

            <!-- DEFAULT: video HeyGen e overlay vari -->
            <template v-else>
              <video id="heygenVideo" class="w-full h-auto rounded-lg" autoplay playsinline controls>
              </video>

              <!-- Modal Trascrizione Email -->
              <div id="emailTranscriptModalHen"
                class="hidden absolute inset-0 flex items-center justify-center z-30 rounded-lg bg-black/60 backdrop-blur-sm">
                <div
                  class="w-full max-w-lg mx-4 bg-[#0b1220] border border-slate-700 rounded-xl shadow-2xl overflow-hidden">
                  <div class="px-4 py-3 border-b border-slate-700 bg-black/50 flex items-center justify-between">
                    <div class="text-slate-100 font-semibold text-base">Invia trascrizione via email</div>
                    <button id="sendTranscriptCancelHen"
                      class="text-slate-300 hover:text-white px-2 py-1 rounded-md hover:bg-slate-700/60">✕</button>
                  </div>
                  <div class="p-4 space-y-3">
                    <label class="block text-slate-300 text-sm">Indirizzo email destinatario</label>
                    <input id="emailTranscriptInputHen" type="email" placeholder="nome@esempio.com"
                      class="w-full px-3 py-2 bg-[#111827] text-white border border-slate-700 rounded-md placeholder-slate-400 focus:border-indigo-500 focus:outline-none" />
                    <div id="emailTranscriptStatusHen" class="text-xs text-slate-400 min-h-[1rem]"></div>
                  </div>
                  <div class="px-4 py-3 border-t border-slate-700 bg-black/50 flex items-center justify-end gap-2">
                    <button id="sendTranscriptCancel2Hen"
                      class="px-3 py-2 bg-slate-700 hover:bg-slate-600 text-white rounded-md transition-colors text-sm">Annulla</button>
                    <button id="sendTranscriptConfirmHen"
                      class="px-3 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-md transition-colors text-sm font-semibold">Invia</button>
                  </div>
                </div>
              </div>

              <!-- Fumetto di pensiero -->
              <div id="thinkingBubble"
                class="hidden absolute top-4 left-1/2 transform -translate-x-1/2 bg-white rounded-lg px-4 py-2 shadow-lg border border-gray-300 z-10">
                <div class="text-gray-700 text-sm font-medium">
                  💭 Sto pensando...
                </div>
                <div class="absolute bottom-0 left-1/2 transform translate-y-full -translate-x-1/2">
                  <div class="w-0 h-0 border-l-4 border-r-4 border-t-4 border-transparent border-t-white"></div>
                </div>
              </div>

              <!-- Status Badge -->
              <div id="videoAvatarStatus"
                class="absolute top-4 right-4 px-3 py-1 bg-slate-900/80 backdrop-blur text-slate-200 text-xs font-medium rounded-full border border-slate-700">
                Inizializzazione...
              </div>

              <!-- Loading Overlay -->
              <div id="loadingOverlay"
                class="absolute inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center rounded-lg z-20">
                <div class="flex flex-col items-center gap-4">
                  <div class="relative w-12 h-12">
                    <div
                      class="absolute inset-0 bg-gradient-to-r from-indigo-600 to-emerald-600 rounded-full animate-spin"
                      style="border-radius: 50%; -webkit-mask-image: radial-gradient(circle 10px at center, transparent 100%, black 100%); mask-image: radial-gradient(circle 10px at center, transparent 100%, black 100%);">
                    </div>
                    <div class="absolute inset-2 bg-black rounded-full flex items-center justify-center">
                      <div class="w-2 h-2 bg-gradient-to-r from-indigo-400 to-emerald-400 rounded-full animate-pulse">
                      </div>
                    </div>
                  </div>
                  <div class="text-white text-sm font-medium">Connessione in corso...</div>
                </div>
              </div>

              <!-- Inizio Chat Button Overlay -->
              <div id="startChatContainer"
                class="hidden absolute inset-0 flex items-center justify-center z-25 rounded-lg bg-black/40 backdrop-blur-sm">
                <button id="startChatBtn"
                  class="px-8 py-4 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-lg transition-colors text-lg shadow-lg hover:shadow-xl transform hover:scale-105">
                  🎤 Inizia Chat
                </button>
              </div>

              <!-- Debug Overlay (mostrato con ?debug=1) -->
              <div id="debugOverlay" class="hidden absolute left-1/2 -translate-x-1/2 top-3 z-10 w-full max-w-2xl px-3"
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
                  <div class="max-h-[50vh] overflow-auto p-2 text-[11px] font-mono text-slate-200 leading-relaxed">
                    <div id="debugContent" class="space-y-1"></div>
                  </div>
                </div>
              </div>
            </template>
          </div>

          <!-- Listening Badge -->
          <div id="listeningBadge"
            class="hidden px-3 py-2 bg-rose-600/90 text-white text-sm font-semibold rounded-md shadow animate-pulse text-center mb-4">
            🎤 Ascolto...
          </div>
          <!-- Thinking Badge -->
          <div id="thinkingBadgeHen"
            class="hidden px-3 py-2 bg-indigo-600/90 text-white text-sm font-semibold rounded-md shadow animate-pulse text-center mb-4">
            💭 Sto pensando...
          </div>
        </div>
      </div>

      <!-- Input Controls Bar (full layout only, non-snippet) -->
      <div v-if="!isWebComponent" id="controlsBar"
        class="bottom-0 left-0 w-full border-t border-slate-700 bg-[#0f172a] z-20 pb-[env(safe-area-inset-bottom)]">
        <div class="px-3 py-3 sm:px-4 sm:py-4">
          <div class="mx-auto w-full max-w-2xl">
            <div class="flex flex-wrap w-full gap-2 items-center min-w-0">
              <input id="textInput" type="text" placeholder="Scrivi il tuo messaggio..."
                class="flex-1 min-w-0 px-3 py-3 bg-slate-700 text-white border border-slate-600 rounded-lg placeholder-slate-400 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 text-sm sm:text-base" />
              <button id="sendBtn"
                class="px-3 py-3 sm:px-4 sm:py-3 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg transition-colors whitespace-nowrap text-sm sm:text-base font-medium">
                📤
              </button>
              <button id="micBtn"
                class="px-3 py-3 sm:px-4 sm:py-3 bg-rose-600 hover:bg-rose-700 text-white rounded-lg transition-colors whitespace-nowrap text-sm sm:text-base font-medium">
                🎤
              </button>
              <button id="emailTranscriptBtnHen"
                class="px-3 py-3 sm:px-4 sm:py-3 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg transition-colors whitespace-nowrap text-sm sm:text-base font-medium">
                📧 Trascrizione
              </button>
            </div>

            <!-- Feedback -->
            <div id="feedbackMsg" class="text-sm text-slate-400 text-center min-h-5 mt-2"></div>
          </div>
        </div>
      </div>
    </div>

    <!-- Floating controls bar (snippet mode) -->
    <div v-if="isWebComponent && widgetOpen" id="henFloatingControls"
      class="fixed z-[9999] bottom-4 right-4 flex items-center gap-2 pointer-events-auto">
      <!-- Menu button -->
      <button id="henMenuBtn" @click="snippetMenuOpen = !snippetMenuOpen"
        class="w-11 h-11 rounded-full bg-slate-900/80 backdrop-blur text-white flex items-center justify-center shadow-lg border border-slate-600/70">
        ⋯
      </button>
      <!-- Mic button -->
      <button id="henMicFloatingBtn" @click="onSnippetMicClick"
        class="w-11 h-11 rounded-full bg-rose-600/90 backdrop-blur text-white flex items-center justify-center shadow-lg border border-rose-400/80">
        🎤
      </button>
      <!-- Keyboard button -->
      <button id="henKeyboardBtn" @click="onSnippetTextClick"
        class="w-11 h-11 rounded-full bg-indigo-600/90 backdrop-blur text-white flex items-center justify-center shadow-lg border border-indigo-400/80">
        ⌨️
      </button>
    </div>


    <!-- Floating options menu (snippet mode) -->
    <div v-if="isWebComponent && widgetOpen && snippetMenuOpen" id="henOptionsPanel"
      class="fixed z-[9999] bottom-28 right-4 w-72 rounded-2xl bg-slate-900/90 backdrop-blur text-slate-100 shadow-2xl border border-slate-700/80 pointer-events-auto">
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
      <div class="px-4 py-3 border-t border-slate-800 text-[11px] font-semibold text-slate-400">
        ALTRO
      </div>
      <div class="px-4 py-2 space-y-2 text-sm">
        <button @click="openSettings" class="w-full text-left px-3 py-2 rounded-lg bg-slate-800/70 hover:bg-slate-700">
          Impostazioni
        </button>
        <button @click="openPrivacyPanel"
          class="w-full text-left px-3 py-2 rounded-lg bg-slate-800/70 hover:bg-slate-700">
          Informazioni / Privacy
        </button>
      </div>
    </div>

    <!-- Settings panel (snippet mode) -->
    <div v-if="isWebComponent && widgetOpen && showSettingsPanel"
      class="fixed z-[9999] top-24 right-4 w-[360px] max-w-[90vw] h-[70vh] pointer-events-auto">
      <div
        class="flex flex-col h-full rounded-2xl bg-slate-900/95 backdrop-blur text-slate-100 shadow-2xl border border-slate-700/80">
        <div class="flex items-center justify-between px-4 py-3 border-b border-slate-800">
          <div class="text-xs font-semibold uppercase tracking-wide text-slate-400">
            IMPOSTAZIONI
          </div>
          <button @click="showSettingsPanel = false"
            class="w-8 h-8 rounded-full bg-slate-800/80 text-white flex items-center justify-center">
            ✕
          </button>
        </div>

        <div class="flex-1 overflow-y-auto px-4 py-4 space-y-5 text-sm">
          <div>
            <div class="text-[11px] font-semibold text-slate-400 mb-2">SUONI</div>
            <div class="space-y-2">
              <label class="flex items-center justify-between text-xs">
                <span>Suoni notifiche</span>
                <button @click="snippetAudioOn = !snippetAudioOn"
                  class="relative inline-flex h-5 w-9 items-center rounded-full"
                  :class="snippetAudioOn ? 'bg-emerald-500' : 'bg-slate-600'">
                  <span class="sr-only">Toggle audio</span>
                  <span class="inline-block h-4 w-4 transform bg-white rounded-full shadow transition-transform"
                    :class="snippetAudioOn ? 'translate-x-4' : 'translate-x-0'"></span>
                </button>
              </label>
            </div>
          </div>

          <div>
            <div class="text-[11px] font-semibold text-slate-400 mb-2">ASPETTO</div>
            <label class="flex items-center justify-between text-xs">
              <span>Testo grande</span>
              <button @click="/* placeholder futuro */ 0"
                class="relative inline-flex h-5 w-9 items-center rounded-full bg-slate-600">
                <span class="sr-only">Toggle font size</span>
                <span class="inline-block h-4 w-4 transform bg-white rounded-full shadow translate-x-0"></span>
              </button>
            </label>
          </div>
        </div>
      </div>
    </div>

    <!-- Privacy / Info panel (snippet mode) -->
    <div v-if="isWebComponent && widgetOpen && showPrivacyPanel"
      class="fixed z-[9999] top-24 right-4 w-[360px] max-w-[90vw] h-[70vh] pointer-events-auto">
      <div
        class="flex flex-col h-full rounded-2xl bg-slate-900/95 backdrop-blur text-slate-100 shadow-2xl border border-slate-700/80">
        <div class="flex items-center justify-between px-4 py-3 border-b border-slate-800">
          <div class="text-xs font-semibold uppercase tracking-wide text-slate-400">
            PRIVACY
          </div>
          <button @click="showPrivacyPanel = false"
            class="w-8 h-8 rounded-full bg-slate-800/80 text-white flex items-center justify-center">
            ✕
          </button>
        </div>

        <div class="flex-1 px-4 py-4 flex flex-col justify-between text-sm">
          <div>
            <p class="text-slate-200 mb-3">
              Qui puoi consultare l'informativa sulla privacy e sui cookie del sito del Comune.
            </p>
            <button @click="openPrivacy"
              class="mt-2 inline-flex items-center justify-between w-full px-3 py-2 rounded-lg bg-slate-800/70 hover:bg-slate-700 text-sm">
              <span>Privacy policy</span>
              <span class="px-3 py-1 text-xs rounded-full bg-emerald-600 text-white">APRI</span>
            </button>
          </div>
          <div class="text-[11px] text-slate-500 mt-4">
            Gestito da EnjoyHen AI
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { onMounted, defineComponent, ref, getCurrentInstance } from "vue";

export default defineComponent({
  name: "EnjoyHen",
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
      default: () => window.location.pathname.split("/").pop(),
    },
  },
  data() {
    return {
      // Variabili locali (non props)
      widgetOpen: true,
      snippetMenuOpen: false,
      snippetTextMode: false,
      snippetInput: "",
      snippetMessages: [],
      snippetMessagesOn: true,
      snippetAudioOn: true,
      introPlayed: false,
      showSettingsPanel: false,
      showPrivacyPanel: false,
      uuid: null,
      heygenAvatar: "",
      heygenVoice: "",
      threadId: null,
      teamSlugLocal: null,
      heygenVideo: null,
      textInput: null,
      sendBtn: null,
      micBtn: null,
      startChatBtn: null,
      startChatContainer: null,
      listeningBadge: null,
      loadingOverlay: null,
      videoAvatarStatus: null,
      feedbackMsg: null,
      isListening: false,
      recognition: null,
      audioCtx: null,
      currentEventSource: null,
      HEYGEN_CONFIG: {
        apiKey: "",
        serverUrl: "https://api.heygen.com",
      },
      heygen: {
        sessionInfo: null,
        room: null,
        mediaStream: null,
        sessionToken: null,
        connecting: false,
        started: false,
      },
    };
  },
  mounted() {
    try {
      console.log("[EnjoyHen] ✓ mounted(), isWebComponent:", import.meta.env.VITE_IS_WEB_COMPONENT);
      if (import.meta.env.VITE_IS_WEB_COMPONENT) {
        this.widgetOpen = false;
      }
      this.rootEl = this.$el || document.getElementById("enjoyHenRoot");
      console.log("[EnjoyHen] rootEl found:", !!this.rootEl);
      this.initComponent();
    } catch (e) {
      console.error("EnjoyHen mount error:", e);
    }
  },
  beforeUnmount() {
    try {
      console.log("[EnjoyHen] beforeUnmount()");
      this.cleanup();
    } catch { }
  },
  methods: {
    onLauncherClick() {
      try {
        this.widgetOpen = true;
        this.snippetMenuOpen = false;
        this.snippetTextMode = false;
        if (this.loadingOverlay) {
          this.loadingOverlay.classList.remove("hidden");
        }
        this.ensureHeyGenSession().then(() => {
          if (!this.introPlayed) {
            const intro =
              "Ciao, sono il tuo assistente virtuale Enjoy Talk 3D. Posso rispondere alle domande riguardanti questo sito internet.";
            this.heygenSendRepeat(intro);
            this.introPlayed = true;
          }
        });
      } catch { }
    },

    closeWidget() {
      try {
        this.widgetOpen = false;
        this.snippetMenuOpen = false;
        this.snippetTextMode = false;
        this.showSettingsPanel = false;
        this.showPrivacyPanel = false;
        // Ferma e silenzia l'avatar quando si esce dalla modalità snippet
        try {
          if (this.heygenVideo) {
            this.heygenVideo.pause?.();
            this.heygenVideo.muted = true;
          }
        } catch { }
      } catch { }
    },

    onSnippetMicClick() {
      try {
        this.onMicClick();
      } catch { }
    },

    onSnippetTextClick() {
      try {
        const nowEnabled = !this.snippetTextMode;
        this.snippetTextMode = nowEnabled;

        // In modalità testo il video deve essere "spento": niente streaming/audio
        try {
          if (nowEnabled && this.heygenVideo) {
            this.heygenVideo.muted = true;
            this.heygenVideo.pause?.();
          } else if (!nowEnabled && this.heygenVideo) {
            // rientro in modalità avatar: il video resta fermo fino alla prossima risposta
            this.heygenVideo.pause?.();
            this.heygenVideo.muted = !this.snippetAudioOn;
          }
        } catch { }

        if (nowEnabled) {
          this.$nextTick &&
            this.$nextTick(() => {
              try {
                const input =
                  this.$el.querySelector &&
                  this.$el.querySelector("#henSnippetInput");
                if (input) {
                  input.focus();
                }
              } catch { }
            });
        }
      } catch { }
    },

    toggleTextInterface() {
      try {
        // Se sono già in modalità testo, torno alla modalità avatar
        if (this.snippetTextMode) {
          // esco dalla modalità testo ma NON riavvio subito HeyGen
          this.snippetTextMode = false;
          try {
            if (this.heygenVideo) {
              this.heygenVideo.pause?.();
              this.heygenVideo.muted = !this.snippetAudioOn;
            }
          } catch { }
        } else {
          this.snippetTextMode = true;

          // In modalità testo il video deve essere "spento": niente streaming/audio
          try {
            if (this.heygenVideo) {
              this.heygenVideo.muted = true;
              this.heygenVideo.pause?.();
            }
          } catch { }

          this.$nextTick &&
            this.$nextTick(() => {
              try {
                const input =
                  this.$el.querySelector &&
                  this.$el.querySelector("#henSnippetInput");
                if (input) input.focus();
              } catch { }
            });
        }
      } catch { }
      this.snippetMenuOpen = false;
    },

    toggleMessages() {
      this.snippetMessagesOn = !this.snippetMessagesOn;
      // Hook funzionale da definire in seguito (es. mostra/nascondi balloon di messaggi)
    },

    toggleAudio() {
      this.snippetAudioOn = !this.snippetAudioOn;
      try {
        if (this.heygenVideo) {
          // In modalità testuale il video resta sempre muto
          if (!this.snippetTextMode) {
            this.heygenVideo.muted = !this.snippetAudioOn;
          } else {
            this.heygenVideo.muted = true;
          }
        }
      } catch { }
    },

    closeSnippetTextMode() {
      try {
        this.snippetTextMode = false;
        // Quando si esce dalla modalità testo NON rilanciamo automaticamente HeyGen.
        // L'avatar video resta fermo e muto fino a quando non ci sarà una nuova risposta da leggere.
        try {
          if (this.heygenVideo) {
            this.heygenVideo.pause?.();
            this.heygenVideo.muted = true;
          }
        } catch { }
      } catch { }
    },

    sendSnippetInput() {
      try {
        const msg = (this.snippetInput || "").trim();
        if (!msg) return;
        const isSnippet = import.meta.env.VITE_IS_WEB_COMPONENT || false;
        if (isSnippet) {
          this.snippetMessages.push({ role: "user", content: msg });
        }
        this.startStream(msg);
        this.snippetInput = "";
      } catch { }
    },

    openSettings() {
      // apre il pannello impostazioni sopra l'avatar / chat
      this.snippetMenuOpen = false;
      this.showPrivacyPanel = false;
      this.showSettingsPanel = true;
    },

    openPrivacyPanel() {
      // apre il pannello con le informazioni sulla privacy
      this.snippetMenuOpen = false;
      this.showSettingsPanel = false;
      this.showPrivacyPanel = true;
    },

    openPrivacy() {
      try {
        const url = "/privacy-policy";
        window.open(url, "_blank");
      } catch { }
    },

    scrollSnippetMessagesToBottom() {
      try {
        const root = this.rootEl || this.$el || document;
        const container =
          root && root.querySelector
            ? root.querySelector("#henTextMessages")
            : document.getElementById("henTextMessages");
        if (container) {
          container.scrollTop = container.scrollHeight;
        }
      } catch { }
    },

    initComponent() {
      console.log("[EnjoyHen] initComponent() start");

      // Funzione per cercare elementi nel Shadow DOM o nel documento
      const $ = (id) => {
        if (this.rootEl && this.rootEl.querySelector) {
          console.log(`[EnjoyHen] Searching for #${id} in rootEl`);
          return this.rootEl.querySelector("#" + id);
        }
        console.log(`[EnjoyHen] Searching for #${id} in document`);
        return document.getElementById(id);
      };

      this.heygenVideo = $("heygenVideo");
      this.textInput = $("textInput");
      this.sendBtn = $("sendBtn");
      this.micBtn = $("micBtn");
      this.startChatBtn = $("startChatBtn");
      this.startChatContainer = $("startChatContainer");
      this.listeningBadge = $("listeningBadge");
      this.loadingOverlay = $("loadingOverlay");
      this.videoAvatarStatus = $("videoAvatarStatus");
      this.feedbackMsg = $("feedbackMsg");
      this.debugOverlay = $("debugOverlay");
      this.debugContent = $("debugContent");
      this.debugCloseBtn = $("debugClose");
      this.debugClearBtn = $("debugClear");
      this.debugCopyBtn = $("debugCopy");
      this.emailTranscriptBtnHen = $("emailTranscriptBtnHen");
      this.emailTranscriptModalHen = $("emailTranscriptModalHen");
      this.emailTranscriptInputHen = $("emailTranscriptInputHen");
      this.emailTranscriptStatusHen = $("emailTranscriptStatusHen");
      this.emailTranscriptCancelHen = $("sendTranscriptCancelHen");
      this.emailTranscriptCancel2Hen = $("sendTranscriptCancel2Hen");
      this.emailTranscriptConfirmHen = $("sendTranscriptConfirmHen");
      this.thinkingBadgeHen = $("thinkingBadgeHen");

      console.log("[EnjoyHen] initComponent() DOM elements found:", {
        heygenVideo: !!this.heygenVideo,
        textInput: !!this.textInput,
        sendBtn: !!this.sendBtn,
        micBtn: !!this.micBtn,
        startChatBtn: !!this.startChatBtn,
      });

      const urlParams = new URLSearchParams(window.location.search);
      this.uuid = urlParams.get("uuid");
      const teamSlug = this.teamSlug || window.location.pathname.split("/").pop();
      this.heygenAvatar = (urlParams.get("avatar") || "").trim();
      this.heygenVoice = (urlParams.get("voice") || "").trim();
      let debugEnabled = urlParams.get("debug") === "1";

      // Disabilita debug su mobile per default (causa frame drops)
      const ua = navigator.userAgent.toLowerCase();
      const isMobile = /android|iphone|ipad|ipod/i.test(ua);
      if (isMobile && !urlParams.get("debug")) {
        debugEnabled = false;
      }

      // Se debug disabilitato, disabilita tutti i console.log globalmente
      if (!debugEnabled) {
        console.log = () => { };
        console.warn = () => { };
        console.error = () => { };
        console.info = () => { };
      }

      console.log("[EnjoyHen] initComponent() params:", {
        uuid: this.uuid,
        teamSlug,
        heygenAvatar: this.heygenAvatar,
        heygenVoice: this.heygenVoice,
        props_heygenApiKey: this.heygenApiKey?.substring(0, 10) + "...",
        debugEnabled,
        isMobile,
      });

      // Inizializza debug overlay
      this.initDebugOverlay(debugEnabled);

      this.HEYGEN_CONFIG = {
        apiKey: this.heygenApiKey || document.getElementById("enjoyHeyRoot")?.dataset?.heygenApiKey || "",
        serverUrl: this.heygenServerUrl || document.getElementById("enjoyHeyRoot")?.dataset?.heygenServerUrl || "https://api.heygen.com",
      };

      console.log("[EnjoyHen] initComponent() HEYGEN_CONFIG:", {
        apiKey: this.HEYGEN_CONFIG.apiKey?.substring(0, 10) + "...",
        serverUrl: this.HEYGEN_CONFIG.serverUrl,
      });

      this.heygen = {
        sessionInfo: null,
        room: null,
        mediaStream: null,
        sessionToken: null,
        connecting: false,
        started: false,
      };

      this.teamSlugLocal = teamSlug;

      this.setupEventListeners();
      const isSnippet = import.meta.env.VITE_IS_WEB_COMPONENT || false;
      if (!isSnippet) {
        this.showStartChatButton();
      } else if (this.loadingOverlay) {
        this.loadingOverlay.classList.add("hidden");
      }
      console.log("[EnjoyHen] initComponent() complete");
    },

    openTranscriptModal() {
      try {
        if (this.emailTranscriptStatusHen) this.emailTranscriptStatusHen.textContent = "";
        if (this.emailTranscriptInputHen) this.emailTranscriptInputHen.value = "";
        if (this.emailTranscriptModalHen) this.emailTranscriptModalHen.classList.remove("hidden");
      } catch { }
    },

    closeTranscriptModal() {
      try {
        if (this.emailTranscriptModalHen) this.emailTranscriptModalHen.classList.add("hidden");
      } catch { }
    },

    async sendTranscriptEmail() {
      try {
        const email = (this.emailTranscriptInputHen?.value || "").trim();
        if (!email) {
          if (this.emailTranscriptStatusHen) this.emailTranscriptStatusHen.textContent = "Inserisci un'email valida.";
          return;
        }
        const tid = this.threadId;
        if (!tid) {
          if (this.emailTranscriptStatusHen) this.emailTranscriptStatusHen.textContent = "Nessun thread disponibile.";
          return;
        }
        if (this.emailTranscriptStatusHen) this.emailTranscriptStatusHen.textContent = "Invio in corso...";
        const res = await fetch("/api/chatbot/email-transcript", {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify({ email, thread_id: tid }),
        });
        const js = await res.json().catch(() => ({}));
        if (!res.ok || js.ok !== true) {
          if (this.emailTranscriptStatusHen)
            this.emailTranscriptStatusHen.textContent = js.error || "Errore nell'invio dell'email.";
          return;
        }
        if (this.emailTranscriptStatusHen) this.emailTranscriptStatusHen.textContent = "✓ Trascrizione inviata con successo.";
        setTimeout(() => this.closeTranscriptModal(), 900);
      } catch {
        if (this.emailTranscriptStatusHen) this.emailTranscriptStatusHen.textContent = "Errore imprevisto durante l'invio.";
      }
    },

    initDebugOverlay(debugEnabled) {
      if (!debugEnabled) return;

      const originalConsole = {
        log: console.log,
        warn: console.warn,
        error: console.error,
        info: console.info,
      };

      const formatForLog = (arg) => {
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
      };

      const appendDebugLine = (type, args) => {
        if (!this.debugContent) return;
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
        this.debugContent.appendChild(line);
        try {
          const max = 400;
          while (this.debugContent.childNodes.length > max)
            this.debugContent.removeChild(this.debugContent.firstChild);
        } catch { }
        try {
          this.debugContent.parentElement.scrollTop =
            this.debugContent.parentElement.scrollHeight;
        } catch { }
      };

      try {
        if (this.debugOverlay) {
          this.debugOverlay.classList.remove("hidden");
        }
      } catch { }

      try {
        const add = (el, evt, fn) => {
          try {
            el && el.addEventListener(evt, fn);
          } catch { }
        };
        add(this.debugCloseBtn, "click", () => {
          if (this.debugOverlay) this.debugOverlay.classList.add("hidden");
        });
        add(this.debugClearBtn, "click", () => {
          if (this.debugContent) this.debugContent.innerHTML = "";
        });
        add(this.debugCopyBtn, "click", async () => {
          try {
            const lines = Array.from(this.debugContent?.children || []).map(
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
          locale: this.locale,
        },
      ]);
    },

    normalizeLangTag(lang, fallback) {
      try {
        const t = (lang || "").replace("_", "-").trim();
        if (!t) return fallback;
        const parts = t.split("-");
        if (parts.length === 1) return parts[0].toLowerCase();
        return parts[0].toLowerCase() + "-" + parts[1].toUpperCase();
      } catch {
        return fallback;
      }
    },

    setupEventListeners() {
      console.log("[EnjoyHen] setupEventListeners()");
      if (this.sendBtn) {
        this.sendBtn.addEventListener("click", () => {
          console.log("[EnjoyHen] SEND button clicked");
          this.onSend();
        });
      }
      if (this.textInput) {
        this.textInput.addEventListener("keypress", (e) => {
          if (e.key === "Enter") {
            console.log("[EnjoyHen] ENTER key pressed");
            this.onSend();
          }
        });
      }
      if (this.micBtn) {
        this.micBtn.addEventListener("click", () => {
          console.log("[EnjoyHen] MIC button clicked");
          this.onMicClick();
        });
      }
      if (this.startChatBtn) {
        this.startChatBtn.addEventListener("click", () => {
          console.log("[EnjoyHen] START CHAT button clicked");
          this.startChat();
        });
      }
      if (this.emailTranscriptBtnHen) {
        this.emailTranscriptBtnHen.addEventListener("click", () => this.openTranscriptModal());
      }
      if (this.emailTranscriptCancelHen) {
        this.emailTranscriptCancelHen.addEventListener("click", () => this.closeTranscriptModal());
      }
      if (this.emailTranscriptCancel2Hen) {
        this.emailTranscriptCancel2Hen.addEventListener("click", () => this.closeTranscriptModal());
      }
      if (this.emailTranscriptConfirmHen) {
        this.emailTranscriptConfirmHen.addEventListener("click", () => this.sendTranscriptEmail());
      }
    },

    showStartChatButton() {
      console.log("[EnjoyHen] showStartChatButton()");
      if (this.startChatContainer) {
        this.startChatContainer.classList.remove("hidden");
      }
      if (this.loadingOverlay) {
        this.loadingOverlay.classList.add("hidden");
      }
    },

    startChat() {
      console.log("[EnjoyHen] startChat()");
      if (this.startChatContainer) {
        this.startChatContainer.classList.add("hidden");
      }
      this.ensureHeyGenSession();
    },

    async onMicClick() {
      if (this.isListening && this.recognition) {
        try {
          this.recognition.stop();
          this.recognition.abort?.();
        } catch { }
        this.isListening = false;
        this.setListeningUI(false);
        console.log("MIC: listening stopped by user");
        return;
      }

      try {
        if (!this.audioCtx) {
          this.audioCtx = new (window.AudioContext || window.webkitAudioContext)();
        }
        if (this.audioCtx.state === "suspended") {
          await this.audioCtx.resume();
        }
      } catch (e) {
        console.warn("AUDIO: failed to init/resume", e);
      }

      const ok = await this.ensureMicPermission();
      if (!ok) {
        alert("Permesso microfono negato. Abilitalo nelle impostazioni del browser.");
        return;
      }

      try {
        const Rec = window.SpeechRecognition || window.webkitSpeechRecognition;
        if (!Rec) throw new Error("Web Speech API non disponibile");

        this.recognition = new Rec();

        // Rilevamento lingua robusto (come in EnjoyTalk3D.vue)
        const urlParams = new URLSearchParams(window.location.search);
        const urlLang = (urlParams.get("lang") || "").trim();
        const navLang = (
          navigator.language ||
          (navigator.languages && navigator.languages[0]) ||
          ""
        ).trim();

        const rawLang = urlLang || this.locale || navLang || "it-IT";

        let recLang = this.normalizeLangTag(rawLang, "it-IT");
        try {
          if (/^it(\b|[-_])/i.test(rawLang) || rawLang.toLowerCase() === "it")
            recLang = "it-IT";
        } catch { }

        this.recognition.lang = recLang;

        console.log("SPEECH: Language detection", {
          urlLang,
          navLang,
          propLocale: this.locale,
          rawLang,
          finalLang: recLang,
          userAgent: navigator.userAgent.substring(0, 50)
        });

        this.recognition.interimResults = false;
        this.recognition.continuous = false;
        this.recognition.maxAlternatives = 1;

        this.recognition.onstart = async () => {
          this.isListening = true;
          this.setListeningUI(true);
          console.log("SPEECH: onstart");
        };

        this.recognition.onspeechstart = () => {
          console.log("SPEECH: onspeechstart");
        };

        this.recognition.onspeechend = () => {
          console.log("SPEECH: onspeechend");
        };

        this.recognition.onerror = async (e) => {
          console.error("SPEECH: onerror", (e && (e.error || e.message)) || e);
          this.isListening = false;
          this.setListeningUI(false);
        };

        this.recognition.onend = async () => {
          this.isListening = false;
          this.setListeningUI(false);
          console.log("SPEECH: onend");
        };

        this.recognition.onresult = (event) => {
          try {
            const res = event.results;
            const last = res[res.length - 1];
            const transcript = last[0].transcript;
            const isFinal = last.isFinal === true || !this.recognition.interimResults;

            console.log("SPEECH: onresult", { transcript, isFinal });

            if (isFinal) {
              this.isListening = false;
              this.setListeningUI(false);

              const safe = (transcript || "").trim();
              if (!safe) {
                console.warn("SPEECH: final transcript empty, not starting stream");
                return;
              }

              this.startStream(safe);
            }
          } catch (err) {
            console.error("SPEECH: onresult handler failed", err);
          }
        };

        console.log("SPEECH: start()", { lang: this.recognition.lang });
        this.recognition.start();
      } catch (err) {
        console.warn("Riconoscimento vocale non disponibile o errore", err);
        alert("Riconoscimento vocale non disponibile in questo browser.");
      }
    },

    async ensureMicPermission() {
      try {
        if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) return true;

        console.log("MIC: requesting permission");
        const stream = await navigator.mediaDevices.getUserMedia({
          audio: { echoCancellation: true },
        });

        try {
          stream.getTracks().forEach((t) => t.stop());
        } catch { }

        console.log("MIC: permission OK");
        return true;
      } catch (e) {
        console.warn("Mic permission denied or error", e);
        return false;
      }
    },

    setListeningUI(active) {
      if (active) {
        if (this.listeningBadge) {
          this.listeningBadge.classList.remove("hidden");
        }
        if (this.micBtn) {
          this.micBtn.classList.remove("bg-rose-600");
          this.micBtn.classList.add(
            "bg-emerald-600",
            "ring-2",
            "ring-emerald-400",
            "animate-pulse"
          );
        }
      } else {
        if (this.listeningBadge) {
          this.listeningBadge.classList.add("hidden");
        }
        if (this.micBtn) {
          this.micBtn.classList.add("bg-rose-600");
          this.micBtn.classList.remove(
            "bg-emerald-600",
            "ring-2",
            "ring-emerald-400",
            "animate-pulse"
          );
        }
      }
    },

    async onSend() {
      console.log("[EnjoyHen] onSend() called");
      const message = (this.textInput?.value || "").trim();
      console.log("[EnjoyHen] onSend() message:", message);
      if (!message) {
        console.log("[EnjoyHen] onSend() empty message, returning");
        return;
      }

      this.textInput.value = "";
      this.textInput.disabled = true;
      this.sendBtn.disabled = true;
      this.setFeedback("Invio in corso...");

      try {
        console.log("[EnjoyHen] onSend() starting stream");
        await this.startStream(message);
      } catch (e) {
        console.error("Error starting stream:", e);
        this.setFeedback("❌ Errore nella comunicazione");
      } finally {
        this.textInput.disabled = false;
        this.sendBtn.disabled = false;
      }
    },

    async startStream(message) {
      console.log("[EnjoyHen] startStream() start", { message });
      if (!message || message.trim() === "") {
        console.warn("[EnjoyHen] startStream() empty message, returning");
        return;
      }

      const isSnippet = import.meta.env.VITE_IS_WEB_COMPONENT || false;

      const params = new URLSearchParams({
        message,
        team: this.teamSlugLocal,
        uuid: this.uuid || "",
        locale: this.locale,
        ts: String(Date.now()),
      });

      if (this.threadId) params.set("thread_id", this.threadId);

      const webComponentOrigin = window.__ENJOY_HEN_ORIGIN__ || window.location.origin;
      const endpoint = `/api/chatbot/neuron-website-stream?${params.toString()}`;

      console.log("[EnjoyHen] startStream() endpoint:", {
        webComponentOrigin,
        endpoint,
        fullUrl: `${webComponentOrigin}${endpoint}`,
      });

      try {
        const thinkingBubble = document.getElementById("thinkingBubble");
        const thinkingBadge = this.thinkingBadgeHen || document.getElementById("thinkingBadgeHen");
        if (thinkingBubble) {
          thinkingBubble.classList.remove("hidden");
        }
        if (thinkingBadge) {
          thinkingBadge.classList.remove("hidden");
        }

        console.log("[EnjoyHen] startStream() creating EventSource");
        const eventSource = new EventSource(`${webComponentOrigin}${endpoint}`);

        let collected = "";
        let firstToken = true;
        this.setFeedback("Avatar sta rispondere...");

        eventSource.addEventListener("message", (e) => {
          console.log("[EnjoyHen] EventSource message received", { data: e.data?.substring(0, 100) });
          try {
            const data = JSON.parse(e.data);
            if (data.token) {
              if (firstToken) {
                firstToken = false;
                console.log("[EnjoyHen] First token received, hiding thinking bubble");
                if (thinkingBubble) {
                  thinkingBubble.classList.add("hidden");
                }
              }
              try {
                const tok = JSON.parse(data.token);
                if (tok && tok.thread_id) {
                  this.threadId = tok.thread_id;
                  console.log("[EnjoyHen] threadId updated:", tok.thread_id);
                  return;
                }
              } catch { }
              collected += data.token;
              console.log("[EnjoyHen] token collected, total length:", collected.length);
            }
          } catch (err) {
            console.warn("Parse error:", err);
          }
        });

        eventSource.addEventListener("done", () => {
          console.log("[EnjoyHen] EventSource done event", { collected: collected.substring(0, 100) });
          try {
            eventSource.close();
          } catch { }
          if (thinkingBubble) {
            thinkingBubble.classList.add("hidden");
          }
          if (thinkingBadge) {
            thinkingBadge.classList.add("hidden");
          }

          const text = this.stripHtml(collected).trim();
          console.log("[EnjoyHen] Processed text:", { text: text.substring(0, 100) });
          if (text) {
            // In modalità testo snippet: SOLO chat testuale, niente speech HeyGen
            if (!isSnippet || !this.snippetTextMode) {
              console.log("[EnjoyHen] sending to heygenSendRepeat");
              this.heygenSendRepeat(text);
            }
            if (isSnippet) {
              this.snippetMessages.push({ role: "assistant", content: text });
              if (this.snippetTextMode && this.$nextTick) {
                this.$nextTick(() => {
                  this.scrollSnippetMessagesToBottom();
                });
              }
            }
          }
          this.setFeedback("");
        });

        eventSource.addEventListener("error", () => {
          console.error("[EnjoyHen] EventSource error");
          try {
            eventSource.close();
          } catch { }
          if (thinkingBubble) {
            thinkingBubble.classList.add("hidden");
          }
          if (thinkingBadge) {
            thinkingBadge.classList.add("hidden");
          }
          this.setFeedback("❌ Errore di connessione");
        });
      } catch (e) {
        console.error("[EnjoyHen] startStream() error:", e);
        this.setFeedback("❌ Errore dello stream");
      }
    },

    async ensureHeyGenSession() {
      if (this.heygen.started || this.heygen.connecting) return;
      this.heygen.connecting = true;

      try {
        if (!this.HEYGEN_CONFIG.apiKey || !this.HEYGEN_CONFIG.serverUrl) {
          this.setStatus("Config mancante");
          throw new Error("HEYGEN config missing");
        }

        this.setStatus("Richiesta token...");

        // Get token
        const tokRes = await fetch(
          `${this.HEYGEN_CONFIG.serverUrl}/v1/streaming.create_token`,
          {
            method: "POST",
            headers: {
              "Content-Type": "application/json",
              "X-Api-Key": this.HEYGEN_CONFIG.apiKey,
            },
          }
        );

        const tokJson = await tokRes.json();
        this.heygen.sessionToken = tokJson?.data?.token;
        if (!this.heygen.sessionToken) throw new Error("No session token");

        this.setStatus("Token OK");

        // Create session
        const body = {
          quality: "high",
          version: "v2",
          video_encoding: "H264",
        };
        if (this.heygenAvatar) body.avatar_name = this.heygenAvatar;
        if (this.heygenVoice) body.voice = { voice_id: this.heygenVoice, rate: 1.0 };

        const newRes = await fetch(
          `${this.HEYGEN_CONFIG.serverUrl}/v1/streaming.new`,
          {
            method: "POST",
            headers: {
              "Content-Type": "application/json",
              Authorization: `Bearer ${this.heygen.sessionToken}`,
            },
            body: JSON.stringify(body),
          }
        );

        const newJson = await newRes.json();
        this.heygen.sessionInfo = newJson?.data;
        if (!this.heygen.sessionInfo?.session_id) throw new Error("No session info");

        this.setStatus("Connessione video...");

        // Setup LiveKit room
        if (!window.LivekitClient) {
          await this.loadLiveKit();
        }

        this.heygen.room = new window.LivekitClient.Room({
          adaptiveStream: false,
          dynacast: true,
          videoCaptureDefaults: {
            resolution: window.LivekitClient.VideoPresets.h720.resolution,
          },
        });

        this.heygen.mediaStream = new MediaStream();

        this.heygen.room.on(
          window.LivekitClient.RoomEvent.TrackSubscribed,
          async (track) => {
            try {
              if (track.kind === "video") {
                this.heygen.mediaStream.addTrack(track.mediaStreamTrack);
                if (this.heygenVideo) {
                  this.heygenVideo.srcObject = this.heygen.mediaStream;
                  await this.heygenVideo.play().catch(() => { });
                }
                this.setStatus("Video connesso");
              }
              if (track.kind === "audio") {
                this.heygen.mediaStream.addTrack(track.mediaStreamTrack);
                this.setStatus("Audio connesso");
              }
            } catch (e) {
              console.warn("Track subscription error:", e);
            }
          }
        );

        this.heygen.room.on(
          window.LivekitClient.RoomEvent.TrackUnsubscribed,
          (track) => {
            const mt = track.mediaStreamTrack;
            if (mt) this.heygen.mediaStream.removeTrack(mt);
          }
        );

        await this.heygen.room.prepareConnection(
          this.heygen.sessionInfo.url,
          this.heygen.sessionInfo.access_token
        );

        await fetch(`${this.HEYGEN_CONFIG.serverUrl}/v1/streaming.start`, {
          method: "POST",
          headers: {
            "Content-Type": "application/json",
            Authorization: `Bearer ${this.heygen.sessionToken}`,
          },
          body: JSON.stringify({ session_id: this.heygen.sessionInfo.session_id }),
        });

        await this.heygen.room.connect(
          this.heygen.sessionInfo.url,
          this.heygen.sessionInfo.access_token
        );

        if (this.heygenVideo?.srcObject && this.heygenVideo.paused) {
          await this.heygenVideo.play();
        }

        this.heygen.started = true;
        this.setStatus("Connesso");
        this.loadingOverlay.classList.add("hidden");
      } catch (e) {
        console.error("HeyGen session error:", e);
        this.setStatus("Errore connessione");
      } finally {
        this.heygen.connecting = false;
      }
    },

    async heygenSendRepeat(text) {
      try {
        await this.ensureHeyGenSession();
        if (!this.heygen.sessionInfo?.session_id) return;

        await fetch(`${this.HEYGEN_CONFIG.serverUrl}/v1/streaming.task`, {
          method: "POST",
          headers: {
            "Content-Type": "application/json",
            Authorization: `Bearer ${this.heygen.sessionToken}`,
          },
          body: JSON.stringify({
            session_id: this.heygen.sessionInfo.session_id,
            text,
            task_type: "repeat",
          }),
        });
      } catch (e) {
        console.error("HeyGen repeat failed:", e);
      }
    },

    async cleanup() {
      try {
        if (this.heygen.sessionInfo?.session_id) {
          await fetch(`${this.HEYGEN_CONFIG.serverUrl}/v1/streaming.stop`, {
            method: "POST",
            headers: {
              "Content-Type": "application/json",
              Authorization: `Bearer ${this.heygen.sessionToken}`,
            },
            body: JSON.stringify({
              session_id: this.heygen.sessionInfo.session_id,
            }),
          });
        }
      } catch { }

      try {
        if (this.heygen.room) this.heygen.room.disconnect();
      } catch { }

      if (this.heygenVideo) {
        try {
          this.heygenVideo.pause();
        } catch { }
        this.heygenVideo.srcObject = null;
      }
    },

    loadLiveKit() {
      return new Promise((resolve) => {
        if (window.LivekitClient) return resolve();
        const s = document.createElement("script");
        s.src = "https://cdn.jsdelivr.net/npm/livekit-client/dist/livekit-client.umd.min.js";
        s.async = true;
        s.onload = () => resolve();
        s.onerror = () => resolve();
        document.head.appendChild(s);
      });
    },

    stripHtml(html) {
      const tmp = document.createElement("div");
      tmp.innerHTML = html;
      return (tmp.textContent || tmp.innerText || "").replace(/\s+/g, " ").trim();
    },

    setStatus(status) {
      if (this.videoAvatarStatus) {
        this.videoAvatarStatus.textContent = status;
      }
    },

    setFeedback(msg) {
      if (this.feedbackMsg) {
        this.feedbackMsg.textContent = msg;
      }
    },
  },
  setup() {
    const rootElRef = ref(null);
    const isWebComponent = import.meta.env.VITE_IS_WEB_COMPONENT || false;

    return {
      isWebComponent,
      rootElRef,
    };
  },
});
</script>

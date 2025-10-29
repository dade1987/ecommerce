<template>
  <div ref="rootEl" id="enjoyHenRoot"
    :class="['flex flex-col', !isWebComponent && 'min-h-[100dvh] max-h-[100dvh]', 'w-full bg-[#0f172a] overflow-hidden']">
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
      <div class="w-full max-w-2xl">
        <!-- Video Avatar -->
        <div class="relative mb-6 rounded-lg overflow-hidden bg-black border border-slate-700">
          <video id="heygenVideo" class="w-full h-auto rounded-lg" autoplay playsinline controls>
          </video>

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
                <div class="absolute inset-0 bg-gradient-to-r from-indigo-600 to-emerald-600 rounded-full animate-spin"
                  style="border-radius: 50%; -webkit-mask-image: radial-gradient(circle 10px at center, transparent 100%, black 100%); mask-image: radial-gradient(circle 10px at center, transparent 100%, black 100%);">
                </div>
                <div class="absolute inset-2 bg-black rounded-full flex items-center justify-center">
                  <div class="w-2 h-2 bg-gradient-to-r from-indigo-400 to-emerald-400 rounded-full animate-pulse"></div>
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
        </div>

        <!-- Listening Badge -->
        <div id="listeningBadge"
          class="hidden px-3 py-2 bg-rose-600/90 text-white text-sm font-semibold rounded-md shadow animate-pulse text-center mb-4">
          🎤 Ascolto...
        </div>
      </div>
    </div>

    <!-- Input Controls Bar -->
    <div id="controlsBar"
      class="bottom-0 left-0 w-full border-t border-slate-700 bg-[#0f172a] z-20 pb-[env(safe-area-inset-bottom)]">
      <div class="px-3 py-3 sm:px-4 sm:py-4">
        <div class="mx-auto w-full max-w-2xl">
          <div class="flex flex-wrap w-full gap-2 items-center min-w-0">
            <input id="textInput" type="text" placeholder="Scrivi il tuo messaggio..."
              class="flex-1 min-w-0 px-3 py-3 bg-slate-700 text-white border border-slate-600 rounded-lg placeholder-slate-400 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 text-sm sm:text-base" />
            <button id="sendBtn"
              class="px-3 py-3 sm:px-4 sm:py-3 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg transition-colors whitespace-nowrap text-sm sm:text-base font-medium">
              📤 Invia
            </button>
            <button id="micBtn"
              class="px-3 py-3 sm:px-4 sm:py-3 bg-rose-600 hover:bg-rose-700 text-white rounded-lg transition-colors whitespace-nowrap text-sm sm:text-base font-medium">
              🎤 Parla
            </button>
          </div>

          <!-- Feedback -->
          <div id="feedbackMsg" class="text-sm text-slate-400 text-center min-h-5 mt-2"></div>
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

      console.log("[EnjoyHen] initComponent() params:", {
        uuid: this.uuid,
        teamSlug,
        heygenAvatar: this.heygenAvatar,
        heygenVoice: this.heygenVoice,
        props_heygenApiKey: this.heygenApiKey?.substring(0, 10) + "...",
      });

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
      this.showStartChatButton();
      console.log("[EnjoyHen] initComponent() complete");
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
        this.recognition.lang = this.locale || "it-IT";
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
        if (thinkingBubble) {
          thinkingBubble.classList.remove("hidden");
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

          const text = this.stripHtml(collected).trim();
          console.log("[EnjoyHen] Processed text:", { text: text.substring(0, 100) });
          if (text) {
            console.log("[EnjoyHen] sending to heygenSendRepeat");
            this.heygenSendRepeat(text);
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

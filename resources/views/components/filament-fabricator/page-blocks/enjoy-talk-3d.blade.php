<div id="enjoyTalkRoot" class="flex flex-col min-h-[100dvh] w-full bg-[#0f172a] pb-[96px] sm:pb-0" data-heygen-api-key="{{ config('services.heygen.api_key') }}" data-heygen-server-url="{{ config('services.heygen.server_url') }}">
  <div class="px-4 py-4">
    <div class="mx-auto w-full max-w-[520px] flex items-center gap-3">
      <img id="teamLogo" src="/images/logoai.jpeg" alt="EnjoyTalk 3D" class="w-10 h-10 rounded-full object-cover border border-slate-600">
      <h1 class="font-sans text-2xl text-white">EnjoyTalk 3D</h1>
    </div>
  </div>

  <!-- Canvas Avatar 3D -->
  <div class="flex-1 flex items-center justify-center p-4">
    <div class="relative w-full">
      <div class="mx-auto w-full max-w-[520px] px-3 sm:px-0">
        <div id="avatarStage" class="bg-[#111827] border border-slate-700 rounded-md overflow-hidden w-full h-auto max-h-[calc(100dvh-220px)] aspect-[3/4]"></div>
        <video id="heygenVideo" class="hidden w-full h-auto max-h-[calc(100dvh-220px)] rounded-md border border-slate-700 bg-black" autoplay playsinline></video>
        <audio id="heygenAudio" class="hidden" autoplay></audio>
      </div>

      <!-- Fumetto di pensiero -->
      <div id="thinkingBubble" class="hidden absolute top-4 left-1/2 transform -translate-x-1/2 bg-white rounded-lg px-4 py-2 shadow-lg border border-gray-300">
        <div class="text-gray-700 text-sm font-medium">💭 Sto pensando...</div>
        <div class="absolute bottom-0 left-1/2 transform translate-y-full -translate-x-1/2">
          <div class="w-0 h-0 border-l-4 border-r-4 border-t-4 border-transparent border-t-white"></div>
        </div>
      </div>
      <!-- Badge ascolto microfono -->
      <div id="listeningBadge" class="hidden absolute top-4 right-4 bg-rose-600/90 text-white text-xs font-semibold px-2.5 py-1 rounded-md shadow animate-pulse">
        🎤 Ascolto...
      </div>
      <!-- Debug Overlay (mostrato con ?debug=1) -->
      <div id="debugOverlay" class="hidden absolute left-1/2 -translate-x-1/2 top-3 z-10 w-full max-w-[520px] px-3 sm:px-0"
           style="pointer-events:auto;">
        <div class="bg-black/70 backdrop-blur-sm border border-slate-600 rounded-md overflow-hidden shadow-lg">
          <div class="flex items-center justify-between px-3 py-2 border-b border-slate-700 bg-black/60 sticky top-0">
            <div class="text-slate-200 text-xs font-semibold">Debug</div>
            <div class="flex items-center gap-2">
              <button id="debugCopy" class="text-[11px] px-2 py-1 bg-slate-700/70 hover:bg-slate-600 text-white rounded">Copia</button>
              <button id="debugClear" class="text-[11px] px-2 py-1 bg-slate-700/70 hover:bg-slate-600 text-white rounded">Pulisci</button>
              <button id="debugClose" class="text-[11px] px-2 py-1 bg-slate-700/70 hover:bg-slate-600 text-white rounded">Chiudi</button>
            </div>
          </div>
          <div class="max-h-[50vh] sm:max-h-[60vh] overflow-auto p-2 text-[11px] font-mono text-slate-200 leading-relaxed"
               style="margin-bottom: calc(var(--controls-pad, 0px));">
            <div id="debugContent" class="space-y-1"></div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Controlli -->
  <div id="controlsBar" class="fixed bottom-0 left-0 w-full border-t border-slate-700 bg-[#0f172a] z-20 pb-[env(safe-area-inset-bottom)]">
    <div class="px-3 py-3 sm:px-4 sm:py-4">
      <div class="mx-auto w-full max-w-[520px] px-3 sm:px-0">
        <div class="flex flex-wrap w-full gap-2 items-center min-w-0">
          <input id="textInput" type="text" placeholder="Scrivi la domanda..." class="flex-1 min-w-0 px-3 py-3 bg-[#111827] text-white border border-slate-700 rounded-md placeholder-slate-400 focus:border-indigo-500 focus:outline-none text-[15px] sm:text-base" />
          <button id="sendBtn" class="px-3 py-3 sm:px-4 sm:py-3 bg-indigo-600 hover:bg-indigo-700 text-white rounded-md transition-colors whitespace-nowrap text-sm sm:text-base">📤 Invia</button>
          <button id="micBtn" class="px-3 py-3 sm:px-4 sm:py-3 bg-rose-600 hover:bg-rose-700 text-white rounded-md transition-colors whitespace-nowrap text-sm sm:text-base">🎤 Parla</button>
        </div>
        <div class="mt-2 flex items-center gap-3 text-slate-300 text-xs sm:text-sm">
          <label class="inline-flex items-center gap-2 cursor-pointer select-none">
            <input id="useVideoAvatar" type="checkbox" class="accent-indigo-600" />
            <span>Video Avatar</span>
          </label>
          <label class="inline-flex items-center gap-2 cursor-pointer select-none">
            <input id="useBrowserTts" type="checkbox" class="accent-indigo-600" checked />
            <span>Usa TTS del browser (italiano)</span>
          </label>
          <span id="browserTtsStatus" class="opacity-70"></span>
          <span id="videoAvatarStatus" class="opacity-70"></span>
          <label class="inline-flex items-center gap-2 cursor-pointer select-none ml-auto">
            <input id="useAdvancedLipsync" type="checkbox" class="accent-emerald-600" />
            <span>LipSync avanzato (WebAudio)</span>
          </label>
        </div>
        <div id="liveText" class="hidden mt-3 text-slate-300 min-h-[1.5rem]"></div>
      </div>
    </div>
  </div>
  <audio id="ttsPlayer" class="hidden" playsinline></audio>
</div>

<script type="module">
// Precarica Three r160 + GLTFLoader come ES Modules e risolvi prima di eseguire il resto dello script
window.THREE_READY = (async () => {
  try {
    const THREE_mod = await import('https://esm.sh/three@0.160.0');
      const {
        GLTFLoader
      } = await import('https://esm.sh/three@0.160.0/examples/jsm/loaders/GLTFLoader.js');
      const {
        FBXLoader
      } = await import('https://esm.sh/three@0.160.0/examples/jsm/loaders/FBXLoader.js');
      const {
        OrbitControls
      } = await import('https://esm.sh/three@0.160.0/examples/jsm/controls/OrbitControls.js');
    window.THREE = THREE_mod;
    window.GLTFLoader = GLTFLoader;
      window.FBXLoader = FBXLoader;
      window.OrbitControls = OrbitControls;
    console.log('Three+GLTFLoader via esm.sh');
    return true;
  } catch (e) {
    try {
      const THREE_mod = await import('https://unpkg.com/three@0.160.0/build/three.module.js');
        const {
          GLTFLoader
        } = await import('https://unpkg.com/three@0.160.0/examples/jsm/loaders/GLTFLoader.js?module');
        const {
          FBXLoader
        } = await import('https://unpkg.com/three@0.160.0/examples/jsm/loaders/FBXLoader.js?module');
        const {
          OrbitControls
        } = await import('https://unpkg.com/three@0.160.0/examples/jsm/controls/OrbitControls.js?module');
      window.THREE = THREE_mod;
      window.GLTFLoader = GLTFLoader;
        window.FBXLoader = FBXLoader;
        window.OrbitControls = OrbitControls;
      console.log('Three+GLTFLoader via unpkg (?module)');
      return true;
    } catch (e2) {
      console.error('Impossibile importare Three/GLTFLoader come ES modules', e2);
      return false;
    }
  }
})();
</script>

<!-- Rimosso Laravel Echo - usa solo streaming SSE nativo -->

<script src="https://cdn.jsdelivr.net/npm/livekit-client/dist/livekit-client.umd.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', async function() {
  // Attendi il preload dei moduli
    try {
      if (window.THREE_READY && window.THREE_READY.then) {
        await Promise.race([window.THREE_READY, new Promise(r => setTimeout(r, 3000))]);
      }
    } catch {}
  const sendBtn = document.getElementById('sendBtn');
  const micBtn = document.getElementById('micBtn');
  const input = document.getElementById('textInput');
  const liveText = document.getElementById('liveText');
  const ttsPlayer = document.getElementById('ttsPlayer');
  const thinkingBubble = document.getElementById('thinkingBubble');
  const useBrowserTts = document.getElementById('useBrowserTts');
  const useVideoAvatar = document.getElementById('useVideoAvatar');
  const browserTtsStatus = document.getElementById('browserTtsStatus');
  const videoAvatarStatus = document.getElementById('videoAvatarStatus');
  const useAdvancedLipsync = document.getElementById('useAdvancedLipsync');
  const heygenVideo = document.getElementById('heygenVideo');
  const heygenAudio = document.getElementById('heygenAudio');
  const teamSlug = window.location.pathname.split('/').pop();
  const urlParams = new URLSearchParams(window.location.search);
  const uuid = urlParams.get('uuid');
  const locale = '{{ app()->getLocale() }}';
  const debugEnabled = urlParams.get('debug') === '1';
  const rootEl = document.getElementById('enjoyTalkRoot');
  const HEYGEN_CONFIG = {
    apiKey: (rootEl?.dataset?.heygenApiKey) || '',
    serverUrl: (rootEl?.dataset?.heygenServerUrl) || 'https://api.heygen.com'
  };
  const heygenAvatar = (urlParams.get('avatar') || '').trim();
  const heygenVoice = (urlParams.get('voice') || '').trim();
  const ua = navigator.userAgent || '';
  const isAndroid = /Android/i.test(ua);
  const isChrome = !!window.chrome && /Chrome\/\d+/.test(ua) && !/Edg\//.test(ua) && !/OPR\//.test(ua) && !/Brave/i.test(ua);
  const urlLang = (urlParams.get('lang') || '').trim();
    // Config opzionale per asse e segno della mandibola (rig senza morph)
    const jawAxisParam = (urlParams.get('jaw_axis') || '').toLowerCase();
    const jawAxis = (jawAxisParam === 'y' || jawAxisParam === 'z') ? jawAxisParam : 'x';
    const jawSign = (urlParams.get('jaw_sign') || '-1') === '1' ? 1 : -1;
    // Config opzionale per nodding testa quando la mandibola non deforma
    const headSign = (urlParams.get('head_sign') || '-1') === '1' ? 1 : -1;
    const headNodForced = (urlParams.get('head_nod') || '').toLowerCase() === '1';
    // Config opzionale per inquadratura testa
    const headDistParam = parseFloat(urlParams.get('head_dist') || '');
    const headFovParam = parseFloat(urlParams.get('head_fov') || '');

  function normalizeLangTag(tag, fallback) {
    try {
        const t = (tag || '').replace('_', '-').trim();
      if (!t) return fallback;
      // Normalizza in BCP47 semplice (xx-YY)
      const parts = t.split('-');
      if (parts.length === 1) return parts[0].toLowerCase();
      return parts[0].toLowerCase() + '-' + parts[1].toUpperCase();
      } catch {
        return fallback;
      }
  }
  const navLang = (navigator.language || (navigator.languages && navigator.languages[0]) || '').trim();
  const rawLang = (urlLang || locale || navLang || 'it-IT');
  let recLang = normalizeLangTag(rawLang, 'it-IT');
  // Forza italiano se il tag in ingresso indica italiano (alcuni device rifiutano 'it')
    try {
      if (/^it(\b|[-_])/i.test(rawLang) || rawLang.toLowerCase() === 'it') recLang = 'it-IT';
    } catch {}
  let threadId = null;
  let assistantThreadId = null;
    let humanoid = null,
      jawBone = null,
      headBone = null,
      mouthLBone = null,
      mouthRBone = null;
    let jawBoneHasInfluence = false; // true se ruotare il jaw cambia la mesh
    let morphMesh = null,
      morphIndex = -1,
      morphValue = 0;
  // Viseme support (browser TTS only): indices for relevant morph targets
    const visemeIndices = {
      jawOpen: -1,
      mouthFunnel: -1,
      mouthPucker: -1,
      mouthSmileL: -1,
      mouthSmileR: -1,
      mouthClose: -1
    };
  let visemeActiveUntil = 0;
  let visemeStrength = 0; // 0..1 current blend magnitude
  // Stato precedente per smoothing e deadband
    let lastVisemes = {
      jawOpen: 0,
      mouthFunnel: 0,
      mouthPucker: 0,
      mouthSmileL: 0,
      mouthSmileR: 0,
      mouthClose: 0
    };
    const deadband = {
      jawOpen: 0.02,
      mouthFunnel: 0.02,
      mouthPucker: 0.02,
      mouthSmileL: 0.02,
      mouthSmileR: 0.02,
      mouthClose: 0.02
    };
    let visemeTargets = {
      jawOpen: 0,
      mouthFunnel: 0,
      mouthPucker: 0,
      mouthSmileL: 0,
      mouthSmileR: 0,
      mouthClose: 0
    };
  let visemeMeshes = [];
  // Scheduler visemi testuali (DISABILITATO: usiamo lipsync da audio)
  let visemeSchedule = [];
  const textVisemeEnabled = true;
  let cloudAudioSpeaking = false;

  // Stato ampiezza audio con envelope per evitare bocca troppo aperta e jitter
  let audioAmp = 0; // 0..1
  const AMP_ATTACK = 0.25; // risposta a salire (più alto = più reattivo)
  const AMP_RELEASE = 0.08; // risposta a scendere (più basso = rilascio lento)
  // Blink/eyes morph indices (opzionali)
    const eyeIndices = {
      eyeBlinkLeft: -1,
      eyeBlinkRight: -1
    };
  let eyeMesh = null;
  let nextBlinkAt = performance.now() + 1200 + Math.random() * 2000;
  let blinkPhase = 0; // 0..1 (chiusura-apertura)
    // Forza chiusura completa (ignora minLipSeparation) per breve tempo (ms)
    let forceFullCloseUntil = 0;
    let syllablePulseUntil = 0; // finestra di chiusura corrente
    let nextSyllablePulseAt = 0; // debouncing
  // Config lipsync: separazione minima e limiti di chiusura
  const lipConfig = {
      restJawOpen: 0.12, // apertura a riposo
      minLipSeparation: 0.07, // separazione minima obbligatoria
      maxMouthClose: 0.35, // limite massimo di chiusura
      closeThresholdForSeparation: 0.2, // oltre questa chiusura, forza separazione minima
      visemeStrengthAlpha: 0.15, // velocità salita visemi (più basso = più lento)
      morphSmoothingBeta: 0.16, // smoothing dei morph target (più basso = più lento)
      jawSmoothingAlpha: 0.12, // smoothing per bone/geom jaw (più basso = più lento)
    };

  function enqueueTextVisemes(text, totalDurationMs = null, startAtMs = null) {
    try {
      if (!text || typeof text !== 'string') return;
      const now = performance.now();
      const start = (typeof startAtMs === 'number' ? startAtMs : now);
      // stima durata per carattere
      const clean = String(text).replace(/\s+/g, ' ').trim();
      const chars = Math.max(1, clean.length);
      const baseDur = 95;
        const dur = (typeof totalDurationMs === 'number' && isFinite(totalDurationMs) && totalDurationMs > 200) ?
          Math.max(40, totalDurationMs / chars) :
          baseDur;
      let accEnd = (visemeSchedule.length > 0 ? visemeSchedule[visemeSchedule.length - 1].end : start);

        // Dividi il testo in sillabe approssimative per l'italiano
        const syllables = clean.toLowerCase()
          .replace(/[^a-z\s]/g, '')
          .replace(/([aeiou])[aeiou]+/g, '$1')
          .match(/[^aeiou]*[aeiou]+/g) || [];

        for (const syl of syllables) {
          const t = {
            jawOpen: 0,
            mouthFunnel: 0,
            mouthPucker: 0,
            mouthSmileL: 0,
            mouthSmileR: 0,
            mouthClose: 0
          };

          // Sillabe che richiedono labbra arrotondate
          if (syl.includes('o') || syl.includes('u')) {
            t.mouthFunnel = 0.8;
            t.mouthPucker = 0.6;
            t.jawOpen = 0.3;
          }
          // Sillabe aperte con 'a'
          else if (syl.includes('a')) {
            t.jawOpen = 0.6;
            t.mouthSmileL = 0.2;
            t.mouthSmileR = 0.2;
          }
          // Sillabe con 'e' o 'i' (sorriso)
          else if (syl.includes('e') || syl.includes('i')) {
            t.mouthSmileL = 0.4;
            t.mouthSmileR = 0.4;
            t.jawOpen = 0.35;
          }

          // Consonanti occlusive che chiudono la bocca
          if (syl.startsWith('p') || syl.startsWith('b') || syl.startsWith('m')) {
            t.mouthClose = 0.9;
            t.jawOpen *= 0.5;
          }
          // Consonanti dentali
          else if (syl.startsWith('t') || syl.startsWith('d') || syl.startsWith('n')) {
            t.mouthClose = 0.6;
            t.jawOpen *= 0.7;
          }
          // Consonanti fricative
          else if (syl.startsWith('f') || syl.startsWith('v') || syl.startsWith('s')) {
            t.mouthFunnel = Math.max(t.mouthFunnel, 0.3);
            t.jawOpen *= 0.8;
          }

          const sylDur = dur * (syl.length * 0.8); // Durata proporzionale alla lunghezza della sillaba
        const s = accEnd;
          const e = s + sylDur;
          visemeSchedule.push({
            start: s,
            end: e,
            targets: t
          });
        accEnd = e;
      }

        // Gestione punteggiatura e pause
        for (const ch of clean) {
          if (/[.,;:!?]/.test(ch)) {
            const t = {
              jawOpen: 0,
              mouthFunnel: 0,
              mouthPucker: 0,
              mouthSmileL: 0,
              mouthSmileR: 0,
              mouthClose: 0.6
            };
            const pauseDur = dur * 1.6;
            const s = accEnd;
            const e = s + pauseDur;
            visemeSchedule.push({
              start: s,
              end: e,
              targets: t
            });
            accEnd = e;
          }
        }

      // limita schedule per evitare code troppo lunghe
      if (visemeSchedule.length > 120) visemeSchedule = visemeSchedule.slice(-120);
    } catch {}
  }
    let isListening = false,
      recognition = null,
      mediaMicStream = null;
  let currentEvtSource = null; // Stream SSE attivo da chiudere se necessario
  let isStartingStream = false;
  
  // Three.js avatar minimale (testa + mandibola)
  let THREELoaded = false;
  let scene, camera, renderer, head, jaw, animationId, analyser, dataArray, audioCtx, mediaNode;
    let meyda = null,
      meydaAnalyzer = null;
  let advancedLipsyncOn = false;

  // TTS queue
  let bufferText = '';
  let ttsBuffer = ''; // Buffer separato per TTS
  let speakQueue = [];
  let isSpeaking = false;
  let lastSpokenTail = '';
  let lastSentToTts = '';
  let ttsProcessedLength = 0; // Traccia quanto del testo è già stato processato per TTS
  let ttsFirstChunkSent = false;
  let ttsKickTimer = null;
  let ttsTick = null;
  let ttsRequestQueue = [];
  let ttsRequestInFlight = false;
  // Ampiezza sintetica per TTS del browser (non fornisce audio samples)
  let speechAmp = 0;
  let speechAmpTarget = 0;
  let speechAmpTimer = null;
  // FFT cache for lipsync when using cloud TTS (audio element)
  let freqData = null;

  // HeyGen Streaming state
    let heygen = {
      sessionInfo: null,
      room: null,
      mediaStream: null,
      sessionToken: null,
      connecting: false,
      started: false
    };

  // Debug overlay wiring
  const debugOverlay = document.getElementById('debugOverlay');
  const debugContent = document.getElementById('debugContent');
  const debugCloseBtn = document.getElementById('debugClose');
  const debugClearBtn = document.getElementById('debugClear');
  const debugCopyBtn = document.getElementById('debugCopy');
    const originalConsole = {
      log: console.log,
      warn: console.warn,
      error: console.error,
      info: console.info
    };

  function formatForLog(arg) {
    try {
      if (arg instanceof Error) {
        return (arg.stack || (arg.name + ': ' + arg.message));
      }
      if (typeof arg === 'object') {
        return JSON.stringify(arg, (k, v) => {
          if (v instanceof Node) return `[Node ${v.nodeName}]`;
          if (v === window) return '[Window]';
          if (v === document) return '[Document]';
          return v;
        });
      }
      return String(arg);
    } catch (_) {
        try {
          return String(arg);
        } catch {
          return '[unserializable]';
        }
    }
  }

  function appendDebugLine(type, args) {
    if (!debugEnabled || !debugContent) return;
    const time = new Date().toLocaleTimeString();
    const line = document.createElement('div');
    line.className = 'whitespace-pre-wrap break-words';
    try {
      const msg = Array.from(args || []).map(formatForLog).join(' ');
      line.textContent = `[${time}] ${type.toUpperCase()} ${msg}`;
    } catch {
      line.textContent = `[${time}] ${type.toUpperCase()} [log append failed]`;
    }
    debugContent.appendChild(line);
    // Trim to last 400 lines
    try {
      const max = 400;
      while (debugContent.childNodes.length > max) {
        debugContent.removeChild(debugContent.firstChild);
      }
    } catch {}
    // Scroll to bottom
      try {
        debugContent.parentElement.scrollTop = debugContent.parentElement.scrollHeight;
      } catch {}
  }

  function initDebugOverlay() {
    if (!debugEnabled) return;
      try {
        debugOverlay?.classList.remove('hidden');
      } catch {}
      try {
        debugCloseBtn?.addEventListener('click', () => {
          debugOverlay.classList.add('hidden');
        });
        debugClearBtn?.addEventListener('click', () => {
          if (debugContent) debugContent.innerHTML = '';
        });
      debugCopyBtn?.addEventListener('click', async () => {
        try {
          const lines = Array.from(debugContent?.children || []).map(n => (n.textContent || ''));
          const text = lines.join('\n');
          if (navigator.clipboard && navigator.clipboard.writeText) {
            await navigator.clipboard.writeText(text);
          } else {
            const ta = document.createElement('textarea');
              ta.value = text;
              document.body.appendChild(ta);
              ta.select();
              document.execCommand('copy');
              document.body.removeChild(ta);
            }
            console.log('DEBUG: logs copied', {
              lines: lines.length
            });
        } catch (e) {
          console.error('DEBUG: copy failed', e);
        }
      });
    } catch {}
    // Mirror console methods
    try {
        ['log', 'warn', 'error', 'info'].forEach((m) => {
        console[m] = function(...a) {
            try {
              appendDebugLine(m, a);
            } catch {}
            try {
              originalConsole[m].apply(console, a);
            } catch {}
        };
      });
    } catch {}
    // Window-level errors
    try {
      window.addEventListener('error', (e) => {
        appendDebugLine('windowError', [e.message, `${e.filename}:${e.lineno}:${e.colno}`]);
      });
      window.addEventListener('unhandledrejection', (e) => {
        const r = e.reason;
        appendDebugLine('promiseRejection', [r && (r.stack || r.message) || String(r)]);
      });
    } catch {}
    // First line
      appendDebugLine('info', ['Debug overlay enabled', {
        ua: navigator.userAgent,
        locale,
        recLang,
        isAndroid,
        isChrome,
        hasWebSpeech: !!(window.SpeechRecognition || window.webkitSpeechRecognition)
      }]);
  }

  // Sanifica testo per TTS (niente markdown/asterischi/URL/decimali ",00")
  function sanitizeForTts(input) {
    let t = stripHtml(input || '');
    // Rimuovi markdown semplice
    t = t.replace(/\*\*(.*?)\*\*/g, '$1');
    t = t.replace(/\*(.*?)\*/g, '$1');
    t = t.replace(/`+/g, '');
    // Rimuovi URL
    t = t.replace(/https?:\/\/\S+/gi, '');
    // Rimuovi bullet / numerazioni
    t = t.replace(/^[\-\*•]\s+/gm, '');
    t = t.replace(/^\d+\.\s+/gm, '');
    // Rimuovi decimali ",00" o ".00"
    t = t.replace(/(\d+)[\.,]00\b/g, '$1');
    // Comprimi punteggiatura e spazi
    t = t.replace(/[\s\u00A0]+/g, ' ').trim();
    // Evita ripetizioni di punti/virgole
    t = t.replace(/([\.!?,;:]){2,}/g, '$1');
    return t;
  }
  initThree();

  // Auto-selezioni TTS di default
  try {
      // Default: Video Avatar disattivo, TTS browser attivo se supportato
      if (useVideoAvatar) {
        useVideoAvatar.checked = false;
      }
      if ('speechSynthesis' in window && useBrowserTts) {
      useBrowserTts.checked = true;
        browserTtsStatus && (browserTtsStatus.textContent = 'TTS browser attivo');
    }
    // Abilita di default LipSync avanzato su Chrome o in debug
      const ua = navigator.userAgent || '';
      const isChrome = !!window.chrome && /Chrome\/\d+/.test(ua) && !/Edg\//.test(ua) && !/OPR\//.test(ua) && !/Brave/i.test(ua);
    if ((isChrome || debugEnabled) && useAdvancedLipsync) {
      useAdvancedLipsync.checked = true;
      advancedLipsyncOn = true;
      ensureMeydaLoaded().then(() => startMeydaAnalyzer()).catch(() => {});
    }
  } catch {}
  // Toggle Video Avatar visibility and session management
  try {
    useVideoAvatar?.addEventListener('change', async () => {
      if (useVideoAvatar.checked) {
        document.getElementById('avatarStage')?.classList.add('hidden');
        heygenVideo?.classList.remove('hidden');
          try {
            await heygenEnsureSession();
          } catch (e) {
            console.error('HEYGEN: ensure session failed', e);
          }
        // Try user-gesture playback unlock on first interaction
        const unlock = async () => {
            try {
              await heygenVideo?.play();
            } catch {}
            try {
              await heygenAudio?.play();
            } catch {}
          document.removeEventListener('click', unlock);
          document.removeEventListener('touchstart', unlock);
        };
          document.addEventListener('click', unlock, {
            once: true
          });
          document.addEventListener('touchstart', unlock, {
            once: true
          });
      } else {
        heygenVideo?.classList.add('hidden');
        document.getElementById('avatarStage')?.classList.remove('hidden');
          try {
            await heygenClose();
          } catch {}
      }
    });
    // Ensure initial state
    if (useVideoAvatar?.checked) {
      document.getElementById('avatarStage')?.classList.add('hidden');
      heygenVideo?.classList.remove('hidden');
        try {
          await heygenEnsureSession();
        } catch {}
      // Ensure autoplay after initial tracks
      setTimeout(async () => {
          try {
            if (heygenVideo?.srcObject && heygenVideo.paused) await heygenVideo.play();
          } catch {}
          try {
            if (heygenAudio?.srcObject && heygenAudio.paused) await heygenAudio.play();
          } catch {}
      }, 800);
    }
  } catch {}


  // Toggle advanced lipsync
  try {
    useAdvancedLipsync?.addEventListener('change', async () => {
      advancedLipsyncOn = !!useAdvancedLipsync.checked;
      if (advancedLipsyncOn) {
        await ensureMeydaLoaded();
        startMeydaAnalyzer();
      } else {
        stopMeydaAnalyzer();
      }
    });
  } catch {}

  // Rimosso chat history: messaggi non più renderizzati, manteniamo solo TTS e indicatori

  // Init debug overlay (at end, so it captures later console logs too)
  initDebugOverlay();

  function startStream(message) {
    if (!message || message.trim() === '') return;
      if (isStartingStream) {
        console.warn('SSE: start already in progress');
        return;
      }
    isStartingStream = true;
      setTimeout(() => {
        isStartingStream = false;
      }, 800);
    
    console.log('TTS: Starting new conversation, resetting state');
      try {
        console.log('SSE: connecting', {
          team: teamSlug,
          uuid,
          locale
        });
      } catch {}
    
    // Chat history rimossa: nessun messaggio renderizzato, manteniamo solo TTS
    
    // Mostra fumetto "Sto pensando..."
    thinkingBubble.classList.remove('hidden');
    
    // Chiudi eventuale stream precedente e resetta per nuova conversazione
      try {
        if (currentEvtSource) {
          currentEvtSource.close();
          currentEvtSource = null;
        }
      } catch {}
    bufferText = '';
    ttsBuffer = '';
    lastSentToTts = '';
    lastSpokenTail = '';
    ttsProcessedLength = 0;
    ttsFirstChunkSent = false;
      if (ttsKickTimer) {
        try {
          clearTimeout(ttsKickTimer);
        } catch {}
        ttsKickTimer = null;
      }
      if (ttsTick) {
        try {
          clearInterval(ttsTick);
        } catch {}
        ttsTick = null;
      }
    
    // Ferma audio corrente e pulisci coda
    if (ttsPlayer && !ttsPlayer.paused) {
      ttsPlayer.pause();
      ttsPlayer.currentTime = 0;
    }
    
    // Pulisci coda TTS
    speakQueue.forEach(item => URL.revokeObjectURL(item.url));
    speakQueue = [];
    isSpeaking = false;
    
    let collected = '';
    let aiMessageDiv = null; // Riferimento al div del messaggio AI
      const params = new URLSearchParams({
        message,
        team: teamSlug,
        uuid: uuid || '',
        locale,
        ts: String(Date.now())
      });
    if (threadId) params.set('thread_id', threadId);
    if (assistantThreadId) params.set('assistant_thread_id', assistantThreadId);
    let done = false;
    let firstToken = true;
    let sseRetryCount = 0;
    let evtSource = null;
    let sseConnectWatchdog = null;
    // Tick ad alta frequenza per tentare TTS chunking, indipendente dal ritmo dei token
    if (!ttsTick) {
      ttsTick = setInterval(() => {
          try {
            checkForTtsChunks();
          } catch {}
      }, 120);
    }

    function bindSse() {
      evtSource.addEventListener('message', (e) => {
        try {
          const data = JSON.parse(e.data);
          if (data.token) {
            try {
              const tok = JSON.parse(data.token);
                if (tok && tok.thread_id) {
                  threadId = tok.thread_id;
                  return;
                }
                if (tok && tok.assistant_thread_id) {
                  assistantThreadId = tok.assistant_thread_id;
                  return;
                }
            } catch {}
            if (firstToken) {
              firstToken = false;
              thinkingBubble.classList.add('hidden');
                if (ttsKickTimer) {
                  try {
                    clearTimeout(ttsKickTimer);
                  } catch {}
                }
              ttsKickTimer = null;
              // Abbiamo ricevuto dati: annulla watchdog di connessione
                if (sseConnectWatchdog) {
                  clearTimeout(sseConnectWatchdog);
                  sseConnectWatchdog = null;
                }
            }
            collected += data.token;
            ttsBuffer += data.token;
            checkForTtsChunks();
          }
          } catch (msgErr) {
            console.warn('Message parse error:', msgErr);
          }
      });
      evtSource.addEventListener('error', () => {
        const state = evtSource.readyState; // 0=CONNECTING,1=OPEN,2=CLOSED
        try {
          if (state === 2) {
              console.error('SSE: closed', {
                attempt: sseRetryCount + 1,
                readyState: state
              });
          } else {
              console.warn('SSE: transient error', {
                attempt: sseRetryCount + 1,
                readyState: state
              });
          }
        } catch {}
        // Se non è CLOSED e non abbiamo finito, lascia che il browser gestisca la riconnessione
          if (state !== 2 && !done) {
            return;
          }
          try {
            evtSource.close();
          } catch {}
        currentEvtSource = null;
          if (sseConnectWatchdog) {
            try {
              clearTimeout(sseConnectWatchdog);
            } catch {}
            sseConnectWatchdog = null;
          }
        // Retry se nessun token ricevuto
        if (!done && collected.length === 0 && sseRetryCount < 2) {
          sseRetryCount++;
          const delay = 220 * sseRetryCount;
            setTimeout(() => {
              openSse();
            }, delay);
          return;
        }
        // Cleanup
        thinkingBubble.classList.add('hidden');
          if (ttsTick) {
            try {
              clearInterval(ttsTick);
            } catch {}
            ttsTick = null;
          }
      });
      evtSource.addEventListener('done', () => {
          try {
            evtSource.close();
          } catch {}
        done = true;
        thinkingBubble.classList.add('hidden');
          try {
            console.log('SSE: done event received');
          } catch {}
          if (sseConnectWatchdog) {
            try {
              clearTimeout(sseConnectWatchdog);
            } catch {}
            sseConnectWatchdog = null;
          }
        if (ttsBuffer.trim().length > 0) {
          const remainingText = stripHtml(ttsBuffer).trim();
            if (remainingText.length > 0) {
              console.log('TTS: Sending remaining text:', remainingText.substring(0, 50) + '...');
              sendToTts(remainingText);
            }
          ttsBuffer = '';
        }
          if (ttsTick) {
            try {
              clearInterval(ttsTick);
            } catch {}
            ttsTick = null;
          }
        });
      }

    function openSse() {
        try {
          if (currentEvtSource) currentEvtSource.close();
        } catch {}
      evtSource = new EventSource(`/api/chatbot/stream?${params.toString()}`);
      currentEvtSource = evtSource;
        try {
          console.log('SSE: connecting', {
            team: teamSlug,
            uuid,
            locale,
            threadId,
            assistantThreadId
          });
        } catch {}
      bindSse();
      // Watchdog solo su Android: se non arrivano token dopo un po', ritenta
      if (isAndroid) {
          if (sseConnectWatchdog) {
            clearTimeout(sseConnectWatchdog);
          }
        sseConnectWatchdog = setTimeout(() => {
          try {
            const state = evtSource.readyState; // 0/1/2
            if (collected.length === 0 && !done && sseRetryCount < 2 && (state === 0 || state === 2)) {
              sseRetryCount++;
                try {
                  evtSource.close();
                } catch {}
              currentEvtSource = null;
              const delay = 280 * sseRetryCount;
                console.warn('SSE: connect watchdog retry', {
                  attempt: sseRetryCount
                });
                setTimeout(() => {
                  openSse();
                }, delay);
            }
          } finally {
              try {
                clearTimeout(sseConnectWatchdog);
              } catch {}
            sseConnectWatchdog = null;
          }
        }, 6000);
      }
    }
    openSse();
  }

  sendBtn.addEventListener('click', async () => {
    // Sblocca l'audio prima di inviare
    try {
        if (!audioCtx) audioCtx = new(window.AudioContext || window.webkitAudioContext)();
      if (audioCtx.state === 'suspended') await audioCtx.resume();
    } catch {}
    startStream(input.value);
    input.value = '';
  });

  input.addEventListener('keyup', async (e) => {
    if (e.key === 'Enter') {
      try {
          if (!audioCtx) audioCtx = new(window.AudioContext || window.webkitAudioContext)();
        if (audioCtx.state === 'suspended') await audioCtx.resume();
      } catch {}
      startStream(input.value);
      input.value = '';
    }
  });

  async function stopAllSpeechOutput() {
    try {
        if (window.speechSynthesis) window.speechSynthesis.cancel();
    } catch {}
      try {
        if (ttsPlayer && !ttsPlayer.paused) {
          ttsPlayer.pause();
          ttsPlayer.currentTime = 0;
        }
      } catch {}
      speakQueue.forEach(item => {
        if (item.url) try {
          URL.revokeObjectURL(item.url);
        } catch {}
      });
      speakQueue = [];
      ttsRequestQueue = [];
      isSpeaking = false;
  }

  function setListeningUI(active) {
    const badge = document.getElementById('listeningBadge');
    if (active) {
      badge.classList.remove('hidden');
      micBtn.classList.remove('bg-rose-600');
        micBtn.classList.add('bg-emerald-600', 'ring-2', 'ring-emerald-400', 'animate-pulse');
    } else {
      badge.classList.add('hidden');
      micBtn.classList.add('bg-rose-600');
        micBtn.classList.remove('bg-emerald-600', 'ring-2', 'ring-emerald-400', 'animate-pulse');
    }
  }

  async function ensureMicPermission() {
    try {
      if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) return true; // Non bloccare se non disponibile
      // Chiedi il permesso in anticipo; chiuderemo subito lo stream
      if (!mediaMicStream) {
        console.log('MIC: requesting permission');
          mediaMicStream = await navigator.mediaDevices.getUserMedia({
            audio: {
              echoCancellation: true
            }
          });
        // Rilascia subito
          try {
            mediaMicStream.getTracks().forEach(t => t.stop());
          } catch {}
        mediaMicStream = null;
      }
      console.log('MIC: permission OK');
      return true;
    } catch (e) {
      console.warn('Mic permission denied or error', e);
      return false;
    }
  }

  micBtn.addEventListener('click', async () => {
    // Toggle ascolto
    if (isListening && recognition) {
        try {
          recognition.stop();
          recognition.abort && recognition.abort();
        } catch {}
        isListening = false;
        setListeningUI(false);
      console.log('MIC: listening stopped by user');
      return;
    }
    // Ferma eventuale parlato e stream corrente
    await stopAllSpeechOutput();
      try {
        if (currentEvtSource) {
          currentEvtSource.close();
          currentEvtSource = null;
        }
      } catch {}

    // Sblocca l'audio (Android)
    try {
        if (!audioCtx) audioCtx = new(window.AudioContext || window.webkitAudioContext)();
        if (audioCtx.state === 'suspended') {
          await audioCtx.resume();
          console.log('AUDIO: context resumed');
        }
      } catch (e) {
        console.warn('AUDIO: failed to init/resume', e);
      }

    // Verifica permesso microfono (Android spesso non mostra prompt con WebSpeech)
    const ok = await ensureMicPermission();
      if (!ok) {
        alert('Permesso microfono negato. Abilitalo nelle impostazioni del browser.');
        return;
      }

    try {
      const Rec = window.SpeechRecognition || window.webkitSpeechRecognition;
      if (!Rec) throw new Error('Web Speech API non disponibile');
      recognition = new Rec();
      recognition.lang = recLang || 'it-IT';
      // Android spesso consegna eventi solo con interim/continuous attivi
      recognition.interimResults = isAndroid ? true : false;
      recognition.continuous = isAndroid ? false : false;
      recognition.maxAlternatives = 1;
      let recognitionWatchdogTimer = null;
      let lastResultAt = 0;

        function clearRecWatchdog() {
          if (recognitionWatchdogTimer) {
            clearTimeout(recognitionWatchdogTimer);
            recognitionWatchdogTimer = null;
          }
        }

      function startRecWatchdog() {
        clearRecWatchdog();
        recognitionWatchdogTimer = setTimeout(() => {
          console.warn('SPEECH: watchdog timeout (no result), restarting');
            try {
              recognition.stop();
              recognition.abort && recognition.abort();
            } catch {}
            setTimeout(() => {
              try {
                recognition.start();
                console.log('SPEECH: restarted by watchdog');
              } catch (e) {
                console.error('SPEECH: restart failed', e);
              }
            }, 250);
        }, 8000);
      }
      recognition.onstart = async () => {
          isListening = true;
          setListeningUI(true);
          console.log('SPEECH: onstart');
        // Sospendi output audio per evitare conflitti con input mic su Android
          try {
            if (audioCtx && audioCtx.state === 'running') {
              await audioCtx.suspend();
              console.log('AUDIO: context suspended for recognition');
            }
          } catch (e) {
            console.warn('AUDIO: suspend failed', e);
          }
        };
        recognition.onaudiostart = () => {
          console.log('SPEECH: onaudiostart');
        };
        recognition.onsoundstart = () => {
          console.log('SPEECH: onsoundstart');
        };
        recognition.onspeechstart = () => {
          console.log('SPEECH: onspeechstart');
          startRecWatchdog();
        };
        recognition.onspeechend = () => {
          console.log('SPEECH: onspeechend');
          clearRecWatchdog();
        };
        recognition.onsoundend = () => {
          console.log('SPEECH: onsoundend');
        };
        recognition.onaudioend = () => {
          console.log('SPEECH: onaudioend');
        };
        recognition.onnomatch = (e) => {
          console.warn('SPEECH: onnomatch', e && e.message || '');
        };
      recognition.onerror = async (e) => {
          console.error('SPEECH: onerror', e && (e.error || e.message) || e);
          isListening = false;
          setListeningUI(false);
          clearRecWatchdog();
          try {
            if (audioCtx && audioCtx.state === 'suspended') {
              await audioCtx.resume();
              console.log('AUDIO: context resumed after error');
            }
          } catch (er) {
            console.warn('AUDIO: resume after error failed', er);
          }
      };
      recognition.onend = async () => {
          isListening = false;
          setListeningUI(false);
          console.log('SPEECH: onend');
          clearRecWatchdog();
          try {
            if (audioCtx && audioCtx.state === 'suspended') {
              await audioCtx.resume();
              console.log('AUDIO: context resumed after end');
            }
          } catch (er) {
            console.warn('AUDIO: resume after end failed', er);
          }
      };
      recognition.onresult = (event) => {
        try {
          lastResultAt = Date.now();
          const res = event.results;
          const last = res[res.length - 1];
          const transcript = last[0].transcript;
          const isFinal = last.isFinal === true || !recognition.interimResults;
            console.log('SPEECH: onresult', {
              transcript,
              isFinal,
              resultIndex: event.resultIndex,
              length: res.length
            });
          if (debugEnabled && liveText) {
            liveText.classList.remove('hidden');
            liveText.textContent = transcript + (isFinal ? '' : ' …');
          }
          if (isFinal) {
              isListening = false;
              setListeningUI(false);
              if (debugEnabled && liveText) setTimeout(() => {
                try {
                  liveText.classList.add('hidden');
                  liveText.textContent = '';
                } catch {}
              }, 800);
            const safe = (transcript || '').trim();
              if (!safe) {
                console.warn('SPEECH: final transcript empty, not starting stream');
                return;
              }
              if (isAndroid) {
                setTimeout(() => startStream(safe), 220);
              } else {
                startStream(safe);
              }
          }
        } catch (err) {
          console.error('SPEECH: onresult handler failed', err);
        }
      };
        console.log('SPEECH: start()', {
          lang: recognition.lang
        });
      recognition.start();
    } catch (err) {
      console.warn('Riconoscimento vocale non disponibile o errore', err);
      alert('Riconoscimento vocale non disponibile in questo browser.');
    }
  });

  function initThree() {
    // Carica Three dal CDN se non presente
    if (!window.THREE) {
      const s = document.createElement('script');
      s.src = 'https://unpkg.com/three@0.160.0/build/three.min.js';
        s.onload = () => {
          THREELoaded = true;
          setupScene();
        };
      document.head.appendChild(s);
    } else {
      THREELoaded = true;
      setupScene();
    }
  }

  function setupScene() {
    const stage = document.getElementById('avatarStage');
    const rect = stage.getBoundingClientRect();
    // Usa dimensioni CSS (con aspect-ratio) come base; fallback per vecchi browser
    let width = Math.floor((rect.width && rect.width > 0) ? rect.width : Math.min(window.innerWidth || 360, 520));
    let height = Math.floor((rect.height && rect.height > 0) ? rect.height : Math.round(width * 4 / 3));
    if (!width || width < 10 || !height || height < 10) {
        width = 800;
        height = 450; // fallback quando i CSS non sono caricati
      stage.style.width = width + 'px';
      stage.style.height = height + 'px';
    }

    scene = new THREE.Scene();
    scene.background = new THREE.Color('#0f172a');

      camera = new THREE.PerspectiveCamera(2, width / height, 0.1, 100);
      camera.position.set(0, 0.5, 2);
      try {
        console.log('CAM init', {
          pos: camera.position.toArray(),
          fov: camera.fov
        });
      } catch {}

      renderer = new THREE.WebGLRenderer({
        antialias: true
      });
    renderer.setPixelRatio(Math.min(window.devicePixelRatio || 1, 1.75));
    renderer.setSize(width, height);
    stage.innerHTML = '';
    stage.appendChild(renderer.domElement);
    renderer.domElement.style.width = '100%';
    renderer.domElement.style.height = '100%';

    const light = new THREE.DirectionalLight(0xffffff, 1.0);
    light.position.set(1, 2, 3);
    scene.add(light);
    scene.add(new THREE.AmbientLight(0xffffff, 0.4));

    // Testa
    const headGeom = new THREE.SphereGeometry(0.6, 32, 32);
      const headMat = new THREE.MeshStandardMaterial({
        color: 0x8fa7ff,
        roughness: 0.6,
        metalness: 0.0
      });
    head = new THREE.Mesh(headGeom, headMat);
    head.position.y = 0.2;
    scene.add(head);

    // Mandibola semplice (box)
    const jawGeom = new THREE.BoxGeometry(0.8, 0.25, 0.6);
      const jawMat = new THREE.MeshStandardMaterial({
        color: 0x9bb0ff,
        roughness: 0.6
      });
    jaw = new THREE.Mesh(jawGeom, jawMat);
    jaw.position.y = -0.25;
    jaw.position.z = 0.0;
    // Punto di rotazione attorno alla "cerniera"
    jaw.geometry.translate(0, 0.12, 0);
    scene.add(jaw);

    console.log('setupScene: start, THREE present =', !!window.THREE);
      // OrbitControls
      try {
        if (window.OrbitControls) {
          window.__orbit = new window.OrbitControls(camera, renderer.domElement);
          renderer.domElement.style.touchAction = 'none';
          __orbit.enableDamping = true;
          __orbit.dampingFactor = 0.08;
          __orbit.enableZoom = true;
          __orbit.zoomSpeed = 1.0;
          __orbit.enablePan = true;
          __orbit.enableRotate = false; // disabilita rotazione con drag
          __orbit.screenSpacePanning = true;
          __orbit.minDistance = 0.1;
          __orbit.maxDistance = 12;
          // Mappa: trascinamento con sinistro = dolly (avvicina/allontana), rotazione con centrale, pan con destro
          if (window.THREE && window.THREE.MOUSE) {
            __orbit.mouseButtons = {
              LEFT: window.THREE.MOUSE.DOLLY,
              MIDDLE: window.THREE.MOUSE.ROTATE,
              RIGHT: window.THREE.MOUSE.PAN,
            };
          }
          __orbit.target.set(0, 0.2, 0);
          __orbit.update();
          try {
            __orbit.addEventListener('change', () => {
              try {
                console.log('CAM orbit change', {
                  pos: camera.position.toArray(),
                  dist: camera.position.distanceTo(__orbit.target).toFixed(3)
                });
              } catch {}
            });
          } catch {}
          console.log('OrbitControls ON');
        } else {
          console.warn('OrbitControls not available');
        }
      } catch (e) {
        console.warn('OrbitControls init failed', e);
      }
    animate();
    // Ridimensionamento reattivo
    window.addEventListener('resize', onResize);
    if ('ResizeObserver' in window) {
      const ro = new ResizeObserver(onResize);
      ro.observe(stage);
    }

    // Prova a caricare un avatar umanoide (se presente in /images/humanoid.glb)
    setTimeout(loadHumanoid, 0);
  }

  function onResize() {
    if (!renderer || !camera) return;
    const stage = document.getElementById('avatarStage');
    const controls = document.getElementById('controlsBar');
    const rect = stage.getBoundingClientRect();
    let width = Math.floor((rect.width && rect.width > 0) ? rect.width : Math.min(window.innerWidth || 360, 520));
    let height = Math.floor((rect.height && rect.height > 0) ? rect.height : Math.round(width * 4 / 3));
    if (!width || width < 10 || !height || height < 10) {
        width = 800;
        height = 450;
      stage.style.width = width + 'px';
      stage.style.height = height + 'px';
    }
    renderer.setSize(width, height);
    camera.aspect = width / height;
    camera.updateProjectionMatrix();
    // Evita che la barra comandi sovrapponga il canvas: aggiusta padding inferiore dinamico
    try {
      const controlsRect = controls ? controls.getBoundingClientRect() : null;
      const pad = controlsRect ? Math.ceil(controlsRect.height) : 0;
      document.body.style.setProperty('--controls-pad', pad + 'px');
    } catch {}

    // Fix barra bianca sotto su Android (forza height dello stage entro viewport reale)
    try {
      const vh = window.visualViewport ? window.visualViewport.height : window.innerHeight;
      const maxH = Math.max(320, Math.floor(vh - (controls?.offsetHeight || 0) - 180));
      stage.style.maxHeight = maxH + 'px';
    } catch {}
  }

  function animate() {
      const forcedClosing = (performance.now() < (forceFullCloseUntil || 0));

    animationId = requestAnimationFrame(animate);
    // Idle breathing
    head.position.y = 0.2 + Math.sin(performance.now() / 1200) * 0.01;
      // Micro-movimenti facciali per realismo quando non parla (sorriso leggero)
      try {
        const talking = cloudAudioSpeaking || (window.speechSynthesis && window.speechSynthesis.speaking);
        if (!talking && Array.isArray(visemeMeshes) && visemeMeshes.length > 0) {
          const t = performance.now() * 0.001;
          const baseSmile = 0.06; // sorriso di riposo
          const microSmile = baseSmile + 0.012 * Math.sin(t * 0.6 + 1.1);
          const microPucker = 0.006 + 0.006 * Math.sin(t * 0.8 + 0.4);
          for (const vm of visemeMeshes) {
            const infl = vm.mesh && vm.mesh.morphTargetInfluences;
            const idxs = vm.indices || {};
            if (!infl) continue;
            if (idxs.mouthSmileL >= 0) infl[idxs.mouthSmileL] = Math.max(0, Math.min(0.10, microSmile));
            if (idxs.mouthSmileR >= 0) infl[idxs.mouthSmileR] = Math.max(0, Math.min(0.10, microSmile));
            if (idxs.mouthPucker >= 0) infl[idxs.mouthPucker] = Math.max(0, Math.min(0.03, microPucker));
          }
        }
      } catch {}

    // Se l'audio sta suonando, usa l'ampiezza per aprire la mandibola
    let amp = 0;
    if (useBrowserTts && useBrowserTts.checked && 'speechSynthesis' in window && window.speechSynthesis.speaking) {
      // Forza uso audio cloud o WebAudio per lipsync: se abbiamo analyser, usalo
        if (analyser && dataArray && !forcedClosing) {
        // prosegui sotto con branch analyser
      } else {
        amp = 0; // nessun drive senza analyser
      }
    } 
    if (analyser && dataArray) {
      // Time-domain RMS for jaw openness
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
      // Envelope con attack/release per ampiezza più stabile
      const targetAmp = Math.min(1, rms * 5.5);
      const a = (targetAmp > audioAmp) ? AMP_ATTACK : AMP_RELEASE;
      audioAmp = audioAmp * (1 - a) + targetAmp * a;
      amp = audioAmp;
      // Simple spectral centroid from frequency bins
      try {
        if (!freqData || freqData.length !== analyser.frequencyBinCount) {
          freqData = new Uint8Array(analyser.frequencyBinCount);
        }
        analyser.getByteFrequencyData(freqData);
          let num = 0,
            den = 0;
        const sr = (audioCtx && audioCtx.sampleRate) ? audioCtx.sampleRate : 44100;
        const nyquist = sr / 2;
        const binHz = nyquist / freqData.length;
          // Calcola centroid e bande di energia
          let lowSum = 0,
            midSum = 0,
            highSum = 0,
            totalSum = 0;
          const LOW_MAX = 300,
            MID_MAX = 2000;
        for (let i = 0; i < freqData.length; i++) {
          const mag = freqData[i];
          if (mag <= 0) continue;
          const f = i * binHz;
          num += f * mag;
          den += mag;
            totalSum += mag;
            if (f <= LOW_MAX) lowSum += mag;
            else if (f <= MID_MAX) midSum += mag;
            else highSum += mag;
        }
        const centroid = den > 0 ? (num / den) : 0;
        const normC = Math.max(0, Math.min(1, centroid / 3500));
        const zcr = zc / dataArray.length; // ~0..0.5
        // Heuristics for visemes (rounded vs wide vowels, closures)
          // Feature mapping esponenziale per ampiezze
          const k = 3.2; // fattore esponenziale
          const lowN = totalSum > 0 ? (lowSum / totalSum) : 0;
          const midN = totalSum > 0 ? (midSum / totalSum) : 0;
          const highN = totalSum > 0 ? (highSum / totalSum) : 0;
          const eLow = 1 - Math.exp(-k * Math.max(0, lowN));
          const eMid = 1 - Math.exp(-k * Math.max(0, midN));
          const eHigh = 1 - Math.exp(-k * Math.max(0, highN));
          const rounded = Math.max(0, 1.0 - normC); // vocali tonde (O/U)
          const wide = Math.max(0, normC - 0.32); // vocali larghe (E/I)
        const closeLike = Math.max(0, (0.05 - zcr) * 9); // chiusure/pausa
        const lowAmp = amp < 0.08;
        // Stima banda centrale (A) ~ centroid medio
        const midBand = Math.max(0, 1 - Math.abs(normC - 0.28) / 0.22); // picco ~0.28
          // Ampiezze esponenziali: basse freq → apertura, alte freq → chiusura
          const MAX_JAW_OPEN = 0.45;
          let jawVal = Math.min(
          MAX_JAW_OPEN,
            Math.max(0.06, 1.05 * eLow + 0.40 * midBand - 0.25 * rounded)
        );
          // Rafforza O/U ma riduci se sembra A (midBand alto)
        const roundBoost = rounded * (1 - 0.6 * midBand);
          let funnelVal = lowAmp ? 0 : Math.min(0.45, Math.max(0, roundBoost * (eLow * 0.9) + rounded * 0.18));
          let puckerVal = lowAmp ? 0 : Math.min(0.38, Math.max(0, roundBoost * (eLow * 0.7) + rounded * 0.12));
          // Accentuazione per vocali O/U (rounded alto)
          const oBoost = Math.min(0.35, Math.pow(Math.max(0, rounded), 1.2) * (0.28 + 0.6 * (amp || 0)));
          funnelVal = Math.min(0.8, funnelVal + oBoost);
          puckerVal = Math.min(0.6, puckerVal + oBoost * 0.6);
          const smileVal = Math.max(0, Math.min(0.30, (eMid + wide * 0.5) * 0.35));
          // Chiusura meno aggressiva da alte frequenze e pause
          let closeVal = lowAmp ? 0.04 : Math.max(0, Math.min(0.40, (eHigh * 0.25) + closeLike * 0.30 * (1 - amp)));
          // Durante alte-freq, riduci jawOpen ma in modo più lieve
          jawVal = Math.max(lipConfig.minLipSeparation, jawVal * (1 - 0.35 * eHigh));
        visemeTargets = {
          jawOpen: jawVal,
          mouthFunnel: funnelVal,
          mouthPucker: puckerVal,
          mouthSmileL: smileVal,
          mouthSmileR: smileVal,
          mouthClose: closeVal,
        };
        visemeActiveUntil = performance.now() + 200;
        // assicurati di avere almeno un tempo attivo per i visemi
        if (!Array.isArray(visemeMeshes) || visemeMeshes.length === 0) {
          visemeActiveUntil = performance.now() + 200;
        }
      } catch {}
    }
    // Priorità ai visemi testuali se pianificati (disabilitato se textVisemeEnabled=false)
      if (textVisemeEnabled && !forcedClosing) {
      const nowT = performance.now();
      visemeSchedule = visemeSchedule.filter(it => it.end > nowT);
      const active = visemeSchedule.find(it => it.start <= nowT && it.end > nowT);
      if (active) {
        const blend = Math.max(0, Math.min(1, (nowT - active.start) / Math.max(1, (active.end - active.start))));
        visemeTargets = {
          jawOpen: active.targets.jawOpen * blend,
          mouthFunnel: active.targets.mouthFunnel * blend,
          mouthPucker: active.targets.mouthPucker * blend,
          mouthSmileL: active.targets.mouthSmileL * blend,
          mouthSmileR: active.targets.mouthSmileR * blend,
          mouthClose: active.targets.mouthClose * blend,
        };
        visemeActiveUntil = nowT + 120;
      }
    }
    // Se non abbiamo audio driving (TTS off e nessun analyser), usa lo scheduler testuale (se abilitato)
      if (textVisemeEnabled && (!analyser || !cloudAudioSpeaking) && !forcedClosing) {
        // Se in chiusura forzata, sovrascrivi i target a bocca chiusa
        if (forcedClosing) {
          visemeTargets = {
            jawOpen: 0,
            mouthFunnel: 0,
            mouthPucker: 0,
            mouthSmileL: 0,
            mouthSmileR: 0,
            mouthClose: 0
          };
          visemeActiveUntil = performance.now() + 60;
        }
      const nowT = performance.now();
      // purge passati
      visemeSchedule = visemeSchedule.filter(it => it.end > nowT);
      const active = visemeSchedule.find(it => it.start <= nowT && it.end > nowT);
      if (active) {
        const blend = Math.max(0, Math.min(1, (nowT - active.start) / Math.max(1, (active.end - active.start))));
        visemeTargets = {
          jawOpen: active.targets.jawOpen * blend,
          mouthFunnel: active.targets.mouthFunnel * blend,
          mouthPucker: active.targets.mouthPucker * blend,
          mouthSmileL: active.targets.mouthSmileL * blend,
          mouthSmileR: active.targets.mouthSmileR * blend,
          mouthClose: active.targets.mouthClose * blend,
        };
        visemeActiveUntil = nowT + 120;
      }
    }
      // Niente euristiche: seguiamo la pipeline lipsync esistente (text viseme o audio analyser)
      // Mantieni vivi i visemi finché il TTS del browser sta ancora parlando
      try {
        if (useBrowserTts && useBrowserTts.checked && 'speechSynthesis' in window && window.speechSynthesis.speaking) {
          visemeActiveUntil = performance.now() + 240;
        }
      } catch {}

    // Animazione lip-sync su avatar umanoide o fallback geometrico
    const now = performance.now();
    const restJawOpen = lipConfig.restJawOpen; // piccola apertura a riposo per evitare sovrapposizione labbra
    let appliedJaw = null; // valore di apertura usato anche per il bone
    // Se abbiamo registrato più mesh con target labiali, applica su tutte
    if ((Array.isArray(visemeMeshes) && visemeMeshes.length > 0 && (visemeActiveUntil > now)) || (!jawBone && morphMesh && morphIndex >= 0)) {
      // Rallenta crescita forza visemi
      visemeStrength = visemeStrength * (1 - lipConfig.visemeStrengthAlpha) + lipConfig.visemeStrengthAlpha;
      for (const vm of visemeMeshes) {
        const infl = vm.mesh.morphTargetInfluences;
        if (!infl) continue;
        // Smoothing con deadband per eliminare tremolio
        const smooth = (key, target) => {
          const prev = lastVisemes[key] || 0;
          const diff = target - prev;
          if (Math.abs(diff) < (deadband[key] || 0.02)) return prev; // dentro deadband, mantieni
          const alpha = lipConfig.morphSmoothingBeta; // smoothing configurabile
          const v = prev * (1 - alpha) + target * alpha;
          lastVisemes[key] = v;
          return v;
        };
          const setIdx = (idx, val, key) => {
            if (idx >= 0) infl[idx] = infl[idx] * 0.7 + Math.max(0, Math.min(1, smooth(key, val) * visemeStrength)) * 0.3;
          };
        // Vincoli per evitare sovrapposizione labbra
        let jaw = Math.min(1, Math.max(0, visemeTargets.jawOpen + (cloudAudioSpeaking ? 0 : restJawOpen)));
        const roundness = Math.min(1, visemeTargets.mouthFunnel + visemeTargets.mouthPucker);
        const closeSuppression = Math.max(0, 1 - jaw * 1.5 - roundness * 0.9);
          // Abilita chiusura controllata
          let constrainedClose = Math.min(lipConfig.maxMouthClose, (visemeTargets.mouthClose || 0) * closeSuppression);
          // Imporre separazione minima: se la chiusura tende a superare la soglia, aumenta jaw (salvo chiusura forzata)
          if (!(performance.now() < forceFullCloseUntil)) {
        if (constrainedClose > lipConfig.closeThresholdForSeparation) {
          jaw = Math.max(jaw, lipConfig.minLipSeparation);
            }
          } else {
            // In chiusura forzata, chiudi solo con la mandibola (no mouthClose)
            jaw = Math.min(jaw, 0.005);
            constrainedClose = 0;
        }
        appliedJaw = jaw;
        setIdx(vm.indices.jawOpen, jaw * 0.9, 'jawOpen');
        setIdx(vm.indices.mouthFunnel, visemeTargets.mouthFunnel * 1.05, 'mouthFunnel');
        setIdx(vm.indices.mouthPucker, visemeTargets.mouthPucker * 1.0, 'mouthPucker');
        setIdx(vm.indices.mouthSmileL, visemeTargets.mouthSmileL * 1.05, 'mouthSmileL');
        setIdx(vm.indices.mouthSmileR, visemeTargets.mouthSmileR * 1.05, 'mouthSmileR');
        setIdx(vm.indices.mouthClose, constrainedClose, 'mouthClose');
        // Blink realistico
        const t = performance.now();
        if (t >= nextBlinkAt) {
          blinkPhase = 0.001; // inizia blink
          nextBlinkAt = t + (2200 + Math.random() * 2200);
        }
        if (blinkPhase > 0) {
          // curva ease-in/out
          blinkPhase = Math.min(1, blinkPhase + 0.065);
          const k = blinkPhase <= 0.5 ? (blinkPhase * 2) : (1 - (blinkPhase - 0.5) * 2);
          const val = Math.pow(Math.max(0, Math.min(1, k)), 1.6);
          if (eyeMesh && eyeMesh.morphTargetInfluences) {
            if (eyeIndices.eyeBlinkLeft >= 0) eyeMesh.morphTargetInfluences[eyeIndices.eyeBlinkLeft] = eyeMesh.morphTargetInfluences[eyeIndices.eyeBlinkLeft] * 0.6 + val * 0.4;
            if (eyeIndices.eyeBlinkRight >= 0) eyeMesh.morphTargetInfluences[eyeIndices.eyeBlinkRight] = eyeMesh.morphTargetInfluences[eyeIndices.eyeBlinkRight] * 0.6 + val * 0.4;
          } else {
            if (eyeIndices.eyeBlinkLeft >= 0) infl[eyeIndices.eyeBlinkLeft] = infl[eyeIndices.eyeBlinkLeft] * 0.6 + val * 0.4;
            if (eyeIndices.eyeBlinkRight >= 0) infl[eyeIndices.eyeBlinkRight] = infl[eyeIndices.eyeBlinkRight] * 0.6 + val * 0.4;
          }
          if (blinkPhase >= 1) blinkPhase = 0;
        }
      }
    } else if (morphMesh && morphIndex >= 0 && Array.isArray(morphMesh.morphTargetInfluences)) {
      // Fallback amp → jawOpen only
      visemeStrength = 0;
      const target = Math.min(1, amp * 2.0 + (cloudAudioSpeaking ? 0 : restJawOpen * 0.6));
      morphValue = morphValue * 0.82 + target * 0.18;
      morphMesh.morphTargetInfluences[morphIndex] = morphValue;
      appliedJaw = Math.max(target, lipConfig.minLipSeparation);
    }
    if (jawBone) {
      if (jawBone.type === 'Bone') {
        // Bone: usa apertura vincolata per rispettare separazione minima
        // Smoothing aggiuntivo per evitare jitter
        window.__jawBonePrev = window.__jawBonePrev ?? 0;
          // Se non abbiamo calcolato appliedJaw via morph, usa visemi e ampiezza audio come fallback
          const ampDrive = Math.min(1, (typeof audioAmp === 'number' ? audioAmp : 0) * 1.4);
          let jawFromVisemes = Math.max(
            Math.min(1, Math.max(0, visemeTargets.jawOpen + (cloudAudioSpeaking ? 0 : restJawOpen))),
            ampDrive
          );
          const jawForBone = Math.max(lipConfig.minLipSeparation, (appliedJaw !== null ? appliedJaw : jawFromVisemes));
        const a = lipConfig.jawSmoothingAlpha;
        const jb = window.__jawBonePrev * (1 - a) + jawForBone * a;
        window.__jawBonePrev = jb;
          // Applica rotazione assoluta sull'asse configurato
          const angle = jawSign * (jb * 0.65);
          if (jawAxis === 'x') jawBone.rotation.x = angle;
          else if (jawAxis === 'y') jawBone.rotation.y = angle;
          else jawBone.rotation.z = angle;
          try {
            jawBone.updateMatrixWorld(true);
          } catch {}
          try {
            humanoid.updateMatrixWorld(true);
          } catch {}
          // Fallback: se il jaw non deforma, nod testa
          try {
            if ((!jawBoneHasInfluence || headNodForced) && headBone) {
              const nod = headSign * (jb * 0.15);
              headBone.rotation.x = nod;
              headBone.updateMatrixWorld(true);
            }
          } catch {}
      }
    } else if (jaw) {
      // Fallback geometrico
      window.__jawGeomPrev = window.__jawGeomPrev ?? 0;
      const jawForBone = Math.max(lipConfig.minLipSeparation, (appliedJaw !== null ? appliedJaw : (amp * 0.9 + (cloudAudioSpeaking ? 0 : restJawOpen * 0.2))));
      const a = lipConfig.jawSmoothingAlpha;
      const jb = window.__jawGeomPrev * (1 - a) + jawForBone * a;
      window.__jawGeomPrev = jb;
      jaw.rotation.x = -(jb * 0.5);
    }

    renderer.render(scene, camera);
  }

  function checkForTtsChunks() {
    const clean = stripHtml(ttsBuffer);
    if (!clean || clean.length < 2) return;

    const boundaryIndex = findSentenceBoundary(clean);
    if (boundaryIndex <= 0) return;

    const chunk = clean.slice(0, boundaryIndex).trim();
    if (!chunk || chunk.length < 4) return;

    if (speakQueue.some(item => item.text === chunk)) {
      // Avanza comunque il buffer per evitare ripetizioni
      ttsBuffer = clean.slice(boundaryIndex).trim();
      return;
    }

    console.log('TTS: Sending sentence:', chunk.substring(0, 80) + '...');
    sendToTts(chunk);
    lastSentToTts = chunk;

    // Mantieni buffer come porzione non ancora pronunciata
    ttsBuffer = clean.slice(boundaryIndex).trim();
  }

  function findSentenceBoundary(text) {
    // Restituisce indice (>=1) dopo il delimitatore di frase dell'ULTIMA frase completa trovata partendo dall'inizio
    // Delimitatori: . ! ? … (gestisce anche ...)
    // Evita abbreviazioni comuni (it) e numeri decimali
    const abbreviations = [
        'es', 'ecc', 'etc', 'sig', 'sigg', 'sigra', 'sig.na', 'sig.ra', 'dott', 'ing', 'avv', 'prof', 'dr', 'dottssa', 'srl', 'spa', 's.p.a', 's.r.l', 'p.es', 'nr', 'n', 'art', 'cap', 'ca', 'vs', 'no'
    ];

    let i = 0;
    let lastSafe = -1;
    while (i < text.length) {
      const ch = text[i];
      const next = i + 1 < text.length ? text[i + 1] : '';
      const prev = i - 1 >= 0 ? text[i - 1] : '';

      let isBoundary = false;
      let endIndex = i + 1;

      if (ch === '.' || ch === '!' || ch === '?' || ch === '…') {
        // Gestisci ellissi ...
        if (ch === '.' && text.slice(i, i + 3) === '...') {
          endIndex = i + 3;
          isBoundary = true;
          i = i + 3;
        } else {
          // Evita numeri decimali tipo 3.14
          const nextNonSpaceIdx = findNextNonSpace(text, i + 1);
          const prevNonSpaceIdx = findPrevNonSpace(text, i - 1);
          const nextCh = nextNonSpaceIdx >= 0 ? text[nextNonSpaceIdx] : '';
          const prevCh = prevNonSpaceIdx >= 0 ? text[prevNonSpaceIdx] : '';
          const decimalLike = ch === '.' && /[0-9]/.test(prevCh) && /[0-9]/.test(nextCh);

          // Evita abbreviazioni (token prima del punto)
          let abbrevLike = false;
          if (ch === '.') {
            const startTok = findTokenStart(text, i - 1);
            const token = text.slice(startTok, i).toLowerCase().replace(/\./g, '');
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
          // Richiedi un separatore successivo (spazio/virgolette) e magari maiuscola all'inizio della prossima frase
          const afterIdx = findNextNonSpace(text, endIndex);
          const afterChunk = afterIdx >= 0 ? text.slice(afterIdx, afterIdx + 2) : '';
          const nextIsUpper = afterIdx >= 0 ? /[A-ZÀ-Ý\(\["'“”‘’]/.test(text[afterIdx]) : true;
          if (afterIdx < 0 || nextIsUpper || text[afterIdx - 1] === '\n') {
            lastSafe = endIndex;
          }
        }
      } else {
        i++;
      }
    }

    return lastSafe; // -1 se non trovato
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
  
  // Funzione legacy mantenuta per compatibilità ma non più utilizzata
  function checkForCompleteSentence() {
    // Questa funzione non è più utilizzata - il TTS ora usa checkForTtsChunks
  }

  function sendToTts(text) {
    const norm = sanitizeForTts(text);
    if (!norm || norm.length < 3) return;
    if (speakQueue.some(item => item.text === norm)) return;

    // If Video Avatar mode, send to HeyGen streaming (repeat)
    if (useVideoAvatar && useVideoAvatar.checked) {
        try {
          heygenSendRepeat(norm);
        } catch (e) {
          console.error('HEYGEN repeat failed', e);
        }
      return;
    }

    // Se la checkbox TTS è selezionata e c'è speechSynthesis, usa TTS del browser
    if (useBrowserTts && useBrowserTts.checked && 'speechSynthesis' in window) {
        speakQueue.push({
          url: null,
          text: norm
        });
      if (!isSpeaking) playNextInQueue();
      return;
    }

    // Altrimenti (TTS disattivato): usa audio cloud; i visemi testuali verranno schedulati su 'playing'
    ttsRequestQueue.push(norm);
    processTtsQueue();
    return;
  }

  async function processTtsQueue() {
    if (ttsRequestInFlight) return;
    const next = ttsRequestQueue.shift();
    if (!next) return;
    ttsRequestInFlight = true;
    try {
      console.log('TTS: Requesting audio for:', next.substring(0, 80));
      const res = await fetch('/api/tts', {
        method: 'POST',
          headers: {
            'Content-Type': 'application/json'
          },
          body: JSON.stringify({
            text: next,
            locale: 'it-IT',
            format: 'mp3'
          })
      });
      if (!res.ok) throw new Error(`TTS API ${res.status}`);
      const blob = await res.blob();
      const url = URL.createObjectURL(blob);
        speakQueue.push({
          url,
          text: next
        });
      if (!isSpeaking) playNextInQueue();
    } catch (err) {
      console.error('TTS request failed:', err);
    } finally {
      ttsRequestInFlight = false;
      // Continua con la prossima richiesta senza attendere altro
      if (ttsRequestQueue.length > 0) processTtsQueue();
    }
  }

  function sendToTtsIfNew() {
    const clean = stripHtml(bufferText);
    if (!clean) return;
      const norm = clean.replace(/\s+/g, ' ').trim();
    if (!norm || norm.length < 3) return;
    
    // Controlla se il testo è già stato parlato
    if (lastSpokenTail.includes(norm)) {
      console.log('TTS: Skipping already spoken text:', norm.substring(0, 50));
      return;
    }
    
    // Controlla se è già in coda
    if (speakQueue.some(item => item.text === norm)) {
      console.log('TTS: Skipping text already in queue:', norm.substring(0, 50));
      return;
    }
    
    console.log('TTS: Sending remaining text:', norm.substring(0, 100));
    sendToTts(norm);
  }

  function enqueueSpeak(text) {
    // Usa la stessa logica di sendToTts per consistenza
    sendToTts(text);
  }

  function playNextInQueue() {
    if (!speakQueue.length) { 
      isSpeaking = false; 
      console.log('TTS: Queue empty, stopping');
      return; 
    }
    
    isSpeaking = true;
    const item = speakQueue.shift();
    console.log('TTS: Playing:', item.text.substring(0, 50), '... Queue remaining:', speakQueue.length);

    if (useBrowserTts && useBrowserTts.checked && 'speechSynthesis' in window) {
      try {
        const utter = new SpeechSynthesisUtterance(item.text);
          utter.lang = 'it-IT';
          utter.rate = 1.0;
          utter.pitch = 1.0;
          utter.volume = 1.0;
        const voices = window.speechSynthesis.getVoices();
          const prefNames = ['Google italiano', 'Microsoft', 'Elsa', 'Lucia', 'Carla', 'Silvia', 'Alice'];
        const itVoices = voices.filter(v => /it[-_]/i.test(v.lang));
        const femaleVoices = itVoices.filter(v => /female|donna|feminine/i.test(v.name + ' ' + (v.voiceURI || '')));
        const chosen = femaleVoices[0] || itVoices.find(v => prefNames.some(n => (v.name || '').includes(n))) || itVoices[0] || voices.find(v => /Italian/i.test(v.name)) || voices[0];
        if (chosen) utter.voice = chosen;
        if (browserTtsStatus) browserTtsStatus.textContent = chosen ? `Voce: ${chosen.name}` : 'Voce IT non trovata (usa default)';
        utter.onstart = () => {
          try {
            const nowT = performance.now();
            const textClean = (item.text || '').replace(/\s+/g, ' ').trim();
            const words = textClean.split(/\s+/).filter(Boolean);
            const charCount = textClean.length;
            const wordCount = words.length || Math.max(1, Math.round(charCount / 5));
            const periods = (textClean.match(/[.!?…]/g) || []).length;
            const commas = (textClean.match(/[,:;]/g) || []).length;
            const parens = (textClean.match(/[()\-\u2013\u2014]/g) || []).length;
            const rate = (typeof utter.rate === 'number' && utter.rate > 0) ? utter.rate : 1;
            // Stima durata: combinazione parole/caratteri + pause da punteggiatura, scalata per rate
            const baseWordSec = wordCount * 0.55; // ~1.8 parole/sec
            const baseCharSec = charCount / 13.5;  // ~13.5 caratteri/sec
            const pauseSec = periods * 0.55 + commas * 0.32 + parens * 0.22;
            let estSec = (baseWordSec * 0.5 + baseCharSec * 0.5) + pauseSec + 0.4;
            estSec = estSec / rate;
            estSec = Math.max(2.2, estSec);
            visemeSchedule = [];
            enqueueTextVisemes(item.text || '', Math.floor(estSec * 1000), nowT);
          } catch {}
        };
          utter.onend = () => {
            // Reset immediato alla posa neutra
            try {
              visemeSchedule = [];
              // Forza chiusura completa per un istante (solo jaw), mouthClose=0
              visemeTargets = {
                jawOpen: 0,
                mouthFunnel: 0,
                mouthPucker: 0,
                mouthSmileL: 0,
                mouthSmileR: 0,
                mouthClose: 0
              };
              forceFullCloseUntil = performance.now() + 220;
              visemeActiveUntil = performance.now() + 180;
              visemeStrength = 0;
              // Azzeriamo anche gli influences se disponibili
              if (Array.isArray(visemeMeshes)) {
                for (const vm of visemeMeshes) {
                  const infl = vm.mesh && vm.mesh.morphTargetInfluences;
                  const idxs = vm.indices || {};
                  if (!infl) continue;
                  try {
                    if (idxs.mouthFunnel >= 0) infl[idxs.mouthFunnel] = 0;
                  } catch {}
                  try {
                    if (idxs.mouthPucker >= 0) infl[idxs.mouthPucker] = 0;
                  } catch {}
                  try {
                    if (idxs.mouthSmileL >= 0) infl[idxs.mouthSmileL] = 0;
                  } catch {}
                  try {
                    if (idxs.mouthSmileR >= 0) infl[idxs.mouthSmileR] = 0;
                  } catch {}
                  try {
                    if (idxs.mouthClose >= 0) infl[idxs.mouthClose] = 0;
                  } catch {}
                  try {
                    if (idxs.jawOpen >= 0) infl[idxs.jawOpen] = 0;
                  } catch {}
                }
              }
              if (humanoid && humanoid.updateMatrixWorld) humanoid.updateMatrixWorld(true);
            } catch {}
            // Mantieni coda ed avanza
            visemeActiveUntil = 0;
            visemeStrength = 0;
            lastSpokenTail = (lastSpokenTail + ' ' + item.text).slice(-400);
            lastSentToTts = '';
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
          console.warn('speechSynthesis error, fallback to audio element', e);
        }
    }

    ttsPlayer.src = item.url;

    if (!audioCtx) {
        audioCtx = new(window.AudioContext || window.webkitAudioContext)();
    }
    if (audioCtx.state === 'suspended') {
      audioCtx.resume().catch(() => {});
    }
    if (!mediaNode) {
      mediaNode = audioCtx.createMediaElementSource(ttsPlayer);
      analyser = audioCtx.createAnalyser();
      analyser.fftSize = 2048;
      dataArray = new Uint8Array(analyser.fftSize);
      mediaNode.connect(analyser);
      analyser.connect(audioCtx.destination);
    }
    // (Re)start Meyda analyzer if advanced lipsync is on
    if (advancedLipsyncOn) {
        try {
          ensureMeydaLoaded().then(() => startMeydaAnalyzer()).catch((e) => console.warn('Meyda start failed', e));
        } catch (e) {
          console.warn('Meyda start failed', e);
        }
    }

    const onEnded = () => {
      URL.revokeObjectURL(item.url);
      // Aggiorna coda parlato (mantieni solo ultimi 400 caratteri)
      lastSpokenTail = (lastSpokenTail + ' ' + item.text).slice(-400);
      console.log('TTS: Finished playing:', item.text.substring(0, 50));
      cloudAudioSpeaking = false;
      // Stop immediato dei visemi testuali
      visemeSchedule = [];
        // Forza chiusura completa per un istante alla fine (solo jaw)
        visemeTargets = {
          jawOpen: 0,
          mouthFunnel: 0,
          mouthPucker: 0,
          mouthSmileL: 0,
          mouthSmileR: 0,
          mouthClose: 0
        };
        forceFullCloseUntil = performance.now() + 220;
        visemeActiveUntil = performance.now() + 180;
        // Azzeramento influences diretto per tutti i morph registrati
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
          if (humanoid && humanoid.updateMatrixWorld) humanoid.updateMatrixWorld(true);
        } catch {}
      ttsPlayer.removeEventListener('ended', onEnded);
      stopMeydaAnalyzer();
      playNextInQueue();
    };
    
    const onError = () => {
      console.error('TTS: Playback error for:', item.text.substring(0, 50));
      URL.revokeObjectURL(item.url);
      ttsPlayer.removeEventListener('ended', onEnded);
      ttsPlayer.removeEventListener('error', onError);
        isSpeaking = false;
        cloudAudioSpeaking = false;
      playNextInQueue();
    };
    
    ttsPlayer.addEventListener('ended', onEnded);
    ttsPlayer.addEventListener('error', onError);
    // All'avvio dell'audio, sincronizza i visemi testuali sulla durata reale
    const onPlaying = () => {
      try {
        // Con lipsync da audio, non pianifichiamo visemi testuali
        visemeSchedule = [];
      } catch {}
        try {
          ttsPlayer.removeEventListener('playing', onPlaying);
        } catch {}
    };
    ttsPlayer.addEventListener('playing', onPlaying);
    
      ttsPlayer.play().then(() => {
        cloudAudioSpeaking = true;
      }).catch((err) => {
      console.error('TTS: Play failed:', err);
      isSpeaking = false; 
      onError();
    });
  }

  async function heygenEnsureSession() {
    if (heygen.started || heygen.connecting) return;
    heygen.connecting = true;
    try {
      if (!HEYGEN_CONFIG.apiKey || !HEYGEN_CONFIG.serverUrl) {
        videoAvatarStatus && (videoAvatarStatus.textContent = 'Config mancante');
        throw new Error('HEYGEN config missing');
      }
      // Create session token
      const tokRes = await fetch(`${HEYGEN_CONFIG.serverUrl}/v1/streaming.create_token`, {
        method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-Api-Key': HEYGEN_CONFIG.apiKey
          },
      });
      const tokJson = await tokRes.json();
      heygen.sessionToken = tokJson?.data?.token;
      if (!heygen.sessionToken) throw new Error('No session token');
      videoAvatarStatus && (videoAvatarStatus.textContent = 'Token OK');

      // Create new streaming session
      const body = {
        quality: 'high',
        version: 'v2',
        video_encoding: 'H264',
      };
      if (heygenAvatar) body.avatar_name = heygenAvatar;
        if (heygenVoice) body.voice = {
          voice_id: heygenVoice,
          rate: 1.0
        };
      const newRes = await fetch(`${HEYGEN_CONFIG.serverUrl}/v1/streaming.new`, {
        method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            Authorization: `Bearer ${heygen.sessionToken}`
          },
        body: JSON.stringify(body),
      });
      const newJson = await newRes.json();
      heygen.sessionInfo = newJson?.data;
      if (!heygen.sessionInfo?.session_id) throw new Error('No session info');

      // LiveKit room
        heygen.room = new LivekitClient.Room({
          adaptiveStream: false,
          dynacast: true,
          videoCaptureDefaults: {
            resolution: LivekitClient.VideoPresets.h720.resolution
          }
        });
      heygen.mediaStream = new MediaStream();
      heygen.room.on(LivekitClient.RoomEvent.TrackSubscribed, async (track, publication, participant) => {
        try {
          if (track.kind === 'video') {
            heygen.mediaStream.addTrack(track.mediaStreamTrack);
            if (heygenVideo) {
              heygenVideo.srcObject = heygen.mediaStream;
              heygenVideo.muted = true;
              await heygenVideo.play().catch(() => {});
            }
            videoAvatarStatus && (videoAvatarStatus.textContent = 'Video connesso');
          }
          if (track.kind === 'audio') {
            // Route audio to a separate element to avoid autoplay locks
            const audioStream = new MediaStream([track.mediaStreamTrack]);
            if (heygenAudio) {
              heygenAudio.srcObject = audioStream;
              await heygenAudio.play().catch(() => {});
            }
            videoAvatarStatus && (videoAvatarStatus.textContent = 'Audio connesso');
          }
          } catch (e) {
            console.warn('HEYGEN: TrackSubscribed handler failed', e);
          }
      });
      heygen.room.on(LivekitClient.RoomEvent.TrackUnsubscribed, (track) => {
          const mt = track.mediaStreamTrack;
          if (mt) heygen.mediaStream.removeTrack(mt);
      });
      await heygen.room.prepareConnection(heygen.sessionInfo.url, heygen.sessionInfo.access_token);

      // Start streaming
      await fetch(`${HEYGEN_CONFIG.serverUrl}/v1/streaming.start`, {
        method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            Authorization: `Bearer ${heygen.sessionToken}`
          },
          body: JSON.stringify({
            session_id: heygen.sessionInfo.session_id
          }),
      });
      // Open WS to keep session alive and receive events
      try {
          const params = new URLSearchParams({
            session_id: heygen.sessionInfo.session_id,
            session_token: heygen.sessionToken,
            silence_response: 'true',
            stt_language: 'en'
          });
        const wsUrl = `wss://${new URL(HEYGEN_CONFIG.serverUrl).hostname}/v1/ws/streaming.chat?${params}`;
        heygen.ws = new WebSocket(wsUrl);
          heygen.ws.addEventListener('message', (evt) => {
            /* no-op; could log */
          });
      } catch {}
      await heygen.room.connect(heygen.sessionInfo.url, heygen.sessionInfo.access_token);
      // Attempt to resume playback after connect
        try {
          if (heygenVideo?.srcObject && heygenVideo.paused) {
            await heygenVideo.play();
          }
        } catch {}
        try {
          if (heygenAudio?.srcObject && heygenAudio.paused) {
            await heygenAudio.play();
          }
        } catch {}
      heygen.started = true;
      videoAvatarStatus && (videoAvatarStatus.textContent = 'Connesso');
    } catch (e) {
      console.error('HEYGEN: init failed', e);
    } finally {
      heygen.connecting = false;
    }
  }

  async function heygenSendRepeat(text) {
    try {
      await heygenEnsureSession();
      if (!heygen.sessionInfo?.session_id) return;
      await fetch(`${HEYGEN_CONFIG.serverUrl}/v1/streaming.task`, {
        method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            Authorization: `Bearer ${heygen.sessionToken}`
          },
          body: JSON.stringify({
            session_id: heygen.sessionInfo.session_id,
            text,
            task_type: 'repeat'
          }),
      });
    } catch (e) {
      console.error('HEYGEN: repeat failed', e);
    }
  }

  async function heygenClose() {
    try {
      if (heygen.sessionInfo?.session_id) {
        await fetch(`${HEYGEN_CONFIG.serverUrl}/v1/streaming.stop`, {
          method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              Authorization: `Bearer ${heygen.sessionToken}`
            },
            body: JSON.stringify({
              session_id: heygen.sessionInfo.session_id
            }),
        });
      }
    } catch {}
      try {
        if (heygen.ws && heygen.ws.readyState < 2) heygen.ws.close();
      } catch {}
      try {
        if (heygen.room) heygen.room.disconnect();
      } catch {}
      if (heygenVideo) {
        try {
          heygenVideo.pause();
        } catch {};
        heygenVideo.srcObject = null;
      }
      if (heygenAudio) {
        try {
          heygenAudio.pause();
        } catch {};
        heygenAudio.srcObject = null;
      }
      heygen = {
        sessionInfo: null,
        room: null,
        mediaStream: null,
        sessionToken: null,
        connecting: false,
        started: false
      };
    videoAvatarStatus && (videoAvatarStatus.textContent = '');
  }


  function stripHtml(html) {
    const tmp = document.createElement('div');
    tmp.innerHTML = html;
    return (tmp.textContent || tmp.innerText || '').replace(/\s+/g, ' ').trim();
  }

  async function ensureMeydaLoaded() {
      if (window.Meyda) {
        meyda = window.Meyda;
        return;
      }
    return new Promise((resolve, reject) => {
      try {
        const s = document.createElement('script');
        s.src = 'https://cdn.jsdelivr.net/npm/meyda@5.6.3/dist/web/meyda.min.js';
        s.async = true;
          s.onload = () => {
            meyda = window.Meyda;
            console.log('Meyda loaded');
            resolve();
          };
          s.onerror = (e) => {
            console.warn('Meyda load failed', e);
            reject(e);
          };
        document.head.appendChild(s);
        } catch (e) {
          reject(e);
        }
    });
  }

  function startMeydaAnalyzer() {
    try {
      if (!advancedLipsyncOn) return;
      if (!audioCtx || !mediaNode || !meyda || meydaAnalyzer) return;
        if (!meyda.isMeydaSupported(audioCtx)) {
          console.warn('Meyda not supported');
          return;
        }
      meydaAnalyzer = meyda.createMeydaAnalyzer({
        audioContext: audioCtx,
        source: mediaNode,
        bufferSize: 1024,
          featureExtractors: ['rms', 'zcr', 'spectralCentroid', 'mfcc'],
        callback: onMeydaFeatures,
      });
      meydaAnalyzer.start();
      console.log('Meyda analyzer started');
      } catch (e) {
        console.warn('startMeydaAnalyzer failed', e);
      }
  }

  function stopMeydaAnalyzer() {
    try {
        if (meydaAnalyzer) {
          meydaAnalyzer.stop();
          meydaAnalyzer = null;
        }
    } catch {}
  }

  function onMeydaFeatures(features) {
    try {
      if (!features) return;
        const rms = features.rms;
        const zcr = features.zcr;
        const sc = features.spectralCentroid;
        const mfcc = features.mfcc;

      // Heuristic viseme mapping:
      // - jawOpen by energy (rms)
      // - funnel/pucker by low centroid (rounded vowels)
      // - smile by higher centroid + certain MFCC patterns
        const jaw = Math.min(1, features.rms * 2.0);
        const funnel = Math.max(0, (1 - features.spectralCentroid / 4000) * 0.7 * features.rms);
        const pucker = Math.max(0, (1 - features.spectralCentroid / 4000) * 0.5 * features.rms);
        const smile = Math.max(0, (features.spectralCentroid / 4000 - 0.3) * 0.6 * features.rms);
        const close = Math.max(0, (0.2 - features.zcr) * 1.2);

      visemeTargets = {
        jawOpen: jaw,
        mouthFunnel: funnel,
        mouthPucker: pucker,
        mouthSmileL: smile * 0.7,
        mouthSmileR: smile * 0.7,
        mouthClose: close,
      };
      visemeActiveUntil = performance.now() + 90; // constantly extended while speaking
    } catch {}
  }

  function loadHumanoid() {
    try {
        const FBXLoaderCtor = window.FBXLoader;
        const GLTFLoaderCtor = window.GLTFLoader;
        if (!window.THREE || (!FBXLoaderCtor && !GLTFLoaderCtor)) {
          console.warn('Loader non presente. THREE:', !!window.THREE, 'FBXLoader:', !!FBXLoaderCtor, 'GLTFLoader:', !!GLTFLoaderCtor);
        return;
      }
        const fbxUrl = "{{ asset('images/Beautiful Model - Lisa.fbx') }}" + "?v=" + Date.now();
        const glbUrl = "{{ asset('images/lisa_-_woman_head_with_blendshapes.glb') }}" + "?v=" + Date.now();

        function attachHumanoid(root) {
          try {
            humanoid = root;
            humanoid.position.set(0, -0.3, 0);
            humanoid.scale.set(1.2, 1.2, 1.2);
            try {
              console.log('HUM attach', {
                pos: humanoid.position.toArray(),
                scale: humanoid.scale.toArray()
              });
            } catch {}
            scene.add(humanoid);
            fitCameraToObject(camera, humanoid, 1.4);
            visemeMeshes = [];
            humanoid.traverse((obj) => {
              const name = (obj.name || '').toLowerCase();
              // Seleziona bones rilevanti
              if (!jawBone && obj.type === 'Bone' && (name.includes('jaw') || name.includes('lowerjaw') || name.includes('mandible') || name.includes('mixamorigjaw'))) {
                jawBone = obj;
              }
              if (!headBone && obj.type === 'Bone' && (name === 'head' || name.includes('head'))) {
                headBone = obj;
              }
              if (!mouthLBone && obj.type === 'Bone' && name === 'mouth_l') {
                mouthLBone = obj;
              }
              if (!mouthRBone && obj.type === 'Bone' && name === 'mouth_r') {
                mouthRBone = obj;
              }
              // Log diagnostico: mesh e morph targets disponibili
              if (obj.isMesh) {
                try {
                  const keys = obj.morphTargetDictionary ? Object.keys(obj.morphTargetDictionary) : [];
                  console.log('Mesh:', obj.name, 'hasMorph:', !!obj.morphTargetDictionary, 'keys:', keys);
                } catch {}
              }
              // Se non ci sono morph, prova a rilevare la mandibola dal Skeleton
              if (!jawBone && obj.isSkinnedMesh && obj.skeleton && Array.isArray(obj.skeleton.bones)) {
                try {
                  const bones = obj.skeleton.bones;
                  // Log elenco ossa per debug
                  try {
                    console.log('Skeleton bones:', bones.map(b => b.name));
                  } catch {}
                  const toLower = (s) => String(s || '').toLowerCase();
                  const hasAny = (s, tokens) => {
                    const n = toLower(s);
                    return tokens.some(t => n.includes(t));
                  };
                  const jawTokens = ['jaw', 'lowerjaw', 'mandible', 'chin', 'facial_jaw', 'bip_c_jaw', '_jaw', 'jawbone', 'ctr_jaw', 'bn_jaw', 'j_jaw', 'mixamorig:jaw', 'mixamorigjaw'];
                  const mouthTokens = ['mouth', 'lowerlip', 'upperlip', 'lip'];
                  let candidate = bones.find(b => hasAny(b.name, jawTokens));
                  if (!candidate) candidate = bones.find(b => hasAny(b.name, mouthTokens) && (b.children?.length || 0) <= 2);
                  if (!candidate) {
                    const head = bones.find(b => /head|neck|face/i.test(b.name));
                    if (head && head.children && head.children.length) {
                      candidate = head.children.find(c => /jaw|mouth|chin|mandible/i.test(c.name)) || head.children[0];
                    }
                  }
                  if (candidate) {
                    jawBone = candidate;
                    console.log('Jaw bone trovato via Skeleton:', candidate.name);
                  }
                  const headCand = bones.find(b => /\bhead\b/i.test(b.name));
                  if (headCand && !headBone) headBone = headCand;
                } catch {}
              }
              if (obj.isMesh && obj.morphTargetDictionary && obj.morphTargetInfluences) {
                const dict = obj.morphTargetDictionary;
                // Mappa case-insensitive: keyLower -> index
                const lowerMap = {};
                try {
                  Object.keys(dict).forEach(k => {
                    lowerMap[String(k).toLowerCase()] = dict[k];
                  });
                } catch {}
                const findKeyCI = (cands) => {
                  for (const name of cands) {
                    const idx = lowerMap[String(name).toLowerCase()];
                    if (idx !== undefined) return idx;
                  }
                  return undefined;
                };
                const jawIdx = findKeyCI(['jawopen', 'jaw_open', 'viseme_aa', 'aa', 'base_jaw', 'j_open']);
                const funnelIdx = findKeyCI(['mouthfunnel', 'lipsfunnel', 'viseme_ou', 'ou', 'uw', 'ow', 'oh']);
                const puckerIdx = findKeyCI(['mouthpucker', 'lipspucker', 'pucker']);
                const smileLIdx = findKeyCI(['mouthsmile_l', 'smileleft', 'mouthsmileleft']);
                const smileRIdx = findKeyCI(['mouthsmile_r', 'smileright', 'mouthsmileright']);
                const closeIdx = findKeyCI(['mouthclose', 'lipsupperclose', 'lipslowerclose', 'viseme_mbp', 'mbp']);
                // Eye blink mapping (Renderpeople style)
                const eyeBlinkLIdx = findKeyCI(['eyeblink_l', 'eyeBlink_L', 'eyelidclose_l', 'eyelid_l']);
                const eyeBlinkRIdx = findKeyCI(['eyeblink_r', 'eyeBlink_R', 'eyelidclose_r', 'eyelid_r']);
                if ((jawIdx !== undefined) && (/wolf3d_head/i.test(obj.name) || !morphMesh)) {
                  morphMesh = obj;
                  morphIndex = jawIdx;
                  console.log('Morph target (jaw) trovato su', obj.name, 'index:', morphIndex);
                  // Cattura ulteriori target per visemi se presenti
                  try {
                    if (jawIdx !== undefined) visemeIndices.jawOpen = jawIdx;
                    if (funnelIdx !== undefined) visemeIndices.mouthFunnel = funnelIdx;
                    if (puckerIdx !== undefined) visemeIndices.mouthPucker = puckerIdx;
                    if (smileLIdx !== undefined) visemeIndices.mouthSmileL = smileLIdx;
                    if (smileRIdx !== undefined) visemeIndices.mouthSmileR = smileRIdx;
                    if (closeIdx !== undefined) visemeIndices.mouthClose = closeIdx;
                    // Alcuni avatar usano chiavi alternative
                    if (visemeIndices.mouthFunnel < 0 && lowerMap['viseme_ou'] !== undefined) visemeIndices.mouthFunnel = lowerMap['viseme_ou'];
                    if (visemeIndices.jawOpen < 0 && lowerMap['viseme_aa'] !== undefined) visemeIndices.jawOpen = lowerMap['viseme_aa'];
                    // Blink indices
                    if (eyeBlinkLIdx !== undefined || eyeBlinkRIdx !== undefined) {
                      eyeMesh = obj;
                      if (eyeBlinkLIdx !== undefined) eyeIndices.eyeBlinkLeft = eyeBlinkLIdx;
                      if (eyeBlinkRIdx !== undefined) eyeIndices.eyeBlinkRight = eyeBlinkRIdx;
                    }
                  } catch {}
                } else if (!morphMesh) {
                  const altIdx = findKeyCI(['jawopen', 'mouthopen', 'viseme_aa', 'aa']);
                  if (altIdx !== undefined) {
                    morphMesh = obj;
                    morphIndex = altIdx;
                    console.log('Morph target (alt jaw) su', obj.name, 'index:', morphIndex);
                  }
                }
                // Registra mesh con almeno un target labiale
                try {
                  const indices = {
                    jawOpen: (jawIdx !== undefined ? jawIdx : -1),
                    mouthFunnel: (funnelIdx !== undefined ? funnelIdx : -1),
                    mouthPucker: (puckerIdx !== undefined ? puckerIdx : -1),
                    mouthSmileL: (smileLIdx !== undefined ? smileLIdx : -1),
                    mouthSmileR: (smileRIdx !== undefined ? smileRIdx : -1),
                    mouthClose: (closeIdx !== undefined ? closeIdx : -1),
                  };
                  // Prova a mappare blink se presenti
                  if (eyeMesh === null) {
                    if (dict['eyeBlinkLeft'] !== undefined || dict['eyeBlinkRight'] !== undefined || eyeBlinkLIdx !== undefined || eyeBlinkRIdx !== undefined) {
                    eyeMesh = obj;
                    if (dict['eyeBlinkLeft'] !== undefined) eyeIndices.eyeBlinkLeft = dict['eyeBlinkLeft'];
                    if (dict['eyeBlinkRight'] !== undefined) eyeIndices.eyeBlinkRight = dict['eyeBlinkRight'];
                      if (eyeBlinkLIdx !== undefined) eyeIndices.eyeBlinkLeft = eyeBlinkLIdx;
                      if (eyeBlinkRIdx !== undefined) eyeIndices.eyeBlinkRight = eyeBlinkRIdx;
                    }
                  }
                  if (Object.values(indices).some(v => v !== -1)) {
                    visemeMeshes.push({
                      mesh: obj,
                      indices
                    });
                    console.log('Viseme mesh registered:', obj.name, indices);
                  }
                } catch {}
              }
            });
            // Zoom/centering sul volto usando headBone se disponibile
            try {
              if (headBone) {
                const p = new THREE.Vector3();
                headBone.getWorldPosition(p);
                // Target leggermente sotto l'osso head per includere fronte+occhi+bocca
                const t = p.clone();
                t.y -= 0.06;
                // Distanza/FOV stabili (overridable via URL)
                const defaultDist = 1.45;
                const dist = (isFinite(headDistParam) && headDistParam > 0.2) ? headDistParam : defaultDist;
                const fov = (isFinite(headFovParam) && headFovParam >= 20 && headFovParam <= 70) ? headFovParam : 38;
                camera.position.set(p.x, t.y + 0.02, p.z + dist);
                camera.fov = fov;
                camera.lookAt(t);
                camera.updateProjectionMatrix();
                try {
                  console.log('CAM headBone target', {
                    pos: camera.position.toArray(),
                    fov: camera.fov,
                    target: t.toArray(),
                    dist
                  });
                } catch {}
                // Verifica che ruotare jawBone influisca: piccola prova e restore
                try {
                  if (jawBone) {
                    const prev = jawBone.rotation.x;
                    jawBone.rotation.x += 0.02;
                    humanoid.updateMatrixWorld(true);
                    const before = new THREE.Box3().setFromObject(humanoid).getSize(new THREE.Vector3());
                    jawBone.rotation.x = prev;
                    humanoid.updateMatrixWorld(true);
                    const after = new THREE.Box3().setFromObject(humanoid).getSize(new THREE.Vector3());
                    jawBoneHasInfluence = (Math.abs(before.y - after.y) > 1e-4 || Math.abs(before.x - after.x) > 1e-4);
                    if (debugEnabled) console.log('jawBoneHasInfluence', jawBoneHasInfluence);
                  }
                } catch {}
              } else {
                // Nessun head bone: usa fitCameraToObject stabile
              let headMesh = null;
                humanoid.traverse((obj) => {
                  if (!headMesh && (/wolf3d_head/i.test(obj.name) || /head$/i.test(obj.name))) headMesh = obj;
                });
                if (headMesh) {
                  fitCameraToObject(camera, headMesh, 1.2);
                } else {
                  fitCameraToObject(camera, humanoid, 1.35);
                }
                try {
                  console.log('CAM fallback fit');
                } catch {}
              }
            } catch {}
            // Nessun fallback a SkinnedMesh per il jawBone: usa solo morph targets se il bone non esiste
            head.visible = false;
            jaw.visible = false;
          } catch {}
        }

        function loadWithFBX() {
          return new Promise((resolve, reject) => {
            try {
              const loader = new FBXLoaderCtor();
              console.log('Carico humanoid FBX da', fbxUrl);
              loader.load(fbxUrl, (obj) => {
                // Modello Z-up: ruota in Y-up per Three.js
                try {

                  obj.updateMatrixWorld(true);
                } catch {}
                attachHumanoid(obj);
                resolve(true);
              }, undefined, (err) => {
                console.warn('Impossibile caricare FBX', err);
                reject(err);
              });
        } catch (e) {
              reject(e);
            }
          });
        }

        function loadWithGLTF() {
          return new Promise((resolve, reject) => {
            try {
              if (!GLTFLoaderCtor) {
                reject(new Error('GLTFLoader non disponibile'));
                return;
              }
              const loader = new GLTFLoaderCtor();
              console.log('Carico humanoid GLB da', glbUrl);
              loader.load(glbUrl, (gltf) => {
                attachHumanoid(gltf.scene);
                resolve(true);
              }, undefined, (err) => {
                console.warn('Impossibile caricare GLB', err);
                reject(err);
              });
            } catch (e) {
              reject(e);
            }
          });
        }

        (async () => {
          try {
            if (FBXLoaderCtor) {
              await loadWithFBX();
              return;
            }
          } catch {}
          try {
            await loadWithGLTF();
          } catch (e) {
            console.warn('Nessun modello caricato', e);
          }
        })();
    } catch (e) {
      console.warn('Errore loadHumanoid()', e);
    }
  }

  function fitCameraToObject(camera, object, offset = 1.25) {
      try {
    const box = new THREE.Box3().setFromObject(object);
    const size = box.getSize(new THREE.Vector3());
    const center = box.getCenter(new THREE.Vector3());
        const aspect = (camera.aspect && isFinite(camera.aspect)) ? camera.aspect : 1;
        const vFOV = THREE.MathUtils.degToRad(camera.fov);
        const hFOV = 2 * Math.atan(Math.tan(vFOV / 2) * aspect);
        const distV = (size.y / 2) / Math.tan(vFOV / 2);
        const distH = (size.x / 2) / Math.tan(hFOV / 2);
        let dist = Math.max(distV, distH) * (offset || 1);
        // Mantieni direzione corrente della camera verso l'oggetto
        const dir = new THREE.Vector3().subVectors(camera.position, center).normalize();
        if (!isFinite(dir.lengthSq()) || dir.lengthSq() === 0) dir.set(0, 0, 1);
        camera.position.copy(center.clone().add(dir.multiplyScalar(dist)));
        // near/far robusti
        camera.near = Math.max(0.01, dist / 100);
        camera.far = Math.max(camera.near + 1, dist * 10 + size.length());
    camera.lookAt(center);
    camera.updateProjectionMatrix();
        if (window.__orbit && window.__orbit.target) {
          try {
            window.__orbit.target.copy(center);
            window.__orbit.update();
          } catch {}
        }

        // =========================
        // Enjoy3D API (Facade)
        // Organizzazione in moduli per una lettura ordinata del codice
        // Pattern: Facade + Controller groupings (scene/model/lipsync/tts/ui/debug)
        // =========================
        // Controller modulari non più usati: definiamo proxy inline

        try {
          // Import dinamici rimossi: tutto è inline in questo file

          window.Enjoy3D = {
            scene: {
              initThree,
              setupScene,
              onResize,
              animate,
              fitCameraToObject,
            },
            model: {
              loadHumanoid,
            },
            lipsync: {
              onMeydaFeatures,
              ensureMeydaLoaded,
              startMeydaAnalyzer,
              stopMeydaAnalyzer,
            },
            tts: {
              sendToTts,
              processTtsQueue,
              playNextInQueue,
              sendToTtsIfNew,
            },
            ui: {
              initDebugOverlay,
            },
            state: {
              get camera() {
                return camera;
              },
              get scene() {
                return scene;
              },
              get renderer() {
                return renderer;
              },
              get humanoid() {
                return humanoid;
              },
              get visemeMeshes() {
                return visemeMeshes;
              },
            },
            // Controller istanziati
            controllers: {
              scene: SceneController ? new SceneController({ fitCameraToObject }) : undefined,
              model: {
                loadHumanoid() {
                  try {
                    if (window.Enjoy3D && window.Enjoy3D.model && typeof window.Enjoy3D.model.loadHumanoid === 'function') {
                      return window.Enjoy3D.model.loadHumanoid();
                    }
                  } catch {}
                },
              },
              lipsync: {
                ensureMeydaLoaded() {
                  try {
                    if (window.Enjoy3D && window.Enjoy3D.lipsync && typeof window.Enjoy3D.lipsync.ensureMeydaLoaded === 'function') {
                      return window.Enjoy3D.lipsync.ensureMeydaLoaded();
                    }
                  } catch {}
                },
                startMeydaAnalyzer() {
                  try {
                    if (window.Enjoy3D && window.Enjoy3D.lipsync && typeof window.Enjoy3D.lipsync.startMeydaAnalyzer === 'function') {
                      return window.Enjoy3D.lipsync.startMeydaAnalyzer();
                    }
                  } catch {}
                },
                stopMeydaAnalyzer() {
                  try {
                    if (window.Enjoy3D && window.Enjoy3D.lipsync && typeof window.Enjoy3D.lipsync.stopMeydaAnalyzer === 'function') {
                      return window.Enjoy3D.lipsync.stopMeydaAnalyzer();
                    }
                  } catch {}
                },
                onMeydaFeatures(features) {
                  try {
                    if (window.Enjoy3D && window.Enjoy3D.lipsync && typeof window.Enjoy3D.lipsync.onMeydaFeatures === 'function') {
                      return window.Enjoy3D.lipsync.onMeydaFeatures(features);
                    }
                  } catch {}
                },
              },
              tts: {
                send(text) {
                  try { return window.Enjoy3D && window.Enjoy3D.tts && window.Enjoy3D.tts.sendToTts ? window.Enjoy3D.tts.sendToTts(text) : undefined; } catch {}
                },
                processQueue() {
                  try { return window.Enjoy3D && window.Enjoy3D.tts && window.Enjoy3D.tts.processTtsQueue ? window.Enjoy3D.tts.processTtsQueue() : undefined; } catch {}
                },
                playNext() {
                  try { return window.Enjoy3D && window.Enjoy3D.tts && window.Enjoy3D.tts.playNextInQueue ? window.Enjoy3D.tts.playNextInQueue() : undefined; } catch {}
                },
              },
              ui: {
                initDebugOverlay() {
                  try { return window.Enjoy3D && window.Enjoy3D.ui && window.Enjoy3D.ui.initDebugOverlay ? window.Enjoy3D.ui.initDebugOverlay() : undefined; } catch {}
                },
              },
            },
          };
          console.log('Enjoy3D API ready:', Object.keys(window.Enjoy3D));
        } catch {}
        try {
          console.log('CAM fitCameraToObject', {
            pos: camera.position.toArray(),
            fov: camera.fov,
            center: center.toArray(),
            size: size.toArray(),
            dist,
            aspect
          });
        } catch {}
      } catch (e) {
        console.warn('fitCameraToObject error', e);
      }
    }
  });
</script>
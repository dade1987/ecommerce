<div class="flex flex-col min-h-[100dvh] w-full bg-[#0f172a] pb-[96px] sm:pb-0">
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
    const { GLTFLoader } = await import('https://esm.sh/three@0.160.0/examples/jsm/loaders/GLTFLoader.js');
    window.THREE = THREE_mod;
    window.GLTFLoader = GLTFLoader;
    console.log('Three+GLTFLoader via esm.sh');
    return true;
  } catch (e) {
    try {
      const THREE_mod = await import('https://unpkg.com/three@0.160.0/build/three.module.js');
      const { GLTFLoader } = await import('https://unpkg.com/three@0.160.0/examples/jsm/loaders/GLTFLoader.js?module');
      window.THREE = THREE_mod;
      window.GLTFLoader = GLTFLoader;
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

<script>
document.addEventListener('DOMContentLoaded', async function() {
  // Attendi il preload dei moduli
  try { if (window.THREE_READY && window.THREE_READY.then) { await Promise.race([window.THREE_READY, new Promise(r => setTimeout(r, 3000))]); } } catch {}
  const sendBtn = document.getElementById('sendBtn');
  const micBtn = document.getElementById('micBtn');
  const input = document.getElementById('textInput');
  const liveText = document.getElementById('liveText');
  const ttsPlayer = document.getElementById('ttsPlayer');
  const thinkingBubble = document.getElementById('thinkingBubble');
  const useBrowserTts = document.getElementById('useBrowserTts');
  const browserTtsStatus = document.getElementById('browserTtsStatus');
  const useAdvancedLipsync = document.getElementById('useAdvancedLipsync');
  const teamSlug = window.location.pathname.split('/').pop();
  const urlParams = new URLSearchParams(window.location.search);
  const uuid = urlParams.get('uuid');
  const locale = '{{ app()->getLocale() }}';
  const debugEnabled = urlParams.get('debug') === '1';
  const ua = navigator.userAgent || '';
  const isAndroid = /Android/i.test(ua);
  const isChrome = !!window.chrome && /Chrome\/\d+/.test(ua) && !/Edg\//.test(ua) && !/OPR\//.test(ua) && !/Brave/i.test(ua);
  const urlLang = (urlParams.get('lang') || '').trim();
  function normalizeLangTag(tag, fallback) {
    try {
      const t = (tag || '').replace('_','-').trim();
      if (!t) return fallback;
      // Normalizza in BCP47 semplice (xx-YY)
      const parts = t.split('-');
      if (parts.length === 1) return parts[0].toLowerCase();
      return parts[0].toLowerCase() + '-' + parts[1].toUpperCase();
    } catch { return fallback; }
  }
  const navLang = (navigator.language || (navigator.languages && navigator.languages[0]) || '').trim();
  const rawLang = (urlLang || locale || navLang || 'it-IT');
  let recLang = normalizeLangTag(rawLang, 'it-IT');
  // Forza italiano se il tag in ingresso indica italiano (alcuni device rifiutano 'it')
  try { if (/^it(\b|[-_])/i.test(rawLang) || rawLang.toLowerCase() === 'it') recLang = 'it-IT'; } catch {}
  let threadId = null;
  let assistantThreadId = null;
  let humanoid = null, jawBone = null;
  let morphMesh = null, morphIndex = -1, morphValue = 0;
  // Viseme support (browser TTS only): indices for relevant morph targets
  const visemeIndices = { jawOpen: -1, mouthFunnel: -1, mouthPucker: -1, mouthSmileL: -1, mouthSmileR: -1, mouthClose: -1 };
  let visemeActiveUntil = 0;
  let visemeStrength = 0; // 0..1 current blend magnitude
  // Stato precedente per smoothing e deadband
  let lastVisemes = { jawOpen: 0, mouthFunnel: 0, mouthPucker: 0, mouthSmileL: 0, mouthSmileR: 0, mouthClose: 0 };
  const deadband = { jawOpen: 0.02, mouthFunnel: 0.02, mouthPucker: 0.02, mouthSmileL: 0.02, mouthSmileR: 0.02, mouthClose: 0.02 };
  let visemeTargets = { jawOpen: 0, mouthFunnel: 0, mouthPucker: 0, mouthSmileL: 0, mouthSmileR: 0, mouthClose: 0 };
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
  const eyeIndices = { eyeBlinkLeft: -1, eyeBlinkRight: -1 };
  let eyeMesh = null;
  let nextBlinkAt = performance.now() + 1200 + Math.random() * 2000;
  let blinkPhase = 0; // 0..1 (chiusura-apertura)
  // Config lipsync: separazione minima e limiti di chiusura
  const lipConfig = {
    restJawOpen: 0.12,                 // apertura a riposo
    minLipSeparation: 0.07,            // separazione minima obbligatoria
    maxMouthClose: 0.35,               // limite massimo di chiusura
    closeThresholdForSeparation: 0.2,  // oltre questa chiusura, forza separazione minima
    visemeStrengthAlpha: 0.15,         // velocità salita visemi (più basso = più lento)
    morphSmoothingBeta: 0.16,          // smoothing dei morph target (più basso = più lento)
    jawSmoothingAlpha: 0.12,           // smoothing per bone/geom jaw (più basso = più lento)
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
      const dur = (typeof totalDurationMs === 'number' && isFinite(totalDurationMs) && totalDurationMs > 200)
        ? Math.max(40, totalDurationMs / chars)
        : baseDur;
      let accEnd = (visemeSchedule.length > 0 ? visemeSchedule[visemeSchedule.length - 1].end : start);
      for (const chRaw of clean) {
        const ch = chRaw.normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLowerCase();
        const t = { jawOpen: 0, mouthFunnel: 0, mouthPucker: 0, mouthSmileL: 0, mouthSmileR: 0, mouthClose: 0 };
        if ('ou'.includes(ch)) { t.mouthFunnel = 1.0; t.mouthPucker = 0.85; t.jawOpen = 0.2; }
        else if (ch === 'a') { t.jawOpen = 0.6; }
        else if ('ei'.includes(ch)) { t.mouthSmileL = 0.55; t.mouthSmileR = 0.55; t.jawOpen = 0.35; }
        else if ('bmp'.includes(ch)) { t.mouthClose = 0.9; }
        else if (/[.,;:!?]/.test(ch)) { t.mouthClose = 0.6; }
        else { t.jawOpen = 0.25; }
        const charDur = /[.,;:!?]/.test(ch) ? dur * 1.6 : (ch === ' ' ? dur * 0.6 : dur);
        const s = accEnd;
        const e = s + charDur;
        visemeSchedule.push({ start: s, end: e, targets: t });
        accEnd = e;
      }
      // limita schedule per evitare code troppo lunghe
      if (visemeSchedule.length > 120) visemeSchedule = visemeSchedule.slice(-120);
    } catch {}
  }
  let isListening = false, recognition = null, mediaMicStream = null;
  let currentEvtSource = null; // Stream SSE attivo da chiudere se necessario
  let isStartingStream = false;
  
  // Three.js avatar minimale (testa + mandibola)
  let THREELoaded = false;
  let scene, camera, renderer, head, jaw, animationId, analyser, dataArray, audioCtx, mediaNode;
  let meyda = null, meydaAnalyzer = null;
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

  // Debug overlay wiring
  const debugOverlay = document.getElementById('debugOverlay');
  const debugContent = document.getElementById('debugContent');
  const debugCloseBtn = document.getElementById('debugClose');
  const debugClearBtn = document.getElementById('debugClear');
  const debugCopyBtn = document.getElementById('debugCopy');
  const originalConsole = { log: console.log, warn: console.warn, error: console.error, info: console.info };

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
      try { return String(arg); } catch { return '[unserializable]'; }
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
    try { debugContent.parentElement.scrollTop = debugContent.parentElement.scrollHeight; } catch {}
  }

  function initDebugOverlay() {
    if (!debugEnabled) return;
    try { debugOverlay?.classList.remove('hidden'); } catch {}
    try {
      debugCloseBtn?.addEventListener('click', () => { debugOverlay.classList.add('hidden'); });
      debugClearBtn?.addEventListener('click', () => { if (debugContent) debugContent.innerHTML = ''; });
      debugCopyBtn?.addEventListener('click', async () => {
        try {
          const lines = Array.from(debugContent?.children || []).map(n => (n.textContent || ''));
          const text = lines.join('\n');
          if (navigator.clipboard && navigator.clipboard.writeText) {
            await navigator.clipboard.writeText(text);
          } else {
            const ta = document.createElement('textarea');
            ta.value = text; document.body.appendChild(ta); ta.select(); document.execCommand('copy'); document.body.removeChild(ta);
          }
          console.log('DEBUG: logs copied', { lines: lines.length });
        } catch (e) {
          console.error('DEBUG: copy failed', e);
        }
      });
    } catch {}
    // Mirror console methods
    try {
      ['log','warn','error','info'].forEach((m) => {
        console[m] = function(...a) {
          try { appendDebugLine(m, a); } catch {}
          try { originalConsole[m].apply(console, a); } catch {}
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
    appendDebugLine('info', ['Debug overlay enabled', { ua: navigator.userAgent, locale, recLang, isAndroid, isChrome, hasWebSpeech: !!(window.SpeechRecognition||window.webkitSpeechRecognition) }]);
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

  // Auto-spunta TTS del browser su Chrome se disponibile
  try {
    const ua = navigator.userAgent || '';
    const isChrome = !!window.chrome && /Chrome\/\d+/.test(ua) && !/Edg\//.test(ua) && !/OPR\//.test(ua) && !/Brave/i.test(ua);
    // Default: abilita automaticamente TTS del browser
    if (isChrome && 'speechSynthesis' in window && useBrowserTts) {
      useBrowserTts.checked = true;
      browserTtsStatus.textContent = 'TTS browser attivo (Chrome)';
    }
    // Abilita di default LipSync avanzato su Chrome o in debug
    if ((isChrome || debugEnabled) && useAdvancedLipsync) {
      useAdvancedLipsync.checked = true;
      advancedLipsyncOn = true;
      ensureMeydaLoaded().then(() => startMeydaAnalyzer()).catch(() => {});
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
    if (isStartingStream) { console.warn('SSE: start already in progress'); return; }
    isStartingStream = true;
    setTimeout(() => { isStartingStream = false; }, 800);
    
    console.log('TTS: Starting new conversation, resetting state');
    try { console.log('SSE: connecting', { team: teamSlug, uuid, locale }); } catch {}
    
    // Chat history rimossa: nessun messaggio renderizzato, manteniamo solo TTS
    
    // Mostra fumetto "Sto pensando..."
    thinkingBubble.classList.remove('hidden');
    
    // Chiudi eventuale stream precedente e resetta per nuova conversazione
    try { if (currentEvtSource) { currentEvtSource.close(); currentEvtSource = null; } } catch {}
    bufferText = '';
    ttsBuffer = '';
    lastSentToTts = '';
    lastSpokenTail = '';
    ttsProcessedLength = 0;
    ttsFirstChunkSent = false;
    if (ttsKickTimer) { try { clearTimeout(ttsKickTimer); } catch {} ttsKickTimer = null; }
    if (ttsTick) { try { clearInterval(ttsTick); } catch {} ttsTick = null; }
    
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
    const params = new URLSearchParams({ message, team: teamSlug, uuid: uuid || '', locale, ts: String(Date.now()) });
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
        try { checkForTtsChunks(); } catch {}
      }, 120);
    }
    function bindSse() {
      evtSource.addEventListener('message', (e) => {
        try {
          const data = JSON.parse(e.data);
          if (data.token) {
            try {
              const tok = JSON.parse(data.token);
              if (tok && tok.thread_id) { threadId = tok.thread_id; return; }
              if (tok && tok.assistant_thread_id) { assistantThreadId = tok.assistant_thread_id; return; }
            } catch {}
            if (firstToken) {
              firstToken = false;
              thinkingBubble.classList.add('hidden');
              if (ttsKickTimer) { try { clearTimeout(ttsKickTimer); } catch {} }
              ttsKickTimer = null;
              // Abbiamo ricevuto dati: annulla watchdog di connessione
              if (sseConnectWatchdog) { clearTimeout(sseConnectWatchdog); sseConnectWatchdog = null; }
            }
            collected += data.token;
            ttsBuffer += data.token;
            checkForTtsChunks();
          }
        } catch (msgErr) { console.warn('Message parse error:', msgErr); }
      });
      evtSource.addEventListener('error', () => {
        const state = evtSource.readyState; // 0=CONNECTING,1=OPEN,2=CLOSED
        try {
          if (state === 2) {
            console.error('SSE: closed', { attempt: sseRetryCount + 1, readyState: state });
          } else {
            console.warn('SSE: transient error', { attempt: sseRetryCount + 1, readyState: state });
          }
        } catch {}
        // Se non è CLOSED e non abbiamo finito, lascia che il browser gestisca la riconnessione
        if (state !== 2 && !done) { return; }
        try { evtSource.close(); } catch {}
        currentEvtSource = null;
        if (sseConnectWatchdog) { try { clearTimeout(sseConnectWatchdog); } catch {} sseConnectWatchdog = null; }
        // Retry se nessun token ricevuto
        if (!done && collected.length === 0 && sseRetryCount < 2) {
          sseRetryCount++;
          const delay = 220 * sseRetryCount;
          setTimeout(() => { openSse(); }, delay);
          return;
        }
        // Cleanup
        thinkingBubble.classList.add('hidden');
        if (ttsTick) { try { clearInterval(ttsTick); } catch {} ttsTick = null; }
      });
      evtSource.addEventListener('done', () => {
        try { evtSource.close(); } catch {}
        done = true;
        thinkingBubble.classList.add('hidden');
        try { console.log('SSE: done event received'); } catch {}
        if (sseConnectWatchdog) { try { clearTimeout(sseConnectWatchdog); } catch {} sseConnectWatchdog = null; }
        if (ttsBuffer.trim().length > 0) {
          const remainingText = stripHtml(ttsBuffer).trim();
          if (remainingText.length > 0) { console.log('TTS: Sending remaining text:', remainingText.substring(0, 50) + '...'); sendToTts(remainingText); }
          ttsBuffer = '';
        }
        if (ttsTick) { try { clearInterval(ttsTick); } catch {} ttsTick = null; }
      });
    }
    function openSse() {
      try { if (currentEvtSource) currentEvtSource.close(); } catch {}
      evtSource = new EventSource(`/api/chatbot/stream?${params.toString()}`);
      currentEvtSource = evtSource;
      try { console.log('SSE: connecting', { team: teamSlug, uuid, locale, threadId, assistantThreadId }); } catch {}
      bindSse();
      // Watchdog solo su Android: se non arrivano token dopo un po', ritenta
      if (isAndroid) {
        if (sseConnectWatchdog) { clearTimeout(sseConnectWatchdog); }
        sseConnectWatchdog = setTimeout(() => {
          try {
            const state = evtSource.readyState; // 0/1/2
            if (collected.length === 0 && !done && sseRetryCount < 2 && (state === 0 || state === 2)) {
              sseRetryCount++;
              try { evtSource.close(); } catch {}
              currentEvtSource = null;
              const delay = 280 * sseRetryCount;
              console.warn('SSE: connect watchdog retry', { attempt: sseRetryCount });
              setTimeout(() => { openSse(); }, delay);
            }
          } finally {
            try { clearTimeout(sseConnectWatchdog); } catch {}
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
      if (!audioCtx) audioCtx = new (window.AudioContext || window.webkitAudioContext)();
      if (audioCtx.state === 'suspended') await audioCtx.resume();
    } catch {}
    startStream(input.value);
    input.value = '';
  });

  input.addEventListener('keyup', async (e) => {
    if (e.key === 'Enter') {
      try {
        if (!audioCtx) audioCtx = new (window.AudioContext || window.webkitAudioContext)();
        if (audioCtx.state === 'suspended') await audioCtx.resume();
      } catch {}
      startStream(input.value);
      input.value = '';
    }
  });

  async function stopAllSpeechOutput() {
    try { if (window.speechSynthesis) window.speechSynthesis.cancel(); } catch {}
    try {
      if (ttsPlayer && !ttsPlayer.paused) { ttsPlayer.pause(); ttsPlayer.currentTime = 0; }
    } catch {}
    speakQueue.forEach(item => { if (item.url) try { URL.revokeObjectURL(item.url); } catch {} });
    speakQueue = []; ttsRequestQueue = []; isSpeaking = false;
  }

  function setListeningUI(active) {
    const badge = document.getElementById('listeningBadge');
    if (active) {
      badge.classList.remove('hidden');
      micBtn.classList.remove('bg-rose-600');
      micBtn.classList.add('bg-emerald-600','ring-2','ring-emerald-400','animate-pulse');
    } else {
      badge.classList.add('hidden');
      micBtn.classList.add('bg-rose-600');
      micBtn.classList.remove('bg-emerald-600','ring-2','ring-emerald-400','animate-pulse');
    }
  }

  async function ensureMicPermission() {
    try {
      if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) return true; // Non bloccare se non disponibile
      // Chiedi il permesso in anticipo; chiuderemo subito lo stream
      if (!mediaMicStream) {
        console.log('MIC: requesting permission');
        mediaMicStream = await navigator.mediaDevices.getUserMedia({ audio: { echoCancellation: true } });
        // Rilascia subito
        try { mediaMicStream.getTracks().forEach(t => t.stop()); } catch {}
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
      try { recognition.stop(); recognition.abort && recognition.abort(); } catch {}
      isListening = false; setListeningUI(false);
      console.log('MIC: listening stopped by user');
      return;
    }
    // Ferma eventuale parlato e stream corrente
    await stopAllSpeechOutput();
    try { if (currentEvtSource) { currentEvtSource.close(); currentEvtSource = null; } } catch {}

    // Sblocca l'audio (Android)
    try {
      if (!audioCtx) audioCtx = new (window.AudioContext || window.webkitAudioContext)();
      if (audioCtx.state === 'suspended') { await audioCtx.resume(); console.log('AUDIO: context resumed'); }
    } catch (e) { console.warn('AUDIO: failed to init/resume', e); }

    // Verifica permesso microfono (Android spesso non mostra prompt con WebSpeech)
    const ok = await ensureMicPermission();
    if (!ok) { alert('Permesso microfono negato. Abilitalo nelle impostazioni del browser.'); return; }

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
      function clearRecWatchdog() { if (recognitionWatchdogTimer) { clearTimeout(recognitionWatchdogTimer); recognitionWatchdogTimer = null; } }
      function startRecWatchdog() {
        clearRecWatchdog();
        recognitionWatchdogTimer = setTimeout(() => {
          console.warn('SPEECH: watchdog timeout (no result), restarting');
          try { recognition.stop(); recognition.abort && recognition.abort(); } catch {}
          setTimeout(() => { try { recognition.start(); console.log('SPEECH: restarted by watchdog'); } catch (e) { console.error('SPEECH: restart failed', e); } }, 250);
        }, 8000);
      }
      recognition.onstart = async () => {
        isListening = true; setListeningUI(true); console.log('SPEECH: onstart');
        // Sospendi output audio per evitare conflitti con input mic su Android
        try { if (audioCtx && audioCtx.state === 'running') { await audioCtx.suspend(); console.log('AUDIO: context suspended for recognition'); } } catch (e) { console.warn('AUDIO: suspend failed', e); }
      };
      recognition.onaudiostart = () => { console.log('SPEECH: onaudiostart'); };
      recognition.onsoundstart = () => { console.log('SPEECH: onsoundstart'); };
      recognition.onspeechstart = () => { console.log('SPEECH: onspeechstart'); startRecWatchdog(); };
      recognition.onspeechend = () => { console.log('SPEECH: onspeechend'); clearRecWatchdog(); };
      recognition.onsoundend = () => { console.log('SPEECH: onsoundend'); };
      recognition.onaudioend = () => { console.log('SPEECH: onaudioend'); };
      recognition.onnomatch = (e) => { console.warn('SPEECH: onnomatch', e && e.message || ''); };
      recognition.onerror = async (e) => {
        console.error('SPEECH: onerror', e && (e.error || e.message) || e); isListening = false; setListeningUI(false); clearRecWatchdog();
        try { if (audioCtx && audioCtx.state === 'suspended') { await audioCtx.resume(); console.log('AUDIO: context resumed after error'); } } catch (er) { console.warn('AUDIO: resume after error failed', er); }
      };
      recognition.onend = async () => {
        isListening = false; setListeningUI(false); console.log('SPEECH: onend'); clearRecWatchdog();
        try { if (audioCtx && audioCtx.state === 'suspended') { await audioCtx.resume(); console.log('AUDIO: context resumed after end'); } } catch (er) { console.warn('AUDIO: resume after end failed', er); }
      };
      recognition.onresult = (event) => {
        try {
          lastResultAt = Date.now();
          const res = event.results;
          const last = res[res.length - 1];
          const transcript = last[0].transcript;
          const isFinal = last.isFinal === true || !recognition.interimResults;
          console.log('SPEECH: onresult', { transcript, isFinal, resultIndex: event.resultIndex, length: res.length });
          if (debugEnabled && liveText) {
            liveText.classList.remove('hidden');
            liveText.textContent = transcript + (isFinal ? '' : ' …');
          }
          if (isFinal) {
            isListening = false; setListeningUI(false);
            if (debugEnabled && liveText) setTimeout(() => { try { liveText.classList.add('hidden'); liveText.textContent = ''; } catch {} }, 800);
            const safe = (transcript || '').trim();
            if (!safe) { console.warn('SPEECH: final transcript empty, not starting stream'); return; }
            if (isAndroid) { setTimeout(() => startStream(safe), 220); } else { startStream(safe); }
          }
        } catch (err) {
          console.error('SPEECH: onresult handler failed', err);
        }
      };
      console.log('SPEECH: start()', { lang: recognition.lang });
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
      s.onload = () => { THREELoaded = true; setupScene(); };
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
      width = 800; height = 450; // fallback quando i CSS non sono caricati
      stage.style.width = width + 'px';
      stage.style.height = height + 'px';
    }

    scene = new THREE.Scene();
    scene.background = new THREE.Color('#0f172a');

    camera = new THREE.PerspectiveCamera(35, width / height, 0.1, 100);
    camera.position.set(0, 0.5, 3);

    renderer = new THREE.WebGLRenderer({ antialias: true });
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
    const headMat = new THREE.MeshStandardMaterial({ color: 0x8fa7ff, roughness: 0.6, metalness: 0.0 });
    head = new THREE.Mesh(headGeom, headMat);
    head.position.y = 0.2;
    scene.add(head);

    // Mandibola semplice (box)
    const jawGeom = new THREE.BoxGeometry(0.8, 0.25, 0.6);
    const jawMat = new THREE.MeshStandardMaterial({ color: 0x9bb0ff, roughness: 0.6 });
    jaw = new THREE.Mesh(jawGeom, jawMat);
    jaw.position.y = -0.25;
    jaw.position.z = 0.0;
    // Punto di rotazione attorno alla "cerniera"
    jaw.geometry.translate(0, 0.12, 0);
    scene.add(jaw);

    console.log('setupScene: start, THREE present =', !!window.THREE);
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
      width = 800; height = 450;
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
    animationId = requestAnimationFrame(animate);
    // Idle breathing
    head.position.y = 0.2 + Math.sin(performance.now() / 1200) * 0.01;

    // Se l'audio sta suonando, usa l'ampiezza per aprire la mandibola
    let amp = 0;
    if (useBrowserTts && useBrowserTts.checked && 'speechSynthesis' in window && window.speechSynthesis.speaking) {
      // Forza uso audio cloud o WebAudio per lipsync: se abbiamo analyser, usalo
      if (analyser && dataArray) {
        // prosegui sotto con branch analyser
      } else {
        amp = 0; // nessun drive senza analyser
      }
    } 
    if (analyser && dataArray) {
      // Time-domain RMS for jaw openness
      analyser.getByteTimeDomainData(dataArray);
      let sum = 0, zc = 0, prev = 0;
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
        let num = 0, den = 0;
        const sr = (audioCtx && audioCtx.sampleRate) ? audioCtx.sampleRate : 44100;
        const nyquist = sr / 2;
        const binHz = nyquist / freqData.length;
        for (let i = 0; i < freqData.length; i++) {
          const mag = freqData[i];
          if (mag <= 0) continue;
          const f = i * binHz;
          num += f * mag;
          den += mag;
        }
        const centroid = den > 0 ? (num / den) : 0;
        const normC = Math.max(0, Math.min(1, centroid / 3500));
        const zcr = zc / dataArray.length; // ~0..0.5
        // Heuristics for visemes (rounded vs wide vowels, closures)
        // Feature mapping più selettivo
        const rounded = Math.max(0, 1.0 - normC);       // vocali tonde (O/U)
        const wide = Math.max(0, normC - 0.32);         // vocali larghe (E/I)
        const closeLike = Math.max(0, (0.05 - zcr) * 9); // chiusure/pausa
        const lowAmp = amp < 0.08;
        // Stima banda centrale (A) ~ centroid medio
        const midBand = Math.max(0, 1 - Math.abs(normC - 0.28) / 0.22); // picco ~0.28
        const MAX_JAW_OPEN = 0.60;
        const jawVal = Math.min(
          MAX_JAW_OPEN,
          Math.max(0.08, 1.35 * amp + 0.45 * midBand - 0.30 * rounded)
        );
        // Rafforza O/U ma riduci se sembra A (midBand alto) per non tornare in O
        const roundBoost = rounded * (1 - 0.6 * midBand);
        const funnelVal = lowAmp ? 0 : Math.min(1, Math.max(0, roundBoost * Math.max(0, (amp - 0.10)) * 2.0 + rounded * 0.25));
        const puckerVal = lowAmp ? 0 : Math.min(1, Math.max(0, roundBoost * Math.max(0, (amp - 0.10)) * 1.5 + rounded * 0.15));
        const smileVal = Math.max(0, wide * (0.55 + amp * 0.40));
        const closeVal = lowAmp ? 0.05 : Math.max(0, closeLike * (1 - amp) * 0.60);
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
    if (textVisemeEnabled) {
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
    if (textVisemeEnabled && (!useBrowserTts || !useBrowserTts.checked) && (!analyser || !cloudAudioSpeaking)) {
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
        const setIdx = (idx, val, key) => { if (idx >= 0) infl[idx] = infl[idx] * 0.7 + Math.max(0, Math.min(1, smooth(key, val) * visemeStrength)) * 0.3; };
        // Vincoli per evitare sovrapposizione labbra
        let jaw = Math.min(1, Math.max(0, visemeTargets.jawOpen + (cloudAudioSpeaking ? 0 : restJawOpen)));
        const roundness = Math.min(1, visemeTargets.mouthFunnel + visemeTargets.mouthPucker);
        const closeSuppression = Math.max(0, 1 - jaw * 1.5 - roundness * 0.9);
        let constrainedClose = 0; // disattiva chiusura per evitare sovrapposizione
        // Se desideri riattivare qualche chiusura controllata, rimuovi la riga sopra e usa la successiva:
        // let constrainedClose = Math.min(lipConfig.maxMouthClose, visemeTargets.mouthClose * closeSuppression);
        // Imporre separazione minima: se la chiusura tende a superare la soglia, aumenta jaw
        if (constrainedClose > lipConfig.closeThresholdForSeparation) {
          jaw = Math.max(jaw, lipConfig.minLipSeparation);
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
        const jawForBone = Math.max(lipConfig.minLipSeparation, (appliedJaw !== null ? appliedJaw : (amp * 0.9 + (cloudAudioSpeaking ? 0 : restJawOpen * 0.2))));
        const a = lipConfig.jawSmoothingAlpha;
        const jb = window.__jawBonePrev * (1 - a) + jawForBone * a;
        window.__jawBonePrev = jb;
        jawBone.rotation.x = -(jb * 0.5) * 0.3;
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
      'es','ecc','etc','sig','sigg','sigra','sig.na','sig.ra','dott','ing','avv','prof','dr','dottssa','srl','spa','s.p.a','s.r.l','p.es','nr','n','art','cap','ca','vs','no'
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
    while (k >= 0 && /[\p{L}\p{N}\.]/u.test(s[k])) { k--; }
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

    // Se la checkbox TTS è selezionata e c'è speechSynthesis, usa TTS del browser
    if (useBrowserTts && useBrowserTts.checked && 'speechSynthesis' in window) {
      speakQueue.push({ url: null, text: norm });
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
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ text: next, locale: 'it-IT', format: 'mp3' })
      });
      if (!res.ok) throw new Error(`TTS API ${res.status}`);
      const blob = await res.blob();
      const url = URL.createObjectURL(blob);
      speakQueue.push({ url, text: next });
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
    const norm = clean.replace(/\s+/g,' ').trim();
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
        utter.lang = 'it-IT'; utter.rate = 1.0; utter.pitch = 1.0; utter.volume = 1.0;
        const voices = window.speechSynthesis.getVoices();
        const prefNames = ['Google italiano','Microsoft','Elsa','Lucia','Carla','Silvia','Alice'];
        const itVoices = voices.filter(v => /it[-_]/i.test(v.lang));
        const femaleVoices = itVoices.filter(v => /female|donna|feminine/i.test(v.name + ' ' + (v.voiceURI || '')));
        const chosen = femaleVoices[0] || itVoices.find(v => prefNames.some(n => (v.name || '').includes(n))) || itVoices[0] || voices.find(v => /Italian/i.test(v.name)) || voices[0];
        if (chosen) utter.voice = chosen;
        if (browserTtsStatus) browserTtsStatus.textContent = chosen ? `Voce: ${chosen.name}` : 'Voce IT non trovata (usa default)';
        utter.onstart = () => {
          try {
            const nowT = performance.now();
            const words = (item.text || '').trim().split(/\s+/).filter(Boolean);
            const estSec = Math.max(2, words.length * 0.42);
            visemeSchedule = [];
            enqueueTextVisemes(item.text || '', Math.floor(estSec * 1000), nowT);
          } catch {}
        };
        utter.onend = () => { visemeActiveUntil = 0; visemeStrength = 0; lastSpokenTail = (lastSpokenTail + ' ' + item.text).slice(-400); lastSentToTts=''; playNextInQueue(); };
        utter.onerror = () => { if (item.url) { ttsPlayer.src = item.url; ttsPlayer.play().catch(() => { isSpeaking = false; }); } else { isSpeaking=false; playNextInQueue(); } };
        window.speechSynthesis.speak(utter);
        return;
      } catch (e) { console.warn('speechSynthesis error, fallback to audio element', e); }
    }

    ttsPlayer.src = item.url;

    if (!audioCtx) {
      audioCtx = new (window.AudioContext || window.webkitAudioContext)();
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
      try { ensureMeydaLoaded().then(() => startMeydaAnalyzer()).catch((e) => console.warn('Meyda start failed', e)); } catch (e) { console.warn('Meyda start failed', e); }
    }

    const onEnded = () => {
      URL.revokeObjectURL(item.url);
      // Aggiorna coda parlato (mantieni solo ultimi 400 caratteri)
      lastSpokenTail = (lastSpokenTail + ' ' + item.text).slice(-400);
      console.log('TTS: Finished playing:', item.text.substring(0, 50));
      cloudAudioSpeaking = false;
      // Stop immediato dei visemi testuali
      visemeSchedule = [];
      // Porta a posa neutra sicura (leggera apertura)
      visemeTargets = { jawOpen: lipConfig.minLipSeparation, mouthFunnel: 0, mouthPucker: 0, mouthSmileL: 0, mouthSmileR: 0, mouthClose: 0 };
      visemeActiveUntil = performance.now() + 120;
      ttsPlayer.removeEventListener('ended', onEnded);
      stopMeydaAnalyzer();
      playNextInQueue();
    };
    
    const onError = () => {
      console.error('TTS: Playback error for:', item.text.substring(0, 50));
      URL.revokeObjectURL(item.url);
      ttsPlayer.removeEventListener('ended', onEnded);
      ttsPlayer.removeEventListener('error', onError);
      isSpeaking = false; cloudAudioSpeaking = false;
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
      try { ttsPlayer.removeEventListener('playing', onPlaying); } catch {}
    };
    ttsPlayer.addEventListener('playing', onPlaying);
    
    ttsPlayer.play().then(() => { cloudAudioSpeaking = true; }).catch((err) => { 
      console.error('TTS: Play failed:', err);
      isSpeaking = false; 
      onError();
    });
  }


  function stripHtml(html) {
    const tmp = document.createElement('div');
    tmp.innerHTML = html;
    return (tmp.textContent || tmp.innerText || '').replace(/\s+/g, ' ').trim();
  }

  async function ensureMeydaLoaded() {
    if (window.Meyda) { meyda = window.Meyda; return; }
    return new Promise((resolve, reject) => {
      try {
        const s = document.createElement('script');
        s.src = 'https://cdn.jsdelivr.net/npm/meyda@5.6.3/dist/web/meyda.min.js';
        s.async = true;
        s.onload = () => { meyda = window.Meyda; console.log('Meyda loaded'); resolve(); };
        s.onerror = (e) => { console.warn('Meyda load failed', e); reject(e); };
        document.head.appendChild(s);
      } catch (e) { reject(e); }
    });
  }

  function startMeydaAnalyzer() {
    try {
      if (!advancedLipsyncOn) return;
      if (!audioCtx || !mediaNode || !meyda || meydaAnalyzer) return;
      if (!meyda.isMeydaSupported(audioCtx)) { console.warn('Meyda not supported'); return; }
      meydaAnalyzer = meyda.createMeydaAnalyzer({
        audioContext: audioCtx,
        source: mediaNode,
        bufferSize: 1024,
        featureExtractors: ['rms','zcr','spectralCentroid','mfcc'],
        callback: onMeydaFeatures,
      });
      meydaAnalyzer.start();
      console.log('Meyda analyzer started');
    } catch (e) { console.warn('startMeydaAnalyzer failed', e); }
  }

  function stopMeydaAnalyzer() {
    try {
      if (meydaAnalyzer) { meydaAnalyzer.stop(); meydaAnalyzer = null; }
    } catch {}
  }

  function onMeydaFeatures(features) {
    try {
      if (!features) return;
      const rms = Math.max(0, Math.min(1, (features.rms || 0)));
      const zcr = features.zcr || 0; // 0..1
      const sc = features.spectralCentroid || 0; // Hz
      const mfcc = features.mfcc || [];

      // Heuristic viseme mapping:
      // - jawOpen by energy (rms)
      // - funnel/pucker by low centroid (rounded vowels)
      // - smile by higher centroid + certain MFCC patterns
      const normCentroid = Math.max(0, Math.min(1, sc / 4000));
      const rounded = Math.max(0, 1 - normCentroid); // low centroid => rounded
      const wide = Math.max(0, normCentroid - 0.3);
      const stopLike = Math.max(0, (0.2 - zcr) * 2); // lower zcr ~ closures

      const jaw = Math.min(1, rms * 2.2);
      const funnel = Math.max(0, rounded * 0.8 * rms);
      const pucker = Math.max(0, (rounded * 0.6 + (mfcc[0] ? 0.1 : 0)) * rms);
      const smile = Math.max(0, wide * 0.8 * rms);
      const close = Math.max(0, stopLike * 0.7);

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
      const LoaderCtor = window.THREE?.GLTFLoader || window.GLTFLoader;
      if (!window.THREE || !LoaderCtor) {
        console.warn('GLTFLoader non presente. THREE:', !!window.THREE, 'GLTFLoader:', !!LoaderCtor);
        return;
      }
      function doLoad() {
        try {
          const loader = new LoaderCtor();
          const humanoidUrl = "{{ asset('images/readyplayerme.glb') }}" + "?v=" + Date.now();
          console.log('Carico humanoid GLB da', humanoidUrl);
          loader.load(humanoidUrl, (gltf) => {
            humanoid = gltf.scene;
            humanoid.position.set(0, -0.3, 0);
            humanoid.scale.set(1.2, 1.2, 1.2);
            scene.add(humanoid);
            fitCameraToObject(camera, humanoid, 1.4);
            visemeMeshes = [];
            humanoid.traverse((obj) => {
              const name = (obj.name || '').toLowerCase();
              // Seleziona solo veri bones della mandibola, evita head/neck/teeth e mesh
              if (!jawBone && obj.type === 'Bone' && (name.includes('jaw') || name.includes('lowerjaw') || name.includes('mandible') || name.includes('mixamorigjaw'))) {
                jawBone = obj;
              }
              // Log diagnostico: mesh e morph targets disponibili
              if (obj.isMesh) {
                try {
                  const keys = obj.morphTargetDictionary ? Object.keys(obj.morphTargetDictionary) : [];
                  console.log('Mesh:', obj.name, 'hasMorph:', !!obj.morphTargetDictionary, 'keys:', keys);
                } catch {}
              }
              if (obj.isMesh && obj.morphTargetDictionary && obj.morphTargetInfluences) {
                const dict = obj.morphTargetDictionary;
        const hasKey = (k) => (k && (dict[k] !== undefined));
        const findKeyCase = (arr) => { for (const k of arr) { if (hasKey(k)) return k; } return null; };
        const jawKey = findKeyCase(['jawOpen','JawOpen','JAWOPEN','viseme_aa','viseme_AA','v_aa','AA','aa']);
        const funnelKey = findKeyCase(['mouthFunnel','viseme_ou','OU','ou','viseme_uw','uw','OW','ow','OH','oh']);
        const puckerKey = findKeyCase(['mouthPucker','PUCKER','pucker']);
        const smileLKey = findKeyCase(['mouthSmileLeft','mouthSmile_L','smileLeft','SmileLeft']);
        const smileRKey = findKeyCase(['mouthSmileRight','mouthSmile_R','smileRight','SmileRight']);
        const closeKey = findKeyCase(['mouthClose','CLOSE','close','viseme_mbp','MBP','mbp']);
        if (jawKey && (/wolf3d_head/i.test(obj.name) || !morphMesh)) {
          morphMesh = obj; morphIndex = dict[jawKey];
          console.log('Morph target trovato:', jawKey, 'index:', morphIndex, 'on', obj.name);
                  // Cattura ulteriori target per visemi se presenti
                  try {
            if (jawKey) visemeIndices.jawOpen = dict[jawKey];
            if (funnelKey) visemeIndices.mouthFunnel = dict[funnelKey];
            if (puckerKey) visemeIndices.mouthPucker = dict[puckerKey];
            if (smileLKey) visemeIndices.mouthSmileL = dict[smileLKey];
            if (smileRKey) visemeIndices.mouthSmileR = dict[smileRKey];
            if (closeKey) visemeIndices.mouthClose = dict[closeKey];
                    // Alcuni avatar usano chiavi alternative
                    if (visemeIndices.mouthFunnel < 0 && dict['viseme_ou'] !== undefined) visemeIndices.mouthFunnel = dict['viseme_ou'];
                    if (visemeIndices.jawOpen < 0 && dict['viseme_aa'] !== undefined) visemeIndices.jawOpen = dict['viseme_aa'];
                  } catch {}
                } else if (!morphMesh) {
          const candidates = ['JawOpen','jawOpen','JAWOPEN','mouthOpen','viseme_aa','viseme_AA','v_aa','AA','aa'];
                  for (const key of candidates) {
                    if (dict[key] !== undefined) { morphMesh = obj; morphIndex = dict[key]; console.log('Morph target trovato:', key, 'index:', morphIndex, 'on', obj.name); break; }
                  }
                }
                // Registra mesh con almeno un target labiale
                try {
                  const indices = {
            jawOpen: (jawKey ? dict[jawKey] : -1),
            mouthFunnel: (funnelKey ? dict[funnelKey] : -1),
            mouthPucker: (puckerKey ? dict[puckerKey] : -1),
            mouthSmileL: (smileLKey ? dict[smileLKey] : -1),
            mouthSmileR: (smileRKey ? dict[smileRKey] : -1),
            mouthClose: (closeKey ? dict[closeKey] : -1),
                  };
                  // Prova a mappare blink se presenti
                  if (eyeMesh === null && (dict['eyeBlinkLeft'] !== undefined || dict['eyeBlinkRight'] !== undefined)) {
                    eyeMesh = obj;
                    if (dict['eyeBlinkLeft'] !== undefined) eyeIndices.eyeBlinkLeft = dict['eyeBlinkLeft'];
                    if (dict['eyeBlinkRight'] !== undefined) eyeIndices.eyeBlinkRight = dict['eyeBlinkRight'];
                  }
                  if (Object.values(indices).some(v => v !== -1)) {
                    visemeMeshes.push({ mesh: obj, indices });
                    console.log('Viseme mesh registered:', obj.name, indices);
                  }
                } catch {}
              }
            });
            // Zoom sul volto se presente (Wolf3D_Head)
            try {
              let headMesh = null;
              humanoid.traverse((obj) => { if (!headMesh && /wolf3d_head/i.test(obj.name)) headMesh = obj; });
              if (headMesh) { fitCameraToObject(camera, headMesh, 1.2); }
            } catch {}
            // Nessun fallback a SkinnedMesh per il jawBone: usa solo morph targets se il bone non esiste
            head.visible = false; jaw.visible = false;
          }, undefined, (err) => { console.warn('Impossibile caricare humanoid.glb', err); });
        } catch (e) {
          console.warn('Errore GLTFLoader/doLoad', e);
        }
      }
      doLoad();
    } catch (e) {
      console.warn('Errore loadHumanoid()', e);
    }
  }

  function fitCameraToObject(camera, object, offset = 1.25) {
    const box = new THREE.Box3().setFromObject(object);
    const size = box.getSize(new THREE.Vector3());
    const center = box.getCenter(new THREE.Vector3());
    const maxDim = Math.max(size.x, size.y, size.z);
    const fov = camera.fov * (Math.PI / 180);
    let cameraZ = Math.abs(maxDim / 2 * Math.tan(fov * 2));
    cameraZ *= offset;
    camera.position.z = center.z + cameraZ;
    camera.position.y = center.y + size.y * 0.1;
    camera.lookAt(center);
    const minZ = box.min.z;
    const cameraToFarEdge = (minZ < 0) ? -minZ + cameraZ : cameraZ - minZ;
    camera.far = cameraToFarEdge * 3;
    camera.updateProjectionMatrix();
  }
});
</script>




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
  let isListening = false, recognition = null, mediaMicStream = null;
  let currentEvtSource = null; // Stream SSE attivo da chiudere se necessario
  let isStartingStream = false;
  
  // Three.js avatar minimale (testa + mandibola)
  let THREELoaded = false;
  let scene, camera, renderer, head, jaw, animationId, analyser, dataArray, audioCtx, mediaNode;

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
    if (isChrome && 'speechSynthesis' in window && useBrowserTts) {
      useBrowserTts.checked = true;
      browserTtsStatus.textContent = 'TTS browser attivo (Chrome)';
    }
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
    threadId = null; assistantThreadId = null;
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
        try { console.error('SSE: error/closed', { attempt: sseRetryCount + 1, readyState: state }); } catch {}
        // Se il browser sta riconnettendo o la connessione è ancora OPEN, non chiudere manualmente
        if (state !== 2 && !done) {
          return;
        }
        try { evtSource.close(); } catch {}
        currentEvtSource = null;
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
      bindSse();
      // Watchdog: se non arrivano token entro 3.5s, ritenta una volta
      if (sseConnectWatchdog) { clearTimeout(sseConnectWatchdog); }
      sseConnectWatchdog = setTimeout(() => {
        if (collected.length === 0 && sseRetryCount < 2 && !done) {
          sseRetryCount++;
          try { evtSource.close(); } catch {}
          currentEvtSource = null;
          const delay = 200 * sseRetryCount;
          console.warn('SSE: connect watchdog retry', { attempt: sseRetryCount });
          setTimeout(() => { openSse(); }, delay);
        }
      }, 3500);
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
      // Smoothing verso il target sintetico
      speechAmp += (speechAmpTarget - speechAmp) * 0.2;
      amp = Math.max(0, Math.min(1, speechAmp));
    } else if (analyser && dataArray) {
      analyser.getByteTimeDomainData(dataArray);
      let sum = 0;
      for (let i = 0; i < dataArray.length; i++) {
        const v = (dataArray[i] - 128) / 128.0;
        sum += v * v;
      }
      const rms = Math.sqrt(sum / dataArray.length);
      amp = Math.min(1, rms * 8);
    }
    // Animazione lip-sync su avatar umanoide o fallback geometrico
    const open = -amp * 0.5;
    if (morphMesh && morphIndex >= 0 && Array.isArray(morphMesh.morphTargetInfluences)) {
      morphValue = morphValue * 0.82 + Math.min(1, amp * 3.0) * 0.18;
      morphMesh.morphTargetInfluences[morphIndex] = morphValue;
    }
    if (jawBone) {
      if (jawBone.type === 'Bone') {
        // Bone: ruota leggermente
        jawBone.rotation.x = open * 0.3;
      } else if (jawBone.type === 'SkinnedMesh') {
        // SkinnedMesh: scala leggermente su Y per simulare apertura bocca
        const baseScale = 1.2;
        jawBone.scale.y = baseScale + amp * 0.05;
      }
    } else if (jaw) {
      // Fallback geometrico
      jaw.rotation.x = open;
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

    // Se uso TTS del browser, non fare richiesta API; enqueue diretto
    if (useBrowserTts && useBrowserTts.checked && 'speechSynthesis' in window) {
      speakQueue.push({ url: null, text: norm });
      if (!isSpeaking) playNextInQueue();
      return;
    }

    // Accoda la richiesta TTS cloud ed esegui in modo strettamente sequenziale
    ttsRequestQueue.push(norm);
    processTtsQueue();
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
        utter.lang = 'it-IT';
        utter.rate = 1.0;
        utter.pitch = 1.0;
        utter.volume = 1.0;
        const voices = window.speechSynthesis.getVoices();
        // Selezione voce femminile italiana, se disponibile
        const prefNames = ['Google italiano', 'Microsoft', 'Elsa', 'Lucia', 'Carla', 'Silvia', 'Alice'];
        let itVoices = voices.filter(v => /it[-_]/i.test(v.lang));
        let femaleVoices = itVoices.filter(v => /female|donna|feminine/i.test(v.name + ' ' + (v.voiceURI || '')));
        let chosen = femaleVoices[0]
          || itVoices.find(v => prefNames.some(n => (v.name || '').includes(n)))
          || itVoices[0]
          || voices.find(v => /Italian/i.test(v.name))
          || voices[0];
        if (chosen) utter.voice = chosen;
        browserTtsStatus.textContent = chosen ? `Voce: ${chosen.name}` : 'Voce IT non trovata (usa default)';
        // Genera un pattern di ampiezza fittizio durante la parlata
        function startSpeechAmp() {
          stopSpeechAmp();
          speechAmp = 0;
          speechAmpTimer = setInterval(() => {
            // Modula il target in base a semplice ritmo
            const now = performance.now();
            const base = (Math.sin(now / 120) + 1) * 0.35; // 0..0.7
            const jitter = Math.random() * 0.15; // 0..0.15
            speechAmpTarget = Math.min(1, base + jitter);
          }, 60);
        }
        function stopSpeechAmp() {
          if (speechAmpTimer) { clearInterval(speechAmpTimer); speechAmpTimer = null; }
          speechAmpTarget = 0;
        }

        utter.onstart = () => { startSpeechAmp(); };
        utter.onend = () => {
          stopSpeechAmp();
          lastSpokenTail = (lastSpokenTail + ' ' + item.text).slice(-400);
          lastSentToTts = '';
          playNextInQueue();
        };
        utter.onerror = () => {
          stopSpeechAmp();
          console.warn('Browser TTS error, fallback audio element');
          // Fallback su audio element se disponibile
          if (item.url) {
            ttsPlayer.src = item.url;
            ttsPlayer.play().catch(() => { isSpeaking = false; });
          } else {
            isSpeaking = false;
            playNextInQueue();
          }
        };
        window.speechSynthesis.speak(utter);
        return; // Non usare audio element se usiamo il TTS del browser
      } catch (e) {
        console.warn('speechSynthesis fallback to audio element', e);
      }
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

    const onEnded = () => {
      URL.revokeObjectURL(item.url);
      // Aggiorna coda parlato (mantieni solo ultimi 400 caratteri)
      lastSpokenTail = (lastSpokenTail + ' ' + item.text).slice(-400);
      console.log('TTS: Finished playing:', item.text.substring(0, 50));
      
      ttsPlayer.removeEventListener('ended', onEnded);
      playNextInQueue();
    };
    
    const onError = () => {
      console.error('TTS: Playback error for:', item.text.substring(0, 50));
      URL.revokeObjectURL(item.url);
      ttsPlayer.removeEventListener('ended', onEnded);
      ttsPlayer.removeEventListener('error', onError);
      isSpeaking = false;
      playNextInQueue();
    };
    
    ttsPlayer.addEventListener('ended', onEnded);
    ttsPlayer.addEventListener('error', onError);
    
    ttsPlayer.play().catch((err) => { 
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
            humanoid.traverse((obj) => {
              const name = (obj.name || '').toLowerCase();
              if (!jawBone && (name.includes('jaw') || name.includes('mixamorigjaw'))) {
                jawBone = obj;
              }
              if (!jawBone && (name.includes('head') || name.includes('mixamorighead'))) {
                jawBone = obj;
              }
              if (!jawBone && (name.includes('neck_joint_2') || (name.includes('neck') && obj.type === 'Bone'))) {
                jawBone = obj;
              }
              if (!jawBone && (name.includes('wolf3d_head') || name.includes('wolf3d_teeth'))) {
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
                if (dict['jawOpen'] !== undefined && (/wolf3d_head/i.test(obj.name) || !morphMesh)) {
                  morphMesh = obj; morphIndex = dict['jawOpen'];
                  console.log('Morph target trovato:', 'jawOpen', 'index:', morphIndex, 'on', obj.name);
                } else if (!morphMesh) {
                  const candidates = ['JawOpen','mouthOpen','viseme_aa','viseme_AA','v_aa'];
                  for (const key of candidates) {
                    if (dict[key] !== undefined) { morphMesh = obj; morphIndex = dict[key]; console.log('Morph target trovato:', key, 'index:', morphIndex, 'on', obj.name); break; }
                  }
                }
              }
            });
            // Zoom sul volto se presente (Wolf3D_Head)
            try {
              let headMesh = null;
              humanoid.traverse((obj) => { if (!headMesh && /wolf3d_head/i.test(obj.name)) headMesh = obj; });
              if (headMesh) { fitCameraToObject(camera, headMesh, 1.2); }
            } catch {}
            if (!jawBone) {
              humanoid.traverse((obj) => { if (!jawBone && obj.type === 'SkinnedMesh') jawBone = obj; });
            }
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




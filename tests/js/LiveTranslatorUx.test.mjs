import assert from 'node:assert/strict';

/**
 * Test di smoke/UX per il componente LiveTranslator.vue.
 *
 * NOTA: questi test non montano il componente con un framework di test,
 * ma verificano comunque alcune convenzioni critiche dell'UX a livello
 * di template/script (senza introdurre nuovi runner JS).
 */

import fs from 'node:fs';
import path from 'node:path';

const componentPath = path.resolve('resources/js/components/LiveTranslator.vue');
const source = fs.readFileSync(componentPath, 'utf8');

function extractVueScript(vueSource) {
  const match = vueSource.match(/<script>\s*[\s\S]*?\s*<\/script>/);
  assert.ok(match, 'LiveTranslator.vue deve contenere un blocco <script>...</script>.');
  return match[0]
    .replace(/^<script>\s*/i, '')
    .replace(/\s*<\/script>\s*$/i, '');
}

function loadComponentOptions({ WhisperSpeechRecognition }) {
  const rawScript = extractVueScript(source);

  // Rimuovi gli import (Node non li può risolvere qui).
  // Manteniamo il contenuto dell'export default e lo valutiamo come plain object.
  let code = rawScript.replace(/^\s*import\s+.*$/gm, '');

  // Trasforma export default in return, così possiamo ottenere l'oggetto opzioni.
  code = code.replace(/export\s+default\s*{/, 'return {');

  // Nel file il blocco termina con "};" → va bene dentro la Function.
  const fakeNetwork = function FakeNetwork() { };
  const factory = new Function('WhisperSpeechRecognition', 'Network', code); // eslint-disable-line no-new-func
  return factory(WhisperSpeechRecognition, fakeNetwork);
}

function createVm(options) {
  assert.ok(options && typeof options === 'object', 'Opzioni Vue non valide.');
  assert.ok(typeof options.data === 'function', 'LiveTranslator.vue deve esportare data().');
  assert.ok(options.methods && typeof options.methods === 'object', 'LiveTranslator.vue deve esportare methods.');
  assert.ok(options.computed && typeof options.computed === 'object', 'LiveTranslator.vue deve esportare computed.');

  // data() usa pochissimo "this" in inizializzazione, ma passiamo comunque una props-like shape.
  const vm = options.data.call({ locale: 'it-IT' });
  vm.locale = 'it-IT';

  // Stubs minimi per evitare crash in metodi richiamati dai test.
  vm.debugLog = () => { };
  vm.$nextTick = (cb) => { if (typeof cb === 'function') cb(); };
  vm.$refs = {};

  // Lingue minime per i flussi call/youtube
  vm.availableLanguages = [
    { code: 'it', micCode: 'it-IT', label: 'Italiano' },
    { code: 'en', micCode: 'en-US', label: 'English' },
  ];
  vm.langA = 'it';
  vm.langB = 'en';
  vm.youtubeLangSource = 'en';
  vm.youtubeLangTarget = 'it';

  // Computed: definiamo getter sul vm (come farebbe Vue)
  for (const [key, def] of Object.entries(options.computed)) {
    if (Object.getOwnPropertyDescriptor(vm, key)) {
      continue;
    }
    if (typeof def === 'function') {
      Object.defineProperty(vm, key, { get: def.bind(vm) });
    } else if (def && typeof def.get === 'function') {
      Object.defineProperty(vm, key, { get: def.get.bind(vm) });
    }
  }

  // Methods: bind su vm
  for (const [key, fn] of Object.entries(options.methods)) {
    if (typeof fn === 'function') {
      vm[key] = fn.bind(vm);
    }
  }

  // In test non vogliamo toccare permessi reali.
  vm.ensureMicPermission = () => true;

  return vm;
}

class FakeWhisperRecognition {
  constructor() {
    this.lang = 'it-IT';
    this.continuous = true;
    this.interimResults = false;
    this.maxAlternatives = 1;

    // Proprietà "feature flags" attese dal componente.
    this.allowedLangs = null;
    this.sourceHint = null;
    this.singleSegmentMode = true;
    this._silenceMs = 800;
    this._silenceThreshold = 0.03;
    this.onAutoPause = null;

    this.onstart = null;
    this.onresult = null;
    this.onerror = null;
    this.onend = null;

    this._startCalls = 0;
    this._stopCalls = 0;
  }

  start() {
    this._startCalls += 1;
    if (typeof this.onstart === 'function') {
      this.onstart();
    }
  }

  stop() {
    this._stopCalls += 1;
    if (typeof this.onend === 'function') {
      this.onend();
    }
  }

  abort() { }
}

class FakeWebSpeechRecognition {
  constructor() {
    this.lang = 'it-IT';
    this.continuous = true;
    this.interimResults = true;
    this.maxAlternatives = 1;

    this.onstart = null;
    this.onresult = null;
    this.onerror = null;
    this.onend = null;

    this._startCalls = 0;
    this._stopCalls = 0;
  }

  start() {
    this._startCalls += 1;
    if (typeof this.onstart === 'function') {
      this.onstart();
    }
  }

  stop() {
    this._stopCalls += 1;
    if (typeof this.onend === 'function') {
      this.onend();
    }
  }

  abort() { }
}

function withWindowWebSpeechStub() {
  const prevWindow = globalThis.window;
  globalThis.window = {
    SpeechRecognition: FakeWebSpeechRecognition,
    webkitSpeechRecognition: FakeWebSpeechRecognition,
  };

  return {
    restore() {
      globalThis.window = prevWindow;
    },
  };
}

function withGlobalNavigatorStub({ userAgent = 'Mozilla/5.0 (Linux; Android 14) AppleWebKit/537.36 Chrome/120.0 Mobile Safari/537.36' } = {}) {
  const prevDesc = Object.getOwnPropertyDescriptor(globalThis, 'navigator');
  const prevValue = globalThis.navigator;
  const nextNav = {
    userAgent,
    language: 'it-IT',
    languages: ['it-IT', 'en-US'],
  };

  // In Node >= 20 navigator può essere definito come getter non-writable.
  // Forziamo un override configurabile se possibile.
  try {
    Object.defineProperty(globalThis, 'navigator', {
      value: nextNav,
      configurable: true,
      writable: true,
      enumerable: true,
    });
  } catch {
    // fallback: prova a modificare i campi se navigator è un oggetto mutabile
    try {
      if (globalThis.navigator && typeof globalThis.navigator === 'object') {
        globalThis.navigator.userAgent = userAgent;
        globalThis.navigator.language = 'it-IT';
        globalThis.navigator.languages = ['it-IT', 'en-US'];
      }
    } catch {
      // se non possiamo stubbare, lasciamo com'è (i test che dipendono dallo stub useranno window)
    }
  }
  return {
    restore() {
      try {
        if (prevDesc) {
          Object.defineProperty(globalThis, 'navigator', prevDesc);
        } else {
          // Se prima non esisteva un descriptor proprio, ripristina valore se possibile
          Object.defineProperty(globalThis, 'navigator', {
            value: prevValue,
            configurable: true,
            writable: true,
            enumerable: true,
          });
        }
      } catch {
        // ignora se non ripristinabile (ambiente Node speciale)
      }
    },
  };
}

// Verifica che il label "Lingua di traduzione" sia presente (regressione su UX lingue)
{
  const hasLabel = source.includes('translation language') ||
    source.includes('Lingua di traduzione') ||
    source.includes('langBLabel');

  assert.ok(
    hasLabel,
    'LiveTranslator.vue deve esporre la label per la lingua di traduzione (langBLabel).',
  );
}

// Regressione: rimossa dicitura "solo desktop" dalla tab YouTube
{
  const hasDesktopOnlyRemoved =
    source.includes("youtubeDesktopOnlyLabel: ''") &&
    !source.includes("youtubeDesktopOnlyLabel: 'Solo desktop'") &&
    !source.includes("youtubeDesktopOnlyLabel: 'Desktop only'");

  assert.ok(
    hasDesktopOnlyRemoved,
    'LiveTranslator.vue: youtubeDesktopOnlyLabel deve essere vuoto (niente “Solo desktop / Desktop only”).',
  );
}

// Avviso mobile YouTube deve essere collassabile (details/summary)
{
  const hasCollapsibleMobileWarning =
    source.includes('youtubeMobileWarningShort') &&
    source.includes('<details') &&
    source.includes('ui.youtubeMobileWarning');

  assert.ok(
    hasCollapsibleMobileWarning,
    'LiveTranslator.vue: avviso mobile YouTube deve usare <details> (collassabile) con label breve + testo completo.',
  );
}

// Avviso “Novità 🎄” dismissibile deve essere presente (wow natalizio)
{
  const hasHolidayNotice =
    source.includes('showHolidayNotice') &&
    source.includes('ui.holidayNoticeTitle') &&
    source.includes('holiday-wow') &&
    source.includes('showHolidayNotice = false');

  assert.ok(
    hasHolidayNotice,
    'LiveTranslator.vue: deve esistere un avviso “Novità 🎄” dismissibile con stile wow natalizio.',
  );
}

// Android/YouTube mobile: niente CTA fixed overlay (evita sovrapposizioni)
{
  const hasFixedCtaOverlay = source.includes('fixed left-0 right-0 bottom-0');
  assert.ok(
    !hasFixedCtaOverlay,
    'LiveTranslator.vue: su YouTube mobile non deve esistere un CTA fixed overlay (causa sovrapposizioni).',
  );
}

// Android/YouTube mobile: CTA deve stare sotto il video (prima dei box original/translation)
{
  const idxVideo = source.indexOf('ref="youtubePlayer"');
  const idxCta = source.indexOf('Mobile: CTA subito sotto il video');
  // Ci sono più originalBox nel file (call + youtube). Prendiamo quello DOPO il CTA.
  const idxBoxes = source.indexOf('ref="originalBox"', Math.max(0, idxCta));

  assert.ok(idxVideo !== -1 && idxCta !== -1 && idxBoxes !== -1, 'LiveTranslator.vue: markup YouTube atteso non trovato (video/cta/box).');
  assert.ok(idxVideo < idxCta && idxCta < idxBoxes, 'LiveTranslator.vue: su mobile YouTube il CTA deve essere sotto il video e sopra i riquadri testo.');
}

// Android/YouTube mobile: i box trascritto/tradotto devono avere altezza fissa (scroll interno)
{
  const hasFixedHeightBoxes =
    source.includes("h-[190px] overflow-y-auto") &&
    source.includes("ref=\"originalBox\"") &&
    source.includes("ref=\"translationBox\"");

  assert.ok(
    hasFixedHeightBoxes,
    'LiveTranslator.vue: su YouTube mobile i box original/translation devono avere altezza fissa (h-[190px]) con overflow-y-auto.',
  );
}

// YouTube desktop: i box trascritto/tradotto devono stare nella pagina senza scroll della tab.
// Quindi usano flex-1/min-h-0 (altezza adattiva) con overflow interno.
{
  const hasDesktopAdaptiveBoxes =
    source.includes("flex-1 min-h-0 rounded-xl border border-slate-700 bg-slate-900/60 p-3 text-sm md:text-base overflow-y-auto leading-relaxed");

  assert.ok(
    hasDesktopAdaptiveBoxes,
    'LiveTranslator.vue: su YouTube desktop i box original/translation devono usare altezza adattiva (flex-1/min-h-0) e scroll interno.',
  );
  assert.ok(
    !source.includes("h-[220px] md:h-[240px] lg:h-[260px]"),
    'LiveTranslator.vue: su YouTube desktop non deve usare altezze fisse (h-[220/240/260px]).',
  );

  // Requisito UX: i box devono arrivare a fine card → la griglia desktop deve essere h-full
  assert.ok(
    source.includes("grid grid-cols-1 lg:grid-cols-3 gap-3 min-h-0 h-full"),
    'LiveTranslator.vue: su YouTube desktop la griglia principale deve avere h-full per far espandere i box fino in fondo al card.',
  );
}

// Mobile Call: se callTranslationEnabled è false (solo trascrizione), non deve comparire "Traduzione disattivata"
{
  const hasTranslationDisabledPlaceholder = source.includes('Traduzione disattivata');
  assert.ok(
    !hasTranslationDisabledPlaceholder,
    'LiveTranslator.vue: su mobile in modalità solo trascrizione non deve mostrare il placeholder "Traduzione disattivata" (deve esserci solo il riquadro trascrizione).',
  );
}

// Gating tab YouTube in emulazione: deve dipendere dalla disponibilità reale WebSpeech, non dal match user-agent.
{
  const options = loadComponentOptions({ WhisperSpeechRecognition: FakeWhisperRecognition });
  const vm = createVm(options);
  vm.activeTab = 'call';
  vm.isMobileLowPower = true;

  // Caso 1: WebSpeech NON disponibile → tab disabilitata
  {
    const prevWindow = globalThis.window;
    globalThis.window = {};
    assert.equal(vm.isYoutubeTabDisabled, true, 'Se WebSpeech non esiste, su mobile YouTube deve essere disabilitata.');
    globalThis.window = prevWindow;
  }

  // Caso 2: WebSpeech disponibile → tab NON disabilitata (anche in emulazione)
  {
    const w = withWindowWebSpeechStub();
    try {
      assert.equal(vm.isYoutubeTabDisabled, false, 'Se WebSpeech esiste, su mobile la tab YouTube deve essere cliccabile.');
    } finally {
      w.restore();
    }
  }

  // Inoltre detectEnvAndDefaultMode deve settare isChromeWithWebSpeech basandosi su hasWebSpeech (non UA)
  {
    const w = withWindowWebSpeechStub();
    const n = withGlobalNavigatorStub({ userAgent: 'Mozilla/5.0 (iPhone; CPU iPhone OS 17_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.0 Mobile/15E148 Safari/604.1' });
    try {
      vm.isChromeWithWebSpeech = false;
      vm.detectEnvAndDefaultMode();
      assert.equal(vm.isChromeWithWebSpeech, true, 'detectEnvAndDefaultMode: con WebSpeech disponibile deve impostare isChromeWithWebSpeech=true anche se UA non è Chrome.');
    } finally {
      n.restore();
      w.restore();
    }
  }

  // setActiveTab deve bloccare lo switch se disabilitata
  {
    const prevWindow = globalThis.window;
    globalThis.window = {};
    vm.activeTab = 'call';
    vm.statusMessage = '';
    vm.setActiveTab('youtube');
    assert.equal(vm.activeTab, 'call', 'setActiveTab: se YouTube è disabilitata non deve cambiare tab.');
    assert.ok((vm.statusMessage || '').length > 0, 'setActiveTab: se YouTube è disabilitata deve mostrare un messaggio di stato.');
    globalThis.window = prevWindow;
  }
}

// -------------------------
// TEST FUNZIONALI (state machine mic / impostazioni UX)
// -------------------------

{
  const options = loadComponentOptions({ WhisperSpeechRecognition: FakeWhisperRecognition });
  const newVm = () => {
    const vm = createVm(options);
    // Setup comune: tab call, Whisper attivo
    vm.activeTab = 'call';
    vm.isMobileLowPower = false;
    vm.callAutoPauseEnabled = true;
    vm.callTranslationEnabled = true;
    vm.readTranslationEnabledCall = true;
    vm.earphonesModeEnabledCall = false;
    vm.isListening = false;
    vm.activeSpeaker = null;
    vm.isTtsPlaying = false;
    vm.recognition = null;
    vm.pendingAutoResumeSpeaker = null;
    vm.pendingAutoResumeSpeakerAfterTts = null;
    return vm;
  };

  const withNodeStubs = () => {
    // processTtsQueue usa URL.createObjectURL + Audio + fetch: in Node vanno stubbare.
    const originalFetch = globalThis.fetch;
    const originalAudio = globalThis.Audio;
    const originalUrl = globalThis.URL;

    const createdAudios = [];

    // Node può avere URL senza createObjectURL: creiamo wrapper minimale.
    const urlObj = typeof originalUrl === 'function' || typeof originalUrl === 'object'
      ? originalUrl
      : {};
    if (!urlObj.createObjectURL) {
      urlObj.createObjectURL = () => 'blob:fake-audio-url';
    }
    if (!urlObj.revokeObjectURL) {
      urlObj.revokeObjectURL = () => { };
    }
    globalThis.URL = urlObj;

    class FakeAudio {
      constructor(src) {
        this.src = src || '';
        this.onended = null;
        this.onerror = null;
        createdAudios.push(this);
      }
      play() {
        return Promise.resolve();
      }
      _triggerEnded() {
        if (typeof this.onended === 'function') {
          this.onended();
        }
      }
    }
    globalThis.Audio = FakeAudio;

    globalThis.fetch = async () => {
      return {
        ok: true,
        headers: { get: () => 'audio/mpeg' },
        blob: async () => new Blob(['x'], { type: 'audio/mpeg' }),
      };
    };

    return {
      createdAudios,
      restore() {
        globalThis.fetch = originalFetch;
        globalThis.Audio = originalAudio;
        globalThis.URL = originalUrl;
      },
    };
  };

  // 1) Se TTS sta leggendo e NON siamo in earphones mode → toggleListening deve essere ignorato
  {
    const vm = newVm();
    let ensureCalls = 0;
    vm.ensureMicPermission = () => { ensureCalls += 1; return true; };
    vm.isTtsPlaying = true;

    await vm.toggleListeningForLang('A');

    assert.equal(ensureCalls, 0, 'Quando TTS è in corso e NON earphones mode, non deve chiedere permessi mic.');
    assert.equal(vm.recognition, null, 'Quando TTS è in corso e NON earphones mode, non deve inizializzare recognition.');
    assert.equal(vm.isListening, false, 'Quando TTS è in corso e NON earphones mode, non deve partire in ascolto.');
  }

  // 2) In earphones mode → anche se TTS sta leggendo, il mic deve potersi avviare (no blocco)
  {
    const vm = newVm();
    let ensureCalls = 0;
    vm.ensureMicPermission = () => { ensureCalls += 1; return true; };
    vm.isTtsPlaying = true;
    vm.readTranslationEnabledCall = true;
    vm.callTranslationEnabled = true;
    vm.earphonesModeEnabledCall = true;

    await vm.toggleListeningForLang('A');

    assert.ok(ensureCalls >= 1, 'In earphones mode deve passare dalla pipeline di avvio mic.');
    assert.ok(vm.recognition, 'In earphones mode deve inizializzare recognition.');
    assert.equal(vm.isListening, true, 'In earphones mode deve andare in ascolto.');
    assert.ok(vm.recognition._startCalls >= 1, 'In earphones mode deve chiamare recognition.start().');
  }

  // 3) Auto-pausa: earphones mode → pendingAutoResumeSpeaker immediato
  {
    const vm = newVm();
    vm.isTtsPlaying = false;
    vm.callAutoPauseEnabled = true;
    vm.readTranslationEnabledCall = true;
    vm.callTranslationEnabled = true;
    vm.earphonesModeEnabledCall = true;

    let stopCalls = 0;
    vm.stopListeningInternal = () => { stopCalls += 1; vm.isListening = false; vm.activeSpeaker = null; };

    await vm.toggleListeningForLang('A');
    assert.ok(vm.recognition && typeof vm.recognition.onAutoPause === 'function', 'onAutoPause deve essere configurato in Whisper (call).');

    // Simula pausa VAD
    vm.recognition.onAutoPause();

    assert.equal(vm.pendingAutoResumeSpeaker, 'A', 'In earphones mode, auto-pausa deve impostare pendingAutoResumeSpeaker.');
    assert.equal(vm.pendingAutoResumeSpeakerAfterTts, null, 'In earphones mode, non deve usare pendingAutoResumeSpeakerAfterTts.');
    assert.equal(stopCalls, 1, 'onAutoPause deve fermare l’ascolto (stopListeningInternal).');
  }

  // 4) Auto-pausa: NO earphones + TTS attivo → pendingAutoResumeSpeakerAfterTts
  {
    const vm = newVm();
    vm.isTtsPlaying = false;
    vm.callAutoPauseEnabled = true;
    vm.readTranslationEnabledCall = true;
    vm.callTranslationEnabled = true;
    vm.earphonesModeEnabledCall = false;

    let stopCalls = 0;
    vm.stopListeningInternal = () => { stopCalls += 1; vm.isListening = false; vm.activeSpeaker = null; };

    await vm.toggleListeningForLang('A');
    vm.recognition.onAutoPause();

    assert.equal(vm.pendingAutoResumeSpeaker, null, 'In modalità normale con TTS, auto-pausa non deve impostare pendingAutoResumeSpeaker.');
    assert.equal(vm.pendingAutoResumeSpeakerAfterTts, 'A', 'In modalità normale con TTS, auto-pausa deve impostare pendingAutoResumeSpeakerAfterTts.');
    assert.equal(stopCalls, 1, 'onAutoPause deve fermare l’ascolto (stopListeningInternal).');
  }

  // 5) Caso “vuoto/filtrato”: dopo onresult con transcript vuoto, deve auto-resumare (no deadlock)
  {
    const vm2 = newVm();
    vm2.activeTab = 'call';
    vm2.isMobileLowPower = false;
    vm2.callAutoPauseEnabled = true;
    vm2.callTranslationEnabled = true;
    vm2.readTranslationEnabledCall = true;
    vm2.earphonesModeEnabledCall = false;
    vm2.isListening = false;
    vm2.isTtsPlaying = false;
    vm2.pendingAutoResumeSpeakerAfterTts = 'A';

    const resumeCalls = [];
    vm2.toggleListeningForLang = (speaker) => { resumeCalls.push(speaker); };

    vm2.initSpeechRecognition();
    assert.ok(vm2.recognition && typeof vm2.recognition.onresult === 'function', 'onresult deve essere configurato.');

    // Event con final vuoto
    const emptyEvent = {
      resultIndex: 0,
      results: [{
        0: { transcript: '' },
        length: 1,
        isFinal: true,
      }],
    };

    vm2.recognition.onresult(emptyEvent);

    assert.deepEqual(resumeCalls, ['A'], 'Con risultato vuoto/filtrato deve ripartire automaticamente sullo stesso speaker.');
    assert.equal(vm2.pendingAutoResumeSpeakerAfterTts, null, 'Dopo resume da risultato vuoto, pendingAutoResumeSpeakerAfterTts deve essere pulito.');
  }

  // 6) YouTube: autoPause enabled → onAutoPause deve essere configurato (desktop/Whisper) e chiamare stopListeningInternal
  {
    const vm = newVm();
    vm.activeTab = 'youtube';
    vm.isMobileLowPower = false; // desktop → Whisper
    vm.youtubeAutoPauseEnabled = true;
    vm.youtubeLangSource = 'en';
    vm.youtubeLangTarget = 'it';

    let stopCalls = 0;
    vm.stopListeningInternal = () => { stopCalls += 1; vm.isListening = false; vm.activeSpeaker = null; };

    await vm.toggleListeningForLang('A');

    assert.ok(vm.recognition && typeof vm.recognition.onAutoPause === 'function', 'YouTube+Whisper: onAutoPause deve essere configurato quando youtubeAutoPauseEnabled=true.');

    vm.recognition.onAutoPause();
    assert.equal(stopCalls, 1, 'YouTube+Whisper: onAutoPause deve fermare l’ascolto (stopListeningInternal).');
  }

  // 7) YouTube: autoPause disabled → onAutoPause deve essere null
  {
    const vm = newVm();
    vm.activeTab = 'youtube';
    vm.isMobileLowPower = false; // desktop → Whisper
    vm.youtubeAutoPauseEnabled = false;
    vm.youtubeLangSource = 'en';
    vm.youtubeLangTarget = 'it';

    await vm.toggleListeningForLang('A');

    assert.equal(vm.recognition && vm.recognition.onAutoPause, null, 'YouTube+Whisper: onAutoPause deve essere null quando youtubeAutoPauseEnabled=false.');
  }

  // 8) YouTube: autoResume deve riaccendere il mic SOLO dopo TTS (audio.onended)
  {
    const stubs = withNodeStubs();
    try {
      const vm = newVm();
      vm.activeTab = 'youtube';
      vm.youtubeAutoResumeEnabled = true;

      // Evita routing audio e logica extra: usa Audio(url)
      vm.ensureTtsAudioRouting = () => false;
      vm.getTtsAudioElementForChannel = () => null;
      vm.updateIsTtsPlaying = () => { };

      const resumeCalls = [];
      vm.toggleListeningForLang = (speaker) => { resumeCalls.push(speaker); };

      // Prepara una coda TTS minimale
      vm.ttsQueueByChannel = { left: [], right: [], center: [{ text: 'ciao', locale: 'it-IT' }] };
      vm.ttsPlayingByChannel = { left: false, right: false, center: false };

      const p = vm.processTtsQueue('center');
      await p;

      assert.deepEqual(resumeCalls, [], 'YouTube autoResume: non deve riaccendere il mic prima della fine del TTS.');
      assert.ok(stubs.createdAudios.length === 1, 'processTtsQueue deve creare un Audio instance.');

      stubs.createdAudios[0]._triggerEnded();

      assert.deepEqual(resumeCalls, ['A'], 'YouTube autoResume: deve riaccendere il mic solo dopo audio.onended.');
    } finally {
      stubs.restore();
    }
  }

  // 9) YouTube: se autoResume è disattivato, non deve riaccendere dopo TTS
  {
    const stubs = withNodeStubs();
    try {
      const vm = newVm();
      vm.activeTab = 'youtube';
      vm.youtubeAutoResumeEnabled = false;

      vm.ensureTtsAudioRouting = () => false;
      vm.getTtsAudioElementForChannel = () => null;
      vm.updateIsTtsPlaying = () => { };

      const resumeCalls = [];
      vm.toggleListeningForLang = (speaker) => { resumeCalls.push(speaker); };

      vm.ttsQueueByChannel = { left: [], right: [], center: [{ text: 'hello', locale: 'en-US' }] };
      vm.ttsPlayingByChannel = { left: false, right: false, center: false };

      await vm.processTtsQueue('center');
      stubs.createdAudios[0]._triggerEnded();

      assert.deepEqual(resumeCalls, [], 'YouTube autoResume disabled: non deve riaccendere il mic dopo TTS.');
    } finally {
      stubs.restore();
    }
  }

  // 10) YouTube mobile (isMobileLowPower): deve usare WebSpeech (non Whisper) e NON configurare onAutoPause se il motore non lo supporta
  {
    const w = withWindowWebSpeechStub();
    try {
      const vm = newVm();
      vm.activeTab = 'youtube';
      vm.isMobileLowPower = true; // mobile → WebSpeech
      vm.youtubeAutoPauseEnabled = true; // UI può essere attiva, ma WebSpeech non espone onAutoPause
      vm.youtubeLangSource = 'en';
      vm.youtubeLangTarget = 'it';

      assert.equal(vm.useWhisperEffective, false, 'YouTube mobile: useWhisperEffective deve essere false (usa WebSpeech).');

      await vm.toggleListeningForLang('A');

      assert.ok(vm.recognition instanceof FakeWebSpeechRecognition, 'YouTube mobile: recognition deve essere WebSpeech (FakeWebSpeechRecognition).');
      assert.equal(vm.recognition.continuous, false, 'YouTube mobile: WebSpeech deve usare continuous=false.');
      assert.equal(vm.recognition.interimResults, false, 'YouTube mobile: WebSpeech deve usare interimResults=false.');
      assert.equal(vm.isListening, true, 'YouTube mobile: deve andare in ascolto.');
      assert.ok(!('onAutoPause' in vm.recognition), 'YouTube mobile: non deve aspettarsi onAutoPause sul motore WebSpeech.');
      assert.equal(typeof vm.recognition.onAutoPause, 'undefined', 'YouTube mobile: onAutoPause deve essere undefined (non configurato).');
    } finally {
      w.restore();
    }
  }

  // 11) YouTube mobile: autoResume deve comunque riaccendere il mic SOLO dopo fine TTS (audio.onended)
  {
    const w = withWindowWebSpeechStub();
    const stubs = withNodeStubs();
    try {
      const vm = newVm();
      vm.activeTab = 'youtube';
      vm.isMobileLowPower = true;
      vm.youtubeAutoResumeEnabled = true;

      // Evita routing audio e logica extra: usa Audio(url)
      vm.ensureTtsAudioRouting = () => false;
      vm.getTtsAudioElementForChannel = () => null;
      vm.updateIsTtsPlaying = () => { };

      const resumeCalls = [];
      vm.toggleListeningForLang = (speaker) => { resumeCalls.push(speaker); };

      vm.ttsQueueByChannel = { left: [], right: [], center: [{ text: 'ciao', locale: 'it-IT' }] };
      vm.ttsPlayingByChannel = { left: false, right: false, center: false };

      await vm.processTtsQueue('center');
      assert.deepEqual(resumeCalls, [], 'YouTube mobile autoResume: non deve riaccendere il mic prima di audio.onended.');

      stubs.createdAudios[0]._triggerEnded();
      assert.deepEqual(resumeCalls, ['A'], 'YouTube mobile autoResume: deve riaccendere il mic solo dopo audio.onended.');
    } finally {
      stubs.restore();
      w.restore();
    }
  }

  // 12) YouTube mobile: se autoResume è disattivato, non deve riaccendere dopo TTS
  {
    const w = withWindowWebSpeechStub();
    const stubs = withNodeStubs();
    try {
      const vm = newVm();
      vm.activeTab = 'youtube';
      vm.isMobileLowPower = true;
      vm.youtubeAutoResumeEnabled = false;

      vm.ensureTtsAudioRouting = () => false;
      vm.getTtsAudioElementForChannel = () => null;
      vm.updateIsTtsPlaying = () => { };

      const resumeCalls = [];
      vm.toggleListeningForLang = (speaker) => { resumeCalls.push(speaker); };

      vm.ttsQueueByChannel = { left: [], right: [], center: [{ text: 'hello', locale: 'en-US' }] };
      vm.ttsPlayingByChannel = { left: false, right: false, center: false };

      await vm.processTtsQueue('center');
      stubs.createdAudios[0]._triggerEnded();
      assert.deepEqual(resumeCalls, [], 'YouTube mobile autoResume disabled: non deve riaccendere il mic dopo TTS.');
    } finally {
      stubs.restore();
      w.restore();
    }
  }
}

// Verifica che il pulsante principale usi il testo "Registra" quando non in ascolto
{
  const hasRecordLabel = source.includes('Registra') || source.includes('speakerASpeak');

  assert.ok(
    hasRecordLabel,
    'LiveTranslator.vue deve avere il pulsante principale etichettato come "Registra" (o tramite ui.speakerASpeak).',
  );
}

// Verifica che il modal di chiarificazione intenzione utilizzi il campo interlocutor_role
{
  const usesInterlocutorRole = source.includes('clarifyIntentInterlocutorRole') &&
    source.includes('interlocutor_role');

  assert.ok(
    usesInterlocutorRole,
    'LiveTranslator.vue deve propagare il ruolo dell’interlocutore (clarifyIntentInterlocutorRole → interlocutor_role).',
  );
}

// Regressione critica: in modalità call "leggi traduzione senza auricolari"
// il mic NON deve riaccendersi in onend (con Whisper onend arriva prima di onresult).
{
  const hasOldOnendFallback = source.includes('fallback auto-resume (no TTS started / empty text)');
  assert.ok(
    !hasOldOnendFallback,
    'LiveTranslator.vue: non deve esistere un fallback auto-resume in onend per pendingAutoResumeSpeakerAfterTts (causa resume troppo presto).',
  );
}

// Deve esistere il resume post-TTS in call (no auricolari) per l’auto-pausa.
{
  const hasResumeAfterTts = source.includes('resuming CALL listening after TTS (auto-pause)') &&
    source.includes('pendingAutoResumeSpeakerAfterTts');

  assert.ok(
    hasResumeAfterTts,
    'LiveTranslator.vue: deve riaccendere il microfono dopo la lettura TTS quando pendingAutoResumeSpeakerAfterTts è impostato.',
  );
}

// Se Whisper restituisce un risultato vuoto/filtrato (quindi niente TTS), deve comunque ripartire dal punto giusto (onresult).
{
  const hasResumeAfterEmptyFiltered = source.includes('resuming CALL listening after empty/filtered result (auto-pause)');
  assert.ok(
    hasResumeAfterEmptyFiltered,
    'LiveTranslator.vue: deve gestire il resume dopo risultato vuoto/filtrato in onresult (auto-pause), per evitare mic bloccato.',
  );
}

// Flussi principali UX (Call): toggle e opzioni chiave devono restare presenti
{
  const hasCallCoreToggles =
    source.includes('callTranslationEnabled') &&
    source.includes('readTranslationEnabledCall') &&
    source.includes('earphonesModeEnabledCall') &&
    source.includes('callAutoPauseEnabled');

  assert.ok(
    hasCallCoreToggles,
    'LiveTranslator.vue: la tab Call deve includere i toggle principali (traduzione, doppiaggio, auricolari, auto-pausa).',
  );
}

// Flussi principali UX (YouTube): opzioni chiave devono restare presenti
{
  const hasYoutubeCoreControls =
    source.includes('readTranslationEnabledYoutube') &&
    source.includes('youtubeAutoPauseEnabled') &&
    source.includes('youtubeAutoResumeEnabled') &&
    source.includes('youtubeUrl') &&
    source.includes('youtubeLangSource') &&
    source.includes('youtubeLangTarget');

  assert.ok(
    hasYoutubeCoreControls,
    'LiveTranslator.vue: la tab YouTube deve includere controlli principali (doppiaggio, auto pause/resume, URL, lingue).',
  );
}

// UX Android/mobile: avviso YouTube deve essere collassabile per non occupare troppo spazio
{
  const youtubeMobileWarningIsDetails =
    source.includes('youtubeMobileWarningShort') &&
    source.includes('<details') &&
    source.includes('ui.youtubeMobileWarning');

  assert.ok(
    youtubeMobileWarningIsDetails,
    'LiveTranslator.vue: l’avviso YouTube su mobile deve essere collassabile (<details>) e includere una label breve + testo completo.',
  );
}

// Avviso “Novità” in pagina (wow natalizio) deve esistere ed essere dismissibile
{
  const hasHolidayNotice =
    source.includes('showHolidayNotice') &&
    source.includes('ui.holidayNoticeTitle') &&
    source.includes('holiday-wow') &&
    source.includes('showHolidayNotice = false');

  assert.ok(
    hasHolidayNotice,
    'LiveTranslator.vue: deve esistere un avviso “Novità” dismissibile (showHolidayNotice) con stile wow natalizio (holiday-wow).',
  );
}









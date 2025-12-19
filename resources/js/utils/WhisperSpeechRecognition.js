/**
 * Wrapper che emula in modo minimale la Web Speech API di Chrome
 * usando Whisper di OpenAI lato backend.
 *
 * Espone la stessa interfaccia di base:
 *  - proprietà: lang, continuous, interimResults, maxAlternatives
 *  - callback: onstart, onresult, onerror, onend
 *  - metodi: start(), stop(), abort()
 *
 * Ogni volta che fermi l'ascolto, invia l'intera registrazione
 * al backend (/api/whisper/transcribe) e genera un unico risultato
 * finale (isFinal=true) con la trascrizione della frase.
 */
export default class WhisperSpeechRecognition {
    constructor() {
        this.lang = 'it-IT';
        this.continuous = true;
        this.interimResults = true;
        this.maxAlternatives = 1;
        // Hint opzionale dal chiamante: 'mic' (voce diretta) o 'speaker' (audio da casse/YouTube)
        this.sourceHint = null;

        // Elenco di lingue consentite (array di codici ISO come 'it', 'en', ecc.),
        // usato dal backend per bloccare trascrizioni in lingue fuori whitelist.
        this.allowedLangs = null;

        this.onstart = null;
        this.onresult = null;
        this.onerror = null;
        this.onend = null;

        this._mediaStream = null;
        this._recorder = null;
        this._isRecording = false;
        this._resultIndex = 0;
        this._chunks = [];
        // Parametri per segmentare in base al silenzio
        this._silenceMs = 800; // quanto tempo di silenzio per chiudere il segmento o triggerare auto-pausa
        this._maxSegmentMs = 6000; // sicurezza: durata massima di un singolo segmento
        // Soglia RMS per considerare "voce": tarata in modo da ignorare rumore di fondo leggero
        // e trattare come "silenzio" anche livelli bassi di rumore costante.
        // Abbassata per rendere più sensibile il VAD e permettere all'auto-pausa
        // di scattare in modo più affidabile anche con microfoni meno sensibili.
        this._silenceThreshold = 0.03;

        this._audioContext = null;
        this._analyser = null;
        this._volumeArray = null;
        this._freqArray = null;
        this._volumeCheckRaf = null;
        this._segmentStartedAt = 0;
        this._lastNonSilentAt = 0;
        this._segmentHasVoice = false;
        // Tempo totale (ms) in cui rileviamo banda voce (80–4000 Hz) nel segmento corrente
        this._voiceBandMs = 0;
        this._lastVadFrameAt = 0;

        // Callback opzionale usata dal chiamante per auto-pausa basata sul silenzio.
        // Non viene usata internamente per fermare la registrazione: serve solo a
        // notificare il layer superiore che è stata rilevata una lunga pausa.
        this.onAutoPause = null;
        this._autoPauseFired = false;

        // Se true, registriamo un unico segmento e inviamo tutto a Whisper
        // solo quando viene chiamato stop(). Utile, ad esempio, per la
        // modalità YouTube dove non vogliamo la segmentazione automatica
        // in base alle pause.
        this.singleSegmentMode = false;

        // Requisito: inviare SOLO se c'è almeno ~0.5s di frequenze vocali.
        this._minVoiceBandMsToSend = 500;
    }

    async start() {
        if (this._isRecording) {
            return;
        }

        try {
            if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                this._emitError('getUserMedia_not_available', 'API getUserMedia non disponibile in questo browser.');
                this._emitEnd();
                return;
            }

            // Su mobile non dobbiamo mai forzare i filtri audio: lasciamo la
            // configurazione di Android invariata usando audio:true.
            // Su desktop manteniamo i constraint "raw" per avere un segnale pulito.
            let audioConstraints = {
                echoCancellation: false,
                noiseSuppression: false,
                autoGainControl: false,
                channelCount: 1,
            };
            try {
                if (typeof window !== 'undefined' && window.matchMedia &&
                    window.matchMedia('(pointer: coarse)').matches) {
                    audioConstraints = true;
                }
            } catch {
                // in caso di errore restiamo con i constraint raw (desktop)
            }

            this._mediaStream = await navigator.mediaDevices.getUserMedia({
                audio: audioConstraints,
            });

            if (typeof MediaRecorder === 'undefined') {
                this._emitError('MediaRecorder_not_available', 'MediaRecorder non disponibile in questo browser.');
                this._stopStream();
                this._emitEnd();
                return;
            }

            // Inizializza l'AudioContext per fare una VAD semplice (rilevazione silenzio)
            if (!this._audioContext && (window.AudioContext || window.webkitAudioContext)) {
                const AC = window.AudioContext || window.webkitAudioContext;
                this._audioContext = new AC();
                const source = this._audioContext.createMediaStreamSource(this._mediaStream);
                this._analyser = this._audioContext.createAnalyser();
                this._analyser.fftSize = 1024;
                source.connect(this._analyser);
                this._volumeArray = new Uint8Array(this._analyser.fftSize);
                this._freqArray = new Uint8Array(this._analyser.frequencyBinCount);
            }

            this._isRecording = true;

            // Avvia il primo segmento di registrazione
            this._startNewSegmentRecorder();

            if (typeof this.onstart === 'function') {
                this.onstart();
            }
        } catch (e) {
            this._emitError(e.name || 'start_error', e.message || 'Errore avvio registrazione');
            this._stopStream();
            this._emitEnd();
        }
    }

    stop() {
        this._isRecording = false;
        if (this._recorder && this._recorder.state !== 'inactive') {
            try {
                this._recorder.stop();
            } catch {
                // ignora errori di stop
            }
        } else {
            this._stopStream();
            this._emitEnd();
        }
    }

    abort() {
        this.stop();
    }

    _stopStream() {
        if (this._mediaStream) {
            try {
                this._mediaStream.getTracks().forEach((t) => t.stop());
            } catch {
                // ignore
            }
        }
        this._mediaStream = null;
        this._recorder = null;
        this._chunks = [];

        if (this._volumeCheckRaf) {
            cancelAnimationFrame(this._volumeCheckRaf);
            this._volumeCheckRaf = null;
        }

        if (this._audioContext) {
            try {
                this._audioContext.close();
            } catch {
                // ignore
            }
        }
        this._audioContext = null;
        this._analyser = null;
        this._volumeArray = null;
        this._freqArray = null;
        this._segmentHasVoice = false;
    }

    _emitError(error, message) {
        if (typeof this.onerror === 'function') {
            try {
                this.onerror({ error, message });
            } catch {
                // ignore errors in user handlers
            }
        }
    }

    _emitEnd() {
        if (typeof this.onend === 'function') {
            try {
                this.onend();
            } catch {
                // ignore
            }
        }
    }

    async _handleChunk(blob) {
        // Inviamo il segmento come singolo file a Whisper
        // e produciamo un risultato "finale" (isFinal=true).
        try {
            if (!blob || blob.size === 0) {
                return;
            }

            const formData = new FormData();

            // Nome file con estensione .webm per aiutare Whisper nel riconoscere il formato
            formData.append('audio', blob, 'audio.webm');

            // Passiamo la lingua BCP-47; il backend ridurrà a codice ISO-639-1
            if (this.lang) {
                formData.append('lang', this.lang);
            }

            // Passiamo anche l'elenco di lingue consentite (se fornito dal chiamante),
            // così il backend può bloccare le trascrizioni in lingue fuori da questa whitelist.
            if (this.allowedLangs && this.allowedLangs.length) {
                try {
                    const cleaned = [];
                    for (let i = 0; i < this.allowedLangs.length; i++) {
                        const code = (this.allowedLangs[i] || '').toString().trim().toLowerCase();
                        if (code && cleaned.indexOf(code) === -1) {
                            cleaned.push(code);
                        }
                    }
                    if (cleaned.length) {
                        formData.append('allowed_langs', cleaned.join(','));
                    }
                } catch {
                    // in caso di errore nella pulizia, semplicemente non inviamo il campo
                }
            }

            // Usa sempre lo stesso "web origin" usato dagli altri widget:
            // - per EnjoyTalk3D:   window.__ENJOY_TALK_3D_ORIGIN__
            // - per EnjoyHen:      window.__ENJOY_HEN_ORIGIN__
            // - fallback generale: window.location.origin
            const origin =
                window.__ENJOY_TALK_3D_ORIGIN__ ||
                window.__ENJOY_HEN_ORIGIN__ ||
                window.location.origin;
            const resp = await fetch(`${origin}/api/whisper/transcribe`, {
                method: 'POST',
                body: formData,
            });

            if (!resp.ok) {
                this._emitError('whisper_http_error', `Errore HTTP Whisper: ${resp.status}`);
                return;
            }

            const json = await resp.json().catch(() => ({}));
            const rawText = (json.text || '').trim();
            if (!rawText) {
                return;
            }

            // Normalizza: rimuovi prefissi tipo "- " o "• " che spesso arrivano da liste/closing credits.
            const text = rawText.replace(/^\s*[-–•]\s+/, '').trim();
            if (!text) {
                return;
            }

            const lower = text.toLowerCase();

            // Filtri anti-rumore aggressivi: scarta output spurio, titoletti, simboli, ecc.
            if (
                text.length < 3 ||
                // Pattern comuni di titoletti/sottotitoli
                lower.includes('amara.org') ||
                lower.includes('sottotitoli creati') ||
                lower.includes('sottotitoli e revisione a cura di qtss') ||
                (lower.includes('sottotitoli') && lower.includes('qtss')) ||
                lower.includes('subtitle') ||
                lower.includes('caption') ||
                // Pattern di simboli/spazzatura
                /^[\*\!\?\#\~\-\_\.\,\;\:\"\'\(\)\[\]\{\}]+$/.test(text) ||
                // Solo numeri o simboli
                /^[\d\s\*\!\?\#\~\-\_\.\,\;\:\"\'\(\)\[\]\{\}]+$/.test(text)
            ) {
                console.log('🚫 WhisperSpeechRecognition: testo filtrato (pattern comune)', { text });
                return;
            }

            // Pattern da scartare (closing credits / filler)
            if (
                lower.includes('grazie per la visione') ||
                // "grazie." / "grazie a tutti." (anche con punteggiatura)
                /^\s*grazie(?:\s+a\s+tutti)?[\s\!\.\,]*$/.test(lower) ||
                // "sigh" isolato o con punteggiatura/emoji-like
                /^sigh[\s\!\.\,\-–•]*$/.test(lower) ||
                // "ssssshhh" / "ssshhh" (filler) anche con punteggiatura
                /^\s*s{3,}h{2,}[\s\!\.\,\-–•]*$/.test(lower)
            ) {
                console.log('🚫 WhisperSpeechRecognition: testo filtrato (closing/filler)', { text });
                return;
            }

            // Filtri per testi corti con molti simboli
            if (text.length <= 20) {
                const hasSpace = /\s/.test(text);
                const lettersOnly = text.replace(/[^A-Za-zÀ-ÖØ-öø-ÿ]/g, '');
                const letterRatio = lettersOnly.length / Math.max(text.length, 1);
                const symbolCount = (text.match(/[\*\!\?\#\~\-\_\.\,\;\:\"\'\(\)\[\]\{\}]/g) || []).length;
                const symbolRatio = symbolCount / Math.max(text.length, 1);

                // Scarta se:
                // - Non ha spazi E ha pochi caratteri letterali (< 40%) E ha molti simboli (> 30%)
                // - O ha pattern evidenti di spazzatura (es. "***!!!", "---", ecc.)
                if (
                    (!hasSpace && letterRatio < 0.4 && symbolRatio > 0.3) ||
                    /[\*\!\?\#\~]{2,}/.test(text) ||
                    /^[\-\_\.]{3,}$/.test(text) ||
                    (symbolRatio > 0.5 && letterRatio < 0.3)
                ) {
                    console.log('🚫 WhisperSpeechRecognition: testo filtrato (troppi simboli)', {
                        text,
                        letterRatio,
                        symbolRatio,
                        hasSpace,
                    });
                    return;
                }
            }

            // Emula parzialmente l'oggetto SpeechRecognitionResult
            const result = {
                0: { transcript: text },
                length: 1,
                isFinal: true,
                item(index) {
                    return this[index];
                },
            };

            const event = {
                // Sempre 0: LiveTranslator scorre da resultIndex a results.length
                resultIndex: 0,
                results: [result],
                // Link di debug per scaricare l'audio inviato al backend
                audioUrl: URL.createObjectURL(blob),
            };

            if (typeof this.onresult === 'function') {
                this.onresult(event);
            }
        } catch (e) {
            this._emitError(e.name || 'whisper_error', e.message || 'Errore chiamata Whisper');
        }
    }

    _buildFinalBlob() {
        if (!this._chunks || this._chunks.length === 0) {
            return new Blob();
        }

        // Proviamo a preservare il tipo del primo chunk, altrimenti default webm
        const first = this._chunks[0];
        const type = first && first.type ? first.type : 'audio/webm';
        return new Blob(this._chunks, { type });
    }

    _startNewSegmentRecorder() {
        if (!this._mediaStream) {
            return;
        }

        const options = {};
        if (typeof MediaRecorder.isTypeSupported === 'function' &&
            MediaRecorder.isTypeSupported('audio/webm')) {
            options.mimeType = 'audio/webm';
        }

        this._chunks = [];
        this._recorder = new MediaRecorder(this._mediaStream, options);

        const now = performance.now ? performance.now() : Date.now();
        this._segmentStartedAt = now;
        this._lastNonSilentAt = now;
        this._segmentHasVoice = false;
        this._autoPauseFired = false;
        this._voiceBandMs = 0;
        this._lastVadFrameAt = now;

        this._recorder.ondataavailable = (event) => {
            if (!event.data || event.data.size === 0) {
                return;
            }
            this._chunks.push(event.data);
        };

        this._recorder.onstop = () => {
            const blob = this._buildFinalBlob();

            // Invia il segmento solo se ha senso:
            // - invia SOLO se abbiamo rilevato frequenze compatibili con voce umana
            //   per almeno 1 secondo nel segmento corrente.
            const shouldSend =
                !!(blob && blob.size > 0)
                && (this._voiceBandMs >= (this._minVoiceBandMsToSend || 1000));

            if (shouldSend) {
                this._handleChunk(blob);
            }

            if (this._isRecording && !this.singleSegmentMode) {
                // Riprendi con un nuovo segmento finché l'utente non ferma il microfono
                this._startNewSegmentRecorder();
            } else {
                // Fermato dall'utente: chiudi completamente lo stream
                this._stopStream();
                this._emitEnd();
            }
        };

        this._recorder.start();

        // Avvia un loop che controlla il volume:
        // - in modalità multi-segmento: chiude il segmento quando c'è silenzio (_silenceMs)
        // - in modalità single-segment: non chiude il segmento, ma può invocare onAutoPause()
        //   per permettere al chiamante di fermare il microfono mantenendo il flusso originale
        //   (lo stop esplicito lancia la trascrizione).
        if (this._analyser && this._volumeArray) {
            const checkVolume = () => {
                if (!this._isRecording || !this._analyser || !this._volumeArray) {
                    this._volumeCheckRaf = null;
                    return;
                }

                this._analyser.getByteTimeDomainData(this._volumeArray);
                let sum = 0;
                for (let i = 0; i < this._volumeArray.length; i++) {
                    const v = (this._volumeArray[i] - 128) / 128;
                    sum += v * v;
                }
                const rms = Math.sqrt(sum / this._volumeArray.length);

                // Analisi spettrale di base: controlliamo che ci sia energia
                // significativa nella banda di frequenze tipica della voce.
                let hasVoiceBandEnergy = false;
                try {
                    if (this._freqArray) {
                        this._analyser.getByteFrequencyData(this._freqArray);
                        const sampleRate = this._audioContext && this._audioContext.sampleRate
                            ? this._audioContext.sampleRate
                            : 44100;
                        const binCount = this._freqArray.length;
                        const fftSize = this._analyser.fftSize || (binCount * 2);

                        let voiceEnergy = 0;
                        let totalEnergy = 0;

                        for (let i = 0; i < binCount; i++) {
                            const amp = this._freqArray[i] / 255;
                            const energy = amp * amp;
                            totalEnergy += energy;

                            const freq = i * (sampleRate / fftSize);
                            // Banda voce molto grossolana: 80 Hz – 4000 Hz
                            if (freq >= 80 && freq <= 4000) {
                                voiceEnergy += energy;
                            }
                        }

                        if (totalEnergy > 0 && voiceEnergy / totalEnergy > 0.2) {
                            hasVoiceBandEnergy = true;
                        }
                    }
                } catch {
                    // Se qualcosa va storto nell'analisi spettrale, non blocchiamo il VAD:
                    // continuiamo a usare solo l'RMS.
                    hasVoiceBandEnergy = false;
                }

                const nowTs = performance.now ? performance.now() : Date.now();

                const prevTs = this._lastVadFrameAt || nowTs;
                const dt = Math.max(0, nowTs - prevTs);
                this._lastVadFrameAt = nowTs;

                // Consideriamo "voce" SOLO quando:
                // - RMS sopra soglia
                // - energia nella banda tipica della voce (80–4000 Hz)
                if (rms > this._silenceThreshold && hasVoiceBandEnergy) {
                    this._lastNonSilentAt = nowTs;
                    this._segmentHasVoice = true;
                    this._voiceBandMs += dt;
                }

                const elapsedSegment = nowTs - this._segmentStartedAt;
                const silentFor = nowTs - this._lastNonSilentAt;

                if (this._recorder && this._recorder.state === 'recording') {
                    // Caso 1: modalità multi-segmento → chiudi il segmento quando c'è silenzio
                    // o si supera la durata massima.
                    if (!this.singleSegmentMode) {
                        if (silentFor >= this._silenceMs || elapsedSegment >= this._maxSegmentMs) {
                            try {
                                this._recorder.stop();
                            } catch {
                                // ignore
                            }
                            this._volumeCheckRaf = null;
                            return;
                        }
                    } else if (this.singleSegmentMode) {
                        // Caso 2: modalità single-segment → non chiudere il recorder qui.
                        // Se è stata registrata una lunga pausa e il chiamante ha fornito
                        // una callback onAutoPause(), notifichiamolo una sola volta.
                        // Richiediamo anche che ci sia stata VOCE reale (_segmentHasVoice)
                        // prima della pausa, così non scatta mai su segmenti completamente vuoti.
                        // Inoltre: se non abbiamo ancora accumulato abbastanza "banda voce",
                        // NON interrompiamo la registrazione per inviare (evita stop inutili).
                        if (!this._autoPauseFired &&
                            typeof this.onAutoPause === 'function' &&
                            this._segmentHasVoice &&
                            this._voiceBandMs >= (this._minVoiceBandMsToSend || 500) &&
                            silentFor >= this._silenceMs) {
                            this._autoPauseFired = true;
                            try {
                                this.onAutoPause();
                            } catch {
                                // ignora errori nella callback dell'utente
                            }
                        }
                    }
                }

                this._volumeCheckRaf = requestAnimationFrame(checkVolume);
            };

            if (!this._volumeCheckRaf) {
                this._volumeCheckRaf = requestAnimationFrame(checkVolume);
            }
        }
    }
}



/**
 * Wrapper che emula la Web Speech API di Chrome
 * usando Google Cloud Speech-to-Text lato backend.
 *
 * Interfaccia esposta:
 *  - proprietà: lang, continuous, interimResults, maxAlternatives
 *  - callback: onstart, onresult, onerror, onend
 *  - metodi: start(), stop(), abort()
 *
 * Implementazione molto simile a WhisperSpeechRecognition:
 * registriamo dal microfono via MediaRecorder, segmentiamo con una VAD
 * semplice e ad ogni segmento inviamo un blob al backend
 * (/api/google-speech/transcribe). Ogni risposta genera un unico risultato
 * finale (isFinal=true).
 */
export default class GoogleSpeechRecognition {
    constructor() {
        this.lang = 'it-IT';
        this.continuous = true;
        this.interimResults = true;
        this.maxAlternatives = 1;
        // Hint opzionale dal chiamante: 'mic' (voce diretta) o 'speaker' (audio da casse/YouTube)
        this.sourceHint = null;

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
        this._silenceMs = 800;
        this._maxSegmentMs = 6000;
        this._silenceThreshold = 0.02;

        this._audioContext = null;
        this._analyser = null;
        this._volumeArray = null;
        this._volumeCheckRaf = null;
        this._segmentStartedAt = 0;
        this._lastNonSilentAt = 0;
        this._segmentHasVoice = false;

        // Per ora manteniamo la stessa semantica di Whisper:
        // se true, registriamo un unico segmento e inviamo tutto solo su stop().
        this.singleSegmentMode = false;
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

            // Come per Whisper: su mobile alcuni device producono solo rumore se forziamo
            // tutti i filtri a false. Per avvicinarci al comportamento dei siti di registrazione,
            // su dispositivi "coarse pointer" chiediamo semplicemente audio: true.
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
                // in caso di errore manteniamo i constraint "raw"
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

            if (!this._audioContext && (window.AudioContext || window.webkitAudioContext)) {
                const AC = window.AudioContext || window.webkitAudioContext;
                this._audioContext = new AC();
                const source = this._audioContext.createMediaStreamSource(this._mediaStream);
                this._analyser = this._audioContext.createAnalyser();
                this._analyser.fftSize = 1024;
                source.connect(this._analyser);
                this._volumeArray = new Uint8Array(this._analyser.fftSize);
            }

            this._isRecording = true;
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
                // Su mobile, quando siamo in singleSegmentMode, dobbiamo esplicitamente
                // richiedere i dati prima di stop() per assicurarci che tutti i chunk
                // siano disponibili nel blob finale. Su mobile il MediaRecorder potrebbe
                // non emettere automaticamente dataavailable prima di stop().
                if (this.singleSegmentMode && typeof this._recorder.requestData === 'function') {
                    console.log('🛑 GoogleSpeechRecognition: stop() in singleSegmentMode, chiamo requestData()');
                    this._recorder.requestData();
                    // Aspettiamo un breve momento per dare tempo al MediaRecorder
                    // di emettere l'evento dataavailable prima di chiamare stop().
                    // Questo è particolarmente importante su mobile.
                    setTimeout(() => {
                        if (this._recorder && this._recorder.state !== 'inactive') {
                            try {
                                console.log('🛑 GoogleSpeechRecognition: chiamo stop() dopo requestData()');
                                this._recorder.stop();
                            } catch {
                                // ignora errori di stop
                            }
                        }
                    }, 150);
                } else {
                    console.log('🛑 GoogleSpeechRecognition: stop() normale', {
                        singleSegmentMode: this.singleSegmentMode,
                        hasRequestData: typeof this._recorder.requestData === 'function',
                    });
                    this._recorder.stop();
                }
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
        this._segmentHasVoice = false;
    }

    _emitError(error, message) {
        if (typeof this.onerror === 'function') {
            try {
                this.onerror({ error, message });
            } catch {
                // ignore
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
        try {
            if (!blob || blob.size === 0) {
                return;
            }

            const formData = new FormData();
            formData.append('audio', blob, 'audio.webm');

            if (this.lang) {
                formData.append('lang', this.lang);
            }

            const origin = window.__NEURON_TRANSLATOR_ORIGIN__ || window.location.origin;
            const resp = await fetch(`${origin}/api/gemini-speech/transcribe`, {
                method: 'POST',
                body: formData,
            });

            if (!resp.ok) {
                this._emitError('google_speech_http_error', `Errore HTTP Google Speech: ${resp.status}`);
                return;
            }

            const json = await resp.json().catch(() => ({}));
            const text = (json.text || '').trim();
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
                console.log('🚫 GoogleSpeechRecognition: testo filtrato (pattern comune)', { text });
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
                    console.log('🚫 GoogleSpeechRecognition: testo filtrato (troppi simboli)', {
                        text,
                        letterRatio,
                        symbolRatio,
                        hasSpace,
                    });
                    return;
                }
            }

            const result = {
                0: { transcript: text },
                length: 1,
                isFinal: true,
                item(index) {
                    return this[index];
                },
            };

            const event = {
                resultIndex: 0,
                results: [result],
                // Link di debug per scaricare l'audio inviato al backend
                audioUrl: URL.createObjectURL(blob),
            };

            if (typeof this.onresult === 'function') {
                this.onresult(event);
            }
        } catch (e) {
            this._emitError(e.name || 'google_speech_error', e.message || 'Errore chiamata Google Speech');
        }
    }

    _buildFinalBlob() {
        if (!this._chunks || this._chunks.length === 0) {
            return new Blob();
        }

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

        this._recorder.ondataavailable = (event) => {
            if (!event.data || event.data.size === 0) {
                console.log('📦 GoogleSpeechRecognition: dataavailable event vuoto', {
                    hasData: !!event.data,
                    dataSize: event.data ? event.data.size : 0,
                });
                return;
            }
            console.log('📦 GoogleSpeechRecognition: dataavailable', {
                chunkSize: event.data.size,
                chunksCount: this._chunks ? this._chunks.length + 1 : 1,
                singleSegmentMode: this.singleSegmentMode,
            });
            this._chunks.push(event.data);
        };

        this._recorder.onstop = () => {
            const blob = this._buildFinalBlob();

            const shouldSend =
                this.singleSegmentMode
                    ? !!(blob && blob.size > 0)
                    : this._segmentHasVoice;

            console.log('🎤 GoogleSpeechRecognition onstop', {
                singleSegmentMode: this.singleSegmentMode,
                blobSize: blob ? blob.size : 0,
                chunksCount: this._chunks ? this._chunks.length : 0,
                segmentHasVoice: this._segmentHasVoice,
                shouldSend,
                isRecording: this._isRecording,
            });

            if (shouldSend) {
                this._handleChunk(blob);
            } else {
                console.warn('⚠️ GoogleSpeechRecognition: blob vuoto o segmentHasVoice=false, non invio trascrizione', {
                    singleSegmentMode: this.singleSegmentMode,
                    blobSize: blob ? blob.size : 0,
                    segmentHasVoice: this._segmentHasVoice,
                    chunksCount: this._chunks ? this._chunks.length : 0,
                });
            }

            if (this._isRecording && !this.singleSegmentMode) {
                this._startNewSegmentRecorder();
            } else {
                this._stopStream();
                this._emitEnd();
            }
        };

        this._recorder.start();

        if (!this.singleSegmentMode && this._analyser && this._volumeArray) {
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

                const nowTs = performance.now ? performance.now() : Date.now();

                if (rms > this._silenceThreshold) {
                    this._lastNonSilentAt = nowTs;
                    this._segmentHasVoice = true;
                }

                const elapsedSegment = nowTs - this._segmentStartedAt;
                const silentFor = nowTs - this._lastNonSilentAt;

                if (this._recorder && this._recorder.state === 'recording') {
                    if (silentFor >= this._silenceMs || elapsedSegment >= this._maxSegmentMs) {
                        try {
                            this._recorder.stop();
                        } catch {
                            // ignore
                        }
                        this._volumeCheckRaf = null;
                        return;
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



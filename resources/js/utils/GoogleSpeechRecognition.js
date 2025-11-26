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

            this._mediaStream = await navigator.mediaDevices.getUserMedia({
                // Niente filtri di pulizia anche qui: trattiamo voce diretta e audio casse
                // allo stesso modo, lasciando a Gemini/Whisper il compito di gestire il rumore.
                audio: {
                    echoCancellation: false,
                    noiseSuppression: false,
                    autoGainControl: false,
                    channelCount: 1,
                },
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

            // Ricicliamo gli stessi filtri anti-rumore usati per Whisper
            if (
                text.length < 4 ||
                lower.includes('amara.org') ||
                lower.includes('sottotitoli creati') ||
                lower.includes('sottotitoli e revisione a cura di qtss') ||
                (lower.includes('sottotitoli') && lower.includes('qtss'))
            ) {
                return;
            }

            if (text.length <= 16) {
                const hasSpace = /\s/.test(text);
                const lettersOnly = text.replace(/[^A-Za-zÀ-ÖØ-öø-ÿ]/g, '');
                const letterRatio = lettersOnly.length / Math.max(text.length, 1);

                if (
                    !hasSpace &&
                    letterRatio < 0.4 &&
                    /[\*\!\?\#\~]{2,}/.test(text)
                ) {
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
                return;
            }
            this._chunks.push(event.data);
        };

        this._recorder.onstop = () => {
            const blob = this._buildFinalBlob();

            const shouldSend =
                this.singleSegmentMode
                    ? !!(blob && blob.size > 0)
                    : this._segmentHasVoice;

            if (shouldSend) {
                this._handleChunk(blob);
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



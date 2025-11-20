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
        this._silenceMs = 800; // quanto tempo di silenzio per chiudere il segmento
        this._maxSegmentMs = 6000; // sicurezza: durata massima di un singolo segmento
        this._silenceThreshold = 0.02; // soglia RMS approssimativa per considerare "voce"

        this._audioContext = null;
        this._analyser = null;
        this._volumeArray = null;
        this._volumeCheckRaf = null;
        this._segmentStartedAt = 0;
        this._lastNonSilentAt = 0;
        this._segmentHasVoice = false;
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

            // Apri una sola volta lo stream del microfono; lo riuseremo per più segmenti
            this._mediaStream = await navigator.mediaDevices.getUserMedia({
                audio: {
                    echoCancellation: true,
                    noiseSuppression: true,
                    autoGainControl: true,
                    channelCount: 1,
                },
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

            const origin = window.__NEURON_TRANSLATOR_ORIGIN__ || window.location.origin;
            const resp = await fetch(`${origin}/api/whisper/transcribe`, {
                method: 'POST',
                body: formData,
            });

            if (!resp.ok) {
                this._emitError('whisper_http_error', `Errore HTTP Whisper: ${resp.status}`);
                return;
            }

            const json = await resp.json().catch(() => ({}));
            const text = (json.text || '').trim();
            if (!text) {
                return;
            }

            const lower = text.toLowerCase();

            // Filtra output chiaramente spurio o generato su rumore
            if (
                text.length < 4 ||
                lower.includes('amara.org') ||
                lower.includes('sottotitoli creati') ||
                lower.includes('sottotitoli e revisione a cura di qtss') ||
                (lower.includes('sottotitoli') && lower.includes('qtss'))
            ) {
                return;
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

        this._recorder.ondataavailable = (event) => {
            if (!event.data || event.data.size === 0) {
                return;
            }
            this._chunks.push(event.data);
        };

        this._recorder.onstop = () => {
            const blob = this._buildFinalBlob();

            // Invia il segmento solo se c'è stata davvero voce
            if (this._segmentHasVoice) {
                this._handleChunk(blob);
            }

            if (this._isRecording) {
                // Riprendi con un nuovo segmento finché l'utente non ferma il microfono
                this._startNewSegmentRecorder();
            } else {
                // Fermato dall'utente: chiudi completamente lo stream
                this._stopStream();
                this._emitEnd();
            }
        };

        this._recorder.start();

        // Avvia un loop che controlla il volume e chiude il segmento quando c'è silenzio
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



@aware(['page'])
@props([
    'slug',
])

<div x-data="orderReader()" x-init="init()" class="relative p-4 md:p-8 bg-gray-100 dark:bg-gray-900 min-h-[50vh] flex flex-col items-center justify-center">

    <div class="w-full max-w-2xl text-center">
        <!-- Step 1: Choose method -->
        <div x-show="step === 'options'"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 transform scale-90"
             x-transition:enter-end="opacity-100 transform scale-100"
             x-transition:leave="transition ease-in duration-300"
             x-transition:leave-start="opacity-100 transform scale-100"
             x-transition:leave-end="opacity-0 transform scale-90">

            <p class="text-lg text-gray-500 dark:text-gray-400 mb-8">Scegli come fornire i dati</p>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Option 1: Upload File -->
                <div @click="setStep('upload')" class="relative p-6 bg-white dark:bg-gray-800 rounded-xl shadow-lg cursor-pointer transform hover:scale-105 transition-transform duration-300">
                    <div class="flex flex-col items-center justify-center">
                        <svg class="w-16 h-16 text-blue-500 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                        <h2 class="text-2xl font-bold text-gray-800 dark:text-white">Carica File</h2>
                        <p class="text-gray-500 dark:text-gray-400 mt-1">Seleziona un file dal tuo dispositivo.</p>
                    </div>
                </div>

                <!-- Option 2: Take Photo -->
                <div @click="setStep('photo')" class="relative p-6 bg-white dark:bg-gray-800 rounded-xl shadow-lg cursor-pointer transform hover:scale-105 transition-transform duration-300">
                     <div class="flex flex-col items-center justify-center">
                        <svg class="w-16 h-16 text-blue-500 mb-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        <h2 class="text-2xl font-bold text-gray-800 dark:text-white">Scatta Foto</h2>
                        <p class="text-gray-500 dark:text-gray-400 mt-1">Usa la fotocamera per un'acquisizione rapida.</p>
                    </div>
                </div>

                <!-- Option 3: Record Audio -->
                <div @click="setStep('audio')" class="relative p-6 bg-white dark:bg-gray-800 rounded-xl shadow-lg cursor-pointer transform hover:scale-105 transition-transform duration-300">
                    <div class="flex flex-col items-center justify-center">
                        <svg class="w-16 h-16 text-red-500 mb-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 18.75a6 6 0 006-6v-1.5a6 6 0 00-12 0v1.5a6 6 0 006 6z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5v1.5a7.5 7.5 0 11-15 0v-1.5a7.5 7.5 0 0115 0z" />
                        </svg>
                        <h2 class="text-2xl font-bold text-gray-800 dark:text-white">Registra Audio</h2>
                        <p class="text-gray-500 dark:text-gray-400 mt-1">Registra un vocale con i dettagli dell'ordine.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Step 2: Upload form -->
        <div x-show="step === 'upload'"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 transform scale-90"
             x-transition:enter-end="opacity-100 transform scale-100">
            
            <button @click="setStep('options')" class="absolute top-4 left-4 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-white z-10">
                &larr; Indietro
            </button>
            
            <div class="max-w-7xl mx-auto bg-white dark:bg-gray-800 shadow-md rounded-lg p-6">
                <form id="upload-form" action="/api/calzaturiero/process-order/{{ $slug }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                    @csrf
                    <div class="flex flex-col">
                        <label for="file" class="mb-2 text-lg font-medium text-gray-700 dark:text-gray-200">Carica il file:</label>
                        <input type="file" id="file" name="file" required class="p-2 border border-gray-300 dark:border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 bg-gray-50 dark:bg-gray-700 dark:text-gray-300">
                    </div>
                    <div class="flex justify-end">
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white font-semibold rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">Invia</button>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Step 3: Camera -->
        <div x-show="step === 'photo'"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 transform scale-90"
             x-transition:enter-end="opacity-100 transform scale-100">

            <button @click="setStep('options')" class="absolute top-4 left-4 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-white z-10">
                &larr; Indietro
            </button>

             <h2 class="mb-4 text-2xl font-bold text-center text-gray-800 dark:text-white">Scatta Foto</h2>
             <div class="mt-8 text-center bg-white dark:bg-gray-800 shadow-md rounded-lg p-6">
                 <div x-show="!photoTaken">
                    <video x-ref="video" class="w-full rounded-lg" autoplay playsinline></video>
                    <canvas x-ref="canvas" class="hidden"></canvas>
                    <button @click.stop="takePhoto()" :disabled="submitting" class="inline-flex items-center justify-center px-5 py-3 mt-4 text-base font-medium text-center text-white bg-blue-700 rounded-lg hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 dark:focus:ring-blue-900 disabled:bg-blue-400">
                        <span x-show="!submitting">Scatta e Invia</span>
                        <span x-show="submitting">Invio in corso...</span>
                    </button>
                 </div>
                 <div x-show="photoTaken" class="mt-4">
                     <img :src="photoUrl" class="w-full rounded-lg">
                     <p class="mt-2 text-sm text-green-500 dark:text-green-400">Foto inviata con successo!</p>
                 </div>
             </div>
        </div>

        <!-- Step 4: Audio Recording -->
        <div x-show="step === 'audio'"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 transform scale-90"
             x-transition:enter-end="opacity-100 transform scale-100">

            <button @click="setStep('options')" class="absolute top-4 left-4 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-white z-10">
                &larr; Indietro
            </button>

            <h2 class="mb-4 text-2xl font-bold text-center text-gray-800 dark:text-white">Registra Ordine Audio</h2>
            <div class="mt-8 text-center bg-white dark:bg-gray-800 shadow-md rounded-lg p-6">
                <div x-show="!isRecording && !audioBlob">
                    <button @click="startRecording()" class="inline-flex items-center justify-center px-5 py-3 text-base font-medium text-center text-white bg-red-700 rounded-lg hover:bg-red-800 focus:ring-4 focus:ring-red-300 dark:focus:ring-red-900">
                        Inizia Registrazione
                    </button>
                </div>
                <div x-show="isRecording">
                    <p class="text-lg text-gray-700 dark:text-gray-300">Registrazione in corso...</p>
                    <div class="mt-4 flex justify-center items-center space-x-2">
                        <span class="relative flex h-3 w-3">
                          <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                          <span class="relative inline-flex rounded-full h-3 w-3 bg-red-500"></span>
                        </span>
                        <span x-text="recordingTime + 's'"></span>
                    </div>
                    <button @click="stopRecording()" class="px-4 py-2 mt-4 bg-gray-600 text-white font-semibold rounded-md hover:bg-gray-700">Ferma</button>
                </div>
                <div x-show="audioBlob">
                     <p class="text-lg text-gray-700 dark:text-gray-300 mb-4">Registrazione completata.</p>
                     <audio :src="audioUrl" controls class="w-full"></audio>
                     <div class="flex justify-center space-x-4 mt-4">
                        <button @click="sendAudio" :disabled="submitting" class="px-4 py-2 bg-blue-600 text-white font-semibold rounded-md hover:bg-blue-700 disabled:bg-blue-400">
                            <span x-show="!submitting">Invia Audio</span>
                            <span x-show="submitting">Invio...</span>
                        </button>
                         <button @click="resetRecording" class="px-4 py-2 bg-gray-300 text-gray-700 font-semibold rounded-md hover:bg-gray-400">Registra di nuovo</button>
                     </div>
                </div>
            </div>
        </div>

    </div>
</div>

@push('scripts')
<script>
    function orderReader() {
        return {
            step: 'options', // 'options', 'upload', 'photo', 'audio'
            photoTaken: false,
            photoUrl: '',
            stream: null,
            submitting: false,
            actionUrl: '',
            // Audio properties
            isRecording: false,
            mediaRecorder: null,
            audioChunks: [],
            audioBlob: null,
            audioUrl: '',
            recordingTime: 0,
            recordingTimer: null,

            init() {
                const form = document.getElementById('upload-form');
                if(form) {
                    this.actionUrl = form.getAttribute('action');
                }
            },

            setStep(newStep) {
                if (this.step === 'photo') {
                    this.stopCamera();
                }
                if (this.step === 'audio') {
                    this.stopRecording(true); // Stop without processing if user navigates away
                }
                this.step = newStep;
                if (newStep === 'photo') {
                    this.startCamera();
                }
                if (newStep === 'audio') {
                    // Audio permissions are requested on button click
                }
            },
            
            async startCamera() {
                this.photoTaken = false;
                this.photoUrl = '';
                try {
                    this.stream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment' } });
                    this.$refs.video.srcObject = this.stream;
                } catch (err) {
                    console.error("Errore nell'accesso alla fotocamera: ", err);
                    alert("Impossibile accedere alla fotocamera. Assicurati di aver dato i permessi e che sia disponibile.");
                    this.setStep('options');
                }
            },

            stopCamera() {
                if (this.stream) {
                    this.stream.getTracks().forEach(track => track.stop());
                    this.stream = null;
                }
            },

            takePhoto() {
                if(this.submitting) return;
                this.submitting = true;

                const video = this.$refs.video;
                const canvas = this.$refs.canvas;
                canvas.width = video.videoWidth;
                canvas.height = video.videoHeight;
                const context = canvas.getContext('2d');
                context.drawImage(video, 0, 0, canvas.width, canvas.height);
                this.photoUrl = canvas.toDataURL('image/png');
                
                this.stopCamera();

                canvas.toBlob(blob => {
                    if (!blob) {
                        console.error('Impossibile creare il blob dall\'immagine');
                        alert('Errore nella creazione dell\'immagine. Riprova.');
                        this.submitting = false;
                        return;
                    }
                    
                    console.log('Blob creato:', blob.size, 'bytes');
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = this.actionUrl;
                    form.style.display = 'none';

                    const csrfTokenElement = document.querySelector('#upload-form input[name="_token"]');
                    if (!csrfTokenElement) {
                        console.error('CSRF token not found');
                        alert('Errore: Token di sicurezza non trovato. Ricaricare la pagina.');
                        this.submitting = false;
                        return;
                    }
                    const csrfInput = document.createElement('input');
                    csrfInput.type = 'hidden';
                    csrfInput.name = '_token';
                    csrfInput.value = csrfTokenElement.value;
                    form.appendChild(csrfInput);

                    const dataUrlInput = document.createElement('input');
                    dataUrlInput.type = 'hidden';
                    dataUrlInput.name = 'file_data_url';
                    dataUrlInput.value = this.photoUrl;
                    form.appendChild(dataUrlInput);

                    document.body.appendChild(form);
                    form.submit();
                }, 'image/png');
            },
            
            async startRecording() {
                if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                    alert('Il tuo browser non supporta la registrazione audio.');
                    return;
                }
                this.resetRecording();
                try {
                    const audioStream = await navigator.mediaDevices.getUserMedia({ audio: true });
                    this.mediaRecorder = new MediaRecorder(audioStream);
                    this.isRecording = true;

                    this.recordingTimer = setInterval(() => {
                        this.recordingTime++;
                    }, 1000);

                    this.mediaRecorder.ondataavailable = event => {
                        this.audioChunks.push(event.data);
                    };

                    this.mediaRecorder.onstop = () => {
                        this.audioBlob = new Blob(this.audioChunks, { type: 'audio/webm' });
                        this.audioUrl = URL.createObjectURL(this.audioBlob);
                        this.isRecording = false;
                        clearInterval(this.recordingTimer);
                        this.recordingTime = 0;
                        // Stop microphone stream
                        audioStream.getTracks().forEach(track => track.stop());
                    };

                    this.mediaRecorder.start();
                } catch (err) {
                    console.error('Errore accesso microfono:', err);
                    alert('Impossibile accedere al microfono. Assicurati di aver dato i permessi.');
                    this.isRecording = false;
                }
            },

            stopRecording(force = false) {
                if (this.mediaRecorder && this.isRecording) {
                    this.mediaRecorder.stop();
                    if (force) {
                         this.resetRecording();
                    }
                }
                 clearInterval(this.recordingTimer);
            },

            resetRecording() {
                this.isRecording = false;
                this.audioBlob = null;
                this.audioUrl = '';
                this.audioChunks = [];
                this.recordingTime = 0;
                if (this.recordingTimer) {
                    clearInterval(this.recordingTimer);
                }
            },

            sendAudio() {
                if (!this.audioBlob || this.submitting) return;
                this.submitting = true;

                const formData = new FormData();
                formData.append('file', this.audioBlob, 'registrazione-ordine.webm');
                
                const csrfTokenElement = document.querySelector('#upload-form input[name="_token"]');
                 if (!csrfTokenElement) {
                    console.error('CSRF token not found');
                    alert('Errore: Token di sicurezza non trovato. Ricaricare la pagina.');
                    this.submitting = false;
                    return;
                }
                formData.append('_token', csrfTokenElement.value);

                fetch(this.actionUrl, { method: 'POST', body: formData })
                    .then(response => {
                        // This will just submit, and the browser will handle the response (e.g. download or page redirect)
                        window.location.href = response.url;
                    })
                    .catch(error => {
                        console.error('Errore invio audio:', error);
                        alert('Si è verificato un errore durante l\'invio.');
                    })
                    .finally(() => {
                        this.submitting = false;
                    });
            },
            
            submitForm(event) {
                const form = event.target;
                if (form.elements.file.files.length === 0) {
                    event.preventDefault();
                    alert('Per favore, seleziona un file prima di inviare.');
                    return;
                }
                
                // Allow native form submission to handle the response
            }
        }
    }
</script>
@endpush

@aware(['page'])
@props([
    'slug',
])

<div x-data="orderReader()" x-init="init('{{ app()->getLocale() }}')" class="relative p-4 md:p-8 bg-gray-100 dark:bg-gray-900 min-h-[50vh] flex flex-col items-center justify-center">

    <div class="w-full max-w-2xl text-center">
        <!-- Step 1: Choose method -->
        <div x-show="step === 'options'"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 transform scale-90"
             x-transition:enter-end="opacity-100 transform scale-100"
             x-transition:leave="transition ease-in duration-300"
             x-transition:leave-start="opacity-100 transform scale-100"
             x-transition:leave-end="opacity-0 transform scale-90">

            <p class="text-lg text-gray-500 dark:text-gray-400 mb-8">{{ __('order-reader-component.choose_method') }}</p>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Option 1: Upload File -->
                <div @click="setStep('upload')" class="relative p-6 bg-white dark:bg-gray-800 rounded-xl shadow-lg cursor-pointer transform hover:scale-105 transition-transform duration-300">
                    <div class="flex flex-col items-center justify-center">
                        <svg class="w-16 h-16 text-blue-500 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                        <h2 class="text-2xl font-bold text-gray-800 dark:text-white">{{ __('order-reader-component.upload_file') }}</h2>
                        <p class="text-gray-500 dark:text-gray-400 mt-1">{{ __('order-reader-component.upload_file_desc') }}</p>
                    </div>
                </div>

                <!-- Option 2: Take Photo -->
                <div @click="setStep('photo')" class="relative p-6 bg-white dark:bg-gray-800 rounded-xl shadow-lg cursor-pointer transform hover:scale-105 transition-transform duration-300">
                     <div class="flex flex-col items-center justify-center">
                        <svg class="w-16 h-16 text-blue-500 mb-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        <h2 class="text-2xl font-bold text-gray-800 dark:text-white">{{ __('order-reader-component.take_photo') }}</h2>
                        <p class="text-gray-500 dark:text-gray-400 mt-1">{{ __('order-reader-component.take_photo_desc') }}</p>
                    </div>
                </div>

                <!-- Option 3: Record Audio -->
                <div @click="setStep('audio')" class="relative p-6 bg-white dark:bg-gray-800 rounded-xl shadow-lg cursor-pointer transform hover:scale-105 transition-transform duration-300">
                    <div class="flex flex-col items-center justify-center">
                        <svg class="w-16 h-16 text-blue-500 mb-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                           <path stroke-linecap="round" stroke-linejoin="round" d="M12 18.75a6 6 0 006-6v-1.5m-6 7.5a6 6 0 01-6-6v-1.5m6 7.5v3.75m-3.75 0h7.5M12 15.75a3 3 0 01-3-3V4.5a3 3 0 016 0v8.25a3 3 0 01-3 3z" />
                        </svg>
                        <h2 class="text-2xl font-bold text-gray-800 dark:text-white">{{ __('order-reader-component.record_audio') }}</h2>
                        <p class="text-gray-500 dark:text-gray-400 mt-1">{{ __('order-reader-component.record_audio_desc') }}</p>
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
                &larr; {{ __('order-reader-component.back') }}
            </button>
            
            <div class="max-w-7xl mx-auto bg-white dark:bg-gray-800 shadow-md rounded-lg p-6">
                <form id="upload-form" action="/api/calzaturiero/process-order/{{ $slug }}" method="POST" enctype="multipart/form-data" class="space-y-6" @submit.prevent="submitForm($event)">
                    @csrf
                    <input type="hidden" name="locale" :value="locale">
                    <div class="flex flex-col">
                        <label for="file" class="mb-2 text-lg font-medium text-gray-700 dark:text-gray-200">{{ __('order-reader-component.upload_the_file') }}</label>
                        <input type="file" id="file" name="file" required class="p-2 border border-gray-300 dark:border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 bg-gray-50 dark:bg-gray-700 dark:text-gray-300">
                    </div>
                    <div class="flex justify-end">
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white font-semibold rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">{{ __('order-reader-component.submit') }}</button>
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
                &larr; {{ __('order-reader-component.back') }}
            </button>

             <h2 class="mb-4 text-2xl font-bold text-center text-gray-800 dark:text-white">{{ __('order-reader-component.take_photo_title') }}</h2>
             <div class="mt-8 text-center bg-white dark:bg-gray-800 shadow-md rounded-lg p-6">
                 <div x-show="!photoTaken">
                    <video x-ref="video" class="w-full rounded-lg" autoplay playsinline></video>
                    <canvas x-ref="canvas" class="hidden"></canvas>
                    <button @click.stop="takePhoto()" :disabled="submitting" class="inline-flex items-center justify-center px-5 py-3 mt-4 text-base font-medium text-center text-white bg-blue-700 rounded-lg hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 dark:focus:ring-blue-900 disabled:bg-blue-400">
                        <span x-show="!submitting">{{ __('order-reader-component.snap_and_send') }}</span>
                        <span x-show="submitting">{{ __('order-reader-component.sending_in_progress') }}</span>
                    </button>
                 </div>
                 <div x-show="photoTaken" class="mt-4">
                     <img :src="photoUrl" class="w-full rounded-lg">
                     <p class="mt-2 text-sm text-green-500 dark:text-green-400">{{ __('order-reader-component.photo_sent_successfully') }}</p>
                 </div>
             </div>
        </div>

        <!-- Step 4: Audio Recording -->
        <div x-show="step === 'audio'"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 transform scale-90"
             x-transition:enter-end="opacity-100 transform scale-100">

            <button @click="setStep('options')" class="absolute top-4 left-4 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-white z-10">
                &larr; {{ __('order-reader-component.back') }}
            </button>

            <h2 class="mb-4 text-2xl font-bold text-center text-gray-800 dark:text-white">{{ __('order-reader-component.record_audio_order') }}</h2>
            <div class="mt-8 text-center bg-white dark:bg-gray-800 shadow-md rounded-lg p-6">
                <div x-show="!isRecording && !audioBlob">
                    <button @click="startRecording()" class="inline-flex items-center justify-center px-5 py-3 text-base font-medium text-center text-white bg-red-700 rounded-lg hover:bg-red-800 focus:ring-4 focus:ring-red-300 dark:focus:ring-red-900">
                        {{ __('order-reader-component.start_recording') }}
                    </button>
                </div>
                <div x-show="isRecording">
                    <p class="text-lg text-gray-700 dark:text-gray-300">{{ __('order-reader-component.recording_in_progress_status') }}</p>
                    <div class="mt-4 flex justify-center items-center space-x-2">
                        <span class="relative flex h-3 w-3">
                          <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                          <span class="relative inline-flex rounded-full h-3 w-3 bg-red-500"></span>
                        </span>
                        <span x-text="recordingTime + 's'"></span>
                    </div>
                    <button @click="stopRecording()" class="px-4 py-2 mt-4 bg-gray-600 text-white font-semibold rounded-md hover:bg-gray-700">{{ __('order-reader-component.stop') }}</button>
                </div>
                <div x-show="audioBlob">
                     <p class="text-lg text-gray-700 dark:text-gray-300 mb-4">{{ __('order-reader-component.recording_complete') }}</p>
                     <audio :src="audioUrl" controls class="w-full"></audio>
                     <div class="flex justify-center space-x-4 mt-4">
                        <button @click="sendAudio" :disabled="submitting" class="px-4 py-2 bg-blue-600 text-white font-semibold rounded-md hover:bg-blue-700 disabled:bg-blue-400">
                            <span x-show="!submitting">{{ __('order-reader-component.send_audio') }}</span>
                            <span x-show="submitting">{{ __('order-reader-component.sending') }}</span>
                        </button>
                         <button @click="resetRecording" class="px-4 py-2 bg-gray-300 text-gray-700 font-semibold rounded-md hover:bg-gray-400">{{ __('order-reader-component.record_again') }}</button>
                     </div>
                </div>
            </div>
        </div>

    </div>
</div>

<script>
    const translations = {
        camera_access_error: "{{ __('order-reader-component.camera_access_error') }}",
        blob_creation_error: "{{ __('order-reader-component.blob_creation_error') }}",
        csrf_token_error: "{{ __('order-reader-component.csrf_token_error') }}",
        audio_unsupported: "{{ __('order-reader-component.audio_unsupported') }}",
        mic_access_error: "{{ __('order-reader-component.mic_access_error') }}",
        audio_conversion_error: "{{ __('order-reader-component.audio_conversion_error') }}",
        select_file_before_submit: "{{ __('order-reader-component.select_file_before_submit') }}",
    };
</script>

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
            locale: 'en',

            _findSupportedMimeType() {
                const mimeTypes = [
                    'audio/ogg; codecs=opus',
                    'audio/wav',
                    'audio/ogg',
                    'audio/webm', // Fallback
                ];
                for (const mimeType of mimeTypes) {
                    if (MediaRecorder.isTypeSupported(mimeType)) {
                        return mimeType;
                    }
                }
                return ''; // Fallback to browser default
            },

            init(currentLocale) {
                this.locale = currentLocale;
                const form = document.getElementById('upload-form');
                if(form) {
                    this.actionUrl = form.getAttribute('action');
                }
                 this.translations = translations;
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
                    alert(this.translations.camera_access_error);
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
                        alert(this.translations.blob_creation_error);
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
                        alert(this.translations.csrf_token_error);
                        this.submitting = false;
                        return;
                    }
                    const csrfInput = document.createElement('input');
                    csrfInput.type = 'hidden';
                    csrfInput.name = '_token';
                    csrfInput.value = csrfTokenElement.value;
                    form.appendChild(csrfInput);

                    const localeInput = document.createElement('input');
                    localeInput.type = 'hidden';
                    localeInput.name = 'locale';
                    localeInput.value = this.locale;
                    form.appendChild(localeInput);

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
                    alert(this.translations.audio_unsupported);
                    return;
                }
                this.resetRecording();
                try {
                    const audioStream = await navigator.mediaDevices.getUserMedia({ audio: true });

                    const supportedMimeType = this._findSupportedMimeType();
                    const options = supportedMimeType ? { mimeType: supportedMimeType } : {};

                    this.mediaRecorder = new MediaRecorder(audioStream, options);
                    this.isRecording = true;

                    this.recordingTimer = setInterval(() => {
                        this.recordingTime++;
                    }, 1000);

                    this.mediaRecorder.ondataavailable = event => {
                        this.audioChunks.push(event.data);
                    };

                    this.mediaRecorder.onstop = () => {
                        const blobMimeType = this.mediaRecorder.mimeType || 'audio/webm';
                        this.audioBlob = new Blob(this.audioChunks, { type: blobMimeType });
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
                    alert(this.translations.mic_access_error);
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

                const reader = new FileReader();
                reader.readAsDataURL(this.audioBlob);

                reader.onloadend = () => {
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = this.actionUrl;
                    form.style.display = 'none';

                    const csrfTokenElement = document.querySelector('#upload-form input[name="_token"]');
                    if (!csrfTokenElement) {
                        console.error('CSRF token not found');
                        alert(this.translations.csrf_token_error);
                        this.submitting = false;
                        return;
                    }
                    const csrfInput = document.createElement('input');
                    csrfInput.type = 'hidden';
                    csrfInput.name = '_token';
                    csrfInput.value = csrfTokenElement.value;
                    form.appendChild(csrfInput);

                    const localeInput = document.createElement('input');
                    localeInput.type = 'hidden';
                    localeInput.name = 'locale';
                    localeInput.value = this.locale;
                    form.appendChild(localeInput);

                    const dataUrlInput = document.createElement('input');
                    dataUrlInput.type = 'hidden';
                    dataUrlInput.name = 'file_data_url';
                    dataUrlInput.value = reader.result;
                    form.appendChild(dataUrlInput);

                    document.body.appendChild(form);
                    form.submit();
                };

                reader.onerror = () => {
                    alert(this.translations.audio_conversion_error);
                    this.submitting = false;
                };
            },
            
            submitForm(event) {
                const form = event.target;
                if (form.elements.file.files.length === 0) {
                    event.preventDefault();
                    alert(this.translations.select_file_before_submit);
                    return;
                }
                
                // Manually submit the form to include the locale
                form.submit();
            }
        }
    }
</script>
@endpush

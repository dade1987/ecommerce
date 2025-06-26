<div class="bg-white dark:bg-gray-900 pt-2 pb-12">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-lg p-8">

            <form id="upload-form" class="space-y-6" action="{{ url('/api/calzaturiero/process-order/' . $slug) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div>
                    <label for="file-upload" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Seleziona un file PDF
                    </label>
                    <div class="mt-2 flex justify-center items-center px-6 pt-5 pb-6 border-2 border-gray-300 dark:border-gray-600 border-dashed rounded-md">
                        <div class="space-y-1 text-center">
                            <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48" aria-hidden="true">
                                <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4V12a4 4 0 014-4h12l8 8z" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                            <div class="flex text-sm text-gray-600 dark:text-gray-400">
                                <label for="file-upload" class="relative cursor-pointer bg-white dark:bg-gray-800 rounded-md font-medium text-indigo-600 dark:text-indigo-400 hover:text-indigo-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-indigo-500">
                                    <span>Carica un file</span>
                                    <input id="file-upload" name="file" type="file" class="sr-only" accept=".pdf">
                                </label>
                                <p class="pl-1">o trascinalo qui</p>
                            </div>
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                Solo PDF
                            </p>
                        </div>
                    </div>
                    <p id="file-name" class="mt-2 text-sm text-gray-500 dark:text-gray-400"></p>
                </div>

                <div class="mt-6 text-center">
                    <p class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Oppure</p>
                    <div class="flex justify-center space-x-4">
                        <button type="button" id="start-recording" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z"></path></svg>
                            Registra Audio
                        </button>
                        <button type="button" id="stop-recording" class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" disabled>
                            <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8 7a1 1 0 00-1 1v4a1 1 0 001 1h4a1 1 0 001-1V8a1 1 0 00-1-1H8z" clip-rule="evenodd"></path></svg>
                            Interrompi
                        </button>
                    </div>
                    <p id="recording-status" class="mt-2 text-sm text-gray-500 dark:text-gray-400"></p>
                </div>

                <div>
                    <button type="submit" id="submit-button" class="w-full flex justify-center py-3 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Estrai Informazioni
                    </button>
                </div>
            </form>

            <div id="loading" class="mt-6 text-center" style="display: none;">
                <p class="text-lg text-gray-700 dark:text-gray-300">Estrazione in corso... Potrebbero volerci fino a 30 secondi.</p>
                <div class="mt-4">
                    <svg class="animate-spin -ml-1 mr-3 h-8 w-8 text-indigo-600 inline-block" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </div>
            </div>

            <div id="results" class="mt-8" style="display: none;">
                <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">Dati Estratti</h3>
                <div class="bg-gray-100 dark:bg-gray-800 p-4 rounded-lg">
                    <pre class="whitespace-pre-wrap text-sm text-gray-800 dark:text-gray-200"><code id="json-output"></code></pre>
                </div>
                 <button id="copy-button" class="mt-4 w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                    Copia JSON
                </button>
            </div>
             <div id="error" class="mt-6 text-center text-red-500" style="display: none;"></div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('upload-form');
    const submitButton = document.getElementById('submit-button');
    const loading = document.getElementById('loading');
    const results = document.getElementById('results');
    const jsonOutput = document.getElementById('json-output');
    const errorDiv = document.getElementById('error');
    const fileInput = document.getElementById('file-upload');
    const fileNameDisplay = document.getElementById('file-name');
    const copyButton = document.getElementById('copy-button');

    // Audio recording elements
    const startRecordingButton = document.getElementById('start-recording');
    const stopRecordingButton = document.getElementById('stop-recording');
    const recordingStatus = document.getElementById('recording-status');
    let mediaRecorder;
    let audioChunks = [];
    let audioFile = null;

    fileInput.addEventListener('change', (e) => {
        const file = e.target.files[0];
        if (file) {
            fileNameDisplay.textContent = `File selezionato: ${file.name}`;
            audioFile = null; // Clear audio file if a regular file is selected
            recordingStatus.textContent = '';
        } else {
            fileNameDisplay.textContent = '';
        }
    });

    startRecordingButton.addEventListener('click', async () => {
        if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
            alert('Il tuo browser non supporta la registrazione audio.');
            return;
        }

        try {
            const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
            mediaRecorder = new MediaRecorder(stream);
            
            audioChunks = [];
            mediaRecorder.ondataavailable = event => {
                audioChunks.push(event.data);
            };

            mediaRecorder.onstop = () => {
                const audioBlob = new Blob(audioChunks, { type: 'audio/webm' });
                audioFile = new File([audioBlob], 'registrazione.webm', { type: 'audio/webm' });
                recordingStatus.textContent = 'Registrazione completata. Premi "Estrai" per processare.';
                fileNameDisplay.textContent = `File audio pronto: registrazione.webm`;
                fileInput.value = ''; // Clear file input
            };

            mediaRecorder.start();
            startRecordingButton.disabled = true;
            stopRecordingButton.disabled = false;
            recordingStatus.textContent = 'Registrazione in corso...';

        } catch (err) {
            console.error('Errore durante l\'accesso al microfono:', err);
            recordingStatus.textContent = 'Errore microfono: ' + err.message;
        }
    });

    stopRecordingButton.addEventListener('click', () => {
        if (mediaRecorder && mediaRecorder.state === 'recording') {
            mediaRecorder.stop();
            startRecordingButton.disabled = false;
            stopRecordingButton.disabled = true;
        }
    });

    form.addEventListener('submit', async function (e) {
        e.preventDefault();

        const formData = new FormData();
        const actionUrl = this.action;
        
        // Check if there is an audio file or a file from input
        if (audioFile) {
            formData.append('file', audioFile, audioFile.name);
        } else if (fileInput.files.length > 0) {
            formData.append('file', fileInput.files[0]);
        } else {
            alert('Per favore, seleziona un file o registra un audio.');
            return;
        }

        loading.style.display = 'block';
        results.style.display = 'none';
        errorDiv.style.display = 'none';
        jsonOutput.textContent = '';

        try {
            const response = await fetch(actionUrl, {
                method: 'POST',
                body: formData,
                headers: {
                    'Accept': 'application/json',
                },
            });

            if (!response.ok) {
                // Try to parse error from JSON response
                let errorMsg = `Errore del server: ${response.status}`;
                try {
                    const errorData = await response.json();
                    if(errorData.error) {
                        errorMsg = errorData.error;
                    }
                } catch(e) {
                    // Ignore if response is not JSON
                }
                throw new Error(errorMsg);
            }

            // Check if response is downloadable file (Excel/CSV) or JSON
            const contentType = response.headers.get("content-type");
            if (contentType && (contentType.indexOf("application/vnd.openxmlformats-officedocument.spreadsheetml.sheet") !== -1 || contentType.indexOf("text/csv") !== -1)) {
                const blob = await response.blob();
                const url = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.style.display = 'none';
                a.href = url;
                
                const contentDisposition = response.headers.get('content-disposition');
                let filename = 'download';
                if (contentDisposition && contentDisposition.indexOf('attachment') !== -1) {
                    const filenameRegex = /filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/;
                    const matches = filenameRegex.exec(contentDisposition);
                    if (matches != null && matches[1]) {
                      filename = matches[1].replace(/['"]/g, '');
                    }
                }
                a.download = filename;

                document.body.appendChild(a);
                a.click();
                window.URL.revokeObjectURL(url);
                document.body.removeChild(a);

                results.style.display = 'block';
                jsonOutput.textContent = `File "${filename}" scaricato con successo.`;


            } else {
                 const data = await response.json();
                 jsonOutput.textContent = JSON.stringify(data, null, 2);
                 results.style.display = 'block';
            }


        } catch (error) {
            console.error('Errore:', error);
            errorDiv.textContent = 'Si è verificato un errore: ' + error.message;
            errorDiv.style.display = 'block';
        } finally {
            loading.style.display = 'none';
        }
    });

    copyButton.addEventListener('click', () => {
        if(jsonOutput.textContent.startsWith('File "')) return; // Don't copy download message
        navigator.clipboard.writeText(jsonOutput.textContent).then(() => {
            copyButton.textContent = 'Copiato!';
            setTimeout(() => {
                copyButton.textContent = 'Copia JSON';
            }, 2000);
        }).catch(err => {
            console.error('Errore durante la copia:', err);
            alert('Impossibile copiare il testo.');
        });
    });
});
</script> 
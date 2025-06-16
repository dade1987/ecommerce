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

                <div>
                    <button type="submit" class="w-full flex justify-center py-3 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
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
    const loading = document.getElementById('loading');
    const results = document.getElementById('results');
    const jsonOutput = document.getElementById('json-output');
    const errorDiv = document.getElementById('error');
    const fileInput = document.getElementById('file-upload');
    const fileNameDisplay = document.getElementById('file-name');
    const copyButton = document.getElementById('copy-button');

    fileInput.addEventListener('change', (e) => {
        const file = e.target.files[0];
        if (file) {
            fileNameDisplay.textContent = `File selezionato: ${file.name}`;
        } else {
            fileNameDisplay.textContent = '';
        }
    });

    form.addEventListener('submit', async function (e) {
        e.preventDefault();

        const formData = new FormData(this);
        const actionUrl = this.action;

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

            const data = await response.json();

            if (!response.ok) {
                 throw new Error(data.error || `Errore del server: ${response.status}`);
            }
            
            jsonOutput.textContent = JSON.stringify(data, null, 2);
            results.style.display = 'block';

        } catch (error) {
            console.error('Errore:', error);
            errorDiv.textContent = 'Si è verificato un errore: ' + error.message;
            errorDiv.style.display = 'block';
        } finally {
            loading.style.display = 'none';
        }
    });

    copyButton.addEventListener('click', () => {
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
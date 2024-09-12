@aware(['page'])

<h2 class="text-2xl font-bold text-gray-800 text-center mb-6">Intelligenza Artificiale per l'Energia Solare</h2>

<div class="flex bg-white h-[70vh]">

    <div id="chatbox-window" class="flex flex-col flex-1 overflow-hidden">
        <div id="drop-area" class="border-dashed border-2 border-gray-400 p-4 rounded-lg mb-2 text-center">
            Trascina qui i file o clicca per caricarli
        </div>
        <input type="file" id="fileElem" class="hidden" multiple>
        <div id="chatbox" class="flex-1 overflow-y-auto p-4 bg-gray-50"></div>
        <div class="pb-4 flex items-center p-2 bg-gray-100">
            <input type="text" id="user-input" class="flex-grow border rounded-l-lg p-2" placeholder="Scrivi un messaggio...">
            <button id="send-button" class="bg-blue-500 text-white p-2 rounded-r-lg">Invia</button>
        </div>
        {{--<button id="generate-quote-button" class="bg-blue-500 text-white rounded-lg w-full p-4">Genera Preventivo</button>--}}
    </div>
</div>


<!-- Modal -->
<div class="fixed inset-0 bg-gray-900 bg-opacity-50 flex items-center justify-center hidden" id="quoteModal">
    <div class="bg-white rounded-lg overflow-hidden w-11/12 md:w-1/2 lg:w-1/3">
        <div class="p-4 flex justify-between items-center border-b">
            <h5 class="text-xl font-bold" id="quoteModalLabel">AI-799</h5>
            <button type="button" class="text-gray-400" id="closeModal">&times;</button>
        </div>
        <div class="p-4">
            <div id="quoteContent" class="p-4"></div>
        </div>
        <div class="p-4 border-t flex justify-end">
            <button type="button" class="bg-gray-500 text-white p-2 rounded mr-2" id="closeModalButton">Chiudi</button>
            <button type="button" class="bg-blue-500 text-white p-2 rounded" id="downloadPdf">Scarica PDF</button>
            <button type="button" class="bg-green-500 text-white p-2 rounded" id="confirmQuote">Conferma Preventivo</button>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js" integrity="sha512-BNaRQnYJYiPSqHHDb58B0yaPfCu+Wgds8Gp/gU33kqBtgNS4tSPHuGibyoeqMV/TJlSKda6FXzoEyYGjTe+vXA==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script>
    let quoteContent = '';
    let chatbotName = 'AI-799';

    const dropArea = document.getElementById('drop-area');
    const fileInput = document.getElementById('fileElem');

    dropArea.addEventListener('dragover', (event) => {
        event.preventDefault();
        dropArea.classList.add('border-green-500');
    });

    dropArea.addEventListener('dragleave', () => {
        dropArea.classList.remove('border-green-500');
    });

    dropArea.addEventListener('drop', (event) => {
        event.preventDefault();
        dropArea.classList.remove('border-green-500');
        const files = event.dataTransfer.files;
        handleFiles(files);
    });

    dropArea.addEventListener('click', () => {
        fileInput.click();
    });

    fileInput.addEventListener('change', () => {
        const files = fileInput.files;
        handleFiles(files);
    });

    function handleFiles(files) {
        for (const file of files) {
            uploadFile(file);
        }
    }

    function uploadFile(file) {
        addLoadingIndicator();
        const formData = new FormData();
        formData.append('file', file);

        fetch('/api/upload-file', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            removeLoadingIndicator();
            addMessageToChatbox(chatbotName, data.response, 'border border-gray-200 text-black');

        })
        .catch(error => {
            addMessageToChatbox('Errore', `Errore durante il caricamento del file: ${file.name}`, 'border border-red-500 text-black');
        });
    }

    document.getElementById('confirmQuote').addEventListener('click', function() {
        console.log(quoteContent);
        alert('Preventivo Inviato');
        document.getElementById('quoteModal').classList.add('hidden');
    });

    document.getElementById('downloadPdf').addEventListener('click', function() {
        const {
            jsPDF
        } = window.jspdf;
        const doc = new jsPDF('p', 'mm', 'a4');
        const quoteContent = document.getElementById('quoteContent');

        html2canvas(quoteContent).then(canvas => {
            const imgData = canvas.toDataURL('image/png');
            const imgWidth = 210;
            const pageHeight = 295;
            const imgHeight = canvas.height * imgWidth / canvas.width;
            let heightLeft = imgHeight;
            let position = 0;

            doc.addImage(imgData, 'PNG', 0, position, imgWidth, imgHeight);
            heightLeft -= pageHeight;

            while (heightLeft >= 0) {
                position = heightLeft - imgHeight;
                doc.addPage();
                doc.addImage(imgData, 'PNG', 0, position, imgWidth, imgHeight);
                heightLeft -= pageHeight;
            }

            doc.save('preventivo.pdf');
        });
    });

    document.getElementById('send-button').addEventListener('click', () => {
        const userInput = document.getElementById('user-input').value;
        if (userInput) {
            addMessageToChatbox('Tu', userInput, 'border border-blue-500 text-black');
            addLoadingIndicator();
            sendMessageToServer(userInput);
            document.getElementById('user-input').value = '';
        }
    });

    document.getElementById('user-input').addEventListener('keydown', (event) => {
        if (event.key === 'Enter' || event.keyCode === 13) {
            const userInput = document.getElementById('user-input').value;
            if (userInput) {
                addMessageToChatbox('Tu', userInput, 'border border-blue-500 text-black');
                addLoadingIndicator();
                sendMessageToServer(userInput);
                document.getElementById('user-input').value = '';
            }
        }
    });

    function addLoadingIndicator() {
        const chatbox = document.getElementById('chatbox');
        const loadingElement = document.createElement('div');
        loadingElement.id = 'loading-indicator';
        loadingElement.classList.add('p-4', 'rounded-lg', 'bg-gray-200', 'text-black', 'mt-2');
        loadingElement.innerHTML = '<h5>'+chatbotName+'</h5><span class="loading-dots">Sto rispondendo</span>';
        chatbox.appendChild(loadingElement);
        chatbox.scrollTop = chatbox.scrollHeight;
    }

    function removeLoadingIndicator() {
        const loadingElement = document.getElementById('loading-indicator');
        if (loadingElement) {
            loadingElement.remove();
        }
    }

    document.getElementById('generate-quote-button').addEventListener('click', () => {
        addMessageToChatbox('Tu', 'Genera Preventivo', 'border border-blue-500 text-black');
        sendMessageToServer('Genera Preventivo');
    });

    function addMessageToChatbox(sender, message, messageType) {
        message = message.replaceAll('\n', '<br>');

        const chatbox = document.getElementById('chatbox');
        const messageElement = document.createElement('div');
        messageElement.innerHTML = `<h5>${sender}</h5>${message}`;
        messageElement.classList.add('p-4', 'rounded-lg', 'mt-2');
        // Aggiunge singolarmente ogni classe extra (messageType)
        messageType.split(' ').forEach(cls => messageElement.classList.add(cls));
        chatbox.appendChild(messageElement);
        chatbox.scrollTop = chatbox.scrollHeight;
    }


    async function sendMessageToServer(message) {
        try {
            const response = await fetch('/api/send-message', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    message
                })
            });
            const data = await response.json();

            const json = data.response;

            console.log(json);

            const jsonString = extractJson(json);

            console.log('json match', jsonString);

            if (jsonString != null) {
                const jsonParsed = JSON.parse(jsonString);

                console.log(jsonParsed);

                if (jsonParsed.company_info !== undefined) {
                    quoteContent = `
                    <div>
                        <h3 class="text-lg font-semibold">AI-799</h3>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold">Informazioni Personali</h3>
                        <p><strong>Nome:</strong> ${jsonParsed.personal_info.first_name} ${jsonParsed.personal_info.last_name}</p>
                        <p><strong>Numero di Telefono:</strong> ${jsonParsed.personal_info.phone_number}</p>
                        <p><strong>Email:</strong> ${jsonParsed.personal_info.email}</p>
                    </div>
                    <table class="w-full mt-4 bg-white shadow-md rounded-lg">
                        <thead class="bg-gray-200">
                            <tr>
                                <th class="p-2">Prodotto</th>
                                <th class="p-2">Quantità</th>
                                <th class="p-2">Prezzo</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${jsonParsed.products.map(product => `
                                                            <tr>
                                                                <td class="p-2 border">${product.name}</td>
                                                                <td class="p-2 border">${product.quantity}</td>
                                                                <td class="p-2 border">€ ${product.price.toFixed(2)}</td>
                                                            </tr>
                                                        `).join('')}
                            <tr class="font-semibold">
                                <td colspan="2" class="p-2 text-right border">Prezzo Netto</td>
                                <td class="p-2 border">€ ${jsonParsed.price_info.net_price.toFixed(2)}</td>
                            </tr>
                            <tr class="font-semibold">
                                <td colspan="2" class="p-2 text-right border">IVA</td>
                                <td class="p-2 border">€ ${jsonParsed.price_info.vat.toFixed(2)}</td>
                            </tr>
                            <tr class="font-semibold">
                                <td colspan="2" class="p-2 text-right border">Prezzo Totale</td>
                                <td class="p-2 border">€ ${jsonParsed.price_info.total.toFixed(2)}</td>
                            </tr>
                        </tbody>
                    </table>
                `;
                    document.getElementById('quoteContent').innerHTML = quoteContent;
                    document.getElementById('quoteModal').classList.remove('hidden');
                } else {
                    addMessageToChatbox(chatbotName, data.response, 'border border-gray-200 text-black');
                }
            } else {
                addMessageToChatbox(chatbotName, data.response, 'border border-gray-200 text-black');
            }
        } catch (error) {
            addMessageToChatbox('Errore', error.message, 'border border-red-500 text-black');
        } finally {
            removeLoadingIndicator();
        }
    }

    async function createThread() {
        await fetch('/api/create-thread', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            }
        });

        addMessageToChatbox(chatbotName,
            'Ciao. Benvenuto in '+chatbotName+'. Attendi alcuni secondi...',
            'border border-gray-200 text-black');

        sendMessageToServer('Intro');
    }

    function extractJson(text) {
        const stack = [];
        let start = -1;

        for (let i = 0; i < text.length; i++) {
            if (text[i] === '{') {
                if (stack.length === 0) {
                    start = i;
                }
                stack.push('{');
            } else if (text[i] === '}') {
                stack.pop();
                if (stack.length === 0) {
                    const jsonString = text.substring(start, i + 1);
                    return jsonString;
                }
            }
        }
        return null;
    }

    createThread();

    document.getElementById('closeModal').addEventListener('click', () => {
        document.getElementById('quoteModal').classList.add('hidden');
    });

    document.getElementById('closeModalButton').addEventListener('click', () => {
        document.getElementById('quoteModal').classList.add('hidden');
    });

</script>

<style>
    #drop-area {
        transition: border-color 0.3s ease;
    }
</style>

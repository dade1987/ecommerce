<div id="chatContainer" class="flex flex-col h-[75vh] bg-white rounded px-6">
    <div class="bg-white">
        <h1 class="font-montserrat text-2xl text-left text-gray-800"><strong>Sommelier</strong> Virtuale</h1>
    </div>

    <!-- Container per i bottoni di risposte rapide -->
    <div id="quickReplies" class="flex flex-wrap gap-2 mb-4">
        <button class="quick-reply-btn bg-blue-100 text-blue-800 px-3 py-2 rounded" data-message="Dammi informazioni su un vino.">Informazioni sul vino</button>
        <button class="quick-reply-btn bg-blue-100 text-blue-800 px-3 py-2 rounded" data-message="Quali abbinamenti consiglieresti per questo piatto?">Abbinamenti cibo-vino</button>
        <button class="quick-reply-btn bg-blue-100 text-blue-800 px-3 py-2 rounded" data-message="Ci sono eventi o degustazioni in programma?">Eventi e degustazioni</button>
        <button class="quick-reply-btn bg-blue-100 text-blue-800 px-3 py-2 rounded" data-message="Raccontami una curiosità sul vino.">Curiosità sul vino</button>
    </div>

    <div id="messages" class="flex-1 overflow-y-scroll bg-white text-gray-800 py-6"></div>
    <div class="flex border-t border-gray-300 bg-white w-full">
        <div class="relative flex-1 flex items-center">
            <div class="flex w-full rounded overflow-hidden bg-white">
                <input id="userInput" type="text" placeholder="Scrivi un messaggio..." class="flex-1 p-4 border-none text-gray-800 font-arial text-base">
                <button id="sendButton" class="bg-white text-black font-arial text-base cursor-pointer border-none">
                    Invia
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const messagesElement = document.getElementById('messages');
        const userInputElement = document.getElementById('userInput');
        const sendButton = document.getElementById('sendButton');
        const quickRepliesContainer = document.getElementById('quickReplies');
        let threadId = null;
        let productIds = []; // Array per gestire eventuali product_ids
        // Estrai il team dall'URL: supponiamo l'ultima parte del percorso
        const team = window.location.pathname.split('/').pop();
        let firstMessageSent = false;

        // Event listener per i bottoni di risposte rapide
        const quickReplyButtons = document.querySelectorAll('.quick-reply-btn');
        quickReplyButtons.forEach(button => {
            button.addEventListener('click', function() {
                const quickMessage = this.getAttribute('data-message');
                if (!firstMessageSent) {
                    firstMessageSent = true;
                    quickRepliesContainer.style.display = 'none';
                }
                userInputElement.value = quickMessage;
                sendMessage();
            });
        });

        // Estrai l'UUID dalla query string per la registrazione della visita (se presente)
        const urlParams = new URLSearchParams(window.location.search);
        const uuid = urlParams.get('uuid');

        if (uuid) {
            fetch(`/api/visit/${uuid}`)
                .then(response => response.json())
                .then(data => {
                    console.log('Visita registrata:', data.message);
                })
                .catch(error => {
                    console.error('Errore durante la registrazione della visita:', error);
                });
        }

        // Invia il messaggio "Buongiorno" al caricamento della pagina
        postMessage('Buongiorno').then(response => {
            const botMessage = {
                id: Date.now(),
                role: 'bot',
                content: `<strong>${response.message.split(' ')[0]}</strong> ${response.message.slice(response.message.indexOf(' ') + 1)}`,
            };
            addMessageToChat(botMessage);
        });

        sendButton.addEventListener('click', sendMessage);
        userInputElement.addEventListener('keyup', function(event) {
            if (event.key === 'Enter') {
                sendMessage();
            }
        });

        async function sendMessage() {
            const userInput = userInputElement.value.trim();
            if (userInput === '') return;
            if (!firstMessageSent) {
                firstMessageSent = true;
                quickRepliesContainer.style.display = 'none';
            }

            const userMessage = {
                id: Date.now(),
                role: 'user',
                content: `<strong>${userInput.split(' ')[0]}</strong> ${userInput.slice(userInput.indexOf(' ') + 1)}`,
            };

            addMessageToChat(userMessage);
            userInputElement.value = '';

            const typingIndicator = addTypingIndicator();
            const response = await postMessage(userInput);
            removeTypingIndicator(typingIndicator);

            const botMessage = {
                id: Date.now() + 1,
                role: 'bot',
                content: `<strong>${response.message.split(' ')[0]}</strong> ${response.message.slice(response.message.indexOf(' ') + 1)}`,
            };
            addMessageToChat(botMessage);

            // Aggiorna eventuali product_ids se presenti nella risposta
            if (response.product_ids) {
                productIds = response.product_ids;
            }
        }

        function addMessageToChat(message) {
            const messageElement = document.createElement('div');
            messageElement.className = `message ${message.role}`;
            messageElement.style = `padding: 10px; margin-bottom: 10px; border-radius: 20px; background-color: #ffffff; color: ${message.role === 'user' ? '#00008b' : 'black'}; border: ${message.role === 'user' ? '2px solid #9090ff' : '3px solid rgb(236, 236, 236)'}; font-family: Montserrat, sans-serif;`;
            messageElement.innerHTML = `<span style="font-size: 16px;">${message.content}</span>`;
            messagesElement.appendChild(messageElement);
            messagesElement.scrollTop = messagesElement.scrollHeight;
        }

        function addTypingIndicator() {
            const typingElement = document.createElement('div');
            typingElement.className = 'message bot';
            typingElement.style = 'padding: 10px; margin-bottom: 10px; border-radius: 20px; background-color: #ffffff; color: black; border: 3px solid rgb(236, 236, 236); font-family: Montserrat, sans-serif;';
            typingElement.textContent = '.';
            messagesElement.appendChild(typingElement);
            messagesElement.scrollTop = messagesElement.scrollHeight;

            const interval = setInterval(() => {
                typingElement.textContent += '.';
                if (typingElement.textContent.length > 3) {
                    typingElement.textContent = '.';
                }
            }, 500);

            typingElement.dataset.intervalId = interval;
            return typingElement;
        }

        function removeTypingIndicator(typingElement) {
            clearInterval(typingElement.dataset.intervalId);
            messagesElement.removeChild(typingElement);
        }

        async function postMessage(message) {
            try {
                const response = await fetch('/api/sommelier/chat', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        message: message,
                        thread_id: threadId,
                        team: team,
                        product_ids: productIds,
                    }),
                });

                const data = await response.json();
                threadId = data.thread_id;
                return data;
            } catch (error) {
                console.error('Errore durante l\'invio del messaggio:', error);
                return { message: '<strong>Errore</strong> durante l\'invio del messaggio. Riprova più tardi.' };
            }
        }
    });
</script>

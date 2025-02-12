<!-- Start of Selection -->
<div id="chatContainer" style="display: flex; flex-direction: column; height: 75vh; background-color: #f0f0f0; border-radius: 5px;">
    <div id="messages" style="flex: 1; padding: 10px; overflow-y: scroll; background-color: #f0f0f0; color: #333;">
        <div class="message bot" style="padding: 10px; margin-bottom: 10px; border-radius: 5px; background-color: #66CCFF; color: #333; border: 1px solid #ccc;">
            Benvenuto al Centro Olistico Demo, un'oasi di serenità e benessere. Come posso assisterti oggi?
        </div>
    </div>
    <div style="display: flex; padding: 10px; border-top: 1px solid #ccc; background-color: #f0f0f0; width: 100%;">
        <input id="userInput" type="text" placeholder="Scrivi un messaggio..." style="flex: 1; padding: 10px; border: 1px solid #ccc; border-radius: 5px; margin-right: 10px; background-color: #ffffff; color: #333;">
        <button id="sendButton" style="padding: 10px 20px; border: none; border-radius: 5px; background-color: #e0e0e0; color: #333; border: 1px solid #ccc;">Invia</button>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const messagesElement = document.getElementById('messages');
        const userInputElement = document.getElementById('userInput');
        const sendButton = document.getElementById('sendButton');
        let threadId = null;
        let productIds = []; // Inizializza un array per gestire i product_ids
        const team = window.location.pathname.split('/').pop(); // Estrae l'ultima parte dell'URL come team

        sendButton.addEventListener('click', sendMessage);
        userInputElement.addEventListener('keyup', function(event) {
            if (event.key === 'Enter') {
                sendMessage();
            }
        });

        async function sendMessage() {
            const userInput = userInputElement.value.trim();
            if (userInput === '') return;

            const userMessage = {
                id: Date.now(),
                role: 'user',
                content: userInput,
            };

            addMessageToChat(userMessage);
            userInputElement.value = ''; // Cancella subito il messaggio dalla barra di input

            const typingIndicator = addTypingIndicator(); // Aggiungi l'indicatore di digitazione
            const response = await postMessage(userInput);
            removeTypingIndicator(typingIndicator); // Rimuovi l'indicatore di digitazione

            const botMessage = {
                id: Date.now() + 1,
                role: 'bot',
                content: response.message,
            };
            addMessageToChat(botMessage);

            // Aggiorna i product_ids se presenti nella risposta
            if (response.product_ids) {
                productIds = response.product_ids;
            }
        }

        function addMessageToChat(message) {
            const messageElement = document.createElement('div');
            messageElement.className = `message ${message.role}`;
            messageElement.style = `padding: 10px; margin-bottom: 10px; border-radius: 5px; background-color: ${message.role === 'user' ? '#FFCC66' : '#66CCFF'}; color: #333; border: 1px solid #ccc;`;
            messageElement.innerHTML = message.content; // Usa innerHTML per supportare il contenuto HTML
            messagesElement.appendChild(messageElement);
            messagesElement.scrollTop = messagesElement.scrollHeight;
        }

        function addTypingIndicator() {
            const typingElement = document.createElement('div');
            typingElement.className = 'message bot';
            typingElement.style = 'padding: 10px; margin-bottom: 10px; border-radius: 5px; background-color: #66CCFF; color: #333; border: 1px solid #ccc;';
            typingElement.textContent = '.';
            messagesElement.appendChild(typingElement);
            messagesElement.scrollTop = messagesElement.scrollHeight;

            // Aggiungi animazione ai puntini di caricamento
            const interval = setInterval(() => {
                typingElement.textContent += '.';
                if (typingElement.textContent.length > 3) {
                    typingElement.textContent = '.';
                }
            }, 500);

            typingElement.dataset.intervalId = interval; // Salva l'ID dell'intervallo per la rimozione
            return typingElement;
        }

        function removeTypingIndicator(typingElement) {
            clearInterval(typingElement.dataset.intervalId); // Ferma l'animazione
            messagesElement.removeChild(typingElement);
        }

        async function postMessage(message) {
            try {
                const response = await fetch('/api/chatbot', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        message: message,
                        thread_id: threadId,
                        team: team, // Passa il team all'API
                        product_ids: productIds, // Passa i product_ids all'API
                    }),
                });

                const data = await response.json();
                threadId = data.thread_id;
                return data;
            } catch (error) {
                console.error('Errore durante l\'invio del messaggio:', error);
                return { message: 'Errore durante l\'invio del messaggio. Riprova più tardi.' };
            }
        }
    });
</script>
<!-- End of Selection -->

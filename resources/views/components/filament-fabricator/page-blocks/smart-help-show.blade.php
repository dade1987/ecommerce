<div id="chatContainer" style="display: flex; flex-direction: column; height: 85vh; background-color: #1e1e1e; border-radius: 5px;">
    <div id="messages" style="flex: 1; padding: 10px; overflow-y: scroll; background-color: #2c2c2c; color: #fff;">
        <div class="message bot" style="padding: 10px; margin-bottom: 10px; border-radius: 5px; background-color: #3a3a3a; color: #fff;">
            Benvenuto al centro olistico! Come posso aiutarti oggi?
        </div>
    </div>
    <div style="display: flex; padding: 10px; border-top: 1px solid #444; background-color: #1e1e1e; width: 100%;">
        <input id="userInput" type="text" placeholder="Scrivi un messaggio..." style="flex: 1; padding: 10px; border: 1px solid #444; border-radius: 5px; margin-right: 10px; background-color: #2c2c2c; color: #fff;">
        <button id="sendButton" style="padding: 10px 20px; border: none; border-radius: 5px; background-color: #3a3a3a; color: #fff;">Send</button>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const messagesElement = document.getElementById('messages');
        const userInputElement = document.getElementById('userInput');
        const sendButton = document.getElementById('sendButton');
        let threadId = null;
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
            const response = await postMessage(userInput);
            const botMessage = {
                id: Date.now() + 1,
                role: 'bot',
                content: response.message,
            };
            addMessageToChat(botMessage);

            userInputElement.value = '';
        }

        function addMessageToChat(message) {
            const messageElement = document.createElement('div');
            messageElement.className = `message ${message.role}`;
            messageElement.style = `padding: 10px; margin-bottom: 10px; border-radius: 5px; background-color: ${message.role === 'user' ? '#81c784' : '#8e24aa'}; color: #fff;`;
            messageElement.textContent = message.content;
            messagesElement.appendChild(messageElement);
            messagesElement.scrollTop = messagesElement.scrollHeight;
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

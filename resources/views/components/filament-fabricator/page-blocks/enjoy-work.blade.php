<!-- Container principale -->
<div id="chatContainer" class="flex flex-col h-screen w-screen bg-[#343541] p-4">

    <!-- Header / Titolo -->
    <div class="mb-4 flex items-center gap-3">
        <img id="teamLogo" src="/images/logoai.jpeg" alt="Logo Azienda" class="w-12 h-12 rounded-full object-cover border border-gray-500">
        <h1 id="teamName" class="font-sans text-3xl text-white">
            EnjoyWork
        </h1>
    </div>
  
    <!-- Bottoni di risposte rapide (quick replies) -->
    <div id="quickReplies" class="flex flex-wrap gap-3 mb-5">
      <button 
        class="quick-reply-btn bg-[#4f4f58] hover:bg-[#5e5e69] text-white px-4 py-3 rounded-md"
        data-message="Quali servizi offrite?"
      >
        Quali servizi offrite?
      </button>
      <button 
        class="quick-reply-btn bg-[#4f4f58] hover:bg-[#5e5e69] text-white px-4 py-3 rounded-md"
        data-message="Quali sono gli orari disponibili per un appuntamento?"
      >
        Orari disponibili?
      </button>
      <button 
        class="quick-reply-btn bg-[#4f4f58] hover:bg-[#5e5e69] text-white px-4 py-3 rounded-md"
        data-message="Qual è il vostro indirizzo?"
      >
        Qual è il vostro indirizzo?
      </button>
    </div>
  
    <!-- Contenitore per i messaggi -->
    <div id="messages" class="flex-1 overflow-y-auto bg-transparent text-white py-4 flex flex-col space-y-4">
      <!-- I messaggi verranno inseriti dinamicamente da JavaScript -->
    </div>
  
    <!-- Footer con input e pulsante di invio -->
    <div class="relative flex h-full max-w-full flex-1 flex-col overflow-hidden">
        <div class="flex border-t border-[#4f4f58] bg-[#343541] w-full pt-4 fixed bottom-0 left-0">
            <div class="relative flex-1 flex items-center">
                <div class="flex w-full rounded-md overflow-hidden bg-[#40414f] border border-[#565869]">
                    <input
                        id="userInput"
                        type="text"
                        placeholder="Scrivi un messaggio..."
                        class="flex-1 p-4 text-white bg-transparent focus:outline-none placeholder-gray-400"
                    />
                    <button
                        id="sendButton"
                        class="bg-[#40414f] px-4 text-white border-l border-[#565869] hover:bg-[#565869]"
                    >
                        Invia
                    </button>
                </div>
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
    let productIds = [];
    const teamSlug = window.location.pathname.split('/').pop();
    let firstMessageSent = false;

    // Carica nome e logo azienda
    fetch(`/api/avatar/${teamSlug}`)
        .then(response => response.json())
        .then(data => {
            const teamNameElement = document.getElementById('teamName');
            const teamLogoElement = document.getElementById('teamLogo');

            if (data.team.name) {
                teamNameElement.textContent = data.team.name;
            } else {
                teamNameElement.textContent = 'EnjoyWork';
            }

            if (data.team.logo) {
                teamLogoElement.src = data.team.logo;
            } else {
                teamLogoElement.src = '/images/logoai.jpeg';
            }
        })
        .catch(error => {
            console.error('Errore caricamento dati azienda:', error);
        });

    // Event listener per i bottoni di risposte rapide
    const quickReplyButtons = document.querySelectorAll('.quick-reply-btn');
    quickReplyButtons.forEach(button => {
        button.addEventListener('click', function() {
            const quickMessage = this.getAttribute('data-message');
            userInputElement.value = quickMessage;
            sendMessage();
        });
    });

    // Estrai l'UUID dalla query string
    const urlParams = new URLSearchParams(window.location.search);
    const uuid = urlParams.get('uuid');

    // Chiama l'API /visit/{uuid} quando la pagina viene caricata
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

    // Invia automaticamente "Buongiorno" all'API quando la pagina viene caricata
    postMessage('Buongiorno').then(response => {
        const botMessage = {
            id: Date.now(),
            role: 'bot',
            content: formatMessageContent(response.message),
        };
        addMessageToChat(botMessage);
    });

    // Gestisci invio messaggio
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
        }

        const userMessage = {
            id: Date.now(),
            role: 'user',
            content: formatMessageContent(userInput),
        };

        addMessageToChat(userMessage);
        userInputElement.value = '';

        const typingIndicator = addTypingIndicator();
        const response = await postMessage(userInput);
        removeTypingIndicator(typingIndicator);

        const botMessage = {
            id: Date.now() + 1,
            role: 'bot',
            content: formatMessageContent(response.message),
        };
        addMessageToChat(botMessage);

        if (response.product_ids) {
            productIds = response.product_ids;
        }
    }

    function formatMessageContent(message) {
        const firstSpaceIndex = message.indexOf(' ');
        if (firstSpaceIndex === -1) {
            return `<strong>${message}</strong>`;
        }
        const firstWord = message.slice(0, firstSpaceIndex);
        const rest = message.slice(firstSpaceIndex + 1);
        return `<strong>${firstWord}</strong> ${rest}`;
    }

    function addMessageToChat(message) {
        const messageElement = document.createElement('div');
        messageElement.className = message.role === 'user' ?
            'message user self-end max-w-[75%] bg-[#3b4b58] text-[#d1d5db] border border-[#565869] px-4 py-3 rounded-md' :
            'message bot self-start max-w-[75%] bg-[#40414f] text-[#e8e8ea] border border-[#565869] px-4 py-3 rounded-md';
        messageElement.innerHTML = message.content;
        messagesElement.appendChild(messageElement);
        messagesElement.scrollTop = messagesElement.scrollHeight;
    }

    function addTypingIndicator() {
        const typingElement = document.createElement('div');
        typingElement.className = 'message bot self-start max-w-[75%] bg-[#40414f] text-white border border-[#565869] px-4 py-3 rounded-md italic opacity-80';
        typingElement.textContent = '.';
        messagesElement.appendChild(typingElement);
        messagesElement.scrollTop = messagesElement.scrollHeight;
        const interval = setInterval(() => {
            typingElement.textContent += '.';
            if (typingElement.textContent.length > 3) typingElement.textContent = '.';
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
            const response = await fetch('/api/chatbot', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ message, thread_id: threadId, team: teamSlug, product_ids: productIds }),
            });
            const data = await response.json();
            threadId = data.thread_id;
            return data;
        } catch (error) {
            console.error('Errore invio messaggio:', error);
            return { message: 'Errore invio messaggio. Riprova più tardi.' };
        }
    }
});
</script>
<!-- Includi Tailwind CSS (solo se non l'hai già incluso altrove) -->
<script src="https://cdn.tailwindcss.com"></script>

<!-- Contenitore principale a tutta altezza -->
<div class="flex flex-col h-screen w-screen bg-[#343541]">

  <!-- Header -->
  <div class="p-4 flex items-center gap-3">
    <img id="teamLogo" src="/images/logoai.jpeg" alt="Logo Azienda" 
         class="w-12 h-12 rounded-full object-cover border border-gray-500">
    <h1 id="teamName" class="font-sans text-3xl text-white">EnjoyWork</h1>
  </div>

  <!-- Bottoni di risposte rapide -->
  <div id="quickReplies" class="px-4 flex flex-wrap gap-3 mb-5">
    <button 
      class="quick-reply-btn bg-[#4f4f58] hover:bg-[#5e5e69] text-white px-2 py-2 rounded-md"
      data-message="Come l'AI può potenziare la mia azienda?"
    >
      Come l'AI può potenziare la mia azienda?
    </button>
  </div>

  <!-- Contenitore dei messaggi (scrollabile) con padding in basso per evitare sovrapposizione -->
  <div id="messages" class="flex-1 overflow-y-auto px-4 text-white space-y-4 pb-32">
    <!-- I messaggi verranno inseriti dinamicamente da JS -->
  </div>

</div>

<!-- Footer fisso in basso -->
<div class="fixed bottom-0 left-0 w-full border-t border-[#4f4f58] bg-[#343541]">
  <div class="px-4 py-4">
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

  // Estrai l'UUID dalla query string
  const urlParams = new URLSearchParams(window.location.search);
  const uuid = urlParams.get('uuid');

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

  // Gestione bottoni di risposte rapide
  document.querySelectorAll('.quick-reply-btn').forEach(button => {
    button.addEventListener('click', function() {
      userInputElement.value = this.getAttribute('data-message');
      sendMessage();
    });
  });

  // Chiamata API /visit/{uuid} al caricamento
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

  // Invia automaticamente "Buongiorno" all'API al caricamento
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
    messageElement.className = message.role === 'user'
      ? 'message user self-end w-full ml-[5%] bg-[#3b4b58] text-[#d1d5db] border border-[#565869] px-4 py-3 rounded-md'
      : 'message bot self-start w-full bg-[#40414f] text-[#e8e8ea] border border-[#565869] px-4 py-3 rounded-md';
    messageElement.innerHTML = message.content;
    messagesElement.appendChild(messageElement);
    messagesElement.scrollTop = messagesElement.scrollHeight;
  }

  function addTypingIndicator() {
    const typingElement = document.createElement('div');
    typingElement.className = 'message bot self-start w-full bg-[#40414f] text-white border border-[#565869] px-4 py-3 rounded-md italic opacity-80';
    typingElement.textContent = 'Attendi.';
    messagesElement.appendChild(typingElement);
    messagesElement.scrollTop = messagesElement.scrollHeight;

    const interval = setInterval(() => {
      typingElement.textContent += '.';
      if (typingElement.textContent.length > 10) {
        typingElement.textContent = 'Attendi.';
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
      const response = await fetch('/api/chatbot', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          message,
          thread_id: threadId,
          team: teamSlug,
          product_ids: productIds,
          uuid: uuid // Passa l'UUID all'API se disponibile
        }),
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

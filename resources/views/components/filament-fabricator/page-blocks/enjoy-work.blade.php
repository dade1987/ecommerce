<!-- Contenitore principale a tutta altezza -->
<div class="flex flex-col h-screen w-screen bg-[#343541]">

  <!-- Header -->
  <div class="p-4 flex flex-col gap-3">
    <div class="flex items-center gap-3">
      <img id="teamLogo" src="/images/logoai.jpeg" alt="{{ __('enjoy-work.company_logo_alt') }}"
           class="w-12 h-12 rounded-full object-cover border border-gray-500">
      <h1 id="teamName" class="font-sans text-3xl text-white">EnjoyWork</h1>
      <a href="/enjoy-talk-3d" class="ml-auto px-3 py-2 text-sm bg-emerald-600 hover:bg-emerald-700 text-white rounded-md">EnjoyTalk 3D</a>
    </div>
    <!-- Toggle Chat Mode -->
    <div class="flex items-center gap-2">
      <button id="toggleChatMode" class="flex items-center gap-2 px-3 py-2 rounded-md bg-[#4f4f58] hover:bg-[#5e5e69] text-white text-sm transition">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path>
        </svg>
        <span id="chatModeLabel" class="font-medium">Modalità Business</span>
      </button>
    </div>
  </div>

  <!-- Bottoni di risposte rapide -->
  <div id="quickReplies" class="px-4 flex flex-wrap gap-3 mb-5">
    <button 
      class="quick-reply-btn bg-[#4f4f58] hover:bg-[#5e5e69] text-white px-2 py-2 rounded-md"
      data-message="{{ __('enjoy-work.quick_reply_message') }}"
    >
      {{ __('enjoy-work.quick_reply_text') }}
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
        placeholder="{{ __('enjoy-work.input_placeholder') }}"
        class="flex-1 p-4 text-white bg-transparent focus:outline-none placeholder-gray-400"
      />
      <button
        id="sendButton"
        class="bg-[#40414f] px-4 text-white border-l border-[#565869] hover:bg-[#565869]"
      >
        {{ __('enjoy-work.send_button') }}
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
  let currentPromptType = 'business'; // 'business' o 'chat_libera'

  // Toggle button
  const toggleButton = document.getElementById('toggleChatMode');
  const chatModeLabel = document.getElementById('chatModeLabel');

  function updateChatModeLabel() {
    chatModeLabel.textContent = currentPromptType === 'business' ? 'Modalità Business' : 'Chat Libera';
  }

  toggleButton.addEventListener('click', function() {
    currentPromptType = currentPromptType === 'business' ? 'chat_libera' : 'business';
    updateChatModeLabel();
    // Reset thread per cambiare modalità
    threadId = null;
    messagesElement.innerHTML = '';
    // Invia nuovo greeting
    postMessage(translations.greeting).then(response => {
      const botMessage = {
        id: Date.now(),
        role: 'bot',
        content: formatMessageContent(response.message),
      };
      addMessageToChat(botMessage);
    });
  });

  updateChatModeLabel();

  // Estrai l'UUID dalla query string
  const urlParams = new URLSearchParams(window.location.search);
  const uuid = urlParams.get('uuid');
  const locale = '{{ app()->getLocale() }}';
  const translations = {
      greeting: "{{ __('enjoy-work.greeting') }}",
      typing_indicator_text: "{{ __('enjoy-work.typing_indicator_text') }}",
      send_error_message: "{{ __('enjoy-work.send_error_message') }}"
  };

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

  // Invia automaticamente il messaggio di benvenuto all'API al caricamento
  postMessage(translations.greeting).then(response => {
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
    typingElement.textContent = translations.typing_indicator_text;
    messagesElement.appendChild(typingElement);
    messagesElement.scrollTop = messagesElement.scrollHeight;

    const interval = setInterval(() => {
      typingElement.textContent += '.';
      if (typingElement.textContent.length > 10) {
        typingElement.textContent = translations.typing_indicator_text;
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
    return new Promise((resolve) => {
      try {
        const params = new URLSearchParams({
          message,
          team: teamSlug,
          locale: locale,
        });

        if (uuid) {
          params.set('uuid', uuid);
        }

        if (threadId) {
          params.set('thread_id', threadId);
        }

        const endpoint = `/api/chatbot/neuron-website-stream?${params.toString()}`;
        const evtSource = new EventSource(endpoint);
        let collected = '';
        let localThreadId = threadId;

        evtSource.addEventListener('message', (e) => {
          try {
            const data = JSON.parse(e.data);
            if (data.token) {
              // Il primo token può contenere il thread_id in JSON
              try {
                const tok = JSON.parse(data.token);
                if (tok && tok.thread_id) {
                  localThreadId = tok.thread_id;
                  return;
                }
              } catch (err) {
                // token è solo testo, continua sotto
              }
              collected += data.token;
            }
          } catch (err) {
            console.warn('Errore parsing SSE message:', err);
          }
        });

        evtSource.addEventListener('done', () => {
          try {
            evtSource.close();
          } catch (e) {}

          threadId = localThreadId || threadId;
          const finalMessage = collected || translations.send_error_message;

          resolve({
            message: finalMessage,
            thread_id: threadId,
          });
        });

        evtSource.addEventListener('error', (e) => {
          console.error('Errore SSE:', e);
          try {
            evtSource.close();
          } catch (err) {}
          resolve({ message: translations.send_error_message });
        });
      } catch (error) {
        console.error('Errore invio messaggio:', error);
        resolve({ message: translations.send_error_message });
      }
    });
  }
});
</script>

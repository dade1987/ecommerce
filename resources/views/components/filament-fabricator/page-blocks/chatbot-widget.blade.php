@aware(['page'])
@props([
    'teamSlug',
])

<div x-data="{ open: false }" class="fixed bottom-4 right-4 z-50">
    <!-- Chat Icon -->
    <div x-show="!open">
        <button @click="open = true" class="bg-blue-600 text-white rounded-full p-4 shadow-lg hover:bg-blue-700 transition">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg>
        </button>
    </div>

    <!-- Chat Window -->
    <div x-show="open" x-cloak 
         class="w-96 h-[70vh] bg-[#343541] rounded-lg shadow-xl flex flex-col"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 transform translate-y-4"
         x-transition:enter-end="opacity-100 transform translate-y-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 transform translate-y-0"
         x-transition:leave-end="opacity-0 transform translate-y-4">

        <!-- Header -->
        <div class="p-4 flex items-center justify-between gap-3 bg-[#40414f] rounded-t-lg">
            <div class="flex items-center gap-3">
                <img id="teamLogo" src="/images/logoai.jpeg" alt="{{ __('enjoy-work.company_logo_alt') }}" 
                     class="w-10 h-10 rounded-full object-cover border-2 border-gray-500">
                <h1 id="teamName" class="font-sans text-xl text-white">EnjoyWork</h1>
            </div>
            <button @click="open = false" class="text-gray-400 hover:text-white">
                 <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>

        <!-- Messages -->
        <div id="messages" class="flex-1 overflow-y-auto px-4 text-white space-y-4 py-4">
            <!-- Dynamic messages -->
        </div>

        <!-- Footer -->
        <div class="p-4 border-t border-[#4f4f58] bg-[#343541] rounded-b-lg">
            <div class="flex w-full rounded-md overflow-hidden bg-[#40414f] border border-[#565869]">
                <input id="userInput" type="text" placeholder="{{ __('enjoy-work.input_placeholder') }}"
                       class="flex-1 p-3 text-white bg-transparent focus:outline-none placeholder-gray-400">
                <button id="sendButton" class="bg-[#40414f] px-4 text-white border-l border-[#565869] hover:bg-[#565869]">
                    {{ __('enjoy-work.send_button') }}
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
    let threadId = null;
    let productIds = [];
    const teamSlug = '{{ $teamSlug }}';
    let firstMessageSent = false;
    const urlParams = new URLSearchParams(window.location.search);
    const uuid = urlParams.get('uuid');
    const locale = '{{ app()->getLocale() }}';
    const translations = {
      greeting: "{{ __('enjoy-work.greeting') }}",
      typing_indicator_text: "{{ __('enjoy-work.typing_indicator_text') }}",
      send_error_message: "{{ __('enjoy-work.send_error_message') }}"
    };

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
        .catch(error => console.error('Error loading company data:', error));

    if (uuid) {
        fetch(`/api/visit/${uuid}`)
            .then(response => response.json())
            .then(data => console.log('Visit registered:', data.message))
            .catch(error => console.error('Error registering visit:', error));
    }

    postMessage(translations.greeting).then(response => {
        const botMessage = {
            id: Date.now(),
            role: 'bot',
            content: formatMessageContent(response.message),
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
        try {
            const response = await fetch('/api/chatbot', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    message,
                    thread_id: threadId,
                    team: teamSlug,
                    product_ids: productIds,
                    uuid: uuid,
                    locale: locale
                }),
            });
            const data = await response.json();
            threadId = data.thread_id;
            return data;
        } catch (error) {
            console.error('Error sending message:', error);
            return { message: translations.send_error_message };
        }
    }
});
</script>

<style>
    [x-cloak] { display: none !important; }
</style> 
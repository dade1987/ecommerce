import { useState, useCallback, useEffect, useRef } from 'react';
import { toast } from 'react-toastify';
import { INITIAL_CHAT_MESSAGE } from '../constants';

/**
 * Custom hook for managing chat state and SSE communication
 * @param {Object} options - Hook options
 * @returns {Object} Chat state and methods
 */
export function useChat({
  chatEndpoint,
  teamSlug,
  locale,
  onResponseComplete,
  isBusy = false,
}) {
  const [chats, setChats] = useState([INITIAL_CHAT_MESSAGE]);
  const [message, setMessage] = useState('');
  const [loading, setLoading] = useState(false);
  const [threadId, setThreadId] = useState(null);
  const chatBoxRef = useRef(null);

  // Auto-scroll chat to bottom
  useEffect(() => {
    if (chatBoxRef.current) {
      chatBoxRef.current.scrollTop = chatBoxRef.current.scrollHeight;
    }
  }, [chats]);

  // Send message via SSE
  const sendMessage = useCallback(async (msg) => {
    const messageText = msg || message;

    if (!messageText.trim()) {
      toast.error("Il messaggio non può essere vuoto.");
      return;
    }

    if (loading || isBusy) {
      toast.error("Sto ancora generando una risposta!");
      return;
    }

    // Add user message to chat
    setChats(prev => [...prev, { msg: messageText, who: 'me' }]);
    setMessage('');
    setLoading(true);

    const startTime = Date.now();

    try {
      // Build SSE URL
      const params = new URLSearchParams({
        message: messageText,
        team: teamSlug,
        locale: locale,
        ...(threadId && { thread_id: threadId })
      });

      const sseUrl = `${chatEndpoint}?${params.toString()}`;
      const evtSource = new EventSource(sseUrl);

      let collectedText = '';
      let newThreadId = threadId;

      evtSource.onmessage = (event) => {
        try {
          const data = JSON.parse(event.data);

          if (data.token && typeof data.token === 'string') {
            try {
              const tokenData = JSON.parse(data.token);
              if (tokenData && typeof tokenData === 'object' && tokenData.thread_id) {
                newThreadId = tokenData.thread_id;
                setThreadId(newThreadId);
                return;
              }
              collectedText += data.token;
            } catch {
              collectedText += data.token;
            }
          }
        } catch (e) {
          console.error('SSE parse error:', e);
        }
      };

      evtSource.addEventListener('done', () => {
        evtSource.close();
        const timeTaken = (Date.now() - startTime) / 1000;
        const responseText = collectedText.trim() || "Mi dispiace, non ho capito.";

        setLoading(false);
        onResponseComplete?.(responseText, timeTaken);
      });

      evtSource.addEventListener('error', (event) => {
        try {
          const data = JSON.parse(event.data);
          console.error('SSE error event:', data);
          toast.error(data.error || "Errore durante la chat");
        } catch {
          console.error('SSE connection error');
        }
        evtSource.close();
        setLoading(false);
      });

      evtSource.onerror = (error) => {
        console.error("SSE Error:", error);
        evtSource.close();

        if (collectedText.trim()) {
          const timeTaken = (Date.now() - startTime) / 1000;
          onResponseComplete?.(collectedText.trim(), timeTaken);
        } else {
          toast.error("Errore nella comunicazione con il server");
        }
        setLoading(false);
      };

    } catch (error) {
      console.error("Chat API Error:", error);
      toast.error("Errore nella comunicazione con il server");
      setLoading(false);
    }
  }, [message, loading, isBusy, chatEndpoint, teamSlug, locale, threadId, onResponseComplete]);

  // Add bot response to chat
  const addBotMessage = useCallback((text, executionTime) => {
    setChats(prev => [...prev, {
      msg: text,
      who: 'bot',
      exct: executionTime
    }]);
  }, []);

  return {
    chats,
    message,
    setMessage,
    loading,
    sendMessage,
    addBotMessage,
    chatBoxRef,
  };
}

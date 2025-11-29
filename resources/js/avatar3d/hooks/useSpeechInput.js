import { useEffect, useCallback } from 'react';
import SpeechRecognition, { useSpeechRecognition } from 'react-speech-recognition';
import { toast } from 'react-toastify';

/**
 * Custom hook for speech recognition input
 * @param {Object} options - Hook options
 * @returns {Object} Speech recognition state and methods
 */
export function useSpeechInput({ locale, enabled, onTranscript, onComplete }) {
  const {
    transcript,
    listening,
    browserSupportsSpeechRecognition,
    resetTranscript,
  } = useSpeechRecognition();

  // Sync transcript with parent
  useEffect(() => {
    if (transcript) {
      onTranscript?.(transcript);
    }
  }, [transcript, onTranscript]);

  const startListening = useCallback(() => {
    if (!enabled) return;

    if (!browserSupportsSpeechRecognition) {
      toast.error("Il riconoscimento vocale non è supportato dal browser.");
      return;
    }

    const language = locale === 'it' ? 'it-IT' : 'en-US';
    SpeechRecognition.startListening({ language });
  }, [enabled, browserSupportsSpeechRecognition, locale]);

  const stopListening = useCallback(() => {
    SpeechRecognition.stopListening();
    onComplete?.(transcript);
  }, [transcript, onComplete]);

  return {
    transcript,
    listening,
    isSupported: browserSupportsSpeechRecognition,
    startListening,
    stopListening,
    resetTranscript,
  };
}

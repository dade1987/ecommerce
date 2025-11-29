import { useState, useRef, useCallback } from 'react';

/**
 * Custom hook for managing audio playback
 * @param {Object} options - Hook options
 * @returns {Object} Audio state and methods
 */
export function useAudio({ onPlayStart, onPlayEnd }) {
  const audioRef = useRef(null);
  const [audioSource, setAudioSource] = useState(null);
  const [playing, setPlaying] = useState(false);

  const handleEnded = useCallback(() => {
    setAudioSource(null);
    setPlaying(false);
    onPlayEnd?.();
  }, [onPlayEnd]);

  const handleCanPlayThrough = useCallback(() => {
    if (audioRef.current) {
      audioRef.current.play();
      setPlaying(true);
      onPlayStart?.();
    }
  }, [onPlayStart]);

  const playAudio = useCallback((source) => {
    setAudioSource(source);
  }, []);

  const stopAudio = useCallback(() => {
    if (audioRef.current) {
      audioRef.current.pause();
      audioRef.current.currentTime = 0;
    }
    setAudioSource(null);
    setPlaying(false);
  }, []);

  // Audio element props
  const audioProps = {
    ref: audioRef,
    src: audioSource,
    onEnded: handleEnded,
    onCanPlayThrough: handleCanPlayThrough,
    style: { display: 'none' },
  };

  return {
    audioRef,
    audioSource,
    playing,
    playAudio,
    stopAudio,
    audioProps,
    setAudioSource,
  };
}

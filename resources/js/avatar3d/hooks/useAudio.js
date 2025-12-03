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
      // play() returns a Promise - handle rejection for mobile autoplay policy
      const playPromise = audioRef.current.play();
      if (playPromise !== undefined) {
        playPromise
          .then(() => {
            setPlaying(true);
            onPlayStart?.();
          })
          .catch((error) => {
            console.warn('Audio autoplay blocked:', error.message);
            // On mobile, user gesture required - we'll need manual play
            // For now, just mark as not playing so UI can show play button
            setPlaying(false);
          });
      }
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
    playsInline: true,  // Required for iOS Safari
    preload: 'auto',
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

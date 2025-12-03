import 'regenerator-runtime/runtime';
import React, { useState, useCallback, useRef } from 'react';
import { ToastContainer } from 'react-toastify';
import 'react-toastify/dist/ReactToastify.css';

import './Avatar3DReact.css';
import { Leva } from './Avatar';
import { DEFAULT_PROPS } from './constants';
import { buildContainerStyle, buildContainerClasses } from './utils/styleHelpers';
import { useChat } from './hooks/useChat';
import { useAudio } from './hooks/useAudio';
import { useSpeechInput } from './hooks/useSpeechInput';
import { StatusIndicator, ChatPanel, WidgetChat, Scene3D, LoadingOverlay } from './components';

/**
 * Avatar 3D React Component
 *
 * Props:
 * - title: string - Title displayed in header
 * - modelUrl: string - Path to the .glb avatar model
 * - voice: string - Azure voice name (e.g., 'it-IT-ElsaNeural')
 * - enableSpeechRecognition: boolean - Enable voice input
 * - enableChat: boolean - Enable text chat
 * - locale: string - Language locale
 * - teamSlug: string - Team identifier
 * - backgroundImage: string - Background image path
 * - showLevaPanel: boolean - Show/hide Leva debug panel
 * - enableBoneControls: boolean - Enable bone controls in Leva panel
 * - chatEndpoint: string - API endpoint for chat
 * - ttsEndpoint: string - API endpoint for TTS
 * - fixedPosition: boolean - Position fixed at bottom
 * - height: string - Container height (e.g., '400px', '50vh')
 * - aspectRatio: number - Width/Height ratio (default: 0.75 = 3:4)
 * - transparentBackground: boolean - Transparent canvas background
 * - positionBottom: string - Bottom offset when fixed
 * - positionRight: string - Right offset when fixed
 * - avatarView: 'bust' | 'full' - Camera view preset
 * - orbitControls: 'none' | 'limited' | 'debug' - Orbit controls mode
 * - mouseTrackingRadius: number|null - Raggio in px per tracking locale (null = viewport intero)
 * - mouseTrackingSpeed: number - Velocità transizione tracking (0.01-0.2, default 0.08)
 * - widgetMode: boolean - Modalità widget (chat collassata con toggle button)
 * - showShadow: boolean - Mostra ombra a terra (default: false)
 * - shadowPreset: 'soft'|'sharp'|'diffuse'|'fullBody' - Preset ombra
 * - shadowOpacity: number - Opacità ombra 0-1 (override preset)
 * - shadowBlur: number - Sfumatura ombra (override preset)
 * - shadowY: number - Altezza terreno per ombra (default: -1)
 */
export default function Avatar3DReact({
  title = DEFAULT_PROPS.title,
  modelUrl = DEFAULT_PROPS.modelUrl,
  voice = DEFAULT_PROPS.voice,
  enableSpeechRecognition = DEFAULT_PROPS.enableSpeechRecognition,
  enableChat = DEFAULT_PROPS.enableChat,
  locale = DEFAULT_PROPS.locale,
  teamSlug = DEFAULT_PROPS.teamSlug,
  backgroundImage = DEFAULT_PROPS.backgroundImage,
  showLevaPanel = DEFAULT_PROPS.showLevaPanel,
  enableBoneControls = DEFAULT_PROPS.enableBoneControls,
  chatEndpoint = DEFAULT_PROPS.chatEndpoint,
  ttsEndpoint = DEFAULT_PROPS.ttsEndpoint,
  fixedPosition = DEFAULT_PROPS.fixedPosition,
  height = DEFAULT_PROPS.height,
  aspectRatio = DEFAULT_PROPS.aspectRatio,
  transparentBackground = DEFAULT_PROPS.transparentBackground,
  positionBottom = DEFAULT_PROPS.positionBottom,
  positionRight = DEFAULT_PROPS.positionRight,
  avatarView = DEFAULT_PROPS.avatarView,
  orbitControls = DEFAULT_PROPS.orbitControls,
  mouseTrackingRadius = DEFAULT_PROPS.mouseTrackingRadius,
  mouseTrackingSpeed = DEFAULT_PROPS.mouseTrackingSpeed,
  showFps = DEFAULT_PROPS.showFps,
  // Widget mode
  widgetMode = DEFAULT_PROPS.widgetMode,
  // Shadow props
  showShadow = DEFAULT_PROPS.showShadow,
  shadowPreset = DEFAULT_PROPS.shadowPreset,
  shadowOpacity = DEFAULT_PROPS.shadowOpacity,
  shadowBlur = DEFAULT_PROPS.shadowBlur,
  shadowY = DEFAULT_PROPS.shadowY,
}) {
  // Container ref for mouse tracking
  const containerRef = useRef(null);

  // TTS state
  const [speak, setSpeak] = useState(false);
  const [text, setText] = useState("Ciao, sono il tuo assistente virtuale 3D.");
  const [exct, setExct] = useState("");

  // Audio hook
  const {
    playing,
    audioProps,
    setAudioSource,
  } = useAudio({
    onPlayStart: () => {},
    onPlayEnd: () => {
      setSpeak(false);
    },
  });

  // Handle chat response complete - trigger TTS
  const handleResponseComplete = useCallback((responseText, timeTaken) => {
    setSpeak(true);
    setText(responseText);
    setExct(timeTaken);
  }, []);

  // Chat hook
  const {
    chats,
    message,
    setMessage,
    loading,
    sendMessage,
    addBotMessage,
    chatBoxRef,
  } = useChat({
    chatEndpoint,
    teamSlug,
    locale,
    onResponseComplete: handleResponseComplete,
    isBusy: speak,
  });

  // Add bot message when audio starts playing
  const handleAudioStart = useCallback(() => {
    addBotMessage(text, exct);
  }, [text, exct, addBotMessage]);

  // Update audio hook with new callback
  const audioPropsWithCallback = {
    ...audioProps,
    onCanPlayThrough: () => {
      audioProps.onCanPlayThrough?.();
      handleAudioStart();
    },
  };

  // Speech recognition hook
  const {
    startListening,
    stopListening,
  } = useSpeechInput({
    locale,
    enabled: enableSpeechRecognition,
    onTranscript: setMessage,
    onComplete: sendMessage,
  });

  // Widget mode: chat collapsed with toggle button (independent from transparent background)
  const isWidgetMode = widgetMode;

  // Container styles
  const containerStyle = buildContainerStyle({
    height,
    aspectRatio,
    fixedPosition,
    positionBottom,
    positionRight,
    transparentBackground,
  });

  const containerClasses = buildContainerClasses({
    fixedPosition,
    transparentBackground,
    widgetMode,
  });

  // Is busy (loading or speaking)
  const isBusy = speak || loading;

  return (
    <div ref={containerRef} className={containerClasses} style={containerStyle}>
      {/* Leva debug panel */}
      <Leva hidden={!showLevaPanel} collapsed={!enableBoneControls} />

      {/* Toast notifications */}
      <ToastContainer
        position="top-left"
        autoClose={4000}
        hideProgressBar={false}
        newestOnTop={true}
        closeOnClick
        rtl={false}
        pauseOnFocusLoss
        draggable
        pauseOnHover
        theme="dark"
      />

      {/* Status indicator - hidden in widget mode */}
      {!isWidgetMode && (
        <StatusIndicator isActive={isBusy} />
      )}

      {/* Chat panel - normal mode */}
      {enableChat && !isWidgetMode && (
        <ChatPanel
          title={title}
          chats={chats}
          message={message}
          onMessageChange={setMessage}
          onSend={() => sendMessage()}
          loading={loading}
          speaking={speak}
          playing={playing}
          enableSpeechRecognition={enableSpeechRecognition}
          onMicDown={startListening}
          onMicUp={stopListening}
          chatBoxRef={chatBoxRef}
        />
      )}

      {/* Widget mode chat */}
      {isWidgetMode && enableChat && (
        <WidgetChat
          chats={chats}
          message={message}
          onMessageChange={setMessage}
          onSend={() => sendMessage()}
          loading={loading}
          speaking={speak}
          playing={playing}
        />
      )}

      {/* Audio player (hidden) */}
      <audio {...audioPropsWithCallback} />

      {/* 3D Scene */}
      <Scene3D
        modelUrl={modelUrl}
        avatarView={avatarView}
        orbitControls={orbitControls}
        transparentBackground={transparentBackground}
        backgroundImage={backgroundImage}
        speak={speak}
        setSpeak={setSpeak}
        text={text}
        setAudioSource={setAudioSource}
        playing={playing}
        ttsEndpoint={ttsEndpoint}
        voice={voice}
        enableBoneControls={enableBoneControls}
        showLevaPanel={showLevaPanel}
        containerRef={containerRef}
        mouseTrackingRadius={mouseTrackingRadius}
        mouseTrackingSpeed={mouseTrackingSpeed}
        showFps={showFps}
        // Shadow
        showShadow={showShadow}
        shadowPreset={shadowPreset}
        shadowOpacity={shadowOpacity}
        shadowBlur={shadowBlur}
        shadowY={shadowY}
      />

      {/* Loading overlay - shows while waiting for response/audio */}
      <LoadingOverlay visible={(loading || speak) && !playing} />
    </div>
  );
}

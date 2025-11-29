import 'regenerator-runtime/runtime';
import React, { Suspense, useEffect, useRef, useState } from 'react'
import { Canvas } from '@react-three/fiber'
import { useTexture, Loader, Environment, OrthographicCamera } from '@react-three/drei';
import { OrbitControls } from '@react-three/drei';
import { ToastContainer, toast } from 'react-toastify';
import 'react-toastify/dist/ReactToastify.css';

import SpeechRecognition, { useSpeechRecognition } from 'react-speech-recognition';

import './Avatar3DReact.css';
import Avatar, { Leva } from './Avatar';

// Base path for assets
const ASSETS_BASE = '/avatar3d';

/**
 * Avatar 3D React Component
 *
 * Props:
 * - title: string - Title displayed in header
 * - modelUrl: string - Path to the .glb avatar model
 * - voice: string - Azure voice name (e.g., 'it-IT-ElsaNeural')
 * - azureSpeechRegion: string - Azure region
 * - enableSpeechRecognition: boolean - Enable voice input
 * - enableChat: boolean - Enable text chat
 * - locale: string - Language locale
 * - teamSlug: string - Team identifier
 * - backgroundImage: string - Background image path
 * - debug: boolean - Show debug orbit controls
 * - showLevaPanel: boolean - Show/hide Leva debug panel
 * - enableBoneControls: boolean - Enable bone controls in Leva panel
 * - chatEndpoint: string - API endpoint for chat
 * - ttsEndpoint: string - API endpoint for TTS
 * - fixedPosition: boolean - Position fixed at bottom
 * - height: string - Container height (e.g., '400px', '50vh')
 * - aspectRatio: number - Width/Height ratio (default: 0.75 = 3:4)
 * - transparentBackground: boolean - Transparent canvas background
 * - positionBottom: string - Bottom offset when fixed (default: '0')
 * - positionRight: string - Right offset when fixed (default: '0')
 */
export default function Avatar3DReact({
  title = 'Parla con il nostro assistente',
  modelUrl = `${ASSETS_BASE}/models/avatar.glb`,
  voice = 'it-IT-ElsaNeural',
  azureSpeechRegion = 'westeurope',
  enableSpeechRecognition = true,
  enableChat = true,
  locale = 'it',
  teamSlug = '',
  backgroundImage = null,
  showLevaPanel = false,
  enableBoneControls = false,
  chatEndpoint = '/api/chatbot/neuron-website-stream',
  ttsEndpoint = '/api/avatar3d/tts',
  fixedPosition = false,
  height = '100vh',
  aspectRatio = 0.75,
  transparentBackground = false,
  positionBottom = '0',
  positionRight = '0',
  avatarView = 'bust', // 'bust' (3/4) o 'full' (tutto il corpo)
  orbitControls = 'none' // 'none' | 'limited' | 'debug'
}) {

  const [chats, setChats] = useState([{ msg: 'Ciao! Come posso aiutarti oggi?', who: 'bot', exct: '0' }])
  const [text, setText] = useState("Ciao, sono il tuo assistente virtuale 3D.");
  const [msg, setMsg] = useState("");
  const [exct, setExct] = useState("");
  const [load, setLoad] = useState(false);

  // Thread ID for conversation continuity
  const [threadId, setThreadId] = useState(null);

  // Chat with backend using SSE streaming (NeuronWebsiteStreamController)
  const getResponse = async (message) => {
    if (message === '') {
      toast.error("Il messaggio non può essere vuoto.");
      return;
    }
    if (load === true || speak === true) {
      toast.error("Sto ancora generando una risposta!");
      return;
    }

    setChats(chats => [...chats, { msg: message, who: 'me' }])
    setMsg("");
    setLoad(true);

    const start = new Date();

    try {
      // Build SSE URL with query params
      const params = new URLSearchParams({
        message: message,
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

          // Check for thread_id in first message
          if (data.token && typeof data.token === 'string') {
            // Try to parse as JSON object (for thread_id)
            try {
              const tokenData = JSON.parse(data.token);
              // Only treat as metadata if it's an object with thread_id
              if (tokenData && typeof tokenData === 'object' && tokenData.thread_id) {
                newThreadId = tokenData.thread_id;
                setThreadId(newThreadId);
                return;
              }
              // Otherwise it's a primitive (number, etc) - add as text
              collectedText += data.token;
            } catch {
              // Not valid JSON, it's actual text
              collectedText += data.token;
            }
          }
        } catch (e) {
          console.error('SSE parse error:', e);
        }
      };

      evtSource.addEventListener('done', (event) => {
        evtSource.close();

        const timeTaken = (new Date()) - start;
        const responseText = collectedText.trim() || "Mi dispiace, non ho capito.";

        // Now trigger TTS with the complete text
        setSpeak(true);
        setText(responseText);
        setExct(timeTaken / 1000);
        setLoad(false);
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
        setLoad(false);
      });

      evtSource.onerror = (error) => {
        console.error("SSE Error:", error);
        evtSource.close();
        if (collectedText.trim()) {
          // We have some text, use it
          const timeTaken = (new Date()) - start;
          setSpeak(true);
          setText(collectedText.trim());
          setExct(timeTaken / 1000);
        } else {
          toast.error("Errore nella comunicazione con il server");
        }
        setLoad(false);
      };

    } catch (error) {
      console.error("Chat API Error:", error);
      toast.error("Errore nella comunicazione con il server");
      setLoad(false);
    }
  }

  useEffect(() => {
    const chatBox = document.querySelector('.avatar3d-chat-box');
    if (chatBox) {
      chatBox.scrollTop = chatBox.scrollHeight;
    }
  }, [chats])

  const audioPlayer = useRef();

  const [speak, setSpeak] = useState(false);
  const [audioSource, setAudioSource] = useState(null);
  const [playing, setPlaying] = useState(false);

  // Toggle tra vista chat e avatar (solo in widget mode)
  const [showChatPanel, setShowChatPanel] = useState(false);

  // Audio player events
  function playerEnded(e) {
    setAudioSource(null);
    setSpeak(false);
    setPlaying(false);
  }

  function playerReady(e) {
    audioPlayer.current.play();
    setPlaying(true);
    setChats(chats => [...chats, { msg: text, who: 'bot', exct: exct }]);
  }

  // Speech recognition
  const {
    transcript,
    browserSupportsSpeechRecognition
  } = useSpeechRecognition();

  const startListening = () => {
    if (browserSupportsSpeechRecognition) {
      SpeechRecognition.startListening({ language: locale === 'it' ? 'it-IT' : 'en-US' })
    } else {
      toast.error("Il riconoscimento vocale non è supportato dal browser.")
    }
  };

  const stopListening = () => {
    getResponse(msg);
    SpeechRecognition.stopListening();
  }

  useEffect(() => {
    setMsg(transcript);
  }, [transcript])

  // Widget mode: fixed + transparent = solo avatar senza UI
  const isWidgetMode = fixedPosition && transparentBackground;

  // Camera settings in base alla vista
  const cameraSettings = avatarView === 'full'
    ? { zoom: 350, posY: 1.0, targetY: 1.0 }   // Tutto il corpo
    : { zoom: 1400, posY: 1.65, targetY: 1.65 }; // Busto (3/4)

  // Calcola stili dinamici per il container
  // Aggiungi 'px' se il valore è solo numerico
  const formatPosition = (val) => {
    if (!val || val === '0') return '0px';
    if (/^\d+$/.test(val)) return `${val}px`;
    return val;
  };

  const containerStyle = {
    height: height,
    width: `calc(${height} * ${aspectRatio})`,
    maxWidth: '100%',
    overflow: 'hidden',
    ...(fixedPosition && {
      position: 'fixed',
      bottom: formatPosition(positionBottom),
      right: formatPosition(positionRight),
      zIndex: 1000,
    }),
    background: transparentBackground ? 'transparent' : undefined,
  };

  return (
    <div
      className={`avatar3d-container ${fixedPosition ? 'avatar3d-fixed' : ''} ${transparentBackground ? 'avatar3d-transparent' : ''}`}
      style={containerStyle}
    >
      {/* Leva debug panel - controllato da showLevaPanel */}
      <Leva hidden={!showLevaPanel} collapsed={!enableBoneControls} />

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

      {/* Status indicator - nascosto in widget mode */}
      {!isWidgetMode && (
        <div className="avatar3d-status">
          <span className={`avatar3d-status-dot ${speak || load ? 'active' : ''}`}></span>
          <span>{speak || load ? 'In elaborazione...' : 'Pronto'}</span>
        </div>
      )}

      {/* Chat panel - modalità normale */}
      {enableChat && !isWidgetMode && (
        <div className='avatar3d-chat-div'>
          <div className='avatar3d-chat-header'>
            <h2>{title}</h2>
          </div>
          <div className='avatar3d-chat-box'>
            {chats.map((chat, index) => (
              <p key={index} className={chat.who}>
                {chat.msg}
                {chat.who === 'bot' && chat.exct !== '0' && (
                  <span className='time'>{"generato in " + chat.exct + "s"}</span>
                )}
              </p>
            ))}

            {(load || speak) && !playing && (
              <p className="loading">
                <span className="loading-dots">
                  <span>.</span><span>.</span><span>.</span>
                </span>
              </p>
            )}
          </div>

          <div className='avatar3d-msg-box'>
            {enableSpeechRecognition && (
              <button
                className='avatar3d-msgbtn'
                id='mic'
                onTouchStart={startListening}
                onMouseDown={startListening}
                onTouchEnd={stopListening}
                onMouseUp={stopListening}
              >
                <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
                  <path d="M12 2a3 3 0 0 0-3 3v7a3 3 0 0 0 6 0V5a3 3 0 0 0-3-3Z"></path>
                  <path d="M19 10v2a7 7 0 0 1-14 0v-2"></path>
                  <line x1="12" x2="12" y1="19" y2="22"></line>
                </svg>
              </button>
            )}
            <input
              type='text'
              value={msg}
              onChange={e => setMsg(e.target.value)}
              onKeyDown={(e) => { if (e.key === 'Enter') { getResponse(msg) } }}
              placeholder='Scrivi un messaggio...'
            />
            <button
              className='avatar3d-msgbtn'
              id='send'
              onClick={() => { getResponse(msg) }}
            >
              <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
                <path d="m22 2-7 20-4-9-9-4Z"></path>
                <path d="M22 2 11 13"></path>
              </svg>
            </button>
          </div>
        </div>
      )}

      {/* Widget mode: toggle button e mini chat */}
      {isWidgetMode && enableChat && (
        <>
          {/* Toggle button */}
          <button
            className="avatar3d-widget-toggle"
            onClick={() => setShowChatPanel(!showChatPanel)}
          >
            {showChatPanel ? (
              <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
                <path d="M18 6 6 18"/><path d="m6 6 12 12"/>
              </svg>
            ) : (
              <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
                <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
              </svg>
            )}
          </button>

          {/* Chat panel overlay */}
          {showChatPanel && (
            <div className='avatar3d-widget-chat'>
              <div className='avatar3d-chat-box'>
                {chats.map((chat, index) => (
                  <p key={index} className={chat.who}>
                    {chat.msg}
                  </p>
                ))}
                {(load || speak) && !playing && (
                  <p className="loading">
                    <span className="loading-dots">
                      <span>.</span><span>.</span><span>.</span>
                    </span>
                  </p>
                )}
              </div>
            </div>
          )}

          {/* Input sempre visibile in basso */}
          <div className='avatar3d-widget-input'>
            <input
              type='text'
              value={msg}
              onChange={e => setMsg(e.target.value)}
              onKeyDown={(e) => { if (e.key === 'Enter') { getResponse(msg) } }}
              placeholder='Scrivi...'
            />
            <button onClick={() => getResponse(msg)}>
              <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
                <path d="m22 2-7 20-4-9-9-4Z"/><path d="M22 2 11 13"/>
              </svg>
            </button>
          </div>
        </>
      )}

      {/* Audio player (hidden) */}
      <audio
        ref={audioPlayer}
        src={audioSource}
        onEnded={playerEnded}
        onCanPlayThrough={playerReady}
        style={{ display: 'none' }}
      />

      {/* 3D Canvas */}
      <Canvas
        dpr={2}
        gl={{ alpha: transparentBackground, antialias: true }}
        onCreated={(ctx) => {
          ctx.gl.physicallyCorrectLights = true;
          if (transparentBackground) {
            ctx.gl.setClearColor(0x000000, 0);
          }
        }}
      >

        <OrthographicCamera
          makeDefault
          zoom={cameraSettings.zoom}
          position={[0, cameraSettings.posY, 1]}
        />

        {/* OrbitControls: 'none' = disabilitati, 'limited' = con limiti, 'debug' = liberi */}
        {orbitControls !== 'none' && (
          <OrbitControls
            target={[0, cameraSettings.targetY, 0]}
            enableZoom={true}
            enablePan={orbitControls === 'debug'}
            enableRotate={true}
            minZoom={orbitControls === 'debug' ? 100 : cameraSettings.zoom * 0.8}
            maxZoom={orbitControls === 'debug' ? 3000 : cameraSettings.zoom * 1.2}
            minPolarAngle={orbitControls === 'debug' ? 0 : Math.PI / 2.5}
            maxPolarAngle={orbitControls === 'debug' ? Math.PI : Math.PI / 1.8}
            minAzimuthAngle={orbitControls === 'debug' ? -Infinity : -Math.PI / 6}
            maxAzimuthAngle={orbitControls === 'debug' ? Infinity : Math.PI / 6}
          />
        )}

        <Suspense fallback={null}>
          <Environment background={false} files={`${ASSETS_BASE}/images/photo_studio_loft_hall_1k.hdr`} />
        </Suspense>

        {backgroundImage && (
          <Suspense fallback={null}>
            <Bg backgroundImage={backgroundImage} />
          </Suspense>
        )}

        <Suspense fallback={null}>
          <Avatar
            avatarUrl={modelUrl}
            speak={speak}
            setSpeak={setSpeak}
            text={text}
            setAudioSource={setAudioSource}
            playing={playing}
            ttsEndpoint={ttsEndpoint}
            enableBoneControls={enableBoneControls}
            showLevaPanel={showLevaPanel}
          />
        </Suspense>
      </Canvas>

      <Loader dataInterpolation={(p) => `Caricamento... attendere`} />
    </div>
  )
}

// Background component
function Bg({ backgroundImage }) {
  const texture = useTexture(backgroundImage);

  if (!backgroundImage) return null;

  return (
    <mesh position={[0, 1.5, -4]} scale={[1.2, 1.2, 1.2]}>
      <planeGeometry />
      <meshBasicMaterial map={texture} />
    </mesh>
  )
}

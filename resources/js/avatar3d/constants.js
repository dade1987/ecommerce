/**
 * Avatar 3D Constants and Default Values
 */

// Base path for avatar assets
export const ASSETS_BASE = '/avatar3d';

// Default props for Avatar3DReact
export const DEFAULT_PROPS = {
  title: 'Parla con il nostro assistente',
  modelUrl: `${ASSETS_BASE}/models/avatar.glb`,
  voice: 'it-IT-ElsaNeural',
  azureSpeechRegion: 'westeurope',
  enableSpeechRecognition: true,
  enableChat: true,
  locale: 'it',
  teamSlug: '',
  backgroundImage: null,
  showLevaPanel: false,
  enableBoneControls: false,
  chatEndpoint: '/api/chatbot/neuron-website-stream',
  ttsEndpoint: '/api/avatar3d/tts',
  fixedPosition: false,
  height: '100vh',
  aspectRatio: 0.75,
  transparentBackground: false,
  positionBottom: '0',
  positionRight: '0',
  avatarView: 'bust',
  orbitControls: 'none',
  mouseTrackingRadius: 400,   // raggio in px, null = tracking su viewport
  mouseTrackingSpeed: 0.08,   // velocità transizione (0.01-0.2)
  showFps: false,             // mostra pannello FPS
  // Widget mode - chat collassata con toggle button
  widgetMode: false,          // true = chat collassata con pulsante toggle
  // Shadow props
  showShadow: false,          // mostra ombra a terra
  shadowPreset: 'soft',       // preset: 'soft', 'sharp', 'diffuse', 'fullBody'
  shadowOpacity: undefined,   // override opacità (0-1)
  shadowBlur: undefined,      // override blur
  shadowY: -1,                // altezza terreno per ombra
};

// Camera presets based on avatar view
export const CAMERA_PRESETS = {
  full: { zoom: 350, posY: 1.0, targetY: 1.0 },
  bust: { zoom: 1400, posY: 1.65, targetY: 1.65 },
};

// Initial chat message
export const INITIAL_CHAT_MESSAGE = {
  msg: 'Ciao! Come posso aiutarti oggi?',
  who: 'bot',
  exct: '0',
};
/**
 * React-only entry point for Filament Fabricator pages
 * Does NOT load Alpine/Livewire (Filament handles those)
 */

import axios from 'axios';
window.axios = axios;
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// React imports
import React from 'react'
import { createRoot } from 'react-dom/client'
import Avatar3DReact from './avatar3d/Avatar3DReact.jsx'

// Mount React component if root element exists
const avatar3dRoot = document.getElementById('avatar3dReactRoot')
if (avatar3dRoot) {
    // Read props from data attributes
    const props = {
        // Base config
        title: avatar3dRoot.dataset.title || 'Parla con il nostro assistente',
        modelUrl: avatar3dRoot.dataset.modelUrl || '/avatar3d/models/avatar.glb',
        voice: avatar3dRoot.dataset.voice || 'it-IT-ElsaNeural',
        enableSpeechRecognition: avatar3dRoot.dataset.enableSpeechRecognition === 'true',
        enableChat: avatar3dRoot.dataset.enableChat !== 'false',
        locale: avatar3dRoot.dataset.locale || 'it',
        teamSlug: avatar3dRoot.dataset.teamSlug || '',
        chatEndpoint: avatar3dRoot.dataset.chatEndpoint || '/api/chatbot/neuron-website-stream',
        ttsEndpoint: avatar3dRoot.dataset.ttsEndpoint || '/api/avatar3d/tts',
        backgroundImage: avatar3dRoot.dataset.backgroundImage || null,

        // Position and layout
        fixedPosition: avatar3dRoot.dataset.fixedPosition === 'true',
        transparentBackground: avatar3dRoot.dataset.transparentBackground === 'true',
        height: avatar3dRoot.dataset.height || '400px',
        aspectRatio: parseFloat(avatar3dRoot.dataset.aspectRatio) || 0.75,
        positionBottom: avatar3dRoot.dataset.positionBottom || '0',
        positionRight: avatar3dRoot.dataset.positionRight || '0',

        // Debug & Controls
        showLevaPanel: avatar3dRoot.dataset.showLevaPanel === 'true',
        enableBoneControls: avatar3dRoot.dataset.enableBoneControls === 'true',
        orbitControls: avatar3dRoot.dataset.orbitControls || 'none', // 'none' | 'limited' | 'debug'

        // View
        avatarView: avatar3dRoot.dataset.avatarView || 'bust',

        // Mouse tracking
        // mouseTrackingRadius: null = tracking su tutto viewport (default)
        // mouseTrackingRadius: 400 = tracking solo entro 400px dal centro container
        mouseTrackingRadius: avatar3dRoot.dataset.mouseTrackingRadius
            ? parseInt(avatar3dRoot.dataset.mouseTrackingRadius, 10)
            : null,
        mouseTrackingSpeed: avatar3dRoot.dataset.mouseTrackingSpeed
            ? parseFloat(avatar3dRoot.dataset.mouseTrackingSpeed)
            : 0.08,

        // Debug
        showFps: avatar3dRoot.dataset.showFps === 'true',

        // Widget mode (chat collassata con toggle button)
        widgetMode: avatar3dRoot.dataset.widgetMode === 'true',

        // Shadow (ombra a terra)
        showShadow: avatar3dRoot.dataset.showShadow === 'true',
        shadowPreset: avatar3dRoot.dataset.shadowPreset || 'soft',
        shadowOpacity: avatar3dRoot.dataset.shadowOpacity
            ? parseFloat(avatar3dRoot.dataset.shadowOpacity)
            : undefined,
        shadowBlur: avatar3dRoot.dataset.shadowBlur
            ? parseFloat(avatar3dRoot.dataset.shadowBlur)
            : undefined,
        shadowY: avatar3dRoot.dataset.shadowY
            ? parseFloat(avatar3dRoot.dataset.shadowY)
            : -1,
    }

    createRoot(avatar3dRoot).render(
        React.createElement(Avatar3DReact, props)
    )
}
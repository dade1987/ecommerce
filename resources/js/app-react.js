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
        ttsEndpoint: avatar3dRoot.dataset.ttsEndpoint || '/api/avatar3d/tts',

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
    }

    createRoot(avatar3dRoot).render(
        React.createElement(Avatar3DReact, props)
    )
}
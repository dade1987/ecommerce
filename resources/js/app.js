import './bootstrap';

// ===================
// VUE Components
// ===================
import { createApp } from 'vue'
import EnjoyTalk3D from './components/EnjoyTalk3D.vue'
import EnjoyHen from './components/EnjoyHen.vue'

// EnjoyTalk3D mount (HeyGen)
const rootEl = document.getElementById('enjoyTalkRoot')
if (rootEl) {
    const props = {
        heygenApiKey: rootEl.dataset.heygenApiKey || '',
        heygenServerUrl: rootEl.dataset.heygenServerUrl || 'https://api.heygen.com',
        locale: rootEl.dataset.locale || 'it-IT',
        teamLogo: rootEl.dataset.teamLogo || '/images/logoai.jpeg',
        teamSlug: rootEl.dataset.teamSlug || ''
    }
    createApp(EnjoyTalk3D, props).mount('#enjoyTalkRoot')
}

// EnjoyHen mount (HeyGen)
const rootElHen = document.getElementById('enjoyHeyRoot')
if (rootElHen) {
    const propsHen = {
        heygenApiKey: rootElHen.dataset.heygenApiKey || '',
        heygenServerUrl: rootElHen.dataset.heygenServerUrl || 'https://api.heygen.com',
        locale: rootElHen.dataset.locale || 'it-IT',
        teamLogo: rootElHen.dataset.teamLogo || '/images/logoai.jpeg'
    }
    createApp(EnjoyHen, propsHen).mount('#enjoyHeyRoot')
}

// ===================
// REACT Components
// ===================
import React from 'react'
import { createRoot } from 'react-dom/client'
import Avatar3DReact from './avatar3d/Avatar3DReact.jsx'

// Avatar3DReact mount (Three.js)
const avatar3dRoot = document.getElementById('avatar3dReactRoot')
if (avatar3dRoot) {
    const props = {
        title: avatar3dRoot.dataset.title || 'Parla con il nostro assistente',
        modelUrl: avatar3dRoot.dataset.modelUrl || '/avatar3d/models/avatar.glb',
        voice: avatar3dRoot.dataset.voice || 'it-IT-ElsaNeural',
        enableSpeechRecognition: avatar3dRoot.dataset.enableSpeechRecognition === 'true',
        enableChat: avatar3dRoot.dataset.enableChat !== 'false',
        locale: avatar3dRoot.dataset.locale || 'it',
        teamSlug: avatar3dRoot.dataset.teamSlug || '',
        ttsEndpoint: avatar3dRoot.dataset.ttsEndpoint || '/api/avatar3d/tts',
        debug: avatar3dRoot.dataset.debug === 'true'
    }

    createRoot(avatar3dRoot).render(
        React.createElement(Avatar3DReact, props)
    )
}

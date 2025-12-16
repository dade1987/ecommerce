import './bootstrap';


import { createApp } from 'vue'
import EnjoyTalk3D from './components/EnjoyTalk3D.vue'
import EnjoyHen from './components/EnjoyHen.vue'
import LiveTranslator from './components/LiveTranslator.vue'

// EnjoyTalk3D mount
const rootEl = document.getElementById('enjoyTalkRoot')
const props = {}
if (rootEl) {
  props.heygenApiKey = rootEl.dataset.heygenApiKey || ''
  props.heygenServerUrl = rootEl.dataset.heygenServerUrl || 'https://api.heygen.com'
  props.locale = rootEl.dataset.locale || 'it-IT'
  props.teamLogo = rootEl.dataset.teamLogo || '/images/logoai.jpeg'

  createApp(EnjoyTalk3D, props).mount('#enjoyTalkRoot')
}

// EnjoyHen mount
const rootElHen = document.getElementById('enjoyHeyRoot')
const propsHen = {}
if (rootElHen) {
  propsHen.heygenApiKey = rootElHen.dataset.heygenApiKey || ''
  propsHen.heygenServerUrl = rootElHen.dataset.heygenServerUrl || 'https://api.heygen.com'
  propsHen.liveAvatarServerUrl = rootElHen.dataset.liveavatarServerUrl || 'https://api.liveavatar.com'
  propsHen.locale = rootElHen.dataset.locale || 'it-IT'
  propsHen.teamLogo = rootElHen.dataset.teamLogo || '/images/logoai.jpeg'

  createApp(EnjoyHen, propsHen).mount('#enjoyHeyRoot')
}

// LiveTranslator mount (Voice Translator block)
const rootElTranslator = document.getElementById('voiceTranslatorRoot')
if (rootElTranslator) {
  const propsTranslator = {}
  propsTranslator.locale = rootElTranslator.dataset.locale || 'it-IT'

  createApp(LiveTranslator, propsTranslator).mount('#voiceTranslatorRoot')
}


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
  if (rootEl.dataset.teamSlug) {
    props.teamSlug = rootEl.dataset.teamSlug
  }
  if (rootEl.dataset.calendlyUrl) {
    props.calendlyUrl = rootEl.dataset.calendlyUrl
  }

  const app = createApp(EnjoyTalk3D, props)
  app.config.devtools = true
  app.mount('#enjoyTalkRoot')
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

  const appHen = createApp(EnjoyHen, propsHen)
  appHen.config.devtools = true
  appHen.mount('#enjoyHeyRoot')
}

// LiveTranslator mount (Voice Translator block)
const rootElTranslator = document.getElementById('voiceTranslatorRoot')
if (rootElTranslator) {
  const propsTranslator = {}
  propsTranslator.locale = rootElTranslator.dataset.locale || 'it-IT'

  const appTranslator = createApp(LiveTranslator, propsTranslator)
  appTranslator.config.devtools = true
  appTranslator.mount('#voiceTranslatorRoot')
}


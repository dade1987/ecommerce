import { createApp } from 'vue'
import EnjoyTalk3D from './components/EnjoyTalk3D.vue'

// Leggi i data attributes dall'elemento host
const rootEl = document.getElementById('enjoyTalkRoot')
const props = {}
if (rootEl) {
  props.heygenApiKey = rootEl.dataset.heygenApiKey || ''
  props.heygenServerUrl = rootEl.dataset.heygenServerUrl || 'https://api.heygen.com'
  props.locale = rootEl.dataset.locale || 'it-IT'
  props.teamLogo = rootEl.dataset.teamLogo || '/images/logoai.jpeg'
}

createApp(EnjoyTalk3D, props).mount('#enjoyTalkRoot')

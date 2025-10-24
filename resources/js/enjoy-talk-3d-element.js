/**
 * EnjoyTalk3D Web Component
 * Espone il componente Vue EnjoyTalk3D come Web Component custom per essere usato in qualsiasi sito
 * 
 * Uso di base:
 * <script src="https://cavalliniservice.com/js/enjoyTalk3D.standalone.js"></script>
 * <enjoy-talk-3d team-slug="mio-team"></enjoy-talk-3d>
 * 
 * Attributi supportati:
 * - team-slug: (REQUIRED) Lo slug del team da usare per le richieste API
 */

import { defineCustomElement } from 'vue'
import EnjoyTalk3D from './components/EnjoyTalk3D.vue'
import cssText from '../css/app.css?inline'

// Inietta CSS nel documento se non è già presente
function injectStylesIfNeeded() {
  console.log('LOG injectStylesIfNeeded')
  if (!document.getElementById('enjoy-talk-3d-styles')) {
    const style = document.createElement('style')
    style.id = 'enjoy-talk-3d-styles'
    style.textContent = cssText
    document.head.appendChild(style)
    console.log('LOG style added', style, document.getElementById('enjoy-talk-3d-styles'))

    document.body.style.backgroundColor = 'black'

    console.log('LOG body background color', document.body.style.backgroundColor)
  }
}

window.addEventListener('load', () => {
  // Chiama injectStylesIfNeeded prima di montare
  injectStylesIfNeeded()
});

// Salva l'origin del server backend (da dove è stato caricato LO SCRIPT)
// Es: https://cavalliniservice.com/js/enjoyTalk3D.standalone.js → https://cavalliniservice.com
window.__ENJOY_TALK_3D_ORIGIN__ = window.__ENJOY_TALK_3D_ORIGIN__ || (() => {
  try {
    const scriptUrl = document.currentScript?.src || import.meta.url
    return new URL(scriptUrl).origin
  } catch {
    return window.location.origin
  }
})()

// Modifica il componente per accettare props
const componentWithProps = {
  ...EnjoyTalk3D,
  props: {
    teamSlug: {
      type: String,
      required: false,
      default: () => {
        // Fallback: leggi dal pathname se non fornito
        return window.location.pathname.split("/").pop()
      }
    },
    glbUrl: {
      type: String,
      default: ""
    }
  }
}

// Converti il componente Vue in Web Component
const EnjoyTalk3DElement = defineCustomElement(componentWithProps)

// Registra il custom element
customElements.define('enjoy-talk-3d', EnjoyTalk3DElement)

// Export per uso come modulo
export default EnjoyTalk3DElement


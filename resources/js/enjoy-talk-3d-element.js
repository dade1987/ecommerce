/**
 * EnjoyTalk3D Web Component
 * Espone il componente Vue EnjoyTalk3D come Web Component custom per essere usato in qualsiasi sito
 * 
 * Uso:
 * <script src="https://tuodominio.com/js/enjoyTalk3D.js"></script>
 * <enjoy-talk-3d team-slug="mio-team"></enjoy-talk-3d>
 * 
 * Attributi supportati:
 * - team-slug: (REQUIRED) Lo slug del team da usare per le richieste API
 */

import { defineCustomElement } from 'vue'
import EnjoyTalk3D from './components/EnjoyTalk3D.vue'

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
    }
  }
}

// Converti il componente Vue in Web Component
const EnjoyTalk3DElement = defineCustomElement(componentWithProps)

// Registra il custom element
customElements.define('enjoy-talk-3d', EnjoyTalk3DElement)

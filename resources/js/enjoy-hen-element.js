/**
 * EnjoyHen Web Component
 * Espone il componente Vue EnjoyHen come Web Component custom per essere usato in qualsiasi sito
 * 
 * Uso di base:
 * <script src="https://cavalliniservice.com/js/enjoyHen.standalone.js"></script>
 * <enjoy-hen team-slug="mio-team" live-avatar-api-key="..." live-avatar-server-url="..."></enjoy-hen>
 * 
 * Attributi supportati:
 * - team-slug: (REQUIRED) Lo slug del team da usare per le richieste API
 * - live-avatar-api-key: API key per LiveAvatar
 * - live-avatar-server-url: URL del server LiveAvatar
 * - heygen-api-key: (legacy) API key (fallback)
 * - heygen-server-url: (legacy) URL server (fallback)
 * - locale: Lingua (default: it-IT)
 * - team-logo: URL del logo del team
 */

import { defineCustomElement } from 'vue'
import EnjoyHen from './components/EnjoyHen.vue'
import cssText from '../css/app.css'

// Salva l'origin del server backend (da dove è stato caricato LO SCRIPT)
// Es: https://cavalliniservice.com/js/enjoyHen.standalone.js → https://cavalliniservice.com
window.__ENJOY_HEN_ORIGIN__ = window.__ENJOY_HEN_ORIGIN__ || (() => {
    try {
        const scriptUrl = document.currentScript?.src || import.meta.url
        return new URL(scriptUrl).origin
    } catch {
        return window.location.origin
    }
})()

// Modifica il componente per accettare props
const componentWithProps = {
    ...EnjoyHen,
    props: {
        teamSlug: {
            type: String,
            required: false,
            default: () => {
                // Fallback: leggi dal pathname se non fornito
                return window.location.pathname.split("/").pop()
            }
        },
        heygenApiKey: {
            type: String,
            default: ""
        },
        heygenServerUrl: {
            type: String,
            default: "https://api.heygen.com"
        },
        liveAvatarApiKey: {
            type: String,
            default: ""
        },
        liveAvatarServerUrl: {
            type: String,
            default: "https://api.liveavatar.com"
        },
        locale: {
            type: String,
            default: "it-IT"
        },
        teamLogo: {
            type: String,
            default: "/images/logoai.jpeg"
        }
    },
    // Inietta gli stili Tailwind nel Shadow DOM del custom element
    styles: [cssText]
}

// Converti il componente Vue in Web Component
const EnjoyHenElement = defineCustomElement(componentWithProps)

// Registra il custom element
customElements.define('enjoy-hen', EnjoyHenElement)

// Export per uso come modulo
export default EnjoyHenElement


import cssText from '../../css/app.css?inline'

// Inietta CSS nel documento se non è già presente
export function injectStylesIfNeeded() {
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

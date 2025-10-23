# EnjoyTalk3D Web Component Setup

## Descrizione

`EnjoyTalk3D` è un Web Component standalone che puoi integrare in qualsiasi sito web per avere un chatbot intelligente basato su siti web del team.

## Build

Compila il web component con:

```bash
npm run build:web-component
```

Output:
- `public_html/js/enjoyTalk3D.standalone.js` - JavaScript bundle (include CSS inline)

## Utilizzo

### 1. Copia il file sul tuo server

Copia il file su un server accessibile pubblicamente:
- `enjoyTalk3D.standalone.js`

### 2. Includi nel tuo HTML

```html
<!DOCTYPE html>
<html>
<head>
    <!-- Niente CSS da includere, è inline nel JS -->
</head>
<body>
    <!-- Web Component -->
    <enjoy-talk-3d 
        team-slug="mio-team"
        glb-url="https://tuodominio.com/models/avatar.glb">
    </enjoy-talk-3d>

    <!-- Script (lo carica anche gli stili) -->
    <script src="https://tuodominio.com/js/enjoyTalk3D.standalone.js"></script>
</body>
</html>
```

## Attributi

### `team-slug` (REQUIRED)
Lo slug del team per cui caricare il chatbot.

**Esempio**: `<enjoy-talk-3d team-slug="cavallini-service"></enjoy-talk-3d>`

### `glb-url` (OPTIONAL)
URL personalizzato del modello 3D (GLB/GLTF). Se non fornito, usa il default.

**Esempio**: `<enjoy-talk-3d team-slug="mio-team" glb-url="https://cdn.example.com/avatar.glb"></enjoy-talk-3d>`

## Possibili Problemi

### ❌ "Impossibile caricare GLB - <!DOCTYPE"

**Causa**: Il file GLB non è trovato (404), quindi il server restituisce HTML.

**Soluzione**:
1. Verifica che il file GLB sia accessibile (controlla l'URL nel DevTools Network tab)
2. Se usi un URL personalizzato con `glb-url`, assicurati che sia corretto e CORS enabled
3. Se il GLB è su un dominio diverso, abilita CORS nell'header del server

### ❌ "Team non trovato" o "Errore API"

**Causa**: Lo slug del team è sbagliato o il server di backend non è raggiungibile.

**Soluzione**:
1. Verifica che `team-slug` sia corretto
2. Verifica che le richieste API a `/api/chatbot/website-stream` funzionino
3. Controlla la console del browser (DevTools > Console) per messaggi di errore

## Sviluppo

### Watch mode

Per sviluppo iterativo:

```bash
npm run build:web-component -- --watch
```

### Debug

Apri DevTools (F12) e guarda la console per i log di debug:

```javascript
// Nel browser console
// Dovrai vedere log tipo:
// "SSE: connecting", { team: teamSlug, uuid, locale }
// "ANIMATION [LOADING] ✓ Humanoid caricato"
```

## Note Tecniche

- Il web component è costruito con **Vue 3** e **Three.js**
- Supporta **SSE (Server-Sent Events)** per lo streaming delle risposte
- Carica **animazioni 3D** dal server
- Gestisce **TTS (Text-to-Speech)** automaticamente
- Supporta **riconoscimento vocale** tramite Web Speech API

## CORS

Se il web component è su un dominio diverso dal backend, assicurati di avere CORS configurato correttamente.

Backend deve accettare:
- `Origin`: Il dominio dove è hostato il web component
- Metodo: `GET` per `/api/chatbot/website-stream`

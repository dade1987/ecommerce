# Guida alla Gestione delle Pagine in Filament Fabricator

Questa guida spiega come creare e gestire le pagine del sito utilizzando il pannello amministrativo Filament Fabricator.

## Accesso alla Gestione Pagine

1. Accedi al pannello amministrativo Filament: `http://127.0.0.1:8000/admin`
2. Nel menu laterale, clicca su **"Pages"**
3. Verrai portato alla lista di tutte le pagine esistenti

## Creare una Nuova Pagina

### Passo 1: Aprire il Form di Creazione

1. Dalla lista delle pagine, clicca sul pulsante **"Nuovo"** (in alto a destra)
2. Si aprirà il form di creazione con due sezioni principali:
   - **Sezione sinistra (2/3)**: Gestione dei blocchi
   - **Sezione destra (1/3)**: Proprietà della pagina

### Passo 2: Configurare le Proprietà della Pagina

Nella sezione destra, compila i seguenti campi:

#### **Title** (Titolo) - *Obbligatorio*
- Il titolo della pagina che apparirà nel browser e nei breadcrumb
- Esempio: "Assistente Virtuale 3D"

#### **Slug** (URL) - *Obbligatorio*
- **IMPORTANTE**: Lo slug della pagina deve terminare con `-show` perché il sistema lo aggiunge automaticamente all'URL finale
- L'URL completo sarà: `/{team-slug}/{page-slug}-show`
- Non può iniziare o finire con `/` (tranne per la homepage che può essere `/`)
- Deve essere unico rispetto alle pagine sorelle (stesso parent)
- **Esempio**: Se lo slug del team è `cavallini-service` e lo slug della pagina è `assistente-virtuale-3d-show`, l'URL finale sarà: `/cavallini-service/assistente-virtuale-3d-show`
- **Nota**: Il sistema aggiunge automaticamente `-show` quando l'ultimo segmento dell'URL è un parametro `item`, quindi lo slug deve già includere `-show`

#### **Description** (Descrizione) - *Opzionale*
- Una breve descrizione della pagina (max 255 caratteri)
- Utile per SEO e anteprime

#### **Layout** (Layout) - *Obbligatorio*
- Seleziona il layout da utilizzare per la pagina:
  - **Simple**: Layout minimale senza header/footer personalizzati
  - **Default**: Layout standard con header e footer
- Il layout determina la struttura HTML della pagina

#### **Parent** (Pagina Genitore) - *Opzionale*
- Seleziona una pagina genitore per creare una gerarchia
- Utile per organizzare pagine correlate (es. sottopagine)
- Clicca sull'icona di apertura per aprire la pagina genitore in una nuova scheda

### Passo 3: Aggiungere Blocchi

I blocchi sono i componenti che compongono il contenuto della pagina.

#### Aggiungere un Blocco

1. Nella sezione sinistra, clicca sul pulsante **"Aggiungi a blocks"**
2. Si aprirà un menu con tutti i blocchi disponibili
3. Seleziona il blocco desiderato

#### Blocchi Disponibili

- **EnjoyTalk 3D**: Componente per l'assistente virtuale 3D con animazioni e interazioni
- **EnjoyHen**: Componente per l'interfaccia EnjoyHen AI
- **EnjoyWork**: Componente per la sezione lavoro
- **ChatbotWidget**: Widget del chatbot
- **Navbar**: Barra di navigazione personalizzata
- **Quotes**: Sezione citazioni/testimonianze

#### Gestire i Blocchi Esistenti

Per ogni blocco nella lista puoi:

- **Riordinare**: Usa le frecce su/giù per cambiare l'ordine di visualizzazione
- **Eliminare**: Clicca sull'icona del cestino per rimuovere il blocco
- **Modificare**: Clicca sul blocco per modificare le sue proprietà (se disponibili)

**Nota**: L'ordine dei blocchi determina l'ordine di visualizzazione nella pagina finale.

### Passo 4: Salvare la Pagina

In basso a sinistra trovi tre pulsanti:

- **Salva**: Salva la pagina e torna alla lista
- **Salva & nuovo**: Salva la pagina e apre immediatamente il form per crearne una nuova
- **Annulla**: Annulla le modifiche e torna alla lista senza salvare

### Passo 5: Visualizzare l'Anteprima

1. Nella sezione destra, clicca sul pulsante **"Preview"** (in alto)
2. La pagina verrà aperta in una nuova scheda con l'anteprima finale

## Modificare una Pagina Esistente

1. Dalla lista delle pagine, clicca sull'icona di modifica (matita) accanto alla pagina desiderata
2. Modifica i campi e i blocchi come necessario
3. Clicca su **"Salva"** per applicare le modifiche

## Clonare una Pagina

1. Dalla lista delle pagine, clicca sull'icona **"Clona"** (due quadrati sovrapposti)
2. Verrà creata una copia della pagina con il titolo modificato aggiungendo " - Copia"
3. Lo slug verrà rigenerato automaticamente
4. Puoi poi modificare la pagina clonata come desideri

## Visualizzare una Pagina

1. Dalla lista delle pagine, clicca sull'icona di visualizzazione (occhio)
2. La pagina verrà aperta nel browser con l'URL pubblico

## Struttura dei Blocchi

I blocchi vengono salvati come JSON nel database e vengono renderizzati in sequenza quando la pagina viene visualizzata.

### Esempio di Struttura Blocchi

```json
[
  {
    "type": "enjoy-talk-3d",
    "data": {}
  },
  {
    "type": "enjoy-work",
    "data": {}
  }
]
```

## Struttura URL delle Pagine

Le pagine vengono servite con la seguente struttura URL:

```
/{team-slug}/{page-slug}-show
```

Dove:
- `{team-slug}` è lo slug del team (es. `cavallini-service`)
- `{page-slug}` è lo slug della pagina che **deve terminare con `-show`**

**Esempio completo:**
- Team slug: `cavallini-service`
- Page slug: `assistente-virtuale-3d-show`
- URL finale: `/cavallini-service/assistente-virtuale-3d-show`

Il sistema aggiunge automaticamente `-show` quando rileva che l'ultimo segmento dell'URL è un parametro `item`, quindi è importante che lo slug della pagina includa già `-show` alla fine.

## Best Practices

1. **Slug Semplici**: Usa slug descrittivi ma brevi, separati da trattini
2. **Slug con `-show`**: **SEMPRE** termina lo slug della pagina con `-show` per garantire il corretto routing
3. **Gerarchia Pagine**: Organizza le pagine usando il campo Parent per creare una struttura logica
4. **Ordine Blocchi**: Pensa all'esperienza utente quando ordini i blocchi
5. **Layout Appropriato**: Scegli il layout più adatto al contenuto della pagina
6. **Descrizioni**: Aggiungi sempre una descrizione per migliorare la SEO

## Troubleshooting

### La pagina non si visualizza correttamente

- Verifica che tutti i blocchi siano configurati correttamente
- Controlla che il layout selezionato esista
- Verifica che lo slug sia unico e valido

### I blocchi non appaiono

- Assicurati di aver salvato la pagina dopo aver aggiunto i blocchi
- Verifica che i blocchi siano stati aggiunti nell'ordine corretto
- Controlla i log per eventuali errori di rendering

### Problemi con lo slug

- **IMPORTANTE**: Lo slug deve terminare con `-show` per funzionare correttamente
- Lo slug non può iniziare o finire con `/` (tranne per la homepage)
- Lo slug deve essere unico tra le pagine sorelle (stesso parent)
- Se modifichi manualmente lo slug, assicurati che sia valido e termini con `-show`
- L'URL completo sarà `/{team-slug}/{page-slug}-show` dove `{page-slug}` include già `-show`

## Note Tecniche

- Le pagine sono salvate nella tabella `pages` del database
- I blocchi sono salvati come JSON nel campo `blocks`
- Il sistema supporta pagine gerarchiche tramite il campo `parent_id`
- Ogni pagina può avere un `team_id` per il multi-tenancy
- I layout sono definiti in `app/Filament/Fabricator/Layouts/`
- I blocchi sono definiti in `app/Filament/Fabricator/PageBlocks/`


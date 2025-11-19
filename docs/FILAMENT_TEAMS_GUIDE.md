# Guida alla Gestione dei Team in Filament

Questa guida spiega come creare e gestire i Team nel pannello amministrativo Filament. I Team sono utilizzati per organizzare i contenuti e configurare lo scraper per siti web multipli.

## Accesso alla Gestione Team

1. Accedi al pannello amministrativo Filament: `http://127.0.0.1:8000/admin`
2. Nel menu laterale, clicca su **"Teams"**
3. Verrai portato alla lista di tutti i team esistenti

## Creare un Nuovo Team

### Passo 1: Aprire il Form di Creazione

1. Dalla lista dei team, clicca sul pulsante **"Nuovo"** (in alto a destra)
2. Si aprirà il form di creazione "Nuovo Team"

### Passo 2: Compilare i Dati del Team

Il form è organizzato in due colonne con i seguenti campi:

#### **Colonna Sinistra**

##### **Name** (Nome) - *Obbligatorio*
- Il nome completo del team/azienda
- Esempio: "Azienda SRL"
- **Nota**: Quando inserisci il nome, lo slug viene generato automaticamente

##### **Nation** (Nazione) - *Opzionale*
- La nazione di appartenenza del team
- Esempio: "Italia"

##### **Province** (Provincia) - *Opzionale*
- La provincia di appartenenza
- Esempio: "Venezia"

##### **Street** (Via/Indirizzo) - *Opzionale*
- L'indirizzo completo della sede
- Esempio: "Via Roma 123"

##### **Phone** (Telefono) - *Opzionale*
- Il numero di telefono del team
- Esempio: "+39 041 1234567"

#### **Colonna Destra**

##### **Slug** (URL) - *Obbligatorio*
- Lo slug univoco del team utilizzato negli URL
- Viene generato automaticamente dal nome quando inserisci il campo Name
- **IMPORTANTE**: Questo slug viene utilizzato negli URL delle pagine
- Esempio: `aziendasrl`
- **Struttura URL**: `http://127.0.0.1:8000/{page-slug}/{team-slug}`
  - Esempio completo: `http://127.0.0.1:8000/assistente-virtuale-3d/aziendasrl`

##### **Region** (Regione) - *Opzionale*
- La regione di appartenenza
- Esempio: "Veneto"

##### **Municipality** (Comune) - *Opzionale*
- Il comune di appartenenza
- Esempio: "Noale"

##### **Postal code** (Codice Postale) - *Opzionale*
- Il codice postale
- Esempio: "30033"

### Passo 3: Configurare i Siti Web (Websites)

La sezione **"Siti Web"** è fondamentale per configurare lo scraper che legge i contenuti dei siti web.

#### Aggiungere URL

1. Nella sezione "Siti Web", clicca su **"Aggiungi URL"** per aggiungere un nuovo sito web
2. Inserisci l'URL completo del sito (deve iniziare con `http://` o `https://`)
   - Esempio: `https://trentaduebit.it/index.new.html`
3. Puoi aggiungere più URL cliccando nuovamente su "Aggiungi URL"

#### Gestire gli URL

Per ogni URL aggiunto puoi:

- **Riordinare**: Usa le frecce su/giù per cambiare l'ordine di scraping
- **Eliminare**: Clicca sull'icona del cestino per rimuovere un URL
- **Espandere/Comprimere**: Usa i link "Espandi tutti" / "Comprimi tutti" per gestire la visualizzazione

#### Validazione URL

- Gli URL devono essere validi e iniziare con `http://` o `https://`
- Se un URL non è valido, verrà evidenziato con una linea rossa ondulata
- Assicurati che tutti gli URL siano corretti prima di salvare

**Nota Importante**: Gli URL inseriti in questa sezione sono quelli che lo scraper utilizzerà per leggere e indicizzare i contenuti dei siti web del team. Assicurati di includere tutte le pagine importanti del sito.

### Passo 4: Salvare il Team

In basso a sinistra trovi i pulsanti:

- **Salva**: Salva il team e torna alla lista
- **Salva & nuovo**: Salva il team e apre immediatamente il form per crearne uno nuovo
- **Annulla**: Annulla le modifiche e torna alla lista senza salvare

## Modificare un Team Esistente

1. Dalla lista dei team, clicca sull'icona di modifica (matita) accanto al team desiderato
2. Modifica i campi come necessario
3. Puoi aggiungere, modificare o rimuovere URL dalla sezione "Siti Web"
4. Clicca su **"Salva"** per applicare le modifiche

## Struttura URL con Team

Quando crei una pagina e un team, l'URL pubblico seguirà questa struttura:

```
http://127.0.0.1:8000/{page-slug}/{team-slug}
```

**Esempio pratico:**
- Page slug: `assistente-virtuale-3d-show`
- Team slug: `aziendasrl`
- URL finale: `http://127.0.0.1:8000/assistente-virtuale-3d/aziendasrl`

**Nota**: Il sistema rimuove automaticamente `-show` dallo slug della pagina nell'URL pubblico quando viene utilizzato con un team.

## Best Practices

1. **Slug Descritti**: Usa slug descrittivi ma brevi per i team (es. `aziendasrl` invece di `azienda-srl`)
2. **URL Completi**: Inserisci sempre URL completi nella sezione "Siti Web", inclusi `http://` o `https://`
3. **URL Prioritari**: Ordina gli URL nella sezione "Siti Web" in base all'importanza (i primi vengono processati per primi)
4. **Dati Completi**: Compila tutti i campi disponibili per avere informazioni complete sul team
5. **Validazione**: Verifica sempre che gli URL siano validi prima di salvare

## Utilizzo con lo Scraper

Gli URL inseriti nella sezione "Siti Web" vengono utilizzati dal sistema di scraping per:

- Indicizzare i contenuti dei siti web
- Aggiornare il database RAG (Retrieval-Augmented Generation)
- Fornire informazioni aggiornate al chatbot AI
- Migliorare le risposte del sistema basate sui contenuti reali del sito

Assicurati di includere:
- La homepage del sito
- Le pagine principali (chi siamo, servizi, contatti)
- Le pagine dei prodotti/servizi
- Qualsiasi altra pagina con contenuti rilevanti

## Troubleshooting

### Lo slug non viene generato automaticamente

- Assicurati di aver inserito prima il campo "Name"
- Lo slug viene generato quando modifichi il campo Name
- Puoi sempre modificare manualmente lo slug se necessario

### Gli URL non vengono validati correttamente

- Verifica che gli URL inizino con `http://` o `https://`
- Assicurati che gli URL siano completi e non relativi
- Controlla che non ci siano spazi o caratteri speciali non validi

### Il team non appare negli URL delle pagine

- Verifica che il team sia stato salvato correttamente
- Controlla che lo slug del team sia valido
- Assicurati che la pagina sia associata al team corretto

## Note Tecniche

- I team sono salvati nella tabella `teams` del database
- Gli URL dei siti web sono salvati come JSON nel campo `websites`
- Il sistema supporta multi-tenancy tramite i team
- Ogni team può avere più URL associati per lo scraping
- Lo scraper processa gli URL nell'ordine in cui sono elencati nella sezione "Siti Web"


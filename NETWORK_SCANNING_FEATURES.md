# Nuove Funzionalità di Network Scanning

## Panoramica

Il sistema di analisi di sicurezza è stato notevolmente migliorato con l'aggiunta di funzionalità avanzate di network scanning e analisi delle vulnerabilità.

## Funzionalità Implementate

### 1. Rilevamento Cloudflare Avanzato
- **Analisi header HTTP**: Ricerca di indicatori specifici come `CF-RAY`, `Server: cloudflare`, `cf-cache-status`
- **Controllo IP ranges**: Verifica se l'IP del dominio appartiene ai range noti di Cloudflare
- **Riduzione automatica del rischio**: Se Cloudflare è rilevato, il punteggio di rischio viene ridotto del 15-20%

### 2. Analisi WordPress Completa
- **Rilevamento versione**: Identificazione della versione WordPress in uso
- **Enumerazione plugin**: Elenco dei plugin installati e attivi
- **Identificazione tema**: Rilevamento del tema utilizzato
- **API REST WordPress**: Controllo dell'API wp-json per informazioni aggiuntive
- **Analisi vulnerabilità**: Verifica di CVE specifiche per WordPress, plugin e temi

### 3. Port Scanning con ReactPHP
- **Scansione asincrona**: Utilizzo di ReactPHP per performance ottimizzate
- **Timeout configurabili**: Controllo preciso dei tempi di scansione
- **Porte comuni**: Scansione delle porte più critiche (21, 22, 25, 80, 443, 3306, etc.)
- **Gestione errori avanzata**: Logging dettagliato e recupero da errori

### 4. Banner Grabbing Intelligente
- **Richieste specifiche per protocollo**: Comandi ottimizzati per ogni servizio
- **Identificazione software**: Riconoscimento automatico di Apache, Nginx, OpenSSH, MySQL, etc.
- **Estrazione versioni**: Parsing delle versioni software dai banner
- **Indicatori di rischio**: Identificazione automatica di configurazioni insicure

### 5. Analisi CVE Potenziata
- **Analisi contestuale**: Utilizzo di GPT-4 per analisi approfondita delle vulnerabilità
- **Scoring del rischio**: Valutazione da 1 a 10 per ogni vulnerabilità
- **Raccomandazioni specifiche**: Suggerimenti di sicurezza per ogni CVE identificata
- **Correlazione dati**: Combinazione di banner, versioni e indicatori di rischio

## Architettura Tecnica

### NetworkScanningService
- **Localizzazione**: `app/Services/NetworkScanningService.php`
- **Funzionalità principali**:
  - `portScan()`: Scansione principale delle porte
  - `extractCVEInfo()`: Estrazione informazioni per analisi CVE
  - `extractSoftwareInfo()`: Identificazione software dai banner
  - `findRiskIndicators()`: Ricerca indicatori di rischio

### DomainAnalysisService (Aggiornato)
- **Nuovi metodi**:
  - `detectCloudflare()`: Rilevamento protezione Cloudflare
  - `analyzeWordPress()`: Analisi completa WordPress
  - `performPortScan()`: Integrazione con NetworkScanningService
  - `analyzeCVE()`: Analisi vulnerabilità migliorata

## Interfaccia Utente

### Nuove Sezioni Dashboard
1. **Protezione Cloudflare**: Badge verde/giallo con dettagli indicatori
2. **WordPress**: Versione, plugin e temi rilevati
3. **Porte Aperte**: Lista dettagliata con servizi identificati
4. **Vulnerabilità CVE**: Elenco con livello di rischio e raccomandazioni

### Visualizzazione Dati
- **Badge colorati**: Indicatori visivi per stato di sicurezza
- **Overflow scrollabile**: Gestione di liste lunghe
- **Dettagli espandibili**: Informazioni aggiuntive per ogni elemento
- **Codifica colori**: Verde (sicuro), Giallo (warning), Rosso (pericolo)

## Prestazioni e Scalabilità

### Ottimizzazioni
- **Scansione asincrona**: Riduzione significativa dei tempi di esecuzione
- **Timeout ottimizzati**: Bilanciamento tra completezza e velocità
- **Caching intelligente**: Riutilizzo risultati per sottodomini simili
- **Gestione memoria**: Limitazione dimensioni banner e dati

### Configurazioni
- **Timeout connessione**: 3 secondi per porta
- **Timeout banner**: 2 secondi per richiesta
- **Limite banner**: 1024 caratteri massimi
- **Porte scansionate**: 20 porte più comuni

## Sicurezza

### Misure di Protezione
- **Rate limiting**: Controllo frequenza richieste
- **Input sanitization**: Validazione domini e parametri
- **Error handling**: Gestione sicura degli errori
- **Logging**: Tracciamento completo delle attività

### Considerazioni Etiche
- **Scansione passiva**: Nessuna attività invasiva
- **Rispetto rate limits**: Conformità alle policy dei servizi
- **Uso responsabile**: Limitazione alle funzionalità necessarie

## Esempi di Utilizzo

### Scansione Completa
```php
$domainService = new DomainAnalysisService();
$results = $domainService->analyze('example.com');
```

### Scansione Network Specifica
```php
$networkService = new NetworkScanningService();
$portResults = $networkService->portScan('example.com');
$cveInfo = $networkService->extractCVEInfo($portResults);
```

### Scansione WordPress
```php
$networkService = new NetworkScanningService();
$wpResults = $networkService->scanWordPressSpecific('example.com');
```

## Configurazione

### Variabili Ambiente
```env
OPENAI_API_KEY=your_openai_key_here
SHODAN_API_KEY=your_shodan_key_here
```

### Dipendenze
- `react/socket`: Connessioni socket asincrone
- `react/stream`: Gestione stream di dati
- `OpenAI client`: Analisi CVE intelligente

## Sistema Critical Points

### Nessuna Limitazione sui Punti Critici

Il sistema è progettato per mostrare **TUTTI** i punti critici trovati durante l'analisi:

- **Formato specifico**: Ogni punto critico specifica il dominio/sottodominio esatto: `[dominio.com] Descrizione specifica del problema`
- **Contatore visivo**: L'interfaccia mostra un contatore che indica il numero totale di punti critici
- **Nessun limite arbitrario**: Non vengono imposti limiti sul numero di punti critici visualizzati
- **Verifica CVE rigorosa**: Ogni CVE è verificato nel database NVD/MITRE prima di essere incluso
- **Punteggio basato su CVSS**: Il rischio è calcolato usando score CVSS reali

### Esempi di Critical Points
- `[admin.example.com] Apache 2.2.15 - CVE-2011-3192 (CVSS 7.8 - DoS)`
- `[blog.example.com] WordPress 4.9.1 - CVE-2018-6389 (CVSS 5.3 - DoS)`
- `[ftp.example.com] vsftpd 2.3.4 - CVE-2011-2523 (CVSS 10.0 - RCE Backdoor)`

## Sistema di Punteggio Rischio

### Calcolo Basato su CVSS Score
- **CVSS 9.0-10.0 (CRITICO)**: +40-50 punti (SQL Injection, RCE)
- **CVSS 7.0-8.9 (ALTO)**: +25-35 punti (XSS, Directory Traversal)  
- **CVSS 4.0-6.9 (MEDIO)**: +10-20 punti (Information Disclosure)
- **CVSS 0.1-3.9 (BASSO)**: +5-10 punti (Minor issues)

### Verifica CVE Rigorosa
- Ogni CVE è verificato nel database NVD/MITRE
- Solo versioni software specifiche vengono considerate
- Nessun CVE fittizio o inventato
- Fallback a pattern matching per vulnerabilità note

## Gestione Timeout

### Scansioni Lunghe
- **Timeout execution**: 5 minuti per scansione completa
- **Timeout processo**: 30 secondi per ogni processo
- **Timeout rete**: 5 secondi per connessioni socket
- **Gestione progressive**: Interrompe scansione se timeout vicino
- **Notifica utente**: Avviso nell'interfaccia per scansioni parziali

## Limitazioni

- **Timeout di rete**: Alcune scansioni potrebbero fallire su reti lente
- **Rate limiting**: API esterne possono limitare le richieste
- **Firewall**: Alcuni firewall potrebbero bloccare la scansione
- **Costi API**: Utilizzo di OpenAI per analisi CVE

## Roadmap Future

1. **Integrazione Shodan**: Utilizzo API Shodan per dati aggiuntivi
2. **Scansione UDP**: Estensione alle porte UDP
3. **Database CVE locale**: Cache locale delle vulnerabilità
4. **Reporting avanzato**: Export PDF dei risultati
5. **Scansione programmata**: Monitoraggio continuo domini

## Troubleshooting

### Problemi Comuni
- **Timeout connessione**: Aumentare timeout in NetworkScanningService
- **Memoria insufficiente**: Ridurre limite banner o porte scansionate
- **API key mancante**: Configurare OPENAI_API_KEY correttamente
- **Permessi rete**: Verificare che PHP possa creare connessioni socket

### Debug
```php
// Abilita logging dettagliato
Log::debug('Port scan results', $portResults);
Log::debug('CVE analysis results', $cveResults);
```

## Conclusioni

Il sistema di network scanning implementato fornisce una soluzione completa e professionale per l'analisi di sicurezza di domini web. Le funzionalità avanzate di rilevamento Cloudflare, analisi WordPress, port scanning e valutazione CVE offrono una copertura completa dei principali vettori di attacco.

L'utilizzo di ReactPHP garantisce performance ottimali, mentre l'integrazione con OpenAI fornisce analisi intelligenti delle vulnerabilità. L'interfaccia utente intuitiva rende i risultati facilmente comprensibili anche per utenti non tecnici. 
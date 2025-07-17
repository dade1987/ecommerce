<?php

return [
    'select_subdomains' => "Dalla seguente lista di sottodomini per ':domain', seleziona i 5 che ritieni più interessanti per un'analisi di sicurezza (es. api, vpn, dev, admin, test, etc.). Restituisci SOLO un array JSON con i 5 domini. Lista: :subdomain_list",

    'wordpress_vulnerability_system' => "Sei un esperto di sicurezza WordPress. Analizza le versioni specifiche di WordPress, plugin e temi per identificare vulnerabilità note con CVE specifici. Rispondi SOLO con dati concreti e verificabili.",
    'wordpress_vulnerability_user' => <<<PROMPT
Analizza questa installazione WordPress per identificare vulnerabilità specifiche e concrete.

CRITERI RIGOROSI PER WORDPRESS:
1. VERIFICA che il CVE esista nel database NVD/WPVulnDB
2. CONFERMA che la versione specifica sia vulnerabile
3. Per plugin/temi senza versione identificata, NON assumere vulnerabilità
4. Fornisci SOLO CVSS score reali e verificati
5. Se non sei sicuro del CVE, ometti dalla risposta
6. NON creare CVE fittizi o inventati
7. Per file esposti, considera solo se rappresentano rischi concreti

ESEMPI CVE WORDPRESS CORRETTI:
- WordPress 4.9.1: CVE-2018-6389 (CVSS 5.3 - DoS)
- Contact Form 7 4.6.1: CVE-2017-9804 (CVSS 6.1 - XSS)
- WP Super Cache 1.4.4: CVE-2017-1000600 (CVSS 8.8 - RCE)
- Yoast SEO 7.0.2: CVE-2018-6511 (CVSS 6.1 - XSS)

INSTALLAZIONE WORDPRESS:
:data_string

Fornisci risposta in formato JSON:
{
  "plugins": [
    {
      "name": "contact-form-7",
      "version": "4.6.1",
      "cves": ["CVE-2017-9804"],
      "risk_level": 8,
      "cvss_score": 6.1,
      "description": "XSS vulnerability in Contact Form 7 before 4.8",
      "recommendation": "Aggiorna a versione 4.8 o superiore"
    }
  ],
  "themes": [
    {
      "name": "twentyfifteen",
      "version": "1.8",
      "cves": ["CVE-2019-8943"],
      "risk_level": 6,
      "description": "Authenticated theme editor vulnerability",
      "recommendation": "Aggiorna tema o disabilita editor"
    }
  ],
  "core_issues": [
    {
      "component": "WordPress Core",
      "version": "4.9.1",
      "cves": ["CVE-2018-6389"],
      "risk_level": 7,
      "cvss_score": 5.3,
      "description": "DoS vulnerability in load-scripts.php",
      "recommendation": "Aggiorna a WordPress 4.9.2 o superiore"
    }
  ]
}
PROMPT,

    'cve_analysis_system' => "Sei un esperto di cybersecurity specializzato in analisi di vulnerabilità CVE. Analizza i servizi forniti e identifica potenziali CVE basandoti sulle informazioni dettagliate del banner, versioni software e indicatori di rischio. Fornisci raccomandazioni specifiche per ogni vulnerabilità identificata.",
    'cve_analysis_user' => "Analizza i seguenti servizi identificati tramite port scan e banner grabbing. IMPORTANTE: Identifica SOLO CVE specifici per versioni software chiaramente identificate.\n\nCRITERI RIGOROSI PER CVE:\n1. VERIFICA che il CVE esista nel database NVD/MITRE\n2. CONFERMA che la versione software identificata sia effettivamente vulnerabile\n3. FORNISCI solo CVSS score reali e verificati\n4. Se la versione non è identificata, NON assumere vulnerabilità\n5. Se non sei sicuro del CVE, ometti dalla risposta\n6. NON creare CVE fittizi o inventati\n\nEsempi di CVE CORRETTI:\n- Apache 2.2.15: CVE-2011-3192 (CVSS 7.8 - DoS)\n- vsftpd 2.3.4: CVE-2011-2523 (CVSS 10.0 - RCE Backdoor)\n- WordPress 4.9.1: CVE-2018-6389 (CVSS 5.3 - DoS)\n- OpenSSH 7.4: CVE-2016-10012 (CVSS 7.8 - Privilege Escalation)\n\nFormato JSON richiesto:\n{\n  \"vulnerabilities\": [\n    {\n      \"port\": 80,\n      \"service\": \"Apache 2.2.15\",\n      \"software\": \"Apache\",\n      \"version\": \"2.2.15\",\n      \"confirmed_cves\": [\"CVE-2011-3192\"],\n      \"risk_level\": 8,\n      \"cvss_score\": 7.8,\n      \"vulnerability_type\": \"DoS\",\n      \"recommendations\": \"Aggiorna ad Apache 2.4.x\"\n    }\n  ]\n}\n\nServizi trovati:\n:cve_info",

    'risk_score_system' => 'Sei un esperto di cybersecurity. Analizza i dati forniti sull\'infrastruttura di un dominio (incluso i suoi sottodomini) e restituisci SOLO un oggetto JSON con due chiavi: "risk_percentage" e "critical_points" (un array di stringhe in italiano). Basa la tua analisi e il punteggio ESCLUSIVAMENTE sui dati concreti forniti. Ignora completamente eventuali campi o dati mancanti.',
    'risk_score_json_error' => "La risposta dell'Intelligenza Artificiale non è un JSON valido.",
    'risk_score_summary_intro' => "Analisi eseguita sul dominio principale :domain. Trovati :subdomains_found sottodomini, di cui :subdomains_scanned sono stati scansionati.",
    'risk_score_summary_timeout' => "ATTENZIONE: La scansione è stata interrotta per timeout, i risultati potrebbero essere parziali.",
    'risk_score_user' => <<<PROMPT
Sulla base di questa analisi completa per il dominio :domain e i suoi sottodomini, fornisci una stima percentuale del rischio di compromissione entro i prossimi 3 mesi.
Evidenzia i punti più critici trovati nell'intera infrastruttura.

ISTRUZIONI CRITICHE PER LA VALUTAZIONE - SOLO DATI CONCRETI:

PUNTEGGIO DI RISCHIO (0-100) - SISTEMA BILANCIATO:
- Basa il punteggio su TUTTI i fattori di sicurezza analizzati, non solo CVE.
- Considera TUTTI questi elementi nell'analisi:

VULNERABILITÀ CONCRETE (peso: 40-60%):
  * CVSS 9.0-10.0 (CRITICO): +40-50 punti (SQL Injection, RCE)
  * CVSS 7.0-8.9 (ALTO): +25-35 punti (XSS, Directory Traversal)
  * CVSS 4.0-6.9 (MEDIO): +10-20 punti (Info Disclosure)
  * CVSS 0.1-3.9 (BASSO): +5-10 punti (Minor issues)

SECURITY HEADERS MANCANTI (peso: 15-20%):
  * Mancanza di CSP, X-Frame-Options, HSTS: +10-15 punti
  * Security score < 30%: +5-10 punti aggiuntivi

TECNOLOGIE OBSOLETE (peso: 10-15%):
  * jQuery < 3.0: +5-8 punti
  * Bootstrap < 4.0: +3-5 punti
  * DOCTYPE HTML 4.01/XHTML: +3-5 punti
  * Adobe Flash rilevato: +8-12 punti
  * Meta tag IE compatibility: +2-3 punti

PORTE APERTE (peso: 5-10%):
  * Porte non standard o inusuali: +5-10 punti per porta
  * Servizi esposti senza versioni identificate: +3-5 punti per servizio

CONFIGURAZIONE GENERALE (peso: 5-10%):
  * Mancanza di robots.txt: +2-3 punti
  * Errori SSL/TLS: +5-8 punti
  * Servizi non responsivi o irraggiungibili: +3-5 punti

FATTORI PROTETTIVI:
  * Cloudflare protection: -15-20 punti
  * HTTPS correttamente configurato: -5-10 punti
  * Security headers presenti: -5-15 punti

CRITICAL POINTS - PROBLEMI DI SICUREZZA IDENTIFICATI:
- Crea una lista concisa e diversificata dei punti critici più importanti (massimo 15).
- L'obiettivo è fornire una panoramica bilanciata dei rischi. Includi risultati da TUTTE le categorie di analisi (Vulnerabilità CVE, Header, Tecnologie, Porte, WordPress, etc.), se sono stati trovati problemi.
- RAGGRUPPA i problemi identici o sistemici. Se lo stesso header manca su più sottodomini, segnalalo una sola volta in modo aggregato.
- Dai priorità ai problemi più gravi (es. CVE critici, porte di database esposte) rispetto a quelli di media o bassa gravità (es. header mancanti).
- Formato: "[DOMINIO o 'Sistemico'] Descrizione specifica e chiara del problema."

ESEMPI DI RAGGRUPPAMENTO E DIVERSIFICAZIONE:
- "[Tutti i target analizzati] Mancanza sistemica dell'header Content-Security-Policy (CSP)."
- "[ftp.example.com] vsftpd 2.3.4 identificato, potenzialmente vulnerabile a backdoor critica (CVE-2011-2523)."
- "[example.com] Il sito utilizza una versione obsoleta di jQuery (1.8.3) che non riceve aggiornamenti di sicurezza dal 2012."
- "[db.example.com] La porta 3306 (MySQL) risulta aperta e accessibile da Internet, un grave rischio per la sicurezza dei dati."
- "[mail.example.com] Host irraggiungibile durante la scansione, impossibile verificare la configurazione web ma la porta 25 (SMTP) è aperta."

REGOLA: Ogni punto critico deve essere verificabile dal cliente. Include dettagli specifici quando disponibili. Se un host è irraggiungibile, non riportare problemi di header o di contenuto web per esso.

:summary_string

Ecco i dati raccolti (in forma riassunta per ogni target):
:data_string

Fornisci la risposta esclusivamente in formato JSON, con le chiavi "risk_percentage" (numero intero da 0 a 100) e "critical_points" (array di stringhe in italiano che descrivono i punti critici specifici trovati, ciascuno con il formato "[DOMINIO] Descrizione").
PROMPT,
    
    'banner_analysis_system' => "Sei un esperto di cybersecurity specializzato nell'identificazione di servizi di rete. Analizza i banner dei servizi per identificare il software specifico, la versione e eventuali vulnerabilità.",
    'banner_analysis_user' => <<<PROMPT
Analizza questo banner di servizio di rete per identificare SOLO informazioni concrete e verificabili.

PORTA: :port
BANNER COMPLETO:
:banner

ISTRUZIONI RIGOROSE:
1. Identifica SOLO software e versioni se chiaramente visibili nel banner
2. NON fare supposizioni o assumere versioni se non esplicite
3. Includi CVE specifici SOLO se la versione è nota e vulnerabile
4. Se non riesci a identificare la versione esatta, specifica solo il software base

ESEMPI ACCETTABILI:
- "Apache 2.2.15 - CVE-2011-3192 (DoS vulnerability)"
- "OpenSSH 7.4 - nessuna vulnerabilità critica nota"
- "vsftpd 2.3.4 - backdoor smiley face (CRITICO)"
- "MySQL 5.5.62 - versione EOL dal 2018"
- "nginx 1.10.3 - CVE-2017-7529 (integer overflow)"

ESEMPI NON ACCETTABILI:
- "Apache (versione probabilmente obsoleta)"
- "SSH (potenzialmente vulnerabile)"
- "FTP (configurazione potenzialmente insicura)"
- "MySQL (versione sconosciuta, possibili rischi)"

REGOLA FONDAMENTALE: Se non hai dati precisi dal banner, restituisci solo il nome del servizio base senza speculazioni sulla sicurezza.

Fornisci la risposta in formato JSON con la chiave "service_identification" contenente una stringa descrittiva basata SOLO su dati concreti dal banner.
PROMPT,
]; 
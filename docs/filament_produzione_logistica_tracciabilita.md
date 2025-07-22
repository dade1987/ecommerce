# Documentazione Filament Resource: Produzione, Logistica, Tracciabilità

Questa documentazione descrive in dettaglio le principali Filament Resource relative ai processi di Produzione, Logistica e Tracciabilità del sistema.

---

## 1. Produzione

### 1.1 ProductionOrderResource
- **Modello collegato:** `ProductionOrder`
- **Gruppo Navigazione:** Produzione
- **Label:** Ordine di Produzione
- **Campi principali (Form):**
  - Cliente (obbligatorio)
  - Data Ordine (obbligatorio, default oggi)
  - Stato (enum, default PENDING)
  - Distinta Base (relazione con BOM, obbligatorio)
  - Quantità (obbligatorio, numerico, min 1)
  - Note (testo libero)
- **Tabella (Table):**
  - ID Ordine, Cliente, Codice BOM, Quantità, Stato (badge), Priorità (icona), Data Ordine
- **Azioni:**
  - Modifica
  - Importazione Ordine tramite AI (caricamento file, parsing, creazione automatica)
- **Filtri:**
  - Stato
- **Relazioni:**
  - Fasi di Produzione (ProductionPhasesRelationManager)
- **Pagine:**
  - Elenco, Crea, Modifica
- **Note:**
  - Supporta importazione intelligente da file tramite servizio AI

### 1.2 ProductionLineResource
- **Modello collegato:** `ProductionLine`
- **Gruppo Navigazione:** Produzione
- **Label:** Linea di Produzione
- **Campi principali (Form):**
  - Nome Linea (obbligatorio)
  - Descrizione
  - Stato (Attiva, Inattiva, In Manutenzione)
- **Tabella (Table):**
  - Nome Linea, Stato (badge colorato), Data Creazione
- **Azioni:**
  - Modifica
- **Relazioni:**
  - Postazioni di Lavoro (WorkstationsRelationManager)
- **Pagine:**
  - Elenco, Crea, Modifica

### 1.3 WorkstationResource
- **Modello collegato:** `Workstation`
- **Gruppo Navigazione:** Produzione
- **Label:** Postazione di Lavoro
- **Campi principali (Form):**
  - Linea di Produzione (relazione, obbligatorio)
  - Nome Postazione (obbligatorio)
  - Descrizione
  - Stato (Attiva, Inattiva, In Manutenzione)
  - Capacità (ore/giorno, default 8)
  - Dimensione Lotto (default 1)
  - Tempo per Unità (minuti, default 10)
  - Campi Digital Twin: Stato real-time, Velocità corrente, Livello usura, Tasso di errore, Data ultima manutenzione
- **Tabella (Table):**
  - Nome Postazione, Linea di Produzione, Stato, Stato Real-Time, Usura, Capacità, Data Creazione, Data Modifica
- **Azioni:**
  - Modifica
- **Relazioni:**
  - Disponibilità (AvailabilitiesRelationManager)
- **Pagine:**
  - Elenco, Crea, Modifica

---

## 2. Logistica

### 2.1 InventoryMovementResource
- **Modello collegato:** `InventoryMovement`
- **Gruppo Navigazione:** Logistica
- **Label:** Movimento di Inventario
- **Campi principali (Form):**
  - Tipo Movimento (Carico, Scarico, Trasferimento)
  - Magazzino Origine/Destinazione (relazione, visibilità dinamica)
  - Prodotto (relazione, visibilità dinamica)
  - Prodotti Specifici (Digital Twin, selezione multipla)
  - Quantità (solo per carico)
  - Distanza (km), Mezzo di Trasporto, Note
- **Tabella (Table):**
  - Tipo (badge), Prodotto, Da, A, Quantità, Unità Mosse, Data
- **Azioni:**
  - Modifica
- **Pagine:**
  - Elenco, Crea, Modifica

---

## 3. Tracciabilità

Attualmente la tracciabilità è gestita tramite i Digital Twin e le relazioni tra risorse di produzione e logistica. I movimenti di inventario e le postazioni di lavoro includono campi e relazioni che permettono la ricostruzione della storia di ogni unità/prodotto.

---

## 4. Relazioni tra le risorse
- Gli ordini di produzione sono collegati alle distinte base e alle fasi di produzione.
- Le linee di produzione sono collegate alle postazioni di lavoro.
- I movimenti di inventario tracciano i prodotti e i Digital Twin tra magazzini.
- Le postazioni di lavoro includono dati real-time e storici per la tracciabilità operativa.

---

## 5. Note aggiuntive
- Tutte le risorse implementano pagine di elenco, creazione e modifica.
- I badge e le icone aiutano la visualizzazione rapida dello stato.
- L’importazione AI degli ordini di produzione velocizza l’inserimento massivo.
- La tracciabilità è garantita dall’integrazione tra risorse e dai Digital Twin.

---

_Questa documentazione è generata automaticamente sulla base del codice delle Filament Resource presenti nel progetto._

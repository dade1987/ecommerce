---
title: "Sistema Integrato di Gestione Industriale"
author: "Gemini AI Assistant"
date: "2024-07-18"
geometry: "margin=1in"
mainfont: "DejaVu Serif"
sansfont: "DejaVu Sans"
monofont: "DejaVu Sans Mono"
---

# Documentazione Tecnica e Funzionale: Sistema Integrato di Gestione Industriale

**Versione:** 1.0
**Autore:** Gemini AI Assistant
**Audience:** Management, Responsabili di Produzione, Logistica e Qualità.

---

## 1. Visione d'Insieme: La Fabbrica Digitale Connessa

Questo documento illustra in dettaglio l'architettura e le funzionalità della piattaforma integrata per la gestione industriale. La soluzione è stata progettata per superare le sfide della moderna manifattura, offrendo un controllo centralizzato e intelligente sui tre pilastri fondamentali dell'operatività: **Produzione**, **Logistica** e **Tracciabilità**.

A differenza dei sistemi gestionali tradizionali, spesso frammentati in moduli non comunicanti, la nostra piattaforma si basa su un **modello di dati unificato**. Ogni informazione, dalla materia prima in ingresso al prodotto finito consegnato, vive all'interno di un unico ecosistema. Questo garantisce coerenza, elimina le ridondanze e abilita funzionalità avanzate di automazione e analisi, come l'**importazione degli ordini tramite Intelligenza Artificiale** e il calcolo dell'**impronta di carbonio (CO2)** per singolo prodotto.

L'obiettivo è fornire uno strumento che non solo registri le operazioni, ma che guidi attivamente le decisioni strategiche per migliorare l'efficienza, ridurre i costi e aumentare la competitività e la sostenibilità del business.

---

## 2. Modulo Produzione: Il Cuore Pulsante della Fabbrica

Il modulo di Produzione è progettato per orchestrare l'intero processo produttivo, dalla pianificazione delle risorse alla gestione degli ordini, fino al controllo delle singole postazioni di lavoro.

### 2.1. Anagrafiche di Produzione

La base per una pianificazione efficace risiede in anagrafiche solide e ben strutturate.

#### 2.1.1. Distinte Base (BOM - Bill of Materials)
- **Cosa fa**: La risorsa `BomResource` permette di definire la "ricetta" di ogni prodotto. Si possono creare distinte base complesse, specificando per ogni prodotto finito l'elenco esatto di materie prime, semilavorati, e le relative quantità.
- **Dati Gestiti**:
    - `Codice Interno`: Identificativo univoco della distinta base.
    - `Materiali`: Un elenco ripetibile di componenti, ognuno con:
        - `Tipo Materiale` (es. "Lamiera Acciaio S235").
        - `Spessore` (in mm).
        - `Quantità` per produrre una singola unità del prodotto finito.
- **Vantaggio per il Business**: Standardizza la produzione, garantisce che vengano usati i materiali corretti e permette un calcolo preciso dei costi standard di produzione e dei fabbisogni di materiali.

#### 2.1.2. Linee di Produzione e Postazioni di Lavoro
- **Cosa fa**: Le risorse `ProductionLineResource` e `WorkstationResource` permettono di mappare digitalmente l'assetto fisico della fabbrica.
- **Dati Gestiti (`ProductionLineResource`)**:
    - `Nome Linea` e `Descrizione`.
    - `Stato`: Attiva, Inattiva, In Manutenzione.
- **Dati Gestiti (`WorkstationResource`)**:
    - `Nome Postazione` e collegamento alla `Linea di Produzione` di appartenenza.
    - `Capacità Produttiva`: Ore/giorno, dimensione lotto, tempo per unità (minuti). Questi dati sono FONDAMENTALI per la schedulazione.
    - **Campi Digital Twin**: Questa sezione rappresenta lo stato in tempo reale della macchina. Include `Stato Real-Time` (in funzione, guasta), `Velocità Corrente`, `Livello di Usura (%)`, `Tasso di Errore (%)` e `Data Ultima Manutenzione`.
- **Vantaggio per il Business**: Fornisce al sistema di pianificazione dati realistici sulla capacità produttiva effettiva. I campi "Digital Twin" aprono la porta alla **manutenzione predittiva**, segnalando un'usura elevata prima che causi un guasto, e a un'analisi delle performance (OEE) molto accurata.

### 2.2. Esecuzione e Controllo della Produzione

#### 2.2.1. Ordini di Produzione (`ProductionOrderResource`)
- **Cosa fa**: È il centro di comando operativo. Gestisce gli ordini che devono essere prodotti.
- **Dati Gestiti**:
    - `Cliente`, `Data Ordine`, `Quantità`.
    - Collegamento alla `Distinta Base` (BOM) per sapere cosa produrre.
    - `Stato`: Un ciclo di vita completo dell'ordine (Es. In Attesa, In Lavorazione, Completato, etc.).
    - `Priorità`: Un indicatore visivo (da "bassa" a "critica") per aiutare i pianificatori a gestire le urgenze.
- **Funzionalità Chiave: Importazione Ordini con AI**
    - **Il Problema**: L'inserimento manuale di ordini da file PDF o email è un'attività lenta, costosa e ad alto rischio di errore.
    - **La Soluzione**: Un'azione speciale "Importa con AI" permette all'utente di caricare un file d'ordine del cliente. Il servizio `OrderParsingService` analizza il documento, estrae le informazioni rilevanti (cliente, prodotto, quantità) e, se trova una corrispondenza con una Distinta Base esistente, pre-compila automaticamente il form di creazione dell'ordine di produzione. Se non trova una corrispondenza, guida l'utente alla creazione di una nuova distinta base.
    - **Vantaggio per il Business**: Drastica riduzione dei tempi di processamento degli ordini, eliminazione degli errori di data-entry, e liberazione di risorse umane per attività a maggior valore. Accelera il flusso "Order-to-Cash".

---

## 3. Modulo Logistica: Flussi di Materiali Ottimizzati

Il modulo Logistica governa tutti i movimenti fisici di merci, garantendo che i materiali giusti siano nel posto giusto al momento giusto.

### 3.1. Mappatura dei Luoghi Fisici (`WarehouseResource`)
- **Cosa fa**: Definisce l'anagrafica di tutti i luoghi in cui può trovarsi l'inventario.
- **Dati Gestiti**:
    - `Nome` del luogo (es. "Magazzino Materie Prime", "Magazzino Prodotti Finiti", "Negozio Milano").
    - `Tipo`: Specifica la natura del luogo (Fornitore, Magazzino, Negozio).
    - `È Destinazione Finale?`: Un flag importante per capire se un prodotto che arriva in quel luogo è da considerarsi "consegnato" al cliente finale.
- **Vantaggio per il Business**: Crea una mappa chiara della supply chain, essenziale per la gestione dei flussi e per la tracciabilità.

### 3.2. Movimenti di Inventario (`InventoryMovementResource`)
- **Cosa fa**: È il registro di ogni singolo spostamento di materiale. È il cuore della logistica e il fondamento della tracciabilità.
- **Tipi di Movimento**:
    - `Carico`: Ingresso di nuovi prodotti/materiali (es. da un fornitore o da fine produzione).
    - `Scarico`: Uscita di prodotti (es. vendita a un cliente).
    - `Trasferimento`: Spostamento tra due magazzini interni.
    - `Reso`: Rientro di merce da un cliente.
- **Dati Gestiti**:
    - `Magazzino Origine` e `Magazzino Destinazione`.
    - `Prodotto` e `Quantità`.
    - Dati per la Sostenibilità: `Distanza (km)` e `Mezzo di Trasporto` (Camion, Treno, etc.).
- **Integrazione con la Tracciabilità**: Per i movimenti di `Scarico` e `Trasferimento`, il sistema non chiede una quantità generica, ma permette di selezionare gli **specifici "Product Twin"** (i singoli pezzi unici) da muovere.
- **Vantaggio per il Business**: Fornisce una visibilità completa e in tempo reale sulle giacenze e sulla loro esatta ubicazione. L'associazione con i Product Twin abilita una tracciabilità a livello di singolo item, non solo di lotto.

---

## 4. Modulo Tracciabilità: Il Passaporto Digitale del Prodotto

Questo modulo offre una visione a 360° sulla storia di ogni singolo prodotto fabbricato, dal primo all'ultimo giorno del suo ciclo di vita.

### 4.1. Il Gemello Digitale del Prodotto (`ProductTwinResource`)
- **Cosa fa**: Ogni volta che un prodotto viene creato (es. a fine produzione), viene generato un `ProductTwin`, ovvero un suo gemello digitale univoco.
- **Dati Gestiti**:
    - Collegamento al `Prodotto` generico.
    - `UUID`: L'identificativo univoco a vita del singolo pezzo.
    - `Stato del Ciclo di Vita`: Uno stato che evolve nel tempo (es. `in_produzione`, `in_stock`, `in_transit`, `in_use`, `recycled`).
    - **`CO2 Totale (kg)`**: Un campo numerico che aggrega le emissioni di CO2 associate a quel singolo pezzo durante tutte le fasi della sua vita (produzione, trasporti, etc.).
- **Visualizzazione dello Storico**: La risorsa permette di visualizzare, per ogni Product Twin, l'elenco completo dei suoi `InventoryMovements`, ricostruendo la sua intera storia: quando è stato prodotto, dove è stato spostato, quando è stato venduto.
- **Vantaggio per il Business**:
    - **Qualità e Sicurezza**: In caso di non conformità, permette di identificare e richiamare specifici pezzi difettosi con precisione chirurgica, invece di interi lotti, con un enorme risparmio economico e di immagine.
    - **Marketing e Valore Aggiunto**: Offre al cliente finale la possibilità di conoscere la storia del prodotto che ha acquistato, un potente strumento di marketing.
    - **Sostenibilità**: Fornisce dati concreti e misurabili sull'impatto ambientale, permettendo di calcolare la Carbon Footprint di prodotto e di supportare le dichiarazioni di sostenibilità aziendale con prove oggettive.

---

## 5. Conclusione: Un Sistema Orientato al Futuro

Questa piattaforma integrata non è solo un sistema gestionale, ma un asset strategico. Automatizzando i processi a basso valore (come il data-entry), fornendo dati in tempo reale per decisioni operative (come la pianificazione della produzione) e abilitando una tracciabilità granulare e sostenibile, il sistema pone le basi per una crescita intelligente, resiliente e responsabile. 
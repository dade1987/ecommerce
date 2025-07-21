# Digital Twin-Enabled Smart Production Process Planning Tool

**Sottotitolo:** Una soluzione per la Challenge 3.1 per l'ottimizzazione e la simulazione intelligente dei processi produttivi.

---

## 1. Abstract / Executive Summary

- **Il Contesto:** Breve introduzione sulla crescente necessità di agilità e resilienza nella produzione moderna.
- **La Sfida (Challenge 3.1):** Sintesi dei requisiti chiave del bando: necessità di un Digital Twin, simulazione "what-if", ottimizzazione AI-driven e validazione a TRL 6-7.
- **La Nostra Soluzione:** Presentazione del nostro gestionale evoluto come una piattaforma integrata che risponde a queste esigenze, trasformando dati statici in un modello produttivo dinamico e predittivo.
- **Risultati Chiave Attesi:** Anticipazione dei benefici quantitativi (es. riduzione dei lead time, aumento dell'OEE, migliore gestione dei colli di bottiglia).

---

## 2. Architettura della Soluzione: Dal Dato al Digital Twin

*TODO: Inserire qui un diagramma dell'architettura (es. Mermaid.js)*

**Flusso dei dati:**
- **Input Dati:** Ordini, anagrafica macchine.
- **Core System (Laravel/PHP):**
  - **Modelli Eloquent Estesi:** `Workstation` con stato real-time, `ProductionPhase` con vincoli di setup/manutenzione.
  - **Motori di Servizio:** `AdvancedSchedulingService`, `SimulationService`, `DemandForecastingService`.
  - **Logger Dedicato:** `SimulationLogManager` che scrive su `simulation.log`.
- **Interfaccia Utente (Filament):** Dashboard KPI, Gantt, azioni di simulazione.
- **Output:** Piani di produzione ottimizzati, report, log di validazione.

**Il Concetto di Digital Twin:** Spiegazione di come il modello `Workstation` non sia solo un'anagrafica, ma un vero "gemello digitale" con attributi dinamici (`real_time_status`, `wear_level`, `current_speed`) che riflettono lo stato operativo.

---

## 3. Funzionalità Chiave Implementate

### 3.1. Schedulazione Avanzata a Vincoli Finiti

- **Problema:** La pianificazione tradizionale ignora tempi di setup e manutenzioni.
- **Soluzione:** Il nostro `AdvancedSchedulingService` ora considera:
  - **Setup/Changeover Time:** Aggiunto a ogni fase produttiva.
  - **Manutenzione Programmata:** Gestita come blocchi non disponibili che influenzano la pianificazione.
- **Dimostrazione "Prima vs Dopo":** *TODO: Inserire screenshot del Gantt prima e dopo l'implementazione dei vincoli.*

### 3.2. Simulazione "What-If"

- **Problema:** Valutare l'impatto di eventi imprevisti senza stravolgere il piano reale.
- **Soluzione:** Il `SimulationService` e l'azione "Simulazione 'What-If'" sulla dashboard.
- **Caso d'Uso:** Demo dello scenario "Ordine Urgente", mostrando come il sistema calcola i potenziali ritardi e i nuovi colli di bottiglia.

### 3.3. Previsione della Domanda (Demand Forecasting)

- **Problema:** Reagire alla domanda futura invece di subirla.
- **Soluzione:** Il `DemandForecastingService` implementato con un modello di **Media Mobile Ponderata (WMA)** basato sugli ordini storici.
- **Impatto Visibile:** La dashboard mostra il volume previsto e suggerisce azioni proattive (es. ordini di stock) per prevenire i `stock-out`.

---

## 4. Validazione a TRL 6-7: Scenari di Test e Risultati

### Protocollo di Test
Descrizione del comando `test:simulation` e dei 5 scenari di test definiti:
1.  Inserimento Ordine Urgente
2.  Guasto Macchina Simulato
3.  Variazione Efficienza Operatore
4.  Cambio di Priorità
5.  Validazione Previsione Domanda

### Raccolta KPI Quantitativa
Tabella riassuntiva con i risultati (da compilare eseguendo i test) ottenuti dai log di `simulation.log`.

| Caso di Test                | KPI Misurato                 | Valore Riscontrato |
| --------------------------- | ---------------------------- | ------------------ |
| 1. Ordine Urgente           | Ordini Esistenti Ritardati   | *da compilare*     |
| 2. Guasto Macchina          | Impatto su OEE               | *da compilare*     |
| 3. Variazione Efficienza    | Aumento Lead Time Medio      | *da compilare*     |
| 5. Previsione Domanda       | Accuratezza (MAE)            | *da compilare*     |

### Collaborazioni
*TODO: Inserire qui i dettagli del Memorandum of Understanding (MoU) con il partner industriale/playground per la certificazione TRL 6-7.*

---

## 5. Conclusione e Sviluppi Futuri

- **Sintesi dei Risultati:** Il nostro strumento ha dimostrato di poter creare piani di produzione più realistici, simulare scenari futuri e fornire supporto decisionale basato sui dati.
- **Prossimi Passi:**
  - Sostituire il modello WMA con algoritmi ML più sofisticati (ARIMA, XGBoost).
  - Affinare ulteriormente l'algoritmo di schedulazione con batch sizing e logica di ottimizzazione multi-obiettivo.
  - Integrare dati da sensori IoT reali per aggiornare automaticamente il Digital Twin. 
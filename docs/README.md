# Documentazione Tecnica del Sistema di Produzione e Logistica

Questo documento descrive l'architettura tecnica del sistema, coprendo i moduli di Produzione e Logistica e la loro integrazione con le nuove funzionalità di Digital Twin e tracciabilità della Carbon Footprint.

## 1. Modulo di Produzione

Il modulo di produzione è progettato per gestire l'intero flusso degli ordini, dalla pianificazione all'esecuzione.

### Entità Principali della Produzione

-   `ProductionLine`: Rappresenta una linea di produzione fisica.
-   `Workstation`: Rappresenta una singola stazione di lavoro all'interno di una linea, con una propria capacità.
-   `Bom` (Bill of Materials): La Distinta Base di un `InternalProduct`, che ne elenca i materiali.
-   `ProductionOrder`: L'ordine per produrre una certa quantità di un `InternalProduct`. È caratterizzato da una priorità e uno stato (es. `in_attesa`, `in_produzione`, `completato`).
-   `ProductionPhase`: Una singola fase di lavorazione richiesta per completare un `ProductionOrder`. Ogni fase è legata a una `Workstation` e ha una durata stimata.

### Servizi e Logiche Chiave

-   **Schedulazione (`ProductionSchedulingService`)**: Questo servizio orchestra la pianificazione.
    -   `scheduleProduction()`: Processa gli ordini in attesa in base alla priorità e alla capacità delle linee, cambiandone lo stato in "in produzione" e schedulando le singole fasi in sequenza.
    -   `predictBottlenecks()`: Analizza il carico di lavoro delle `Workstation` per identificare potenziali colli di bottiglia, confrontando le ore di lavoro stimate con la capacità disponibile.
    -   `balanceProductionLines()`: Assegna automaticamente gli ordini non ancora pianificati alla linea di produzione con il carico di lavoro minore.

-   **Simulazione "What-If" (`SimulationService`)**:
    -   `runWhatIfSimulation()`: Permette di simulare l'impatto di un nuovo ordine ipotetico sulla pianificazione esistente. Il servizio crea un ordine fittizio in memoria, lo aggiunge alla coda degli ordini reali e riesegue la schedulazione per visualizzare l'effetto sulla produzione senza modificare i dati reali.

---

## 2. Modulo di Logistica

Il modulo di logistica gestisce l'inventario e le movimentazioni dei prodotti.

### Entità Principali della Logistica

-   `Warehouse`: Rappresenta un magazzino, che può essere di diverso tipo (es. `magazzino`, `fornitore`, `negozio`).
-   `InventoryMovement`: Traccia ogni movimento di inventario (carico, scarico, trasferimento) tra magazzini.

### Logica Chiave

-   Il sistema traccia la quantità di `InternalProduct` in ogni `Warehouse` calcolando la somma dei movimenti di entrata e di uscita.
-   I movimenti possono essere manuali o generati automaticamente al completamento di un ordine di produzione.

---

## 3. Digital Twin e Carbon Footprint

Le nuove funzionalità si innestano sui moduli esistenti per creare una tracciabilità di dettaglio e un monitoraggio ambientale.

### Concetti e Modelli

-   **`InternalProduct`**: È l'entità che unifica Produzione e Logistica. Contiene anche i dati per la sostenibilità (fattore di emissione, durata, riciclabilità).
-   **`ProductTwin`**: È il gemello digitale di una *singola istanza fisica* di un `InternalProduct`. Nasce al termine della produzione e segue il prodotto per tutta la sua vita.
    -   **Traccia lo stato**: `lifecycle_status` (es. `in_production`, `in_stock`, `in_transit`).
    -   **Accumula CO₂**: `co2_emissions_production`, `co2_emissions_logistics`, `co2_emissions_total`.

### Integrazione e Flusso dei Dati

1.  **Fine Produzione**: Quando un `ProductionOrder` viene completato, il sistema crea N `ProductTwin` (dove N è la quantità prodotta). Le **emissioni di CO₂ della produzione** vengono calcolate in base al `energy_consumption` delle fasi e salvate in ogni gemello.
2.  **Movimentazione Logistica**: Un `InventoryMovement` non sposta più una quantità, ma **specifici `ProductTwin`**, grazie a una tabella pivot.
3.  **Aggiornamento Continuo**: Ad ogni movimento, lo **stato del `ProductTwin` viene aggiornato** e le **emissioni di CO₂ della logistica** (calcolate da `distance_km` e `transport_mode`) vengono aggiunte al totale di ogni gemello.

### Reporting e Tracciabilità

La risorsa **Product Twins** in Filament permette di:
-   Visualizzare ogni gemello digitale, il suo stato e la sua carbon footprint totale.
-   Accedere a una vista di dettaglio con lo **storico di tutti i movimenti** subiti da quello specifico prodotto e la suddivisione delle emissioni. 
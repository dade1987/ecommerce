# Avvio Docker Compose: Locale vs Produzione

## Panoramica

Il progetto utilizza file `.dockerignore` diversi per sviluppo locale e build di produzione. Questo è necessario perché:

- **Sviluppo**: Il Dockerfile-php ha bisogno di accedere a `docker-dev-playwright/php.ini`
- **Produzione**: La directory `docker-dev-playwright/` deve essere esclusa per ridurre la dimensione dell'immagine

## File Disponibili

```
ecommerce/
├── .dockerignore          # File attivo (da switchare)
├── .dockerignore.dev      # Configurazione per sviluppo locale
├── .dockerignore.prod     # Configurazione per build di produzione
└── .dockerignore.bak      # Backup
```

## Sviluppo Locale

### 1. Attiva il dockerignore per dev

```bash
cd ecommerce
cp .dockerignore.dev .dockerignore
```

### 2. Avvia i container

```bash
cd docker-dev-playwright
docker-compose up --build
```

### Differenza chiave `.dockerignore.dev`

Esclude solo file specifici, mantenendo `php.ini` accessibile:

```
docker-dev-playwright/Dockerfile
docker-dev-playwright/nginx.conf
docker-dev-playwright/docker-compose.yml
docker-dev-playwright/certs/
# php.ini NON escluso - necessario per COPY nel Dockerfile
```

## Build di Produzione

### 1. Attiva il dockerignore per prod

```bash
cd ecommerce
cp .dockerignore.prod .dockerignore
```

### 2. Build immagine di produzione

```bash
cd docker-prod
docker-compose -f docker-compose.prod.yml build
```

### Differenza chiave `.dockerignore.prod`

Esclude l'intera directory di sviluppo:

```
docker-dev-playwright/   # Tutta la directory esclusa
```

## Troubleshooting

### Errore: `failed to compute cache key: "/docker-dev-playwright/php.ini": not found`

**Causa**: Stai usando `.dockerignore.prod` per un build di sviluppo.

**Soluzione**:
```bash
cp .dockerignore.dev .dockerignore
docker-compose build --no-cache
```

### Errore: Immagine di produzione troppo grande

**Causa**: Stai usando `.dockerignore.dev` per un build di produzione.

**Soluzione**:
```bash
cp .dockerignore.prod .dockerignore
docker-compose -f docker-compose.prod.yml build --no-cache
```

## Script di Utility (Opzionale)

Puoi creare uno script per semplificare lo switch:

```bash
#!/bin/bash
# switch-dockerignore.sh

case "$1" in
  dev)
    cp .dockerignore.dev .dockerignore
    echo "Switchato a .dockerignore.dev"
    ;;
  prod)
    cp .dockerignore.prod .dockerignore
    echo "Switchato a .dockerignore.prod"
    ;;
  *)
    echo "Uso: $0 {dev|prod}"
    exit 1
    ;;
esac
```

Utilizzo:
```bash
./switch-dockerignore.sh dev   # Per sviluppo
./switch-dockerignore.sh prod  # Per produzione
```
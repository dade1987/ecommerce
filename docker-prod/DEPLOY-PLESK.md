# Deploy su Plesk - Avatar 3D v1

## Prerequisiti

- Plesk con estensione Docker installata
- Accesso SSH al server (opzionale ma consigliato)
- Subscription creata per `demo-avatar.trentaduebit.it`

---

## Step 1: Build dell'Immagine

Sul tuo Mac, dalla directory `ecommerce/`:

```bash
# Rendi eseguibile lo script
chmod +x docker-prod/build.sh

# Build dell'immagine
./docker-prod/build.sh latest

# Esporta come file tar.gz per upload
./docker-prod/build.sh latest --save
```

Questo crea `avatar-3d-v1-latest.tar.gz` (~300-500MB).

---

## Step 2: Configura Database su Plesk

1. **Vai su Plesk** → Subscription `demo-avatar.trentaduebit.it`
2. **Databases** → **Add Database**
   - Database name: `avatar3d_prod`
   - Database user: `avatar3d_user`
   - Password: (genera password sicura)
3. **Configura accesso dalla rete Docker**:
   - phpMyAdmin → SQL tab → esegui:
   ```sql
   GRANT ALL ON avatar3d_prod.* TO 'avatar3d_user'@'172.17.0.%' IDENTIFIED BY 'TUA_PASSWORD';
   FLUSH PRIVILEGES;
   ```

---

## Step 3: Upload e Carica Immagine

### Opzione A: Via SSH (Consigliata)

```bash
# Upload del tar.gz
scp avatar-3d-v1-latest.tar.gz user@server:/tmp/

# SSH nel server
ssh user@server

# Carica immagine in Docker
docker load -i /tmp/avatar-3d-v1-latest.tar.gz

# Verifica
docker images | grep avatar
```

### Opzione B: Via Docker Hub

```bash
# Tag per registry
docker tag avatar-3d-v1:latest tuousername/avatar-3d-v1:latest

# Push
docker login
docker push tuousername/avatar-3d-v1:latest
```

Poi su Plesk Docker Extension: **Run Image** → `tuousername/avatar-3d-v1:latest`

---

## Step 4: Configura Container su Plesk

### Via Docker Extension di Plesk

1. **Docker** → **Run Image**
2. Seleziona `avatar-3d-v1:latest`
3. **Container settings**:
   - Name: `avatar-3d-v1-prod`
   - Port mapping: `8080:80`

4. **Environment variables** (IMPORTANTE):

```
APP_NAME=Avatar3D
APP_ENV=production
APP_DEBUG=false
APP_URL=https://demo-avatar.trentaduebit.it
APP_KEY=base64:GENERA_CON_php_artisan_key:generate

DB_CONNECTION=mysql
DB_HOST=172.17.0.1
DB_PORT=3306
DB_DATABASE=avatar3d_prod
DB_USERNAME=avatar3d_user
DB_PASSWORD=TUA_PASSWORD_DB

OPENAI_API_KEY=sk-proj-xxx
OPENAI_ASSISTANT_ID=asst_xxx

CACHE_DRIVER=file
SESSION_DRIVER=file
QUEUE_CONNECTION=database

RUN_MIGRATIONS=true
```

5. **Volume mappings** (IMPORTANTE per persistenza dati):

   Senza volumi, i dati vengono persi al riavvio del container!

   In Plesk Docker Extension, aggiungi questi mapping:

   | Container Path | Host Path | Descrizione |
   |----------------|-----------|-------------|
   | `/var/www/html/storage/app` | `/var/www/vhosts/demo-avatar.trentaduebit.it/storage/app` | Upload utenti |
   | `/var/www/html/storage/logs` | `/var/www/vhosts/demo-avatar.trentaduebit.it/storage/logs` | Log Laravel |

   **Oppure usa volumi Docker named:**
   - `avatar_storage_app:/var/www/html/storage/app`
   - `avatar_storage_logs:/var/www/html/storage/logs`

   **Prima di avviare**, crea le directory sull'host:
   ```bash
   mkdir -p /var/www/vhosts/demo-avatar.trentaduebit.it/storage/{app,logs}
   chown -R 82:82 /var/www/vhosts/demo-avatar.trentaduebit.it/storage
   ```
   (82 è l'UID di www-data in Alpine)

6. **Run**

### Via SSH con Docker Compose

```bash
# Crea directory config
mkdir -p /opt/avatar-3d-v1
cd /opt/avatar-3d-v1

# Crea file .env
cat > .env << 'EOF'
APP_KEY=base64:xxx
DB_HOST=172.17.0.1
DB_PORT=3306
DB_DATABASE=avatar3d_prod
DB_USERNAME=avatar3d_user
DB_PASSWORD=xxx
OPENAI_API_KEY=sk-proj-xxx
OPENAI_ASSISTANT_ID=asst_xxx
RUN_MIGRATIONS=true
EOF

# Upload docker-compose.prod.yml e avvia
docker-compose -f docker-compose.prod.yml up -d
```

---

## Step 5: Configura Proxy in Plesk

Il container gira sulla porta 8080. Devi configurare Plesk per fare proxy:

1. **Subscription** → **Hosting Settings**
2. **Apache & nginx Settings** → **Additional nginx directives**:

```nginx
location / {
    proxy_pass http://127.0.0.1:8080;
    proxy_http_version 1.1;
    proxy_set_header Host $host;
    proxy_set_header X-Real-IP $remote_addr;
    proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
    proxy_set_header X-Forwarded-Proto $scheme;
    proxy_read_timeout 300;
    proxy_send_timeout 300;
}
```

3. **OK** → Plesk riavvia nginx

---

## Step 6: SSL Certificate

1. **Subscription** → **SSL/TLS Certificates**
2. **Let's Encrypt** → genera certificato per `demo-avatar.trentaduebit.it`
3. Il proxy passerà HTTPS → HTTP al container

---

## Step 7: Verifica Deploy

```bash
# Test health check
curl https://demo-avatar.trentaduebit.it/health

# Logs container
docker logs avatar-3d-v1-prod

# Shell nel container
docker exec -it avatar-3d-v1-prod sh

# Test database connection
docker exec -it avatar-3d-v1-prod php artisan tinker
>>> DB::connection()->getPdo();
```

---

## Troubleshooting

### Container non si avvia

```bash
# Controlla logs
docker logs avatar-3d-v1-prod

# Verifica variabili
docker inspect avatar-3d-v1-prod | grep -A 50 "Env"
```

### Database connection refused

1. Verifica IP gateway Docker:
   ```bash
   docker network inspect bridge | grep Gateway
   ```
2. Assicurati che MySQL accetti connessioni da `172.17.0.%`
3. Verifica firewall non blocchi la porta 3306

### 502 Bad Gateway

- Container non è running
- Porta 8080 non è quella corretta
- Verifica: `docker ps` e `curl http://127.0.0.1:8080/health`

### Permission denied su storage

```bash
docker exec -it avatar-3d-v1-prod sh
chown -R www-data:www-data /var/www/html/storage
chmod -R 775 /var/www/html/storage
```

---

## Aggiornamenti

Per deployare una nuova versione:

```bash
# Sul Mac: build nuova immagine
./docker-prod/build.sh v1.1 --save

# Upload e carica sul server
scp avatar-3d-v1-v1.1.tar.gz user@server:/tmp/
ssh user@server "docker load -i /tmp/avatar-3d-v1-v1.1.tar.gz"

# Stop vecchio container
docker stop avatar-3d-v1-prod
docker rm avatar-3d-v1-prod

# Avvia con nuova immagine
# (stesse env variables di prima)
```

---

## Comandi Utili

### Gestione Container

```bash
# Status container
docker ps -a | grep avatar

# Logs in tempo reale
docker logs -f avatar-3d-v1-prod

# Restart
docker restart avatar-3d-v1-prod

# Entrare nel container (shell)
docker exec -it avatar-3d-v1-prod sh

# Entrare come www-data (per operazioni file)
docker exec -it -u www-data avatar-3d-v1-prod sh
```

### Comandi Artisan (da eseguire nel container)

```bash
# Entrare nel container
docker exec -it avatar-3d-v1-prod sh

# Una volta dentro:

# Storage link (già eseguito automaticamente all'avvio)
php artisan storage:link

# Migrazioni
php artisan migrate:status
php artisan migrate --force

# Cache (già eseguito automaticamente se APP_ENV=production)
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
php artisan optimize

# Pulisci TUTTE le cache
php artisan optimize:clear

# Queue
php artisan queue:restart
php artisan queue:work --once  # Esegui un singolo job
php artisan queue:failed       # Vedi job falliti
php artisan queue:retry all    # Riprova tutti i job falliti

# Database
php artisan db:seed --force
php artisan tinker

# Debug
php artisan about
php artisan route:list
```

### Eseguire Artisan dall'esterno

```bash
# Senza entrare nel container
docker exec -it avatar-3d-v1-prod php artisan migrate:status
docker exec -it avatar-3d-v1-prod php artisan queue:restart
docker exec -it avatar-3d-v1-prod php artisan optimize:clear
docker exec -it avatar-3d-v1-prod php artisan tinker
```

### Logs Laravel

```bash
# Vedere gli ultimi log
docker exec -it avatar-3d-v1-prod tail -f /var/www/html/storage/logs/laravel.log

# Oppure monta il volume e leggi da host
```

---

## Note Importanti

1. **APP_KEY**: Genera con `php artisan key:generate --show` in locale
2. **Backup**: Configura backup del database da Plesk
3. **Logs**: I log Laravel sono in `/var/www/html/storage/logs/`
4. **Queue**: Il container avvia automaticamente 2 queue workers via supervisor
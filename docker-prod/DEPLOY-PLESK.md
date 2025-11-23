# Deploy Avatar 3D v1 su Plesk

Guida completa per il deploy del container Docker su Plesk.

---

## 1. Build dell'immagine

```bash
cd ecommerce
./docker-prod/build.sh latest --save
```

Output: `avatar-3d-v1-latest.tar.gz` (~270MB)

**Nota**: Il build usa `--platform linux/amd64` per compatibilità con server Plesk.

---

## 2. Upload e Import su Plesk

### 2.1 Upload
Carica `avatar-3d-v1-latest.tar.gz` sul server via SFTP:
```
/var/www/vhosts/demo-avatar.trentaduebit.it/
```

### 2.2 Import immagine
```bash
ssh root@server
cd /var/www/vhosts/demo-avatar.trentaduebit.it/
docker load -i avatar-3d-v1-latest.tar.gz
docker images | grep avatar-3d-v1
```

---

## 3. Configurazione MySQL

### 3.1 Bind Address
Modifica la configurazione MySQL per accettare connessioni dal container Docker:

```bash
nano /etc/mysql/mariadb.conf.d/50-server.cnf
# oppure
nano /etc/mysql/mysql.conf.d/mysqld.cnf
```

Cambia:
```ini
bind-address = 0.0.0.0
```

Riavvia MySQL:
```bash
systemctl restart mysql
# oppure
systemctl restart mariadb
```

### 3.2 Firewall Plesk
In Plesk: **Tools & Settings → Firewall**

Aggiungi regola per permettere connessioni dal Docker bridge network:
- **Source**: 172.17.0.0/16 (Docker network)
- **Port**: 3306
- **Action**: Allow

Oppure via iptables:
```bash
iptables -A INPUT -s 172.17.0.0/16 -p tcp --dport 3306 -j ACCEPT
```

### 3.3 Permessi utente MySQL
L'utente MySQL deve poter connettersi dall'IP del container:

```sql
GRANT ALL PRIVILEGES ON your_database.* TO 'your_user'@'172.17.%' IDENTIFIED BY 'your_password';
FLUSH PRIVILEGES;
```

---

## 4. Preparazione File System

### 4.1 Trova UID/GID utente Plesk
```bash
id username
# Output esempio: uid=10050(username) gid=1003(psacln)
```

**IMPORTANTE**: Questi valori servono per `PUID` e `PGID`!

### 4.2 Crea directory storage
```bash
cd /var/www/vhosts/demo-avatar.trentaduebit.it/
mkdir -p storage
chown -R username:psacln storage
chmod -R 775 storage
```

### 4.3 Crea file .env.prod
```bash
nano /var/www/vhosts/demo-avatar.trentaduebit.it/.env.prod
```

Contenuto:
```env
APP_NAME="Avatar 3D"
APP_ENV=production
APP_KEY=base64:YOUR_APP_KEY_HERE
APP_DEBUG=false
APP_URL=https://demo-avatar.trentaduebit.it

LOG_CHANNEL=stack
LOG_LEVEL=error

# Database - usa IP gateway Docker bridge
DB_CONNECTION=mysql
DB_HOST=172.17.0.1
DB_PORT=3306
DB_DATABASE=your_database
DB_USERNAME=your_user
DB_PASSWORD=your_password

# Cache & Session
CACHE_DRIVER=file
SESSION_DRIVER=file
QUEUE_CONNECTION=database

# OpenAI
OPENAI_API_KEY=sk-proj-xxx
OPENAI_ASSISTANT_ID=asst_xxx

# MongoDB Atlas (opzionale)
MONGODB_URI=mongodb+srv://...
MONGODB_DATABASE=your_mongo_db
```

---

## 5. Avvio Container

### Via SSH (RACCOMANDATO)

```bash
docker run -d \
  --name avatar-3d-v1 \
  -p 8080:80 \
  -e PUID=10050 \
  -e PGID=1003 \
  -v /var/www/vhosts/demo-avatar.trentaduebit.it/.env.prod:/var/www/html/.env:ro \
  -v /var/www/vhosts/demo-avatar.trentaduebit.it/storage:/var/www/html/storage \
  --restart unless-stopped \
  avatar-3d-v1:latest
```

**IMPORTANTE**:
- Sostituisci `PUID` e `PGID` con i valori del tuo utente Plesk (da `id username`)!
- L'entrypoint cambierà l'utente www-data per matchare questi UID/GID

### Via Plesk Docker Extension

Se usi l'interfaccia Plesk Docker:
1. Aggiungi variabili ambiente: `PUID=10050`, `PGID=1003`
2. Configura i volume mapping
3. **IMPORTANTE**: Dopo aver aggiunto le variabili, devi **ricreare** il container (stop + remove + create), non solo restart!

### Verifica
```bash
# Controlla che www-data abbia UID/GID corretto
docker exec avatar-3d-v1 id www-data
# DEVE mostrare: uid=10050(www-data) gid=1003(www-data)
# Se mostra uid=82, le variabili non sono state applicate!

# Controlla logs
docker logs -f avatar-3d-v1

# Verifica .env montato
docker exec avatar-3d-v1 cat /var/www/html/.env
```

---

## 6. Migrazioni Database

### 6.1 Opzione A: Migrazioni Automatiche all'avvio

Aggiungi la variabile `RUN_MIGRATIONS=true` al comando docker run:

```bash
docker run -d \
  --name avatar-3d-v1 \
  -p 8080:80 \
  -e PUID=10050 \
  -e PGID=1003 \
  -e RUN_MIGRATIONS=true \
  -v /var/www/vhosts/demo-avatar.trentaduebit.it/.env.prod:/var/www/html/.env:ro \
  -v /var/www/vhosts/demo-avatar.trentaduebit.it/storage:/var/www/html/storage \
  --restart unless-stopped \
  avatar-3d-v1:latest
```

**Nota**: L'entrypoint eseguirà automaticamente `php artisan migrate --force` ad ogni avvio.

### 6.2 Opzione B: Migrazioni Manuali

Se preferisci controllare manualmente le migrazioni:

```bash
# Verifica stato migrazioni
docker exec avatar-3d-v1 php artisan migrate:status

# Esegui migrazioni
docker exec avatar-3d-v1 php artisan migrate --force

# Rollback se necessario
docker exec avatar-3d-v1 php artisan migrate:rollback
```

### 6.3 Operazioni Automatiche dell'Entrypoint

All'avvio del container, l'entrypoint esegue automaticamente:

1. **Cambio UID/GID www-data** per matchare PUID/PGID
2. **Creazione directory storage** (cache, sessions, views, logs)
3. **Storage link** (`public/storage` → `storage/app/public`)
4. **Database SQLite WebScraper** (crea e migra se non esiste)
5. **Cache configurazioni** (config, routes, views, events)
6. **Migrazioni** (solo se `RUN_MIGRATIONS=true`)

---

## 7. Configurazione HTTPS

### 6.1 SSL Certificate
In Plesk: **Domains → demo-avatar.trentaduebit.it → SSL/TLS Certificates → Let's Encrypt**

### 6.2 Configurazione Nginx

**NOTA**: Non usare "Additional nginx directives" in Plesk per il proxy - dà errore!

Il container ha già nginx configurato internamente (`docker-prod/nginx.conf`) con:
- Passaggio headers `X-Forwarded-Proto`, `X-Forwarded-For`, `X-Real-IP` a PHP-FPM
- Timeouts per OpenAI API (300s)
- Buffer settings ottimizzati

In Plesk devi solo:
1. Puntare il dominio alla porta del container (8080)
2. Oppure usare **Docker Proxy Rules** se disponibile nell'estensione Docker

### 6.3 Configurazione Laravel per HTTPS

Il container è già configurato per gestire HTTPS dietro reverse proxy:

- **TrustProxies middleware**: `$proxies = '*'` (accetta tutti i proxy)
- **AppServiceProvider**: `URL::forceScheme('https')` in produzione
- **Nginx del container**: passa headers `X-Forwarded-Proto`, `X-Forwarded-For`, `X-Real-IP`

---

## 7. Troubleshooting

### Permission Denied su file in src/Modules

**Sintomo**:
```
include(/var/www/html/src/Modules/WebScraper/...): Failed to open stream: Permission denied
```

**Causa**: Mismatch UID/GID tra container (www-data=82) e volumi Plesk (es. 10050:1003).

**Verifica**:
```bash
docker exec avatar-3d-v1 id www-data
```

Se mostra `uid=82`, le variabili `PUID`/`PGID` non sono state applicate!

**Soluzione**:
1. Ricrea il container con `PUID` e `PGID` corretti
2. Oppure fix manuale (temporaneo):
```bash
docker exec avatar-3d-v1 sh -c "
  chown -R www-data:www-data /var/www/html/src/Modules
  find /var/www/html/src/Modules -type d -exec chmod 755 {} \;
  find /var/www/html/src/Modules -type f -exec chmod 644 {} \;
"
```

### Mixed Content (HTTP invece di HTTPS)

**Causa**: Laravel non rileva HTTPS dietro proxy.

**Verifica**:
1. TrustProxies middleware configurato con `$proxies = '*'`
2. AppServiceProvider ha `URL::forceScheme('https')`
3. Nginx del container passa gli headers X-Forwarded-*

### APP_ENV not set

**Causa**: Volume .env non montato correttamente.

**Verifica**:
```bash
docker exec avatar-3d-v1 ls -la /var/www/html/.env
docker exec avatar-3d-v1 cat /var/www/html/.env
```

### Laravel Queue workers crashano

**Sintomo nei logs**:
```
WARN exited: laravel-queue_00 (exit status 1; not expected)
INFO gave up: laravel-queue_00 entered FATAL state
```

**Causa**: Database non raggiungibile o non configurato.

**Verifica**:
```bash
docker exec avatar-3d-v1 php artisan tinker --execute="DB::connection()->getPdo(); echo 'OK';"
```

### exec format error

**Causa**: Immagine buildata per architettura sbagliata (ARM64 invece di AMD64).

**Soluzione**: Rebuild con `--platform linux/amd64` (già incluso in build.sh).

### Database connection refused

1. Verifica bind-address MySQL sia `0.0.0.0`
2. Verifica firewall Plesk permetta 3306 da 172.17.0.0/16
3. Verifica permessi MySQL per utente da `172.17.%`

---

## 8. Comandi Utili

### Gestione container
```bash
# Logs
docker logs -f avatar-3d-v1

# Shell nel container
docker exec -it avatar-3d-v1 sh

# Restart
docker restart avatar-3d-v1

# Stop e rimuovi
docker stop avatar-3d-v1 && docker rm avatar-3d-v1
```

### Artisan commands
```bash
docker exec avatar-3d-v1 php artisan migrate:status
docker exec avatar-3d-v1 php artisan optimize:clear
docker exec avatar-3d-v1 php artisan queue:restart
docker exec avatar-3d-v1 php artisan config:cache
```

### Debug
```bash
# Verifica permessi
docker exec avatar-3d-v1 ls -la /var/www/html/src/Modules/

# Verifica PHP-FPM user
docker exec avatar-3d-v1 ps aux | grep php-fpm

# Verifica connessione DB
docker exec avatar-3d-v1 php artisan tinker --execute="DB::connection()->getPdo(); echo 'OK';"
```

---

## 9. Aggiornamenti

Per aggiornare l'applicazione:

```bash
# 1. Build nuova immagine (sul Mac)
./docker-prod/build.sh latest --save

# 2. Upload e import (sul server)
scp avatar-3d-v1-latest.tar.gz user@server:/tmp/
ssh user@server
docker load -i /tmp/avatar-3d-v1-latest.tar.gz

# 3. Ricrea container
docker stop avatar-3d-v1
docker rm avatar-3d-v1

# 4. Avvia con stessi parametri del punto 5
docker run -d \
  --name avatar-3d-v1 \
  -p 8080:80 \
  -e PUID=10050 \
  -e PGID=1003 \
  -v /var/www/vhosts/demo-avatar.trentaduebit.it/.env.prod:/var/www/html/.env:ro \
  -v /var/www/vhosts/demo-avatar.trentaduebit.it/storage:/var/www/html/storage \
  --restart unless-stopped \
  avatar-3d-v1:latest
```

---

## 10. Checklist Deploy

- [ ] Build immagine con `./docker-prod/build.sh latest --save`
- [ ] Upload tar.gz su Plesk
- [ ] Import con `docker load`
- [ ] Configurare bind-address MySQL a `0.0.0.0`
- [ ] Configurare firewall Plesk per porta 3306 da 172.17.0.0/16
- [ ] Creare utente MySQL con permessi da `172.17.%`
- [ ] Creare file .env.prod con configurazione completa
- [ ] Creare directory storage con permessi corretti
- [ ] Trovare UID/GID utente Plesk con `id username`
- [ ] Avviare container con `PUID` e `PGID` corretti
- [ ] Verificare `docker exec avatar-3d-v1 id www-data` mostra UID/GID Plesk
- [ ] Configurare SSL Let's Encrypt
- [ ] Configurare reverse proxy nginx
- [ ] Test finale su https://demo-avatar.trentaduebit.it

---

*Ultimo aggiornamento: 2025-11-21*
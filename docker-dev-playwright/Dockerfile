# Usa l'immagine ufficiale di Nginx
FROM nginx:stable-alpine

# Installa solo certificati CA se necessario
RUN apk add --no-cache ca-certificates

# Copia i certificati SSL
COPY certs/avatar-3d-v1.local.pem /etc/ssl/certs/
COPY certs/avatar-3d-v1.local-key.pem /etc/ssl/certs/

# Copia il file di configurazione di Nginx
COPY nginx.conf /etc/nginx/nginx.conf

# Espone le porte 80 e 443
EXPOSE 80 443
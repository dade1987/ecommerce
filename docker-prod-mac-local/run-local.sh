#!/bin/bash
# =============================================================================
# Run Avatar 3D v1 Production Container - Local Test
# =============================================================================

set -e

CONTAINER_NAME="avatar-3d-v1-local-test"
IMAGE_TAG="latest"
IMAGE_BASE="avatar-3d-v1-local"
NETWORK="docker-dev-playwright_avatar-3d-v1"
PORT="8081"
ENV_FILE="$(dirname "$0")/../.env.prod.local"

# Check for --playwright flag
USE_PLAYWRIGHT=false
for arg in "$@"; do
    case $arg in
        --playwright)
            USE_PLAYWRIGHT=true
            ;;
    esac
done

if [ "$USE_PLAYWRIGHT" = true ]; then
    IMAGE_NAME="${IMAGE_BASE}:${IMAGE_TAG}-playwright"
else
    IMAGE_NAME="${IMAGE_BASE}:${IMAGE_TAG}"
fi

# Verifica che il file env esista
if [ ! -f "$ENV_FILE" ]; then
    echo "ERROR: File $ENV_FILE non trovato!"
    exit 1
fi

# Rimuovi container esistente
echo "Removing old container if exists..."
docker rm -f "$CONTAINER_NAME" 2>/dev/null || true

# Avvia nuovo container
echo "Starting container..."
docker run -d \
  --name "$CONTAINER_NAME" \
  --network "$NETWORK" \
  -p "$PORT:80" \
  --env-file "$ENV_FILE" \
  "$IMAGE_NAME"

echo ""
echo "Container started!"
echo "URL: http://localhost:$PORT"
echo ""
echo "Useful commands:"
echo "  docker logs -f $CONTAINER_NAME"
echo "  docker exec -it $CONTAINER_NAME bash"
echo "  docker stop $CONTAINER_NAME"
echo ""
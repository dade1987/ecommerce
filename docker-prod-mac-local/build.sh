#!/bin/bash
# =============================================================================
# Build Script - Avatar 3D v1 Production Image
# =============================================================================

set -e

# Configurazione
IMAGE_NAME="avatar-3d-v1"
IMAGE_TAG="${1:-latest}"

# Usa Dockerfile con Playwright se --playwright è specificato
USE_PLAYWRIGHT=false
DOCKERFILE="docker-prod-mac-local/Dockerfile"

for arg in "$@"; do
    case $arg in
        --playwright)
            USE_PLAYWRIGHT=true
            DOCKERFILE="docker-prod-mac-local/Dockerfile.playwright"
            shift
            ;;
        --save)
            SAVE_TAR=true
            shift
            ;;
    esac
done

if [ "$USE_PLAYWRIGHT" = true ]; then
    FULL_IMAGE_NAME="${IMAGE_NAME}:${IMAGE_TAG}-playwright"
else
    FULL_IMAGE_NAME="${IMAGE_NAME}:${IMAGE_TAG}"
fi

echo "=========================================="
echo "Building ${FULL_IMAGE_NAME}"
if [ "$USE_PLAYWRIGHT" = true ]; then
    echo "(with Playwright/Chromium for browser scraping)"
else
    echo "(lightweight version without Playwright)"
fi
echo "=========================================="

# Vai alla directory ecommerce
cd "$(dirname "$0")/.."

# Verifica che siamo nella directory corretta
if [ ! -f "artisan" ]; then
    echo "ERROR: artisan file not found. Are you in the ecommerce directory?"
    exit 1
fi

# Verifica che il Dockerfile esista
if [ ! -f "$DOCKERFILE" ]; then
    echo "ERROR: Dockerfile not found at $DOCKERFILE"
    exit 1
fi

# Build dell'immagine
echo ""
echo "Step 1: Building Docker image for Mac (native ARM64)..."
docker build \
    -f "${DOCKERFILE}" \
    -t "${FULL_IMAGE_NAME}" \
    --progress=plain \
    .

# Mostra dimensione immagine
echo ""
echo "Step 2: Image built successfully!"
docker images "${IMAGE_NAME}" --format "table {{.Repository}}\t{{.Tag}}\t{{.Size}}"

# Opzionale: salva come tar per upload manuale
if [ "$SAVE_TAR" = true ]; then
    echo ""
    echo "Step 3: Saving image as tar file..."
    TAR_FILE="${IMAGE_NAME}-${IMAGE_TAG}.tar"
    if [ "$USE_PLAYWRIGHT" = true ]; then
        TAR_FILE="${IMAGE_NAME}-${IMAGE_TAG}-playwright.tar"
    fi
    docker save -o "${TAR_FILE}" "${FULL_IMAGE_NAME}"

    # Comprimi
    gzip -f "${TAR_FILE}"
    echo "Saved as: ${TAR_FILE}.gz"
    ls -lh "${TAR_FILE}.gz"
fi

echo ""
echo "=========================================="
echo "Build completed!"
echo "=========================================="
echo ""
echo "Next steps:"
echo "1. Test locally:"
echo "   docker run -d -p 8080:80 --name avatar-test ${FULL_IMAGE_NAME}"
echo ""
echo "2. Push to registry (if using):"
echo "   docker tag ${FULL_IMAGE_NAME} your-registry.com/${FULL_IMAGE_NAME}"
echo "   docker push your-registry.com/${FULL_IMAGE_NAME}"
echo ""
echo "3. Export for Plesk upload:"
echo "   ./build.sh ${IMAGE_TAG} --save"
echo ""
echo "Available build options:"
echo "  ./build.sh latest              # Lightweight (~1.6GB)"
echo "  ./build.sh latest --playwright # With Playwright (~2.6GB)"
echo "  ./build.sh latest --save       # Save as .tar.gz"
echo ""
#!/bin/bash
# =============================================================================
# Build Script - Avatar 3D v1 Production Image
# =============================================================================

set -e

# Configurazione
IMAGE_NAME="avatar-3d-v1"
IMAGE_TAG="${1:-latest}"
FULL_IMAGE_NAME="${IMAGE_NAME}:${IMAGE_TAG}"

echo "=========================================="
echo "Building ${FULL_IMAGE_NAME}"
echo "=========================================="

# Vai alla directory ecommerce
cd "$(dirname "$0")/.."

# Verifica che siamo nella directory corretta
if [ ! -f "artisan" ]; then
    echo "ERROR: artisan file not found. Are you in the ecommerce directory?"
    exit 1
fi

# Build dell'immagine
echo ""
echo "Step 1: Building Docker image..."
docker build \
    -f docker-prod/Dockerfile \
    -t "${FULL_IMAGE_NAME}" \
    --progress=plain \
    .

# Mostra dimensione immagine
echo ""
echo "Step 2: Image built successfully!"
docker images "${IMAGE_NAME}" --format "table {{.Repository}}\t{{.Tag}}\t{{.Size}}"

# Opzionale: salva come tar per upload manuale
if [ "$2" = "--save" ]; then
    echo ""
    echo "Step 3: Saving image as tar file..."
    TAR_FILE="${IMAGE_NAME}-${IMAGE_TAG}.tar"
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
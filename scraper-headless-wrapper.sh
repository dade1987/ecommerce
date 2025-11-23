#!/bin/bash
# Wrapper script for Playwright scraper with proper environment variables

# Load environment variables from .env.playwright
if [ -f /var/www/html/.env.playwright ]; then
    set -a
    source /var/www/html/.env.playwright
    set +a
fi

# Fallback if .env.playwright doesn't exist
export PLAYWRIGHT_BROWSERS_PATH=${PLAYWRIGHT_BROWSERS_PATH:-/var/www/.cache/ms-playwright}

# Find chromium directory dynamically (handles different versions)
CHROMIUM_DIR=$(ls -d "$PLAYWRIGHT_BROWSERS_PATH"/chromium-* 2>/dev/null | head -1)

if [ -z "$CHROMIUM_DIR" ]; then
    echo "ERROR: No chromium directory found in $PLAYWRIGHT_BROWSERS_PATH" >&2
    ls -la "$PLAYWRIGHT_BROWSERS_PATH/" >&2 2>/dev/null || echo "Directory does not exist" >&2
    exit 1
fi

node /var/www/html/scraper-headless.js "$1"
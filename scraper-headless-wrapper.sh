#!/bin/bash
# Wrapper script for Playwright scraper with proper environment variables

# Debug: print user info
echo "Running as user: $(whoami)" >&2
echo "UID: $(id -u)" >&2
echo "HOME: $HOME" >&2

# Load environment variables from .env.playwright
if [ -f /var/www/html/.env.playwright ]; then
    set -a
    source /var/www/html/.env.playwright
    set +a
fi

# Fallback if .env.playwright doesn't exist
export PLAYWRIGHT_BROWSERS_PATH=${PLAYWRIGHT_BROWSERS_PATH:-/var/www/.cache/ms-playwright}

# Disable crashpad handler completely to avoid Docker issues
export CHROME_DEVEL_SANDBOX=/usr/lib/chromium-browser/chrome-sandbox
export CHROME_CRASHPAD_HANDLER_PATH=""

# Debug: print the path being used (will appear in error logs)
echo "Using PLAYWRIGHT_BROWSERS_PATH: $PLAYWRIGHT_BROWSERS_PATH" >&2

# Check if file exists before running
if [ -f "$PLAYWRIGHT_BROWSERS_PATH/chromium-1194/chrome-linux/chrome" ]; then
    echo "Chrome executable found at: $PLAYWRIGHT_BROWSERS_PATH/chromium-1194/chrome-linux/chrome" >&2
else
    echo "ERROR: Chrome executable NOT found at: $PLAYWRIGHT_BROWSERS_PATH/chromium-1194/chrome-linux/chrome" >&2
    ls -la "$PLAYWRIGHT_BROWSERS_PATH/" >&2
fi

# Run the scraper
# Playwright will automatically find the browser using PLAYWRIGHT_BROWSERS_PATH
node /var/www/html/scraper-headless.js "$1"
#!/usr/bin/env node

/**
 * Headless Browser Scraper using Playwright
 *
 * Usage: node scraper-headless.js <URL>
 * Output: JSON to stdout with scraped HTML content
 */

import { chromium } from 'playwright';

// Get URL from command line argument
const url = process.argv[2];

if (!url) {
    console.error(JSON.stringify({
        error: 'No URL provided. Usage: node scraper-headless.js <URL>'
    }));
    process.exit(1);
}

(async () => {
    let browser;

    try {
        // Explicitly set the browser path to use the full chromium browser
        const browserPath = process.env.PLAYWRIGHT_BROWSERS_PATH || '/var/www/.cache/ms-playwright';
        const executablePath = `${browserPath}/chromium-1194/chrome-linux/chrome`;

        // Launch Chromium browser with anti-detection settings
        browser = await chromium.launch({
            headless: true,
            executablePath: executablePath,
            args: [
                '--no-sandbox',
                '--disable-setuid-sandbox',
                '--disable-dev-shm-usage',
                '--disable-blink-features=AutomationControlled',
                '--disable-features=IsolateOrigins,site-per-process'
            ]
        });

        const context = await browser.newContext({
            viewport: { width: 1920, height: 1080 },
            userAgent: 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36',
            extraHTTPHeaders: {
                'Accept-Language': 'it-IT,it;q=0.9,en-US;q=0.8,en;q=0.7',
                'Accept': 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,*/*;q=0.8',
                'Cache-Control': 'max-age=0'
            },
            // Add more realistic browser fingerprint
            locale: 'it-IT',
            timezoneId: 'Europe/Rome',
            permissions: [],
            javaScriptEnabled: true
        });

        const page = await context.newPage();

        // Navigate to URL with extended timeout (for POW captcha)
        await page.goto(url, {
            waitUntil: 'domcontentloaded', // Don't wait for networkidle initially
            timeout: 90000 // 90 seconds
        });

        // Wait for POW captcha to complete (if present)
        // The captcha will automatically solve and redirect
        try {
            // Wait up to 20 seconds for either the captcha to complete OR the real content to load
            await page.waitForFunction(
                () => {
                    const title = document.title;
                    // If title is NOT "Robot Challenge", we're on the real page
                    return !title.includes('Robot Challenge') && !title.includes('Challenge');
                },
                { timeout: 20000 }
            );
        } catch (e) {
            // Timeout waiting for captcha, just continue
            console.error('Captcha timeout, continuing anyway');
        }

        // Final wait for content to stabilize
        await page.waitForTimeout(2000);

        // Wait for network to be idle now
        try {
            await page.waitForLoadState('networkidle', { timeout: 10000 });
        } catch (e) {
            // Network might not go idle, that's OK
        }

        // Get the full HTML content
        const html = await page.content();

        // Get page title
        const title = await page.title();

        // Get final URL (in case of redirects)
        const finalUrl = page.url();

        // Return JSON result
        const result = {
            success: true,
            url: url,
            final_url: finalUrl,
            title: title,
            html: html,
            html_length: html.length,
            timestamp: new Date().toISOString()
        };

        console.log(JSON.stringify(result));

    } catch (error) {
        // Return error as JSON
        console.error(JSON.stringify({
            success: false,
            error: error.message,
            stack: error.stack,
            url: url
        }));
        process.exit(1);

    } finally {
        if (browser) {
            await browser.close();
        }
    }
})();
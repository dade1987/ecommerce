#!/usr/bin/env node

// Debug script to see what Playwright sees
import { chromium } from 'playwright';
import { execSync } from 'child_process';
import fs from 'fs';

console.log('=== Environment Debug ===');
console.log('USER:', process.env.USER);
console.log('HOME:', process.env.HOME);
console.log('PLAYWRIGHT_BROWSERS_PATH:', process.env.PLAYWRIGHT_BROWSERS_PATH);
console.log('Current working directory:', process.cwd());

const browserPath = process.env.PLAYWRIGHT_BROWSERS_PATH || '/root/.cache/ms-playwright';
const executablePath = `${browserPath}/chromium-1194/chrome-linux/chrome`;

console.log('\n=== Checking executable path ===');
console.log('Constructed path:', executablePath);
console.log('File exists?', fs.existsSync(executablePath));

if (fs.existsSync(executablePath)) {
    const stats = fs.statSync(executablePath);
    console.log('File size:', stats.size);
    console.log('Is executable?', (stats.mode & fs.constants.S_IXUSR) !== 0);

    try {
        const output = execSync(`ls -la ${executablePath}`).toString();
        console.log('ls -la output:', output);
    } catch (e) {
        console.log('ls error:', e.message);
    }
}

console.log('\n=== Trying to launch browser ===');
try {
    const browser = await chromium.launch({
        headless: true,
        executablePath: executablePath,
        args: ['--no-sandbox', '--disable-setuid-sandbox']
    });
    console.log('SUCCESS! Browser launched');
    await browser.close();
} catch (error) {
    console.log('FAILED:', error.message);
    console.log('Stack:', error.stack);
}
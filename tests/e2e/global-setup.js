/**
 * Global setup for Playwright tests
 * Logs in once and saves auth state for reuse across test files.
 */

import { chromium } from '@playwright/test';
import path from 'path';
import { fileURLToPath } from 'url';

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const AUTH_STATE_PATH = path.resolve(__dirname, '.auth/user.json');

async function globalSetup(config) {
    const baseURL = config.projects[0].use?.baseURL || 'http://expenses.local';

    const browser = await chromium.launch();
    const context = await browser.newContext();
    const page = await context.newPage();

    try {
        // Login as primary test user
        await page.goto(`${baseURL}/login`);
        await page.fill('#email', 'email4johnson@gmail.com');
        await page.fill('#password', '24AdaPlace');
        await page.click('button[type="submit"]');
        await page.waitForURL('**/dashboard', { timeout: 15000 });

        // Save signed-in state
        await context.storageState({ path: AUTH_STATE_PATH });

        console.log('Global setup: Auth state saved successfully');
    } catch (error) {
        console.error('Global setup: Failed to login -', error.message);
        // Don't throw - let individual tests handle auth
    }

    await browser.close();
}

export default globalSetup;

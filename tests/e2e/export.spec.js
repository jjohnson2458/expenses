// @ts-check
import { test, expect } from '@playwright/test';

test.use({ storageState: 'tests/e2e/.auth/user.json' });

test.describe('Export Features', () => {

    for (const [name, path] of [
        ['CSV', '/export/csv'],
        ['OFX', '/export/ofx'],
        ['QFX', '/export/qfx'],
        ['QBO', '/export/qbo'],
        ['QuickBooks IIF', '/export/quickbooks'],
        ['iCal', '/export/calendar'],
    ]) {
        test(`${name} export triggers download`, async ({ page }) => {
            await page.goto('/dashboard');
            const downloadPromise = page.waitForEvent('download');
            await page.evaluate((url) => {
                window.location.href = url;
            }, path);
            const download = await downloadPromise;
            expect(download.suggestedFilename()).toBeTruthy();
        });
    }

    test('sidebar shows export links', async ({ page }) => {
        await page.goto('/dashboard');

        await expect(page.locator('.sidebar a[href*="/export/csv"]')).toHaveCount(1);
        await expect(page.locator('.sidebar a[href*="/export/ofx"]')).toHaveCount(1);
        await expect(page.locator('.sidebar a[href*="/export/qfx"]')).toHaveCount(1);
        await expect(page.locator('.sidebar a[href*="/export/qbo"]')).toHaveCount(1);
        await expect(page.locator('.sidebar a[href*="/export/quickbooks"]')).toHaveCount(1);
        await expect(page.locator('.sidebar a[href*="/export/calendar"]')).toHaveCount(1);
    });

    test('export with date filters works', async ({ page }) => {
        await page.goto('/dashboard');
        const downloadPromise = page.waitForEvent('download');
        await page.evaluate(() => {
            window.location.href = '/export/csv?from=2026-03-01&to=2026-03-31';
        });
        const download = await downloadPromise;
        expect(download.suggestedFilename()).toMatch(/\.csv$/i);
    });

    test('export requires authentication', async ({ browser }) => {
        const context = await browser.newContext({
            baseURL: 'http://expenses.local',
            storageState: { cookies: [], origins: [] },
        });
        const page = await context.newPage();

        await page.goto('/dashboard');
        await expect(page).toHaveURL(/\/login/);

        await context.close();
    });

});

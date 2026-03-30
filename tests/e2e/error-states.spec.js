// @ts-check
import { test, expect } from '@playwright/test';

test.use({ storageState: 'tests/e2e/.auth/user.json' });

test.describe('Error States', () => {

    // --- Server Error Handling ---

    test('dashboard handles API failure gracefully', async ({ page }) => {
        await page.route('**/dashboard', (route) => {
            if (route.request().resourceType() === 'xhr' || route.request().resourceType() === 'fetch') {
                route.fulfill({ status: 500, body: 'Internal Server Error' });
            } else {
                route.continue();
            }
        });

        await page.goto('/dashboard');

        // Should NOT show a raw stack trace
        const bodyText = await page.locator('body').textContent();
        expect(bodyText).not.toContain('Stack trace');
        expect(bodyText).not.toContain('Whoops');
    });

    test('expense creation handles server error gracefully', async ({ page }) => {
        await page.goto('/expenses/create');
        await page.fill('#description', 'Error Test Expense');
        await page.fill('#amount', '25.00');
        await page.fill('#date', '2026-03-22');

        await page.route('**/expenses', (route) => {
            if (route.request().method() === 'POST') {
                route.fulfill({
                    status: 500,
                    contentType: 'text/html',
                    body: '<html><body>Internal Server Error</body></html>',
                });
            } else {
                route.continue();
            }
        });

        await page.click('button[type="submit"]');
        await page.waitForLoadState('networkidle');

        const bodyText = await page.locator('body').textContent();
        expect(bodyText).not.toContain('Stack trace');
    });

    test('receipt scan handles API timeout gracefully', async ({ page }) => {
        await page.goto('/expenses/create');

        await page.route('**/expenses/scan', (route) => {
            route.fulfill({
                status: 504,
                contentType: 'application/json',
                body: JSON.stringify({ success: false, message: 'Gateway Timeout' }),
            });
        });

        const fileInput = page.locator('#scanInput');
        if (await fileInput.count() > 0) {
            await fileInput.setInputFiles({
                name: 'test-receipt.jpg',
                mimeType: 'image/jpeg',
                buffer: Buffer.from('fake-image-data'),
            });

            await page.waitForTimeout(2000);

            const bodyText = await page.locator('body').textContent();
            expect(bodyText).not.toContain('Stack trace');
            expect(bodyText).not.toContain('Whoops');
        }
    });

    test('billing page handles Stripe unavailability', async ({ page }) => {
        await page.route('**/billing/**', (route) => {
            if (route.request().method() === 'POST') {
                route.fulfill({
                    status: 503,
                    contentType: 'application/json',
                    body: JSON.stringify({ error: 'Service Unavailable' }),
                });
            } else {
                route.continue();
            }
        });

        await page.goto('/billing');

        const bodyText = await page.locator('body').textContent();
        expect(bodyText).not.toContain('Stack trace');
        expect(bodyText).not.toContain('Whoops');
    });

    test('import handles corrupted file gracefully', async ({ page }) => {
        await page.goto('/import');

        const fileInput = page.locator('#import_file');
        await fileInput.setInputFiles({
            name: 'corrupted.csv',
            mimeType: 'text/csv',
            buffer: Buffer.from('not,real,data\n\x00\x01\x02'),
        });

        await page.click('button:has-text("Import")');
        await page.waitForLoadState('networkidle');

        // Should handle gracefully — no raw PHP stack trace
        const bodyText = await page.locator('body').textContent();
        expect(bodyText).not.toContain('Stack trace');
    });

    test('export handles empty data gracefully', async ({ page }) => {
        await page.goto('/dashboard');
        const downloadPromise = page.waitForEvent('download');
        await page.evaluate(() => {
            window.location.href = '/export/csv?from=1900-01-01&to=1900-01-31';
        });
        const download = await downloadPromise;
        expect(download.suggestedFilename()).toBeTruthy();
    });

    // --- 404 Handling ---

    test('accessing nonexistent expense shows error', async ({ page }) => {
        const response = await page.goto('/expenses/999999/edit');

        const status = response.status();
        expect([404, 302, 500]).toContain(status);

        if (status === 200 || status === 500) {
            const bodyText = await page.locator('body').textContent();
            expect(bodyText).not.toContain('Stack trace');
        }
    });

    test('accessing nonexistent report shows error', async ({ page }) => {
        const response = await page.goto('/reports/999999');

        const status = response.status();
        expect([404, 302, 500]).toContain(status);
    });

    test('accessing nonexistent category edit shows error', async ({ page }) => {
        const response = await page.goto('/categories/999999/edit');

        const status = response.status();
        expect([404, 302, 500]).toContain(status);
    });

    test('404 page for completely invalid route', async ({ page }) => {
        const response = await page.goto('/this-route-does-not-exist');

        expect(response.status()).toBe(404);
    });

    // --- Network Failure Simulation ---

    test('page handles CSS/JS asset failure', async ({ page }) => {
        await page.route('**/*.css', (route) => route.abort());

        await page.goto('/dashboard');

        await expect(page.locator('body')).toContainText(/dashboard|Dashboard/i);
    });

    test('page handles image load failure', async ({ page }) => {
        await page.route('**/*.{png,jpg,jpeg,gif,webp,svg}', (route) => route.abort());

        await page.goto('/dashboard');

        await expect(page.locator('body')).toContainText(/dashboard|Dashboard/i);
    });

});

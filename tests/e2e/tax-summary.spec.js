// @ts-check
import { test, expect } from '@playwright/test';

test.use({ storageState: 'tests/e2e/.auth/user.json' });

test.describe('Tax Summary', () => {

    test('tax summary page loads with correct title', async ({ page }) => {
        await page.goto('/tax/summary');

        await expect(page).toHaveTitle(/Tax Summary/);
    });

    test('tax summary heading is visible', async ({ page }) => {
        await page.goto('/tax/summary');

        await expect(page.locator('h4.fw-bold').filter({ hasText: /Tax Summary/ })).toBeVisible();
    });

    test('tax summary shows financial summary cards', async ({ page }) => {
        await page.goto('/tax/summary');

        await expect(page.locator('body')).toContainText('Gross Income');
        await expect(page.locator('body')).toContainText('Total Expenses');
        await expect(page.locator('body')).toContainText('Net Profit');
        await expect(page.locator('body')).toContainText('SE Tax');
    });

    test('tax summary shows Schedule C Line Items table', async ({ page }) => {
        await page.goto('/tax/summary');

        await expect(page.locator('body')).toContainText('Schedule C Line Items');
        await expect(page.locator('table').first()).toBeVisible();
    });

    test('tax summary shows Quarterly Estimates section', async ({ page }) => {
        await page.goto('/tax/summary');

        await expect(page.locator('body')).toContainText('Quarterly Estimates');
    });

    test('tax summary navigation from sidebar', async ({ page }) => {
        await page.goto('/dashboard');

        // Open sidebar on mobile if needed
        const toggle = page.locator('#sidebarToggle');
        if (await toggle.isVisible()) {
            await toggle.click();
            await page.locator('#sidebar.show').waitFor({ timeout: 3000 });
        }

        const taxLink = page.locator('#sidebar .nav-link').filter({ hasText: /Tax Summary/i }).first();
        const hasTaxLink = await taxLink.count();

        if (hasTaxLink > 0) {
            await taxLink.click();
            expect(page.url()).toContain('/tax');
        }
    });

});

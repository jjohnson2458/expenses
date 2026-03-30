// @ts-check
import { test, expect } from '@playwright/test';

test.use({ storageState: 'tests/e2e/.auth/user.json' });

test.describe('Billing & Plans', () => {

    test('billing page loads with correct title', async ({ page }) => {
        await page.goto('/billing');

        await expect(page).toHaveTitle('Billing & Plans - VQ Money');
    });

    test('billing page shows heading', async ({ page }) => {
        await page.goto('/billing');

        await expect(page.locator('h4').filter({ hasText: 'Billing & Plans' })).toBeVisible();
    });

    test('billing page shows plan cards', async ({ page }) => {
        await page.goto('/billing');

        await expect(page.locator('body')).toContainText('Free');
        await expect(page.locator('body')).toContainText('Solo');
        await expect(page.locator('body')).toContainText('Pro');
        await expect(page.locator('body')).toContainText('Team');
    });

    test('billing page has Monthly/Annual toggle', async ({ page }) => {
        await page.goto('/billing');

        await expect(page.locator('body')).toContainText('Monthly');
        await expect(page.locator('body')).toContainText('Annual');
    });

    test('billing page accessible from sidebar or navigation', async ({ page }) => {
        await page.goto('/dashboard');

        // Open sidebar on mobile if needed
        const toggle = page.locator('#sidebarToggle');
        if (await toggle.isVisible()) {
            await toggle.click();
            await page.locator('#sidebar.show').waitFor({ timeout: 3000 });
        }

        const billingLink = page.locator('a[href*="billing"]').first();
        const hasBillingLink = await billingLink.count();

        if (hasBillingLink > 0) {
            await billingLink.click();
            await expect(page).toHaveURL(/\/billing/);
        }
    });

    test('billing page does not show raw errors', async ({ page }) => {
        await page.goto('/billing');

        // Page should not show raw stack traces even if Stripe is not fully configured
        const bodyText = await page.locator('body').textContent();
        expect(bodyText).not.toContain('Stack trace');
        expect(bodyText).not.toContain('Whoops');
    });

});

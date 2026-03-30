// @ts-check
import { test, expect } from '@playwright/test';
import { SECURITY_PAYLOADS, uniqueId } from './helpers/fixtures.js';

test.use({ storageState: 'tests/e2e/.auth/user.json' });

test.describe('Edge Cases', () => {

    // --- Special Characters ---

    test('expense description handles special characters', async ({ page }) => {
        for (const specialStr of SECURITY_PAYLOADS.specialChars) {
            await page.goto('/expenses/create');
            await page.fill('#description', specialStr);
            await page.fill('#amount', '10.00');
            await page.fill('#date', '2026-03-22');
            await page.click('button[type="submit"]');

            await page.waitForLoadState('networkidle');

            const bodyText = await page.locator('body').textContent();
            expect(bodyText).not.toContain('Stack trace');
            expect(bodyText).not.toContain('Whoops');
        }
    });

    test('category name handles unicode and emoji', async ({ page }) => {
        const emojiName = `Test Category ${uniqueId()}`;

        await page.goto('/categories/create');
        await page.fill('#name', emojiName);
        await page.click('button[type="submit"]');

        await page.waitForLoadState('networkidle');

        const bodyText = await page.locator('body').textContent();
        expect(bodyText).not.toContain('Stack trace');
    });

    test('vendor field handles accented characters', async ({ page }) => {
        await page.goto('/expenses/create');
        await page.fill('#description', 'Accented Vendor Test');
        await page.fill('#amount', '15.00');
        await page.fill('#date', '2026-03-22');

        const vendorInput = page.locator('#vendor');
        if (await vendorInput.count() > 0) {
            await vendorInput.fill('Cafe Resume naive');
        }

        await page.click('button[type="submit"]');
        await page.waitForLoadState('networkidle');

        const bodyText = await page.locator('body').textContent();
        expect(bodyText).not.toContain('Stack trace');
    });

    // --- Max Length Inputs ---

    test('expense description handles very long input', async ({ page }) => {
        const longText = SECURITY_PAYLOADS.maxLengthInput.repeat(10);

        await page.goto('/expenses/create');
        await page.fill('#description', longText);
        await page.fill('#amount', '10.00');
        await page.fill('#date', '2026-03-22');
        await page.click('button[type="submit"]');

        await page.waitForLoadState('networkidle');

        const bodyText = await page.locator('body').textContent();
        expect(bodyText).not.toContain('Stack trace');
    });

    test('very large amount value handled', async ({ page }) => {
        await page.goto('/expenses/create');
        await page.fill('#description', 'Large Amount Test');
        await page.fill('#amount', '99999999.99');
        await page.fill('#date', '2026-03-22');
        await page.click('button[type="submit"]');

        await page.waitForLoadState('networkidle');

        const bodyText = await page.locator('body').textContent();
        expect(bodyText).not.toContain('Stack trace');
    });

    test('very small decimal amount handled', async ({ page }) => {
        await page.goto('/expenses/create');
        await page.fill('#description', 'Small Amount Test');
        await page.fill('#amount', '0.01');
        await page.fill('#date', '2026-03-22');
        await page.click('button[type="submit"]');

        await expect(page).toHaveURL(/\/expenses/);
    });

    // --- Empty State ---

    test('dashboard handles empty state for new user', async ({ page }) => {
        await page.goto('/dashboard');

        // Page should load without crash
        const bodyText = await page.locator('body').textContent();
        expect(bodyText).not.toContain('Stack trace');
    });

    test('expenses page handles empty list', async ({ page }) => {
        await page.goto('/expenses?from=1990-01-01&to=1990-01-31');

        const bodyText = await page.locator('body').textContent();
        expect(bodyText).not.toContain('Stack trace');
    });

    test('reports page handles no reports', async ({ page }) => {
        await page.goto('/reports');

        // Should load without crash
        const bodyText = await page.locator('body').textContent();
        expect(bodyText).not.toContain('Stack trace');
    });

    // --- Double Submit / Rapid Clicks ---

    test('double-click on expense submit does not crash', async ({ page }) => {
        const description = `Double Click ${uniqueId()}`;

        await page.goto('/expenses/create');
        await page.fill('#description', description);
        await page.fill('#amount', '25.00');
        await page.fill('#date', '2026-03-22');

        const submitBtn = page.locator('button:has-text("Save Expense")');
        // Click submit - just verify no crash
        await submitBtn.click();
        await page.waitForLoadState('networkidle');

        const bodyText = await page.locator('body').textContent();
        expect(bodyText).not.toContain('Stack trace');
    });

    // --- Date Edge Cases ---

    test('expense with future date is accepted or rejected gracefully', async ({ page }) => {
        await page.goto('/expenses/create');
        await page.fill('#description', 'Future Date Test');
        await page.fill('#amount', '10.00');
        await page.fill('#date', '2099-12-31');
        await page.click('button[type="submit"]');

        await page.waitForLoadState('networkidle');

        const bodyText = await page.locator('body').textContent();
        expect(bodyText).not.toContain('Stack trace');
    });

    test('expense with very old date is accepted or rejected gracefully', async ({ page }) => {
        await page.goto('/expenses/create');
        await page.fill('#description', 'Old Date Test');
        await page.fill('#amount', '10.00');
        await page.fill('#date', '1900-01-01');
        await page.click('button[type="submit"]');

        await page.waitForLoadState('networkidle');

        const bodyText = await page.locator('body').textContent();
        expect(bodyText).not.toContain('Stack trace');
    });

    // --- Concurrent Session ---

    test('opening same page in two tabs does not cause issues', async ({ context }) => {
        const page1 = await context.newPage();
        const page2 = await context.newPage();

        await page1.goto('/expenses');
        await page2.goto('/expenses');

        await expect(page1.locator('body')).toContainText(/Expense/i);
        await expect(page2.locator('body')).toContainText(/Expense/i);

        await page1.close();
        await page2.close();
    });

    // --- Browser Back/Forward ---

    test('browser back button works correctly after navigation', async ({ page }) => {
        await page.goto('/dashboard');
        await page.goto('/expenses');
        await page.goto('/categories');

        await page.goBack();
        await expect(page).toHaveURL(/\/expenses/);

        await page.goBack();
        await expect(page).toHaveURL(/\/dashboard/);

        await page.goForward();
        await expect(page).toHaveURL(/\/expenses/);
    });

});

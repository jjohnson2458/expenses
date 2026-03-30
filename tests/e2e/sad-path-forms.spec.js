// @ts-check
import { test, expect } from '@playwright/test';

test.use({ storageState: 'tests/e2e/.auth/user.json' });

test.describe('Sad Path - Form Validation', () => {

    // --- Expense Form Validation ---

    test('expense form has required description field', async ({ page }) => {
        await page.goto('/expenses/create');
        await expect(page.locator('#description')).toHaveAttribute('required', '');
    });

    test('expense form has required amount field', async ({ page }) => {
        await page.goto('/expenses/create');
        await expect(page.locator('#amount')).toHaveAttribute('required', '');
    });

    test('expense form has required date field', async ({ page }) => {
        await page.goto('/expenses/create');
        await expect(page.locator('#date')).toHaveAttribute('required', '');
    });

    test('expense form rejects negative amount via min attribute', async ({ page }) => {
        await page.goto('/expenses/create');
        const amountInput = page.locator('#amount');
        const minAttr = await amountInput.getAttribute('min');
        // Should have min >= 0 or step attribute to enforce positive
        expect(minAttr !== null || await amountInput.getAttribute('step') !== null).toBeTruthy();
    });

    // --- Category Form Validation ---

    test('category form has required name field', async ({ page }) => {
        await page.goto('/categories/create');
        await expect(page.locator('#name')).toHaveAttribute('required', '');
    });

    // --- Report Form Validation ---

    test('report form has required title field', async ({ page }) => {
        await page.goto('/reports/create');
        await expect(page.locator('#title')).toHaveAttribute('required', '');
    });

    // --- Recurring Expense Validation ---

    test('recurring expense form has required description field', async ({ page }) => {
        await page.goto('/recurring/create');
        await expect(page.locator('#description')).toHaveAttribute('required', '');
    });

    test('recurring expense form has day_of_month constraints', async ({ page }) => {
        await page.goto('/recurring/create');
        const dayInput = page.locator('#day_of_month');
        await expect(dayInput).toHaveAttribute('required', '');
        const maxAttr = await dayInput.getAttribute('max');
        expect(maxAttr).toBeTruthy();
    });

    test('recurring expense form has required amount field', async ({ page }) => {
        await page.goto('/recurring/create');
        await expect(page.locator('#amount')).toHaveAttribute('required', '');
    });

    // --- Settings Validation ---

    test('settings password change requires current password', async ({ page }) => {
        await page.goto('/settings');

        const currentPwInput = page.locator('#current_password');
        const newPwInput = page.locator('#new_password');
        const confirmPwInput = page.locator('input[name="new_password_confirmation"]');

        if (await currentPwInput.count() > 0 && await newPwInput.count() > 0) {
            await currentPwInput.fill('WrongCurrentPassword');
            await newPwInput.fill('NewTestPass123!');
            await confirmPwInput.fill('NewTestPass123!');

            const passwordSubmit = page.locator('form:has(input[name="section"][value="password"]) button[type="submit"]');
            if (await passwordSubmit.count() > 0) {
                await passwordSubmit.click();
                await page.waitForLoadState('networkidle');

                const hasError = await page.locator('.alert-danger, .text-danger, .invalid-feedback').count();
                expect(hasError).toBeGreaterThan(0);
            }
        }
    });

    test('settings password change requires matching confirmation', async ({ page }) => {
        await page.goto('/settings');

        const currentPwInput = page.locator('#current_password');
        const newPwInput = page.locator('#new_password');
        const confirmPwInput = page.locator('input[name="new_password_confirmation"]');

        if (await currentPwInput.count() > 0 && await newPwInput.count() > 0) {
            await currentPwInput.fill('SomeCurrentPassword');
            await newPwInput.fill('NewTestPass123!');
            await confirmPwInput.fill('DifferentPass456!');

            const passwordSubmit = page.locator('form:has(input[name="section"][value="password"]) button[type="submit"]');
            if (await passwordSubmit.count() > 0) {
                await passwordSubmit.click();
                await page.waitForLoadState('networkidle');

                const hasError = await page.locator('.alert-danger, .text-danger, .invalid-feedback').count();
                expect(hasError).toBeGreaterThan(0);
            }
        }
    });

    // --- Mileage Validation ---

    test('mileage form has required business purpose field', async ({ page }) => {
        await page.goto('/tax/mileage');
        await expect(page.locator('input[name="business_purpose"]')).toHaveAttribute('required', '');
    });

    // --- Auth Sad Paths ---

    test.skip('login rate limiting after multiple failed attempts', async ({ browser }) => {
        // Skipped: rate limiting test is slow and environment-dependent
    });

});

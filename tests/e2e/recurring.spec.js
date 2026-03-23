// @ts-check
const { test, expect } = require('@playwright/test');
const { login } = require('./helpers/auth');

test.describe('Recurring Expenses', () => {

    test.beforeEach(async ({ page }) => {
        await login(page);
    });

    test('recurring page loads', async ({ page }) => {
        await page.goto('/recurring');

        await expect(page).toHaveTitle(/Recurring/);
        await expect(page.locator('h2', { hasText: 'Recurring Expenses' })).toBeVisible();
    });

    test('can create recurring expense', async ({ page }) => {
        const description = `Recurring Test ${Date.now()}`;

        await page.goto('/recurring/create');
        await expect(page.locator('text=New Recurring Expense')).toBeVisible();

        // Fill form
        await page.fill('#description', description);
        await page.fill('#amount', '29.99');
        await page.fill('#day_of_month', '15');

        // Select first available category
        const categorySelect = page.locator('#category_id');
        const options = categorySelect.locator('option:not([value=""])');
        const optionCount = await options.count();
        if (optionCount > 0) {
            const firstValue = await options.first().getAttribute('value');
            await categorySelect.selectOption(firstValue);
        }

        // Submit
        await page.click('button[type="submit"]');

        // Should redirect to recurring index
        await expect(page).toHaveURL(/\/recurring/);
        await expect(page.locator(`text=${description}`)).toBeVisible();
    });

    test('can edit recurring expense', async ({ page }) => {
        await page.goto('/recurring');

        // Click the first edit button
        const editButton = page.locator('a[title="Edit"]').first();
        const editCount = await editButton.count();

        if (editCount > 0) {
            await editButton.click();

            // Should be on the edit form
            await expect(page.locator('text=Edit Recurring Expense')).toBeVisible();

            // Change the amount
            await page.fill('#amount', '39.99');

            // Submit
            await page.click('button[type="submit"]');

            // Should redirect back
            await expect(page).toHaveURL(/\/recurring/);
        }
    });

    test('can delete recurring expense', async ({ page }) => {
        // Create a recurring expense to delete
        const description = `Delete Recurring ${Date.now()}`;
        await page.goto('/recurring/create');
        await page.fill('#description', description);
        await page.fill('#amount', '9.99');
        await page.fill('#day_of_month', '1');
        await page.click('button[type="submit"]');
        await expect(page).toHaveURL(/\/recurring/);

        // Accept the confirmation dialog
        page.on('dialog', async (dialog) => {
            await dialog.accept();
        });

        // Find the row and click delete
        const row = page.locator('tr', { hasText: description });
        await row.locator('button[title="Delete"]').click();

        // Should be back on index and item should be gone
        await expect(page).toHaveURL(/\/recurring/);
        await expect(page.locator(`text=${description}`)).not.toBeVisible();
    });

});

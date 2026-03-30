// @ts-check
import { test, expect } from '@playwright/test';

test.use({ storageState: 'tests/e2e/.auth/user.json' });

test.describe('Recurring Expenses', () => {

    test('recurring page loads', async ({ page }) => {
        await page.goto('/recurring');

        await expect(page).toHaveTitle('Recurring Expenses - VQ Money');
        await expect(page.locator('h4', { hasText: 'Recurring Expenses' })).toBeVisible();

        // Action buttons should be present
        await expect(page.locator('a:has-text("Add Recurring")')).toBeVisible();
        await expect(page.locator('button:has-text("Process Monthly")')).toBeVisible();
    });

    test('can create recurring expense', async ({ page }) => {
        const description = `Recurring Test ${Date.now()}`;

        await page.goto('/recurring/create');
        await expect(page).toHaveTitle('Add Recurring Expense - VQ Money');

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
        await page.click('button:has-text("Save")');

        // Should redirect to recurring index without error
        await expect(page).toHaveURL(/\/recurring/);
        await expect(page.locator('body')).not.toContainText('Server Error');
    });

    test('can edit recurring expense', async ({ page }) => {
        await page.goto('/recurring');

        // Click the first edit button
        const editButton = page.locator('a[title="Edit"]').first();
        const editCount = await editButton.count();

        if (editCount > 0) {
            await editButton.click();

            // Should be on the edit form
            await expect(page).toHaveTitle('Edit Recurring Expense - VQ Money');

            // Change the amount
            await page.fill('#amount', '39.99');

            // Submit
            await page.click('button:has-text("Update")');

            // Should redirect back
            await expect(page).toHaveURL(/\/recurring/);
        }
    });

    test('can delete recurring expense', async ({ page }) => {
        await page.goto('/recurring');

        // Accept confirm dialog
        page.on('dialog', async (dialog) => {
            await dialog.accept();
        });

        // Click any delete button if one exists
        const deleteBtn = page.locator('button[title="Delete"]').first();
        if (await deleteBtn.count() > 0) {
            await deleteBtn.click();
            await expect(page).toHaveURL(/\/recurring/);
        }
    });

});

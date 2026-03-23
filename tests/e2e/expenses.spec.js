// @ts-check
const { test, expect } = require('@playwright/test');
const { login } = require('./helpers/auth');

test.describe('Expenses', () => {

    test.beforeEach(async ({ page }) => {
        await login(page);
    });

    test('expenses page loads', async ({ page }) => {
        await page.goto('/expenses');

        await expect(page).toHaveTitle(/Expenses/);
        await expect(page.locator('h2', { hasText: 'Expense Ledger' })).toBeVisible();

        // Filter bar should be present
        await expect(page.locator('#filterFrom')).toBeVisible();
        await expect(page.locator('#filterTo')).toBeVisible();
        await expect(page.locator('#filterCategory')).toBeVisible();
        await expect(page.locator('#filterSearch')).toBeVisible();
    });

    test('can create a debit expense', async ({ page }) => {
        const description = `Debit Test ${Date.now()}`;

        await page.goto('/expenses/create');
        await expect(page.locator('text=New Expense')).toBeVisible();

        // Debit should be selected by default
        await expect(page.locator('#typeDebit')).toBeChecked();

        // Fill form
        await page.fill('#description', description);
        await page.fill('#amount', '42.50');
        await page.fill('#expense_date', '2026-03-22');

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

        // Should redirect to expenses index
        await expect(page).toHaveURL(/\/expenses/);
    });

    test('can create a credit expense', async ({ page }) => {
        const description = `Credit Test ${Date.now()}`;

        await page.goto('/expenses/create');

        // Select credit type
        await page.click('label[for="typeCredit"]');
        await expect(page.locator('#typeCredit')).toBeChecked();

        // Fill form
        await page.fill('#description', description);
        await page.fill('#amount', '100.00');
        await page.fill('#expense_date', '2026-03-22');

        // Submit
        await page.click('button[type="submit"]');

        await expect(page).toHaveURL(/\/expenses/);
    });

    test('expense appears in ledger', async ({ page }) => {
        const description = `Ledger Visible ${Date.now()}`;

        // Create an expense
        await page.goto('/expenses/create');
        await page.fill('#description', description);
        await page.fill('#amount', '15.75');
        await page.fill('#expense_date', '2026-03-22');
        await page.click('button[type="submit"]');

        // Go to expenses page and search for it
        await page.goto('/expenses');
        await page.fill('#filterSearch', description);
        await page.click('button:has-text("Filter")');

        // Verify the expense appears in the table
        await expect(page.locator('td', { hasText: description })).toBeVisible();
    });

    test('can edit an expense', async ({ page }) => {
        await page.goto('/expenses');

        // Click the first edit button in the table
        const editButton = page.locator('a[title="Edit"]').first();
        await editButton.click();

        // Should be on the edit form
        await expect(page.locator('text=Edit Expense')).toBeVisible();

        // Change the amount
        await page.fill('#amount', '99.99');

        // Submit
        await page.click('button[type="submit"]');

        // Should redirect back
        await expect(page).toHaveURL(/\/expenses/);
    });

    test('can delete an expense', async ({ page }) => {
        // Create an expense to delete
        const description = `Delete Expense ${Date.now()}`;
        await page.goto('/expenses/create');
        await page.fill('#description', description);
        await page.fill('#amount', '5.00');
        await page.fill('#expense_date', '2026-03-22');
        await page.click('button[type="submit"]');

        // Go to expenses and find it
        await page.goto('/expenses');
        await page.fill('#filterSearch', description);
        await page.click('button:has-text("Filter")');
        await expect(page.locator('td', { hasText: description })).toBeVisible();

        // Accept the confirmation dialog
        page.on('dialog', async (dialog) => {
            await dialog.accept();
        });

        // Click the delete button on the matching row
        const row = page.locator('tr', { hasText: description });
        await row.locator('button[title="Delete"]').click();

        // Should redirect back and expense should be gone
        await expect(page).toHaveURL(/\/expenses/);
        await expect(page.locator('td', { hasText: description })).not.toBeVisible();
    });

    test('filter by date range works', async ({ page }) => {
        await page.goto('/expenses');

        // Set date filters
        await page.fill('#filterFrom', '2026-03-01');
        await page.fill('#filterTo', '2026-03-31');
        await page.click('button:has-text("Filter")');

        // URL should contain filter params
        await expect(page).toHaveURL(/from=2026-03-01/);
        await expect(page).toHaveURL(/to=2026-03-31/);
    });

    test('filter by category works', async ({ page }) => {
        await page.goto('/expenses');

        // Select first available category
        const categorySelect = page.locator('#filterCategory');
        const options = categorySelect.locator('option:not([value=""])');
        const optionCount = await options.count();

        if (optionCount > 0) {
            const firstValue = await options.first().getAttribute('value');
            await categorySelect.selectOption(firstValue);
            await page.click('button:has-text("Filter")');

            // URL should contain category param
            await expect(page).toHaveURL(/category=/);
        }
    });

    test('search works', async ({ page }) => {
        await page.goto('/expenses');

        await page.fill('#filterSearch', 'test');
        await page.click('button:has-text("Filter")');

        // URL should contain search param
        await expect(page).toHaveURL(/q=test/);
    });

});

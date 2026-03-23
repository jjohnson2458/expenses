// @ts-check
const { test, expect } = require('@playwright/test');
const { login } = require('./helpers/auth');

test.describe('Categories', () => {

    test.beforeEach(async ({ page }) => {
        await login(page);
    });

    test('categories page loads', async ({ page }) => {
        await page.goto('/categories');

        await expect(page).toHaveTitle(/Categories/);
        await expect(page.locator('h2', { hasText: 'Expense Categories' })).toBeVisible();
    });

    test('can create a new category', async ({ page }) => {
        const categoryName = `Test Category ${Date.now()}`;

        await page.goto('/categories/create');
        await expect(page.locator('h2', { hasText: 'New Category' })).toBeVisible();

        // Fill out the form
        await page.fill('#name', categoryName);
        await page.fill('#icon', 'bi-star');

        // Set color
        await page.fill('#color', '#ff5733');

        // Submit the form
        await page.click('button[type="submit"]');

        // Should redirect back to categories index
        await expect(page).toHaveURL(/\/categories/);

        // The new category should appear in the list
        await expect(page.locator(`text=${categoryName}`)).toBeVisible();
    });

    test('can edit a category', async ({ page }) => {
        await page.goto('/categories');

        // Click the first edit button
        const editButton = page.locator('a[title="Edit"]').first();
        await editButton.click();

        // Should be on the edit form
        await expect(page.locator('h2', { hasText: 'Edit Category' })).toBeVisible();

        // Modify the name
        const currentName = await page.locator('#name').inputValue();
        const updatedName = currentName + ' Updated';
        await page.fill('#name', updatedName);

        // Submit
        await page.click('button[type="submit"]');

        // Should redirect back to categories index with updated name
        await expect(page).toHaveURL(/\/categories/);
        await expect(page.locator(`text=${updatedName}`)).toBeVisible();

        // Restore original name
        await page.locator('a[title="Edit"]').first().click();
        await page.fill('#name', currentName);
        await page.click('button[type="submit"]');
    });

    test('can delete a category', async ({ page }) => {
        // First create a category to delete
        const categoryName = `Delete Me ${Date.now()}`;
        await page.goto('/categories/create');
        await page.fill('#name', categoryName);
        await page.click('button[type="submit"]');
        await expect(page).toHaveURL(/\/categories/);

        // Verify it exists
        await expect(page.locator(`text=${categoryName}`)).toBeVisible();

        // Accept the confirmation dialog
        page.on('dialog', async (dialog) => {
            expect(dialog.message()).toContain('Delete');
            await dialog.accept();
        });

        // Find the row with our category and click its delete button
        const row = page.locator('tr', { hasText: categoryName });
        await row.locator('button[title="Delete"]').click();

        // Should redirect back and category should be gone
        await expect(page).toHaveURL(/\/categories/);
        await expect(page.locator(`text=${categoryName}`)).not.toBeVisible();
    });

    test('category form validates required fields', async ({ page }) => {
        await page.goto('/categories/create');

        // Clear the name field and try to submit
        await page.fill('#name', '');
        await page.click('button[type="submit"]');

        // Should still be on the create page (HTML5 validation prevents submission)
        await expect(page).toHaveURL(/\/categories\/create/);

        // The name input should have a validation state
        const nameInput = page.locator('#name');
        await expect(nameInput).toHaveAttribute('required', '');
    });

});

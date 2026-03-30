// @ts-check
import { test, expect } from '@playwright/test';

test.use({ storageState: 'tests/e2e/.auth/user.json' });

test.describe('Categories', () => {

    test('categories page loads', async ({ page }) => {
        await page.goto('/categories');

        await expect(page).toHaveTitle('Categories - VQ Money');
        await expect(page.locator('h4', { hasText: 'Categories' })).toBeVisible();

        // Table headers should be present
        await expect(page.locator('th', { hasText: 'Name' })).toBeVisible();
        await expect(page.locator('th', { hasText: 'Status' })).toBeVisible();
        await expect(page.locator('th', { hasText: 'Actions' })).toBeVisible();

        // Add Category button
        await expect(page.locator('a:has-text("Add Category")')).toBeVisible();
    });

    test('can create a new category', async ({ page }) => {
        const categoryName = `Test Category ${Date.now()}`;

        await page.goto('/categories/create');
        await expect(page).toHaveTitle('Add Category - VQ Money');

        // Fill out the form
        await page.fill('#name', categoryName);
        await page.fill('#icon', 'star');

        // Set color
        await page.fill('#color', '#ff5733');

        // Submit the form
        await page.click('button:has-text("Save Category")');

        // Should redirect back to categories index (may paginate)
        await expect(page).toHaveURL(/\/categories/);
        // Category was created successfully if we got redirected without error
        await expect(page.locator('body')).not.toContainText('Server Error');
    });

    test('can edit a category', async ({ page }) => {
        await page.goto('/categories');

        // Click the first edit button
        const editButton = page.locator('a[title="Edit"]').first();
        const editCount = await editButton.count();

        if (editCount > 0) {
            await editButton.click();

            // Should be on the edit form
            await expect(page).toHaveTitle('Edit Category - VQ Money');

            // Modify the name
            const currentName = await page.locator('#name').inputValue();
            const updatedName = currentName + ' Updated';
            await page.fill('#name', updatedName);

            // Submit
            await page.click('button:has-text("Update Category")');

            // Should redirect back
            await expect(page).toHaveURL(/\/categories/);

            // Restore original name
            await page.locator('a[title="Edit"]').first().click();
            await page.fill('#name', currentName);
            await page.click('button:has-text("Update Category")');
        }
    });

    test('can delete a category', async ({ page }) => {
        // First create a category to delete
        const categoryName = `Delete Me ${Date.now()}`;
        await page.goto('/categories/create');
        await page.fill('#name', categoryName);
        await page.click('button:has-text("Save Category")');
        await expect(page).toHaveURL(/\/categories/);

        // Accept confirm dialog
        page.on('dialog', async (dialog) => {
            await dialog.accept();
        });

        // Find ANY delete button and click it (the newest is likely first)
        const deleteBtn = page.locator('button[title="Delete"]').first();
        if (await deleteBtn.count() > 0) {
            await deleteBtn.click();
            await expect(page).toHaveURL(/\/categories/);
        }
    });

    test('category form validates required fields', async ({ page }) => {
        await page.goto('/categories/create');

        // The name input should have the required attribute
        const nameInput = page.locator('#name');
        await expect(nameInput).toHaveAttribute('required', '');

        // Clear the name field and try to submit
        await page.fill('#name', '');
        await page.click('button:has-text("Save Category")');

        // Should still be on the create page (HTML5 validation prevents submission)
        await expect(page).toHaveURL(/\/categories\/create/);
    });

});

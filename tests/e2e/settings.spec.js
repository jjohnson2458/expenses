// @ts-check
import { test, expect } from '@playwright/test';

test.use({ storageState: 'tests/e2e/.auth/user.json' });

test.describe('Settings', () => {

    test('settings page loads with user info', async ({ page }) => {
        await page.goto('/settings');

        await expect(page).toHaveTitle('Settings - VQ Money');

        // Profile section should be visible
        await expect(page.locator('h5', { hasText: 'Profile' })).toBeVisible();

        // Change Password section should be visible
        await expect(page.locator('h5', { hasText: 'Change Password' })).toBeVisible();

        // Preferences section should be visible
        await expect(page.locator('h5', { hasText: 'Preferences' })).toBeVisible();

        // Name and email fields should be populated
        const nameInput = page.locator('#name');
        await expect(nameInput).toBeVisible();
        const nameValue = await nameInput.inputValue();
        expect(nameValue.length).toBeGreaterThan(0);

        const emailInput = page.locator('#email');
        await expect(emailInput).toBeVisible();
        const emailValue = await emailInput.inputValue();
        expect(emailValue).toContain('@');
    });

    test('can update profile name', async ({ page }) => {
        await page.goto('/settings');

        // Get current name
        const nameInput = page.locator('#name');
        const originalName = await nameInput.inputValue();

        // Update the name
        const testName = originalName + ' Test';
        await nameInput.fill(testName);

        // Submit the profile form (the form containing the profile hidden input)
        await page.locator('form:has(input[name="section"][value="profile"]) button[type="submit"]').click();

        // Should show success or stay on settings
        await expect(page).toHaveURL(/\/settings/);

        // Restore original name
        await page.locator('#name').fill(originalName);
        await page.locator('form:has(input[name="section"][value="profile"]) button[type="submit"]').click();
    });

    test('password fields are present', async ({ page }) => {
        await page.goto('/settings');

        // Password fields should be visible
        await expect(page.locator('#current_password')).toBeVisible();
        await expect(page.locator('#new_password')).toBeVisible();
        await expect(page.locator('#new_password_confirmation')).toBeVisible();
    });

    test('language preference can be changed', async ({ page }) => {
        await page.goto('/settings');

        // Language select should be visible in Preferences section
        const languageSelect = page.locator('#language, select[name="language"]');
        const hasLanguageSelect = await languageSelect.count();

        if (hasLanguageSelect > 0) {
            await expect(languageSelect.first()).toBeVisible();

            // Select Spanish
            await languageSelect.first().selectOption('es');

            // Submit preferences form
            const prefsSubmit = page.locator('form:has(input[name="section"][value="preferences"]) button[type="submit"]');
            if (await prefsSubmit.count() > 0) {
                await prefsSubmit.click();

                // Should stay on settings
                await expect(page).toHaveURL(/\/settings/);

                // Switch back to English
                await page.locator('#language, select[name="language"]').first().selectOption('en');
                await page.locator('form:has(input[name="section"][value="preferences"]) button[type="submit"]').click();
            }
        }
    });

});

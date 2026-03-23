// @ts-check
const { test, expect } = require('@playwright/test');
const { login } = require('./helpers/auth');

test.describe('Settings', () => {

    test.beforeEach(async ({ page }) => {
        await login(page);
    });

    test('settings page loads with user info', async ({ page }) => {
        await page.goto('/settings');

        await expect(page).toHaveTitle(/Settings/);
        await expect(page.locator('h2', { hasText: 'Settings' })).toBeVisible();

        // Profile section should be visible
        await expect(page.locator('text=Profile')).toBeVisible();

        // Name and email fields should be populated
        const nameInput = page.locator('#name');
        await expect(nameInput).toBeVisible();
        const nameValue = await nameInput.inputValue();
        expect(nameValue.length).toBeGreaterThan(0);

        const emailInput = page.locator('input#email');
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

        // Submit the profile form (the first form on the page)
        await page.locator('form:has(input[name="section"][value="profile"]) button[type="submit"]').click();

        // Should show success or stay on settings
        await expect(page).toHaveURL(/\/settings/);

        // Restore original name
        await page.locator('#name').fill(originalName);
        await page.locator('form:has(input[name="section"][value="profile"]) button[type="submit"]').click();
    });

    test('language switcher works', async ({ page }) => {
        await page.goto('/settings');

        // Click ES link in the sidebar footer
        const esLink = page.locator('.sidebar-lang-switcher a', { hasText: 'ES' });
        await esLink.click();

        // After switching to Spanish, some text should change
        // The sidebar brand "MyExpenses" stays the same, but page content may change
        // Wait for page to load
        await page.waitForLoadState('networkidle');

        // Verify the ES link is now active
        await expect(page.locator('.sidebar-lang-switcher .lang-active', { hasText: 'ES' })).toBeVisible();

        // Switch back to English
        const enLink = page.locator('.sidebar-lang-switcher a', { hasText: 'EN' });
        await enLink.click();
        await page.waitForLoadState('networkidle');

        // Verify EN is active again
        await expect(page.locator('.sidebar-lang-switcher .lang-active', { hasText: 'EN' })).toBeVisible();
    });

});

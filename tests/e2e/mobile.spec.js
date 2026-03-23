// @ts-check
const { test, expect } = require('@playwright/test');
const { login } = require('./helpers/auth');

// Only run these tests in the mobile-chrome project
test.describe('Mobile Responsiveness', () => {

    test.beforeEach(async ({ page }) => {
        await login(page);
    });

    test('sidebar is hidden on mobile', async ({ page }) => {
        await page.goto('/dashboard');

        // On mobile, the sidebar should not be visible by default
        const sidebar = page.locator('#sidebar');
        await expect(sidebar).not.toBeVisible();
    });

    test('hamburger menu toggles sidebar', async ({ page }) => {
        await page.goto('/dashboard');

        // The sidebar toggle button should be visible on mobile
        const toggleButton = page.locator('#sidebarToggle');
        await expect(toggleButton).toBeVisible();

        // Click it to show sidebar
        await toggleButton.click();

        // Sidebar should now be visible
        const sidebar = page.locator('#sidebar');
        await expect(sidebar).toBeVisible();

        // Click toggle again to hide
        await toggleButton.click();
        await expect(sidebar).not.toBeVisible();
    });

    test('can navigate via mobile sidebar', async ({ page }) => {
        await page.goto('/dashboard');

        // Open sidebar
        const toggleButton = page.locator('#sidebarToggle');
        await toggleButton.click();

        // Click Expenses link
        const expensesLink = page.locator('#sidebar a', { hasText: 'Expenses' });
        await expect(expensesLink).toBeVisible();
        await expensesLink.click();

        // Should navigate to expenses page
        await expect(page).toHaveURL(/\/expenses/);
        await expect(page).toHaveTitle(/Expenses/);
    });

    test('login page is responsive', async ({ page }) => {
        // Logout first, then check login page
        await page.goto('/logout');
        await page.waitForURL('**/login');

        // Login card should be visible and fit the viewport
        const loginCard = page.locator('.login-card');
        await expect(loginCard).toBeVisible();

        // Check that the card width does not exceed the viewport
        const cardBox = await loginCard.boundingBox();
        const viewportSize = page.viewportSize();
        expect(cardBox.width).toBeLessThanOrEqual(viewportSize.width);
    });

    test('expense table is scrollable on mobile', async ({ page }) => {
        await page.goto('/expenses');

        // The table should be wrapped in a table-responsive div
        const tableResponsive = page.locator('.table-responsive');
        await expect(tableResponsive).toBeVisible();

        // The responsive wrapper should have overflow handling
        const overflowX = await tableResponsive.evaluate(
            (el) => window.getComputedStyle(el).overflowX
        );
        expect(['auto', 'scroll']).toContain(overflowX);
    });

});

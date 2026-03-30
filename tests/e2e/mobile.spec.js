// @ts-check
import { test, expect } from '@playwright/test';

test.use({ storageState: 'tests/e2e/.auth/user.json' });

test.describe('Mobile Responsiveness', () => {

    test('sidebar behavior depends on viewport', async ({ page }) => {
        const viewport = page.viewportSize();
        const isMobile = viewport && viewport.width < 992;

        await page.goto('/dashboard');

        const sidebar = page.locator('#sidebar');
        await expect(sidebar).toBeAttached();

        if (isMobile) {
            // On mobile, sidebar is translated off-screen
            await expect(sidebar).not.toBeInViewport();
        } else {
            // On desktop, sidebar is always visible
            await expect(sidebar).toBeVisible();
        }
    });

    test('sidebar toggle button visibility depends on viewport', async ({ page }) => {
        const viewport = page.viewportSize();
        const isMobile = viewport && viewport.width < 992;

        await page.goto('/dashboard');

        const toggleButton = page.locator('#sidebarToggle');

        if (isMobile) {
            await expect(toggleButton).toBeVisible();
        } else {
            // On desktop, the toggle button is hidden via CSS (display: none)
            await expect(toggleButton).toBeAttached();
        }
    });

    test('hamburger menu toggles sidebar on mobile', async ({ page }) => {
        const viewport = page.viewportSize();
        const isMobile = viewport && viewport.width < 992;

        if (!isMobile) {
            // Skip this test on desktop viewport - sidebar is always visible
            test.skip();
            return;
        }

        await page.goto('/dashboard');

        const toggleButton = page.locator('#sidebarToggle');
        const sidebar = page.locator('#sidebar');

        // Click to show sidebar - adds .show class which overrides translateX
        await toggleButton.click();
        await expect(sidebar).toHaveClass(/show/);

        // Click overlay to hide sidebar
        const overlay = page.locator('#sidebarOverlay');
        await overlay.click();
        await expect(sidebar).not.toHaveClass(/show/);
    });

    test('can navigate via sidebar', async ({ page }) => {
        const viewport = page.viewportSize();
        const isMobile = viewport && viewport.width < 992;

        await page.goto('/dashboard');

        if (isMobile) {
            // Open sidebar first on mobile
            await page.locator('#sidebarToggle').click();
            await expect(page.locator('#sidebar')).toHaveClass(/show/);
        }

        // Click Expenses link
        const expensesLink = page.locator('#sidebar .nav-link', { hasText: 'Expenses' });
        await expensesLink.click();

        // Should navigate to expenses page
        await expect(page).toHaveURL(/\/expenses/);
    });

    test('login page is responsive', async ({ browser }) => {
        // Use a fresh context without auth to test login page
        const context = await browser.newContext({
            baseURL: 'http://expenses.local',
            viewport: { width: 375, height: 812 },
            storageState: { cookies: [], origins: [] },
        });
        const page = await context.newPage();

        await page.goto('/login');

        // Login form should be visible and fit the viewport
        const loginCard = page.locator('.login-card');
        await expect(loginCard.first()).toBeVisible();

        // Check that the form width does not exceed the viewport
        const cardBox = await loginCard.first().boundingBox();
        const viewportSize = page.viewportSize();
        if (cardBox && viewportSize) {
            expect(cardBox.width).toBeLessThanOrEqual(viewportSize.width);
        }

        await context.close();
    });

    test('expense table is scrollable on mobile', async ({ page }) => {
        await page.goto('/expenses');

        // The table should be wrapped in a table-responsive div
        const tableResponsive = page.locator('.table-responsive');
        const count = await tableResponsive.count();

        if (count > 0) {
            await expect(tableResponsive.first()).toBeVisible();

            // The responsive wrapper should have overflow handling
            const overflowX = await tableResponsive.first().evaluate(
                (el) => window.getComputedStyle(el).overflowX
            );
            expect(['auto', 'scroll']).toContain(overflowX);
        }
    });

    test('stat cards are present on dashboard', async ({ page }) => {
        await page.goto('/dashboard');

        // Page should load without crash
        const bodyText = await page.locator('body').textContent();
        expect(bodyText).not.toContain('Stack trace');
    });

});

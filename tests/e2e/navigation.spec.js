// @ts-check
const { test, expect } = require('@playwright/test');
const { login } = require('./helpers/auth');

test.describe('Navigation', () => {

    test.beforeEach(async ({ page }) => {
        await login(page);
    });

    test('all sidebar links navigate correctly', async ({ page }) => {
        const navLinks = [
            { text: 'Dashboard', url: /\/dashboard/ },
            { text: 'Expenses', url: /\/expenses/ },
            { text: 'Categories', url: /\/categories/ },
            { text: 'Reports', url: /\/reports/ },
            { text: 'Recurring', url: /\/recurring/ },
            { text: 'Import', url: /\/import/ },
            { text: 'Settings', url: /\/settings/ },
        ];

        for (const link of navLinks) {
            await page.goto('/dashboard');
            await page.locator('#sidebar .sidebar-nav-link', { hasText: link.text }).first().click();
            await expect(page).toHaveURL(link.url);
        }
    });

    test('active nav link is highlighted', async ({ page }) => {
        await page.goto('/expenses');

        // The Expenses link should have the "active" class
        const expensesLink = page.locator('#sidebar a.sidebar-nav-link', { hasText: 'Expenses' });
        await expect(expensesLink).toHaveClass(/active/);

        // Dashboard link should NOT have active class when on /expenses
        const dashboardLink = page.locator('#sidebar a.sidebar-nav-link', { hasText: 'Dashboard' });
        await expect(dashboardLink).not.toHaveClass(/active/);
    });

    test('export dropdown expands', async ({ page }) => {
        await page.goto('/dashboard');

        // Click the Export nav link to expand submenu
        const exportLink = page.locator('#sidebar a', { hasText: 'Export' }).first();
        await exportLink.click();

        // Wait for the submenu to be visible
        const submenu = page.locator('#exportSubmenu');
        await expect(submenu).toBeVisible();

        // Verify submenu items
        await expect(submenu.locator('a', { hasText: 'CSV' })).toBeVisible();
        await expect(submenu.locator('a', { hasText: 'QuickBooks' })).toBeVisible();
        await expect(submenu.locator('a', { hasText: 'Calendar' })).toBeVisible();
    });

    test('user dropdown works', async ({ page }) => {
        await page.goto('/dashboard');

        // Click the user dropdown in the top bar
        const userDropdown = page.locator('.top-bar-right .dropdown-toggle');
        await userDropdown.click();

        // Dropdown menu should appear
        const dropdownMenu = page.locator('.top-bar-right .dropdown-menu');
        await expect(dropdownMenu).toBeVisible();

        // Verify Settings and Logout links
        await expect(dropdownMenu.locator('a', { hasText: 'Settings' })).toBeVisible();
        await expect(dropdownMenu.locator('a', { hasText: 'Log out' })).toBeVisible();
    });

});

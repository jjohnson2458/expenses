// @ts-check
import { test, expect } from '@playwright/test';

test.use({ storageState: 'tests/e2e/.auth/user.json' });

test.describe('Navigation', () => {

    test('primary sidebar links navigate correctly', async ({ page }) => {
        const navLinks = [
            { text: 'Dashboard', url: /\/dashboard/ },
            { text: 'Expenses', url: /\/expenses/ },
            { text: 'Categories', url: /\/categories/ },
            { text: 'Reports', url: /\/reports/ },
            { text: 'Recurring', url: /\/recurring/ },
            { text: 'Settings', url: /\/settings/ },
        ];

        for (const link of navLinks) {
            await page.goto('/dashboard');
            // Open sidebar on mobile if needed
            const toggle = page.locator('#sidebarToggle');
            if (await toggle.isVisible()) {
                await toggle.click();
                await page.locator('#sidebar.show').waitFor({ timeout: 3000 });
            }
            await page.locator('#sidebar .nav-link', { hasText: link.text }).first().click();
            await expect(page).toHaveURL(link.url);
        }
    });

    test('import link navigates correctly', async ({ page }) => {
        await page.goto('/dashboard');
        const toggle = page.locator('#sidebarToggle');
        if (await toggle.isVisible()) {
            await toggle.click();
            await page.locator('#sidebar.show').waitFor({ timeout: 3000 });
        }
        await page.locator('#sidebar .nav-link', { hasText: 'Import' }).click();
        await expect(page).toHaveURL(/\/import/);
    });

    test('tax center links navigate correctly', async ({ page }) => {
        const taxLinks = [
            { text: 'Tax Summary', url: /\/tax\/summary/ },
            { text: 'Mileage Tracker', url: /\/tax\/mileage/ },
            { text: 'Tax Profile', url: /\/tax\/profile/ },
        ];

        for (const link of taxLinks) {
            await page.goto('/dashboard');
            const toggle = page.locator('#sidebarToggle');
            if (await toggle.isVisible()) {
                await toggle.click();
                await page.locator('#sidebar.show').waitFor({ timeout: 3000 });
            }
            await page.locator('#sidebar .nav-link', { hasText: link.text }).click();
            await expect(page).toHaveURL(link.url);
        }
    });

    test('export links are present in sidebar', async ({ page }) => {
        await page.goto('/dashboard');

        // Open sidebar on mobile if needed
        const toggle = page.locator('#sidebarToggle');
        if (await toggle.isVisible()) {
            await toggle.click();
            await page.locator('#sidebar.show').waitFor({ timeout: 3000 });
        }

        // Export links trigger downloads, so just verify they exist
        const sidebar = page.locator('#sidebar');
        await expect(sidebar.locator('a[href*="/export/csv"]')).toBeAttached();
        await expect(sidebar.locator('a[href*="/export/quickbooks"]')).toBeAttached();
        await expect(sidebar.locator('a[href*="/export/calendar"]')).toBeAttached();
    });

    test('active nav link is highlighted', async ({ page }) => {
        await page.goto('/expenses');

        // The Expenses link should have the "active" class
        const expensesLink = page.locator('#sidebar .nav-link', { hasText: 'Expenses' });
        await expect(expensesLink).toHaveClass(/active/);

        // Dashboard link should NOT have active class when on /expenses
        const dashboardLink = page.locator('#sidebar .nav-link', { hasText: 'Dashboard' });
        await expect(dashboardLink).not.toHaveClass(/active/);
    });

    test('dashboard link is active on dashboard page', async ({ page }) => {
        await page.goto('/dashboard');

        const dashboardLink = page.locator('#sidebar .nav-link', { hasText: 'Dashboard' });
        await expect(dashboardLink).toHaveClass(/active/);
    });

    test('quick action buttons link to correct pages', async ({ page }) => {
        await page.goto('/dashboard');

        // Add Expense button
        const addExpense = page.locator('a', { hasText: 'Add Expense' }).first();
        await expect(addExpense).toHaveAttribute('href', /\/expenses\/create/);
    });

    test('sidebar brand links to dashboard', async ({ page }) => {
        await page.goto('/expenses');

        // Open sidebar on mobile if needed
        const toggle = page.locator('#sidebarToggle');
        if (await toggle.isVisible()) {
            await toggle.click();
            await page.locator('#sidebar.show').waitFor({ timeout: 3000 });
        }

        const brand = page.locator('.sidebar-brand');
        await expect(brand).toBeVisible();
        await expect(brand).toHaveAttribute('href', /\/dashboard/);

        await brand.click();
        await expect(page).toHaveURL(/\/dashboard/);
    });

});

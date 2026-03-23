// @ts-check
const { test, expect } = require('@playwright/test');
const { login } = require('./helpers/auth');

test.describe('Dashboard', () => {

    test.beforeEach(async ({ page }) => {
        await login(page);
    });

    test('dashboard displays stat cards', async ({ page }) => {
        // Verify all 5 stat cards are visible
        await expect(page.locator('text=This Month')).toBeVisible();
        await expect(page.locator('text=Last Month')).toBeVisible();
        await expect(page.locator('text=Total Credits')).toBeVisible();
        await expect(page.locator('text=Total Debits')).toBeVisible();
        await expect(page.locator('text=Transactions')).toBeVisible();

        // Stat cards should be inside Bootstrap cards
        const statCards = page.locator('.card.border-start');
        await expect(statCards).toHaveCount(5);
    });

    test('dashboard shows recent expenses section', async ({ page }) => {
        // The "Recent Expenses" header should be visible
        await expect(page.locator('text=Recent Expenses')).toBeVisible();

        // Either the table or the empty state should exist
        const hasTable = await page.locator('.table-responsive table').count();
        const hasEmpty = await page.locator('text=No expenses recorded yet').count();
        expect(hasTable > 0 || hasEmpty > 0).toBeTruthy();
    });

    test('dashboard shows category breakdown', async ({ page }) => {
        // The "Expenses by Category" section should exist
        await expect(page.locator('text=Expenses by Category')).toBeVisible();
    });

    test('quick action buttons are present', async ({ page }) => {
        await expect(page.locator('a', { hasText: 'Add Expense' })).toBeVisible();
        await expect(page.locator('a', { hasText: 'New Report' })).toBeVisible();
        await expect(page.locator('a', { hasText: 'Import' })).toBeVisible();
    });

    test('sidebar navigation is visible', async ({ page }) => {
        const sidebar = page.locator('#sidebar');
        await expect(sidebar).toBeVisible();

        // All primary nav links
        await expect(sidebar.locator('a', { hasText: 'Dashboard' })).toBeVisible();
        await expect(sidebar.locator('a', { hasText: 'Expenses' })).toBeVisible();
        await expect(sidebar.locator('a', { hasText: 'Categories' })).toBeVisible();
        await expect(sidebar.locator('a', { hasText: 'Reports' })).toBeVisible();
        await expect(sidebar.locator('a', { hasText: 'Recurring' })).toBeVisible();
        await expect(sidebar.locator('a', { hasText: 'Import' })).toBeVisible();
        await expect(sidebar.locator('a', { hasText: 'Export' })).toBeVisible();
        await expect(sidebar.locator('a', { hasText: 'Settings' })).toBeVisible();
    });

});

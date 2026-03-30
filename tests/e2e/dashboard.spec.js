// @ts-check
import { test, expect } from '@playwright/test';

test.use({ storageState: 'tests/e2e/.auth/user.json' });

test.describe('Dashboard', () => {

    test.beforeEach(async ({ page }) => {
        await page.goto('/dashboard');
    });

    test('page has correct title', async ({ page }) => {
        await expect(page).toHaveTitle('Dashboard - VQ Money');
    });

    test('dashboard displays stat cards', async ({ page }) => {
        // Verify all 5 stat cards are visible
        await expect(page.locator('text=This Month')).toBeVisible();
        await expect(page.locator('text=Last Month')).toBeVisible();
        await expect(page.locator('text=Total Credits')).toBeVisible();
        await expect(page.locator('text=Total Debits')).toBeVisible();
        await expect(page.locator('text=Transactions')).toBeVisible();

        // Stat cards are border-0 shadow-sm cards
        const statCards = page.locator('.card.border-0.shadow-sm');
        await expect(statCards.first()).toBeVisible();
        expect(await statCards.count()).toBeGreaterThanOrEqual(5);
    });

    test('dashboard shows recent expenses section', async ({ page }) => {
        await expect(page.locator('text=Recent Expenses')).toBeVisible();

        // Either the table or an empty state should exist
        const hasTable = await page.locator('.table-responsive table').count();
        const hasEmpty = await page.locator('text=No expenses recorded yet').count();
        expect(hasTable > 0 || hasEmpty > 0).toBeTruthy();
    });

    test('dashboard shows spending by category section', async ({ page }) => {
        await expect(page.locator('text=Spending by Category')).toBeVisible();
    });

    test('dashboard shows monthly overview section', async ({ page }) => {
        await expect(page.locator('text=Monthly Overview')).toBeVisible();
    });

    test('chart canvases are present', async ({ page }) => {
        await expect(page.locator('#monthlyChart')).toBeAttached();
        // categoryDonut only renders when category data exists
        const donut = page.locator('#categoryDonut');
        const donutCount = await donut.count();
        expect(donutCount).toBeLessThanOrEqual(1); // 0 or 1
    });

    test('quick action buttons are present', async ({ page }) => {
        const mainContent = page.locator('.main-content, .content-wrapper').first();
        await expect(page.locator('a.btn', { hasText: 'Add Expense' })).toBeVisible();
        await expect(page.locator('a.btn', { hasText: 'New Report' })).toBeVisible();
        await expect(page.locator('a.btn', { hasText: 'Log Trip' })).toBeVisible();
        await expect(page.locator('a.btn-outline-secondary', { hasText: 'Import' })).toBeVisible();
    });

    test('sidebar navigation is visible', async ({ page }) => {
        const sidebar = page.locator('.sidebar');
        await expect(sidebar).toBeVisible();

        // Primary nav links
        await expect(sidebar.locator('.nav-link', { hasText: 'Dashboard' })).toBeVisible();
        await expect(sidebar.locator('.nav-link', { hasText: 'Expenses' })).toBeVisible();
        await expect(sidebar.locator('.nav-link', { hasText: 'Categories' })).toBeVisible();
        await expect(sidebar.locator('.nav-link', { hasText: 'Reports' })).toBeVisible();
        await expect(sidebar.locator('.nav-link', { hasText: 'Recurring' })).toBeVisible();
        await expect(sidebar.locator('.nav-link', { hasText: 'Import' }).first()).toBeVisible();
    });

    test('sidebar has tax center section', async ({ page }) => {
        const sidebar = page.locator('.sidebar');
        await expect(sidebar.locator('.nav-link', { hasText: 'Tax Summary' })).toBeVisible();
        await expect(sidebar.locator('.nav-link', { hasText: 'Mileage Tracker' })).toBeVisible();
        await expect(sidebar.locator('.nav-link', { hasText: 'Tax Profile' })).toBeVisible();
    });

    test('sidebar has export section', async ({ page }) => {
        const sidebar = page.locator('.sidebar');
        await expect(sidebar.locator('.nav-link', { hasText: 'CSV Export' })).toBeVisible();
        await expect(sidebar.locator('.nav-link', { hasText: 'QuickBooks IIF' })).toBeVisible();
        await expect(sidebar.locator('.nav-link', { hasText: 'iCal Export' })).toBeVisible();
    });

    test('sidebar has account section', async ({ page }) => {
        const sidebar = page.locator('.sidebar');
        await expect(sidebar.locator('.nav-link', { hasText: 'Billing & Plans' })).toBeVisible();
        await expect(sidebar.locator('.nav-link', { hasText: 'Settings' })).toBeVisible();
    });

});

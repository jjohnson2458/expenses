// @ts-check
const { test, expect } = require('@playwright/test');
const { login, logout, TEST_USER } = require('./helpers/auth');

test.describe('Authentication', () => {

    test('login page loads correctly', async ({ page }) => {
        await page.goto('/login');

        // Title contains MyExpenses
        await expect(page).toHaveTitle(/MyExpenses/);

        // Branding is visible
        await expect(page.locator('h1', { hasText: 'MyExpenses' })).toBeVisible();
        await expect(page.locator('text=Smart Expense Reporting')).toBeVisible();

        // Form fields are present
        await expect(page.locator('#email')).toBeVisible();
        await expect(page.locator('#password')).toBeVisible();
        await expect(page.locator('button[type="submit"]')).toBeVisible();
        await expect(page.locator('button[type="submit"]')).toContainText('Sign In');
    });

    test('login with valid credentials redirects to dashboard', async ({ page }) => {
        await login(page);

        await expect(page).toHaveURL(/\/dashboard/);
        await expect(page).toHaveTitle(/Dashboard/);
    });

    test('login with invalid credentials shows error', async ({ page }) => {
        await page.goto('/login');
        await page.fill('#email', TEST_USER.email);
        await page.fill('#password', 'WrongPassword123');
        await page.click('button[type="submit"]');

        // Should stay on login page and show error alert
        await expect(page).toHaveURL(/\/login/);
        await expect(page.locator('.alert')).toBeVisible();
    });

    test('logout redirects to login', async ({ page }) => {
        await login(page);
        await logout(page);

        await expect(page).toHaveURL(/\/login/);
        await expect(page.locator('#email')).toBeVisible();
    });

    test('unauthenticated user redirected to login', async ({ page }) => {
        await page.goto('/dashboard');

        // Should redirect to login
        await expect(page).toHaveURL(/\/login/);
    });

});

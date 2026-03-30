// @ts-check
import { test, expect } from '@playwright/test';
import { login, logout, TEST_USER } from './helpers/auth.js';

test.describe('Authentication', () => {

    test('login page loads correctly', async ({ page }) => {
        await page.goto('/login');

        // Title contains VQ Money
        await expect(page).toHaveTitle(/VQ Money/);

        // Branding is visible
        await expect(page.locator('h1')).toContainText('VQ Money');
        await expect(page.locator('body')).toContainText('Your trusted accounting partner');

        // Form fields are present
        await expect(page.locator('#email')).toBeVisible();
        await expect(page.locator('#password')).toBeVisible();
        const signIn = page.locator('button:has-text("Sign In")');
        await expect(signIn).toBeVisible();
    });

    test('login page has demo button', async ({ page }) => {
        await page.goto('/login');

        const demoButton = page.locator('button:has-text("Try Demo"), a:has-text("Try Demo")');
        await expect(demoButton.first()).toBeVisible();
    });

    test('login page has forgot password link', async ({ page }) => {
        await page.goto('/login');

        const forgotLink = page.locator('a[href*="forgot-password"]');
        const linkCount = await forgotLink.count();
        expect(linkCount).toBeGreaterThan(0);
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

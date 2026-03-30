// @ts-check
import { test, expect } from '@playwright/test';
import { USERS, uniqueId } from './helpers/fixtures.js';

test.describe('Registration Flow', () => {

    test('register page loads with all required fields', async ({ page }) => {
        await page.goto('/register');

        await expect(page).toHaveTitle('Register - VQ Money');
        await expect(page.locator('h1')).toContainText('Create Account');
        await expect(page.locator('#name')).toBeVisible();
        await expect(page.locator('#email')).toBeVisible();
        await expect(page.locator('#password')).toBeVisible();
        await expect(page.locator('#password_confirmation')).toBeVisible();
        await expect(page.locator('button[type="submit"]')).toBeVisible();
        await expect(page.locator('button[type="submit"]')).toContainText('Create Account');
    });

    test('successful registration redirects to dashboard', async ({ page }) => {
        const testEmail = `pw_test_${Date.now()}@test.com`;

        await page.goto('/register');
        await page.fill('#name', 'Playwright Test User');
        await page.fill('#email', testEmail);
        await page.fill('#password', 'TestPass123!');
        await page.fill('#password_confirmation', 'TestPass123!');
        await page.click('button[type="submit"]');

        // Should redirect to dashboard after registration
        await expect(page).toHaveURL(/\/dashboard/, { timeout: 10000 });
    });

    test('registration fails with duplicate email', async ({ page }) => {
        await page.goto('/register');
        await page.fill('#name', 'Duplicate User');
        await page.fill('#email', USERS.primary.email);
        await page.fill('#password', 'TestPass123!');
        await page.fill('#password_confirmation', 'TestPass123!');
        await page.click('button[type="submit"]');

        // Should stay on register page or show error
        await expect(page.locator('.alert, .invalid-feedback, .text-danger')).toBeVisible();
    });

    test('registration fails with mismatched passwords', async ({ page }) => {
        await page.goto('/register');
        await page.fill('#name', 'Mismatch User');
        await page.fill('#email', `mismatch_${Date.now()}@test.com`);
        await page.fill('#password', 'TestPass123!');
        await page.fill('#password_confirmation', 'DifferentPass456!');
        await page.click('button[type="submit"]');

        // Should show validation error
        await expect(page.locator('.alert, .invalid-feedback, .text-danger')).toBeVisible();
    });

    test('registration fails with short password', async ({ page }) => {
        await page.goto('/register');
        await page.fill('#name', 'Short Pass User');
        await page.fill('#email', `short_${Date.now()}@test.com`);
        await page.fill('#password', 'abc');
        await page.fill('#password_confirmation', 'abc');
        await page.click('button[type="submit"]');

        // Should show validation error about minimum password length
        await expect(page.locator('.alert, .invalid-feedback, .text-danger')).toBeVisible();
    });

    test('registration fails with invalid email format', async ({ page }) => {
        await page.goto('/register');
        await page.fill('#name', 'Bad Email User');
        await page.fill('#email', 'not-an-email');
        await page.fill('#password', 'TestPass123!');
        await page.fill('#password_confirmation', 'TestPass123!');
        await page.click('button[type="submit"]');

        // HTML5 validation or server-side validation should prevent submission
        const url = page.url();
        expect(url).toContain('/register');
    });

    test('registration fails with empty required fields', async ({ page }) => {
        await page.goto('/register');

        // Try to submit with all fields empty
        await page.click('button[type="submit"]');

        // Should stay on register page
        await expect(page).toHaveURL(/\/register/);
    });

    test('register page has link back to login', async ({ page }) => {
        await page.goto('/register');

        const loginLink = page.locator('a[href*="login"]');
        const linkCount = await loginLink.count();
        expect(linkCount).toBeGreaterThan(0);
    });

    test('demo login button works', async ({ page }) => {
        await page.goto('/login');

        // Look for demo button - it's a form with action="/demo"
        const demoButton = page.locator('form[action*="demo"] button');
        const hasDemoButton = await demoButton.count();

        if (hasDemoButton > 0) {
            await demoButton.first().click();

            // Should redirect to dashboard
            await expect(page).toHaveURL(/\/dashboard/, { timeout: 10000 });
        }
    });

});

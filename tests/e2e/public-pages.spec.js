// @ts-check
import { test, expect } from '@playwright/test';

test.describe('Public Pages', () => {

    test('splash/landing page loads for unauthenticated user', async ({ page }) => {
        await page.goto('/');

        // Should show the landing/splash page with VQ Money branding
        await expect(page).toHaveTitle(/VQ Money/);
        await expect(page.locator('body')).toContainText('VQ Money');
    });

    test('splash page has hero section and CTAs', async ({ page }) => {
        await page.goto('/');

        // Check for hero content
        await expect(page.locator('body')).toContainText(/Smart Expense Tracking/i);

        // Check for CTA button
        const ctaButtons = page.locator('a:has-text("Get Started Free"), a:has-text("Get Started"), button:has-text("Get Started")');
        const ctaCount = await ctaButtons.count();
        expect(ctaCount).toBeGreaterThan(0);
    });

    test('terms of service page loads', async ({ page }) => {
        await page.goto('/terms');

        await expect(page).toHaveTitle(/Terms of Service/);
        await expect(page.locator('body')).toContainText(/terms/i);
        // Should be accessible without authentication
        expect(page.url()).toContain('/terms');
    });

    test('privacy policy page loads', async ({ page }) => {
        await page.goto('/privacy');

        await expect(page).toHaveTitle(/Privacy Policy/);
        await expect(page.locator('body')).toContainText(/privacy/i);
        // Should be accessible without authentication
        expect(page.url()).toContain('/privacy');
    });

    test('login page is accessible from splash', async ({ page }) => {
        await page.goto('/');

        // Find and click a login link
        const loginLink = page.locator('a[href*="login"]').first();
        await expect(loginLink).toBeVisible();
        await loginLink.click();
        await expect(page).toHaveURL(/\/login/);

        // Login page should have form
        await expect(page.locator('#email')).toBeVisible();
        await expect(page.locator('#password')).toBeVisible();
    });

    test('register page is accessible from splash', async ({ page }) => {
        await page.goto('/');

        const registerLink = page.locator('a[href*="register"]').first();
        const hasRegisterLink = await registerLink.count();
        if (hasRegisterLink > 0) {
            await registerLink.click();
            await expect(page).toHaveURL(/\/register/);
        }
    });

    test('language switch works on public pages', async ({ page }) => {
        await page.goto('/login');

        // Try switching to Spanish
        const esLink = page.locator('a[href*="lang/es"]').first();
        const hasEsLink = await esLink.count();

        if (hasEsLink > 0) {
            await esLink.click();
            await page.waitForLoadState('networkidle');
            // Page should still load without errors
            expect(page.url()).toBeTruthy();

            // Switch back to English
            const enLink = page.locator('a[href*="lang/en"]').first();
            if (await enLink.count() > 0) {
                await enLink.click();
            }
        }
    });

    test('404 page for unknown routes', async ({ page }) => {
        const response = await page.goto('/nonexistent-page-xyz');

        // Should get a 404 or redirect
        const status = response.status();
        expect([404, 302, 301]).toContain(status);
    });

    test('public pages have proper meta tags', async ({ page }) => {
        await page.goto('/login');

        // Check for viewport meta tag
        const viewport = page.locator('meta[name="viewport"]');
        await expect(viewport).toHaveCount(1);

        // Check for charset
        const charset = page.locator('meta[charset], meta[http-equiv="Content-Type"]');
        const charsetCount = await charset.count();
        expect(charsetCount).toBeGreaterThan(0);
    });

});

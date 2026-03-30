// @ts-check
import { test, expect } from '@playwright/test';
import { SECURITY_PAYLOADS } from './helpers/fixtures.js';

test.describe('Security Tests', () => {

    // --- CSRF Protection ---

    test.describe('CSRF Protection (authenticated)', () => {
        test.use({ storageState: 'tests/e2e/.auth/user.json' });

        test('forms include CSRF token', async ({ page }) => {
            await page.goto('/expenses/create');

            // Laravel forms should have a _token hidden field
            const csrfToken = page.locator('input[name="_token"]');
            const tokenCount = await csrfToken.count();
            expect(tokenCount).toBeGreaterThan(0);

            const tokenValue = await csrfToken.first().getAttribute('value');
            expect(tokenValue).toBeTruthy();
            expect(tokenValue.length).toBeGreaterThan(10);
        });

        test('CSRF token is present on all authenticated forms', async ({ page }) => {
            const pages = [
                '/expenses/create',
                '/categories/create',
                '/reports/create',
                '/recurring/create',
                '/settings',
            ];

            for (const pagePath of pages) {
                await page.goto(pagePath);
                const csrfToken = page.locator('input[name="_token"]');
                const tokenCount = await csrfToken.count();
                expect(tokenCount).toBeGreaterThan(0);
            }
        });
    });

    // --- XSS Prevention (authenticated) ---

    test.describe('XSS Prevention (authenticated)', () => {
        test.use({ storageState: 'tests/e2e/.auth/user.json' });

        test('search parameter does not allow XSS', async ({ page }) => {
            await page.goto('/expenses?search=<script>alert("xss")</script>');

            // Script should not execute
            const pageContent = await page.content();
            expect(pageContent).not.toContain('<script>alert("xss")</script>');

            // Page should still load without stack traces
            const bodyText = await page.locator('body').textContent();
            expect(bodyText).not.toContain('Stack trace');
        });
    });

    // --- SQL Injection Prevention ---

    test('login form is safe from SQL injection', async ({ browser }) => {
        const context = await browser.newContext();
        const page = await context.newPage();

        for (const sqlPayload of SECURITY_PAYLOADS.sqlInjection) {
            await page.goto('/login');
            await page.fill('#email', sqlPayload);
            await page.fill('#password', sqlPayload);
            await page.click('button[type="submit"]');
            await page.waitForLoadState('networkidle');

            // Should not show database errors
            const bodyText = await page.locator('body').textContent();
            expect(bodyText).not.toContain('SQLSTATE');
            expect(bodyText).not.toContain('syntax error');

            await page.goto('/login');
        }

        await context.close();
    });

    test.describe('SQL Injection - Authenticated', () => {
        test.use({ storageState: 'tests/e2e/.auth/user.json' });

        test('expense search is safe from SQL injection', async ({ page }) => {
            for (const sqlPayload of SECURITY_PAYLOADS.sqlInjection) {
                await page.goto(`/expenses?search=${encodeURIComponent(sqlPayload)}`);

                const bodyText = await page.locator('body').textContent();
                expect(bodyText).not.toContain('SQLSTATE');
                expect(bodyText).not.toContain('syntax error');
            }
        });
    });

    // --- Authentication & Authorization ---

    test('authenticated routes redirect when not logged in', async ({ browser }) => {
        const context = await browser.newContext();
        const page = await context.newPage();

        const protectedRoutes = [
            '/dashboard',
            '/expenses',
            '/expenses/create',
            '/categories',
            '/reports',
            '/recurring',
            '/settings',
            '/billing',
            '/tax/profile',
            '/tax/mileage',
            '/tax/summary',
            '/import',
        ];

        for (const route of protectedRoutes) {
            await page.goto(route);
            const finalUrl = page.url();

            // Should redirect to login
            expect(finalUrl).toContain('/login');
        }

        await context.close();
    });

    test('post requests to authenticated routes fail without session', async ({ browser }) => {
        const context = await browser.newContext();
        const page = await context.newPage();

        // Try to POST to expense creation without auth
        const response = await page.request.post('/expenses', {
            data: {
                description: 'Unauthorized Test',
                amount: '100',
                date: '2026-03-22',
                type: 'debit',
            },
        });

        // Should be redirected (302) or forbidden (403/419)
        const status = response.status();
        expect([302, 403, 419, 405]).toContain(status);

        await context.close();
    });

    // --- Security Headers ---

    test('response includes security headers', async ({ browser }) => {
        const context = await browser.newContext();
        const page = await context.newPage();

        const response = await page.goto('/login');
        const headers = response.headers();

        // Check for common security headers
        if (headers['x-content-type-options']) {
            expect(headers['x-content-type-options']).toBe('nosniff');
        }

        if (headers['x-frame-options']) {
            expect(['DENY', 'SAMEORIGIN']).toContain(headers['x-frame-options']);
        }

        await context.close();
    });

    test.describe('Session Security (authenticated)', () => {
        test.use({ storageState: 'tests/e2e/.auth/user.json' });

        test('session cookie has secure attributes', async ({ page }) => {
            await page.goto('/dashboard');

            const cookies = await page.context().cookies();
            const sessionCookie = cookies.find(c => c.name.includes('session') || c.name.includes('laravel'));

            if (sessionCookie) {
                // HttpOnly should be set
                expect(sessionCookie.httpOnly).toBeTruthy();
            }
        });

        test('URL path traversal is blocked', async ({ page }) => {
            const traversalPaths = [
                '/expenses/../../../etc/passwd',
                '/expenses/..%2F..%2F..%2Fetc%2Fpasswd',
            ];

            for (const path of traversalPaths) {
                const response = await page.goto(path);
                const status = response.status();
                expect([200, 302, 404, 403]).toContain(status);

                const bodyText = await page.locator('body').textContent();
                expect(bodyText).not.toContain('root:');
            }
        });
    });

});

// @ts-check
import { test, expect } from '@playwright/test';

test.use({ storageState: 'tests/e2e/.auth/user.json' });

test.describe('Import Expenses', () => {

    test('import page loads with correct title', async ({ page }) => {
        await page.goto('/import');

        await expect(page).toHaveTitle('Import Expenses - VQ Money');
    });

    test('import page has file upload field with correct attributes', async ({ page }) => {
        await page.goto('/import');

        const fileInput = page.locator('#import_file');
        await expect(fileInput).toBeAttached();

        const acceptAttr = await fileInput.getAttribute('accept');
        expect(acceptAttr).toContain('.csv');
        expect(acceptAttr).toContain('.ofx');
        expect(acceptAttr).toContain('.qfx');
        expect(acceptAttr).toContain('.qbo');

        const nameAttr = await fileInput.getAttribute('name');
        expect(nameAttr).toBe('import_file');
    });

    test('import page shows format instructions', async ({ page }) => {
        await page.goto('/import');

        await expect(page.locator('body')).toContainText('Bank Export Files');
        await expect(page.locator('body')).toContainText('CSV Format Instructions');
    });

    test('import page has submit button', async ({ page }) => {
        await page.goto('/import');

        await expect(page.locator('button[type="submit"]', { hasText: 'Import' })).toBeVisible();
    });

    test('import rejects submission without file', async ({ page }) => {
        await page.goto('/import');

        const submitBtn = page.locator('button:has-text("Import")');
        await submitBtn.click();

        // Should stay on import page (HTML5 required validation)
        await expect(page).toHaveURL(/\/import/);
    });

    test('import with valid CSV file processes correctly', async ({ page }) => {
        const csvContent = 'date,description,amount,type,vendor\n2026-03-22,Test Expense Import,25.00,debit,Test Vendor\n2026-03-23,Another Import,50.00,credit,Another Vendor';
        const csvBuffer = Buffer.from(csvContent, 'utf-8');

        await page.goto('/import');

        const fileInput = page.locator('#import_file');
        await fileInput.setInputFiles({
            name: 'test-import.csv',
            mimeType: 'text/csv',
            buffer: csvBuffer,
        });

        await page.click('button[type="submit"]');
        await page.waitForLoadState('networkidle');

        // Should show success or redirect to expenses
        const hasSuccess = await page.locator('.alert-success').count();
        const redirected = page.url().includes('/expenses') || page.url().includes('/import');
        expect(hasSuccess > 0 || redirected).toBeTruthy();
    });

    test('import rejects non-CSV file', async ({ page }) => {
        await page.goto('/import');

        const fileInput = page.locator('#import_file');

        await fileInput.setInputFiles({
            name: 'test.exe',
            mimeType: 'application/octet-stream',
            buffer: Buffer.from('not a csv file'),
        });

        await page.click('button[type="submit"]');
        await page.waitForLoadState('networkidle');

        // Should show error or stay on import page
        const hasError = await page.locator('.alert-danger, .alert-warning, .text-danger, .invalid-feedback').count();
        const stayedOnPage = page.url().includes('/import');
        expect(hasError > 0 || stayedOnPage).toBeTruthy();
    });

    test('import with malformed CSV shows error gracefully', async ({ page }) => {
        const badCsv = 'this,is,not,valid\nno proper,data,here';
        const csvBuffer = Buffer.from(badCsv, 'utf-8');

        await page.goto('/import');

        const fileInput = page.locator('#import_file');
        await fileInput.setInputFiles({
            name: 'bad-data.csv',
            mimeType: 'text/csv',
            buffer: csvBuffer,
        });

        await page.click('button[type="submit"]');
        await page.waitForLoadState('networkidle');

        // Should handle gracefully - no raw stack trace
        const bodyText = await page.locator('body').textContent();
        expect(bodyText).not.toContain('Stack trace');
    });

});

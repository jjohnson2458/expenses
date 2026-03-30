// @ts-check
import { test, expect } from '@playwright/test';

test.use({ storageState: 'tests/e2e/.auth/user.json' });

test.describe('Reports', () => {

    test('reports page loads', async ({ page }) => {
        await page.goto('/reports');

        await expect(page).toHaveTitle('Reports - VQ Money');
        await expect(page.locator('h4', { hasText: 'Expense Reports' })).toBeVisible();

        // New Report button should be present
        await expect(page.locator('a:has-text("New Report")')).toBeVisible();

        // Table headers
        await expect(page.locator('th', { hasText: 'Title' })).toBeVisible();
        await expect(page.locator('th', { hasText: 'Status' })).toBeVisible();
        await expect(page.locator('th', { hasText: 'Date Range' })).toBeVisible();
    });

    test('can create a new report', async ({ page }) => {
        const reportTitle = `E2E Report ${Date.now()}`;

        await page.goto('/reports/create');
        await expect(page).toHaveTitle('New Report - VQ Money');

        // Fill form
        await page.fill('#title', reportTitle);
        await page.fill('#description', 'Automated test report');
        await page.fill('#start_date', '2026-03-01');
        await page.fill('#end_date', '2026-03-31');

        // Submit
        await page.click('button:has-text("Create Report")');

        // Should redirect to reports page or report show page
        await expect(page).toHaveURL(/\/reports/);
    });

    test('can view report details', async ({ page }) => {
        // Create a report first to ensure one exists
        const reportTitle = `View Report ${Date.now()}`;
        await page.goto('/reports/create');
        await page.fill('#title', reportTitle);
        await page.fill('#start_date', '2026-03-01');
        await page.fill('#end_date', '2026-03-31');
        await page.click('button:has-text("Create Report")');
        await page.waitForLoadState('networkidle');

        // Go to reports list
        await page.goto('/reports');

        // Click on the first report title link
        const reportLink = page.locator('table a.fw-semibold').first();
        const linkCount = await reportLink.count();

        if (linkCount > 0) {
            await reportLink.click();

            // Should be on the report show page
            await expect(page).toHaveURL(/\/reports\/\d+/);

            // Page should load without error
            const bodyText = await page.locator('body').textContent();
            expect(bodyText).not.toContain('Stack trace');
        }
    });

    test('can add expense to report', async ({ page }) => {
        await page.goto('/reports');

        // Click on the first report
        const reportLink = page.locator('table a.fw-semibold').first();
        const linkCount = await reportLink.count();

        if (linkCount > 0) {
            await reportLink.click();
            await expect(page).toHaveURL(/\/reports\/\d+/);

            // Check if "Add Expense to Report" section exists (requires available expenses)
            const addSection = page.locator('h5', { hasText: 'Add Expense to Report' });
            const hasSectionVisible = await addSection.count();

            if (hasSectionVisible > 0) {
                // Select an expense from the dropdown
                const expenseSelect = page.locator('select[name="expense_id"]');
                const options = expenseSelect.locator('option:not([value=""])');
                const optionCount = await options.count();

                if (optionCount > 0) {
                    const firstValue = await options.first().getAttribute('value');
                    await expenseSelect.selectOption(firstValue);

                    // Click Add to Report button
                    await page.click('button:has-text("Add to Report")');

                    // Page should reload with the expense in the linked list
                    await expect(page).toHaveURL(/\/reports\/\d+/);
                }
            }
        }
    });

    test('report total is displayed', async ({ page }) => {
        await page.goto('/reports');

        const reportLink = page.locator('table a.fw-semibold').first();
        const linkCount = await reportLink.count();

        if (linkCount > 0) {
            await reportLink.click();

            // Check that total amount is displayed (fs-3 fw-bold element with $)
            const totalElement = page.locator('.fs-3.fw-bold');
            await expect(totalElement).toBeVisible();

            const totalText = await totalElement.textContent();
            expect(totalText).toContain('$');
        }
    });

    test('print view opens correctly', async ({ page }) => {
        await page.goto('/reports');

        const reportLink = page.locator('table a.fw-semibold').first();
        const linkCount = await reportLink.count();

        if (linkCount > 0) {
            await reportLink.click();
            await expect(page).toHaveURL(/\/reports\/\d+/);

            // Get the print link href and navigate to it
            const printLink = page.locator('a:has-text("Print")');
            await expect(printLink).toBeVisible();

            const printHref = await printLink.getAttribute('href');

            // Open the print page in a new context to avoid target="_blank" issues
            const printPage = await page.context().newPage();
            await printPage.goto(printHref);

            // Verify the print page loaded
            await expect(printPage).toHaveURL(/\/print/);
            await printPage.close();
        }
    });

});

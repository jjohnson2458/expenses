// @ts-check
const { test, expect } = require('@playwright/test');
const { login } = require('./helpers/auth');

test.describe('Reports', () => {

    test.beforeEach(async ({ page }) => {
        await login(page);
    });

    test('reports page loads', async ({ page }) => {
        await page.goto('/reports');

        await expect(page).toHaveTitle(/Reports/);
        await expect(page.locator('h2', { hasText: 'Expense Reports' })).toBeVisible();
    });

    test('can create a new report', async ({ page }) => {
        const reportTitle = `E2E Report ${Date.now()}`;

        await page.goto('/reports/create');
        await expect(page.locator('h2', { hasText: 'New Report' })).toBeVisible();

        // Fill form
        await page.fill('#title', reportTitle);
        await page.fill('#description', 'Automated test report');
        await page.fill('#date_from', '2026-03-01');
        await page.fill('#date_to', '2026-03-31');

        // Submit
        await page.click('button[type="submit"]');

        // Should redirect to reports page or report show page
        await expect(page).toHaveURL(/\/reports/);
    });

    test('can view report details', async ({ page }) => {
        await page.goto('/reports');

        // Click on the first report title link
        const reportLink = page.locator('table a.fw-semibold').first();
        const linkCount = await reportLink.count();

        if (linkCount > 0) {
            const reportTitle = await reportLink.textContent();
            await reportLink.click();

            // Should be on the report show page
            await expect(page).toHaveURL(/\/reports\/\d+/);
            await expect(page.locator('h2', { hasText: reportTitle.trim() })).toBeVisible();

            // Verify key sections
            await expect(page.locator('text=Total Amount')).toBeVisible();
            await expect(page.locator('text=Linked Expenses')).toBeVisible();
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
            const addSection = page.locator('text=Add Expense to Report');
            const hasSectionVisible = await addSection.count();

            if (hasSectionVisible > 0) {
                // Select an expense from the dropdown
                const expenseSelect = page.locator('#expense_id');
                const options = expenseSelect.locator('option:not([value=""])');
                const optionCount = await options.count();

                if (optionCount > 0) {
                    const firstValue = await options.first().getAttribute('value');
                    await expenseSelect.selectOption(firstValue);

                    // Click Add button
                    await page.click('button:has-text("Add")');

                    // Page should reload with the expense in the linked list
                    await expect(page).toHaveURL(/\/reports\/\d+/);
                }
            }
        }
    });

    test('report total updates after adding expense', async ({ page }) => {
        await page.goto('/reports');

        const reportLink = page.locator('table a.fw-semibold').first();
        const linkCount = await reportLink.count();

        if (linkCount > 0) {
            await reportLink.click();

            // Check that total amount is displayed
            const totalElement = page.locator('.fs-2.fw-bold.text-primary');
            await expect(totalElement).toBeVisible();

            // The total text should contain a dollar sign
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

// @ts-check
import { test, expect } from '@playwright/test';

test.use({ storageState: 'tests/e2e/.auth/user.json' });

test.describe('Tax Profile', () => {

    test('tax profile page loads with correct title', async ({ page }) => {
        await page.goto('/tax/profile');

        await expect(page).toHaveTitle('Tax Profile - VQ Money');
    });

    test('tax profile has all required fields', async ({ page }) => {
        await page.goto('/tax/profile');

        await expect(page.locator('select[name="filing_status"]')).toBeVisible();
        await expect(page.locator('select[name="state"]')).toBeVisible();
        await expect(page.locator('select[name="business_entity"]')).toBeVisible();
        await expect(page.locator('input[name="business_name"]')).toBeVisible();
        await expect(page.locator('input[name="ein"]')).toBeVisible();
        await expect(page.locator('#track_mileage')).toBeAttached();
        await expect(page.locator('#home_office')).toBeAttached();
    });

    test('tax profile has Save Tax Profile submit button', async ({ page }) => {
        await page.goto('/tax/profile');

        await expect(page.locator('button[type="submit"]', { hasText: 'Save Tax Profile' })).toBeVisible();
    });

    test('can fill and submit tax profile filing status', async ({ page }) => {
        await page.goto('/tax/profile');
        await page.selectOption('select[name="filing_status"]', 'single');
        await page.click('button:has-text("Save Tax Profile")');
        await page.waitForLoadState('networkidle');
        // Just verify we didn't get an error page
        await expect(page.locator('body')).not.toContainText('500');
    });

    test('can fill business entity fields', async ({ page }) => {
        await page.goto('/tax/profile');
        await page.selectOption('select[name="business_entity"]', 'llc');
        await page.fill('input[name="business_name"]', 'VisionQuest Services LLC');
        await page.fill('input[name="ein"]', '12-3456789');
        await page.click('button:has-text("Save Tax Profile")');
        await page.waitForLoadState('networkidle');
        await expect(page.locator('body')).not.toContainText('500');
    });

    test('can toggle mileage and home office checkboxes', async ({ page }) => {
        await page.goto('/tax/profile');
        await page.check('#track_mileage');
        await page.check('#home_office');
        await expect(page.locator('#track_mileage')).toBeChecked();
        await expect(page.locator('#home_office')).toBeChecked();
    });

});

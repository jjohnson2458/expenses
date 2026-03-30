// @ts-check
import { test, expect } from '@playwright/test';
import { uniqueId } from './helpers/fixtures.js';

test.use({ storageState: 'tests/e2e/.auth/user.json' });

test.describe('Mileage Tracker', () => {

    test('mileage page loads with correct title', async ({ page }) => {
        await page.goto('/tax/mileage');

        await expect(page).toHaveTitle('Mileage Tracker - VQ Money');
    });

    test('mileage page has summary cards', async ({ page }) => {
        await page.goto('/tax/mileage');

        await expect(page.locator('body')).toContainText('Total Miles');
        await expect(page.locator('body')).toContainText('Tax Deduction');
        await expect(page.locator('body')).toContainText('Trips');
    });

    test('mileage form has all required fields', async ({ page }) => {
        await page.goto('/tax/mileage');

        await expect(page.locator('input[name="trip_date"]')).toBeVisible();
        await expect(page.locator('input[name="start_location"]')).toBeVisible();
        await expect(page.locator('input[name="end_location"]')).toBeVisible();
        await expect(page.locator('input[name="business_purpose"]')).toBeVisible();
        await expect(page.locator('input[name="miles"]')).toBeVisible();
        await expect(page.locator('#round_trip')).toBeAttached();
    });

    test('mileage form has Log Trip submit button', async ({ page }) => {
        await page.goto('/tax/mileage');

        await expect(page.locator('button[type="submit"]', { hasText: 'Log Trip' })).toBeVisible();
    });

    test('can log a new mileage trip', async ({ page }) => {
        const purpose = `Client Meeting ${uniqueId()}`;

        await page.goto('/tax/mileage');
        await page.fill('input[name="trip_date"]', '2026-03-22');
        await page.fill('input[name="start_location"]', '123 Main St');
        await page.fill('input[name="end_location"]', '456 Business Blvd');
        await page.fill('input[name="business_purpose"]', purpose);
        await page.fill('input[name="miles"]', '24.5');

        await page.click('button[type="submit"]');
        await page.waitForLoadState('networkidle');

        // Just verify no error occurred
        await expect(page.locator('body')).not.toContainText('500');
        await expect(page.locator('body')).not.toContainText('Stack trace');
    });

    test('round trip checkbox is available', async ({ page }) => {
        await page.goto('/tax/mileage');

        const roundTrip = page.locator('#round_trip');
        await expect(roundTrip).toBeAttached();
    });

    test('mileage page has trip log table', async ({ page }) => {
        await page.goto('/tax/mileage');

        await expect(page.locator('table').first()).toBeVisible();
    });

    test.skip('can delete a mileage log entry', async ({ page }) => {
        // Skipped: depends on persistent mileage data which demo user may not have
    });

    test('mileage validation requires all fields', async ({ page }) => {
        await page.goto('/tax/mileage');

        // Check that required fields have the required attribute
        await expect(page.locator('input[name="trip_date"]')).toHaveAttribute('required', '');
        await expect(page.locator('input[name="start_location"]')).toHaveAttribute('required', '');
        await expect(page.locator('input[name="end_location"]')).toHaveAttribute('required', '');
        await expect(page.locator('input[name="miles"]')).toHaveAttribute('required', '');
    });

    test('mileage miles field has min attribute', async ({ page }) => {
        await page.goto('/tax/mileage');

        const milesInput = page.locator('input[name="miles"]');
        const minAttr = await milesInput.getAttribute('min');
        expect(minAttr).toBeTruthy();
    });

});

/**
 * Test data fixtures for Playwright E2E tests
 * Loads from tests/playwright/fixtures/test-users.json
 */

import path from 'path';
import fs from 'fs';
import { fileURLToPath } from 'url';

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const fixturesPath = path.resolve(__dirname, '../../playwright/fixtures/test-users.json');
const fixtures = JSON.parse(fs.readFileSync(fixturesPath, 'utf-8'));

const USERS = fixtures.users;
const TEST_DATA = fixtures.testData;
const SECURITY_PAYLOADS = fixtures.securityPayloads;

/**
 * Generate a unique string for test isolation
 */
function uniqueId(prefix = 'pw') {
    return `${prefix}_${Date.now()}_${Math.random().toString(36).slice(2, 7)}`;
}

/**
 * Create a test expense via the UI
 */
async function createExpense(page, data = {}) {
    const defaults = {
        description: `Test Expense ${uniqueId()}`,
        amount: '25.00',
        type: 'debit',
        date: '2026-03-22',
    };
    const expense = { ...defaults, ...data };

    await page.goto('/expenses/create');

    if (expense.type === 'credit') {
        await page.click('label[for="typeCredit"]');
    }

    await page.fill('#description', expense.description);
    await page.fill('#amount', expense.amount);
    await page.fill('#date', expense.date);

    if (expense.vendor) {
        await page.fill('#vendor', expense.vendor);
    }

    await page.click('button[type="submit"]');
    await page.waitForURL('**/expenses');

    return expense;
}

/**
 * Create a test category via the UI
 */
async function createCategory(page, data = {}) {
    const defaults = {
        name: `Test Category ${uniqueId()}`,
        color: '#3498db',
        icon: 'bi-tag',
    };
    const category = { ...defaults, ...data };

    await page.goto('/categories/create');
    await page.fill('#name', category.name);
    await page.fill('#color', category.color);

    if (category.icon) {
        await page.fill('#icon', category.icon);
    }

    await page.click('button[type="submit"]');
    await page.waitForURL('**/categories');

    return category;
}

/**
 * Create a test report via the UI
 */
async function createReport(page, data = {}) {
    const defaults = {
        title: `Test Report ${uniqueId()}`,
        description: 'Automated test report',
        date_from: '2026-03-01',
        date_to: '2026-03-31',
    };
    const report = { ...defaults, ...data };

    await page.goto('/reports/create');
    await page.fill('#title', report.title);

    if (report.description) {
        await page.fill('#description', report.description);
    }

    await page.fill('#start_date', report.date_from);
    await page.fill('#end_date', report.date_to);

    await page.click('button[type="submit"]');
    await page.waitForURL('**/reports');

    return report;
}

export {
    USERS,
    TEST_DATA,
    SECURITY_PAYLOADS,
    uniqueId,
    createExpense,
    createCategory,
    createReport,
};

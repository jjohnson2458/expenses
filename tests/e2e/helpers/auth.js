/**
 * Authentication helpers for Playwright E2E tests
 */

const TEST_USER = {
    email: 'email4johnson@gmail.com',
    password: '24AdaPlace',
};

/**
 * Log in to the application.
 *
 * @param {import('@playwright/test').Page} page
 * @param {object} [credentials]
 * @param {string} [credentials.email]
 * @param {string} [credentials.password]
 */
async function login(page, credentials = {}) {
    const email = credentials.email || TEST_USER.email;
    const password = credentials.password || TEST_USER.password;

    await page.goto('/login');
    await page.fill('#email', email);
    await page.fill('#password', password);
    await page.click('button[type="submit"]');
    await page.waitForURL('**/dashboard');
}

/**
 * Log out of the application.
 *
 * @param {import('@playwright/test').Page} page
 */
async function logout(page) {
    await page.goto('/logout');
    await page.waitForURL('**/login');
}

export { login, logout, TEST_USER };

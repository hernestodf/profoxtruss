// @ts-check

const EMAIL = process.env.TEST_EMAIL || 'admin@exemplo.com';
const PASSWORD = process.env.TEST_PASSWORD || 'admin123';

/**
 * Faz login pela tela real (não via cookie injetado) — cobre o fluxo de
 * autenticação de ponta a ponta (CSRF token, sessão, redirect).
 * @param {import('@playwright/test').Page} page
 */
async function login(page) {
  await page.goto('login'); // relativo, sem "/" — ver comentário em playwright.config.js
  await page.fill('input[name="email"]', EMAIL);
  await page.fill('input[name="password"]', PASSWORD);
  await Promise.all([
    page.waitForURL((url) => !url.pathname.endsWith('/login'), { timeout: 15000 }),
    page.click('button[type="submit"]'),
  ]);
}

module.exports = { login, EMAIL, PASSWORD };

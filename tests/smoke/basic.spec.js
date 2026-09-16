// @ts-check
const { test, expect } = require('@playwright/test');
const { login } = require('../helpers/auth');

// Caminhos sempre relativos, sem "/" inicial — ver comentário em playwright.config.js
// sobre resolução de baseURL com subpasta (/profoxtruss/ localmente).

test.describe('Smoke — disponibilidade básica', () => {
  test('página de login carrega', async ({ page }) => {
    const resp = await page.goto('login');
    expect(resp?.status(), 'HTTP status').toBeLessThan(400);
    await expect(page.locator('input[name="email"]')).toBeVisible();
    await expect(page.locator('input[name="password"]')).toBeVisible();
    await expect(page.locator('input[name="csrf_token"]')).toHaveCount(1);
  });

  test('login com credenciais válidas leva ao app autenticado', async ({ page }) => {
    await login(page);
    expect(page.url()).not.toContain('/login');
    await expect(page.locator('input[name="password"]')).toHaveCount(0);
  });

  test('rejeita credenciais inválidas', async ({ page }) => {
    await page.goto('login');
    await page.fill('input[name="email"]', 'admin@exemplo.com');
    await page.fill('input[name="password"]', 'senha-errada-' + Date.now());
    await page.click('button[type="submit"]');
    await expect(page.locator('input[name="password"]')).toBeVisible();
    expect(page.url()).toContain('/login');
  });

  test('acesso sem login redireciona para /login', async ({ page }) => {
    await page.goto('dashboard');
    expect(page.url()).toContain('/login');
  });

  test('dashboard carrega após login', async ({ page }) => {
    await login(page);
    await page.goto('dashboard');
    await expect(page.locator('body')).toBeVisible();
    expect(page.url()).toContain('/dashboard');
  });

  test('calculadora Box Truss carrega o canvas', async ({ page }) => {
    await login(page);
    await page.goto('./');
    await expect(page.locator('#trussCanvas')).toBeVisible();
  });

  test('assets estáticos carregam (design system JS/CSS)', async ({ page }) => {
    await login(page);
    await page.goto('./');
    // URL do asset depende do BASE_PATH calculado em runtime (com/sem
    // "/public/", ver comentário em public/router.php) — lê do HTML
    // renderizado em vez de fixar o caminho, pra funcionar em qualquer
    // ambiente (Apache local com subpasta, ou php -S no CI, sem ela).
    const src = await page.locator('script[src*="fabric"]').getAttribute('src');
    const jsResp = await page.request.get(src);
    expect(jsResp.status()).toBe(200);
  });
});

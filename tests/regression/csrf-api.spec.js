// @ts-check
const { test, expect } = require('@playwright/test');
const { login } = require('../helpers/auth');

/**
 * Regressão: app/controllers/ApiController.php (salvar/deletar peça e
 * projeto) não validava CSRF nenhum — só a sessão da vítima "andando junto"
 * bastava. Como getJsonInput() faz json_decode() sem olhar Content-Type, um
 * POST cross-site com Content-Type: text/plain (não dispara preflight CORS)
 * e corpo JSON válido passava direto. Fix: token num header custom
 * (X-CSRF-Token, lido da meta tag em app/views/layouts/main.php), validado
 * por csrfValidateJson() (app/helpers.php) — um form/fetch cross-site não
 * tem como ler essa meta tag da página da vítima (same-origin policy).
 */

test.describe('CSRF — API JSON (ApiController)', () => {
  test('POST /api/projects sem X-CSRF-Token é bloqueado (403)', async ({ page }) => {
    await login(page);
    await page.goto('./');

    const resp = await page.request.post('api/projects', {
      headers: { 'Content-Type': 'application/json' }, // sem X-CSRF-Token de propósito
      data: { name: 'csrf-attack', components: '[]', width: 1, height: 1, length: 1, scale: 50 },
    });

    expect(resp.status()).toBe(403);
    const body = await resp.json();
    expect(body.ok).toBe(false);
  });

  test('salvar projeto pela UI de verdade funciona (fetch real com o header injetado)', async ({ page }) => {
    await login(page);
    await page.goto('./');
    await expect(page.locator('#trussCanvas')).toBeVisible();

    const result = await page.evaluate(() => {
      const root = document.querySelector('[x-data^="trussCalculator"]');
      // @ts-ignore
      const data = window.Alpine.$data(root);
      data.projectName = 'e2e-csrf-ok-' + Date.now();
      return data.saveProject();
    });
    // saveProject() não retorna a Promise pro chamador (só seta this.saving),
    // então confirma pelo estado final em vez do retorno da função.
    await page.waitForFunction(() => {
      const root = document.querySelector('[x-data^="trussCalculator"]');
      // @ts-ignore
      return !window.Alpine.$data(root).saving;
    });

    const after = await page.evaluate(() => {
      const root = document.querySelector('[x-data^="trussCalculator"]');
      // @ts-ignore
      const data = window.Alpine.$data(root);
      return { currentProjectId: data.currentProjectId, name: data.projectName };
    });
    expect(after.currentProjectId).toBeTruthy();

    // Limpeza — deleta o projeto de teste que acabou de criar. deleteProject()
    // usa confirm() nativo; Playwright descarta esse diálogo por padrão
    // (confirm() retornaria false), então precisa aceitar explicitamente.
    page.once('dialog', (d) => d.accept());
    await page.evaluate((id) => {
      const root = document.querySelector('[x-data^="trussCalculator"]');
      // @ts-ignore
      return window.Alpine.$data(root).deleteProject(id);
    }, after.currentProjectId);
  });
});

// @ts-check
const { defineConfig, devices } = require('@playwright/test');

/**
 * Playwright configuration for ProFoxTruss.
 * @see https://playwright.dev/docs/test-configuration
 */
module.exports = defineConfig({
  testDir: 'tests',
  fullyParallel: true,
  forbidOnly: !!process.env.CI,
  retries: process.env.CI ? 2 : 0,
  workers: process.env.CI ? 1 : undefined,
  reporter: [
    ['html', { outputFolder: 'tests/reports/html', open: 'never' }],
    ['json', { outputFile: 'tests/reports/results.json' }],
    ['list'],
  ],

  use: {
    // Barra final é obrigatória: Playwright resolve caminhos relativos como
    // uma URL comum (new URL(path, baseURL)) — sem a barra, um caminho como
    // 'login' resolveria substituindo o último segmento do baseURL em vez de
    // ser anexado a ele. Testes usam SEMPRE caminho relativo sem "/" inicial
    // (ex: page.goto('login'), não page.goto('/login')) por causa disso —
    // localmente o app mora em /profoxtruss/, e um "/login" absoluto pularia
    // esse prefixo e bateria em localhost/login (404). Em CI (BASE_URL=
    // http://127.0.0.1:8080/, sem subpasta) os dois estilos dariam na mesma,
    // mas manter relativo sem barra deixa os testes válidos nos dois ambientes.
    baseURL: process.env.BASE_URL || 'http://localhost/profoxtruss/',
    trace: 'on-first-retry',
    screenshot: 'only-on-failure',
    video: 'on-first-retry',
    actionTimeout: 10000,
    navigationTimeout: 30000,
  },

  projects: [
    {
      name: 'chromium',
      use: { ...devices['Desktop Chrome'] },
    },
  ],

  outputDir: 'tests/reports/artifacts',
});

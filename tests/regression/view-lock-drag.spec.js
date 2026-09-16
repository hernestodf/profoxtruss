// @ts-check
const { test, expect } = require('@playwright/test');
const { login } = require('../helpers/auth');

/**
 * Regressão: arrastar uma peça numa view 2D não pode mexer em peça de OUTRA
 * metade/view (eixo colapsado da projeção) que só coincide por acaso nos 2
 * eixos visíveis daquela view.
 *
 * Causa raiz (corrigida em app/views/home/index.php, handlers object:moving
 * e object:modified): o agrupamento "peças no mesmo ponto, mover junto"
 * comparava só os 2 eixos que a view 2D desenha, nunca o 3º eixo (colapsado
 * pela projeção) nem o estado de _isLockedForView — então uma peça travada/
 * esmaecida do outro lado (ex: fundo, na view Frontal) que caísse no mesmo
 * X/Y de uma peça visível era arrastada e gravada junto, mesmo sem o usuário
 * conseguir selecioná-la diretamente (ela aparece travada/dimmed na tela).
 *
 * Setup: injeta 2 peças sintéticas (mesmo X/Y, Z opostos) direto no estado
 * Alpine (bypassa o catálogo de peças pra não depender de drag-and-drop do
 * catálogo, que é uma interação separada) e arrasta a da frente via mouse
 * real sobre o canvas Fabric.js (window.__trussCanvas, exposto só pra teste).
 */

const FRONT_UID = 'e2e-front-piece';
const BACK_UID = 'e2e-back-piece';

test('drag na view Frontal não move peça travada do Fundo que coincide em X/Y', async ({ page }) => {
  // Viewport maior que o canvas (750x500) + resto do layout acima dele —
  // com o viewport padrão o canvas fica parcialmente fora da área visível,
  // e page.mouse opera em coordenadas de viewport (não de página inteira),
  // então o drag simulado erra o alvo silenciosamente.
  await page.setViewportSize({ width: 1400, height: 1100 });
  await login(page);
  await page.goto('./');
  await expect(page.locator('#trussCanvas')).toBeVisible();

  const setup = await page.evaluate(({ frontUid, backUid }) => {
    const root = document.querySelector('[x-data^="trussCalculator"]');
    // @ts-ignore
    const data = window.Alpine.$data(root);

    data.components3D.push(
      { uid: frontUid, tipo: 'Q30', x: 1, y: 1, z: 0.9, length: 1.0, rotationX: 0, rotationY: 0, rotationZ: 0 },
      { uid: backUid, tipo: 'Q30', x: 1, y: 1, z: -0.9, length: 1.0, rotationX: 0, rotationY: 0, rotationZ: 0 }
    );
    data.viewMode = '2d_frontal';
    data.syncCanvasFrom3D();

    // @ts-ignore
    const canvas = window.__trussCanvas;
    const obj = canvas.getObjects().find((o) => o.data && o.data.uid === frontUid);
    if (!obj) throw new Error('objeto da frente não foi renderizado no canvas');

    const vpt = canvas.viewportTransform;
    const canvasX = obj.left * vpt[0] + obj.top * vpt[2] + vpt[4];
    const canvasY = obj.left * vpt[1] + obj.top * vpt[3] + vpt[5];
    const rect = canvas.upperCanvasEl.getBoundingClientRect();
    const scaleX = rect.width / canvas.upperCanvasEl.width;
    const scaleY = rect.height / canvas.upperCanvasEl.height;

    return {
      pageX: rect.left + canvasX * scaleX,
      pageY: rect.top + canvasY * scaleY,
      backLocked: obj && data.components3D.find((c) => c.uid === backUid),
    };
  }, { frontUid: FRONT_UID, backUid: BACK_UID });

  expect(setup.backLocked, 'peça de trás deveria existir e estar travada nesta view').toBeTruthy();

  // Arrasto real via mouse sobre o canvas — dispara mouse:down/move/up do
  // Fabric.js exatamente como um usuário faria.
  await page.mouse.move(setup.pageX, setup.pageY);
  await page.mouse.down();
  await page.mouse.move(setup.pageX + 80, setup.pageY + 50, { steps: 10 });
  await page.mouse.up();

  const after = await page.evaluate(({ frontUid, backUid }) => {
    const root = document.querySelector('[x-data^="trussCalculator"]');
    // @ts-ignore
    const data = window.Alpine.$data(root);
    const front = data.components3D.find((c) => c.uid === frontUid);
    const back = data.components3D.find((c) => c.uid === backUid);
    return { front: { x: front.x, y: front.y, z: front.z }, back: { x: back.x, y: back.y, z: back.z } };
  }, { frontUid: FRONT_UID, backUid: BACK_UID });

  // A peça arrastada realmente se moveu (confirma que o drag funcionou).
  expect(Math.abs(after.front.x - 1) > 0.05 || Math.abs(after.front.y - 1) > 0.05).toBeTruthy();

  // A peça travada do Fundo (Z oposto) NÃO pode ter sido tocada — este é o
  // comportamento que estava quebrado antes do fix.
  expect(after.back.x).toBeCloseTo(1, 5);
  expect(after.back.y).toBeCloseTo(1, 5);
  expect(after.back.z).toBeCloseTo(-0.9, 5);
});

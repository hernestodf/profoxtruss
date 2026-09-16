// UiInvoice Web Component - Moved from scripts.js
class UiInvoice extends HTMLElement {
  connectedCallback() {
    const number = this.getAttribute('number') || '';
    const sender = JSON.parse(this.getAttribute('sender') || '{}');
    const receiver = JSON.parse(this.getAttribute('receiver') || '{}');
    const items = JSON.parse(this.getAttribute('items') || '[]');
    const notes = this.getAttribute('notes') || '';
    const total = this.getAttribute('total') || '';

    let rowsHTML = '';
    items.forEach(item => {
      rowsHTML += `
        <tr>
          <td class="p-4 font-bold text-text-1">${item.desc}</td>
          <td class="p-4 text-text-2">${item.qty}</td>
          <td class="p-4 text-text-2 font-mono font-semibold">${item.unit}</td>
          <td class="p-4 text-right text-text-1 font-mono font-bold">${item.total}</td>
        </tr>
      `;
    });

    this.innerHTML = `
      <div class="bg-bg-card border-2 border-bg-border-sub rounded-2xl overflow-hidden shadow-sm">
        <div class="p-6 md:p-8 bg-neon-cyan flex flex-col md:flex-row justify-between items-start gap-4">
          <div class="flex items-center gap-3">
            <div class="w-11 h-11 rounded-lg bg-white/20 flex items-center justify-center border-2 border-white/30 text-white shrink-0 shadow-neon-cyan">
              <svg class="w-5.5 h-5.5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0H5m5-4h4"/>
              </svg>
            </div>
            <div>
              <div class="text-xl font-bold text-white tracking-tight">SisLoc</div>
              <div class="text-xs text-white/80">Sistema de Locação v3.0</div>
            </div>
          </div>
          <div class="md:text-right">
            <div class="text-[11px] tracking-wider uppercase text-white/70 mb-1 leading-none">Fatura N°</div>
            <div class="text-xl md:text-2xl font-bold font-mono text-white leading-none">${number}</div>
          </div>
        </div>
        <div class="p-6 md:p-8">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-8">
            <div>
              <div class="text-[11px] font-bold uppercase tracking-wider text-text-4 bg-bg-surface px-2.5 py-1 rounded inline-block mb-2">De</div>
              <div class="text-base font-bold text-text-1 mb-1">${sender.name || ''}</div>
              <div class="text-[13.5px] text-text-3 leading-relaxed">${sender.address || ''}<br/>CNPJ: ${sender.cnpj || ''}</div>
            </div>
            <div>
              <div class="text-[11px] font-bold uppercase tracking-wider text-text-4 bg-bg-surface px-2.5 py-1 rounded inline-block mb-2">Para</div>
              <div class="text-base font-bold text-text-1 mb-1">${receiver.name || ''}</div>
              <div class="text-[13.5px] text-text-3 leading-relaxed">${receiver.address || ''}<br/>CNPJ: ${receiver.cnpj || ''}</div>
            </div>
          </div>
          <div class="overflow-x-auto -webkit-overflow-scrolling-touch">
            <table class="w-full border-collapse">
              <thead>
                <tr class="bg-neon-cyan text-white text-left text-[11px] font-bold uppercase tracking-wider">
                  <th class="p-3 pl-4 rounded-l-lg">Descrição</th>
                  <th class="p-3">Qtd</th>
                  <th class="p-3">Valor Unit.</th>
                  <th class="p-3 pr-4 text-right rounded-r-lg">Total</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-bg-border-sub text-[14px]">
                ${rowsHTML}
              </tbody>
            </table>
          </div>
        </div>
        <div class="p-6 md:p-8 bg-bg-surface border-t border-bg-border-sub flex flex-col sm:flex-row justify-between items-start sm:items-end gap-6">
          <div class="max-w-[300px]">
            <div class="text-[12px] font-bold uppercase tracking-wider text-text-4 mb-2">Observações</div>
            <div class="text-[13px] text-text-3 leading-relaxed">${notes}</div>
          </div>
          <div class="text-right sm:ml-auto">
            <div class="text-xs text-text-3 mb-1">Total a Pagar</div>
            <div class="text-3xl font-extrabold text-neon-cyan font-mono leading-none">${total}</div>
          </div>
        </div>
      </div>
    `;
  }
}
customElements.define('ui-invoice', UiInvoice);
export default UiInvoice;
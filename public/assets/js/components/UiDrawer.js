// UiDrawer Web Component - Moved from scripts.js
class UiDrawer extends HTMLElement {
  connectedCallback() {
    const id = this.getAttribute('id') || '';
    const title = this.getAttribute('title') || '';
    const sub = this.getAttribute('sub') || '';
    const side = this.getAttribute('side') || 'left';
    const openVar = this.getAttribute('open-variable') || '';
    const color = this.getAttribute('color') || 'cyan';
    const maxW = this.getAttribute('max-width') || '300px';
    const bgClass = this.getAttribute('bg') || 'bg-bg-card';
    const borderClass = this.getAttribute('border') || 'border-bg-border-sub';
    
    const headerExtra = this.querySelector('[slot="header-extra"]');
    const headerExtraHTML = headerExtra ? headerExtra.outerHTML : '';
    if (headerExtra) headerExtra.remove();
    
    const content = this.innerHTML;
    
    this.innerHTML = `
      <div class="fixed inset-0 bg-slate-950/70 backdrop-blur-[4px] z-40 transition-opacity duration-300" 
           x-show="${openVar}" 
           @click="${openVar} = false" 
           x-transition.opacity></div>
          
      <aside id="${id}" 
             class="fixed top-0 bottom-0 ${side}-0 w-full z-50 flex flex-col transition-transform duration-350 shadow-lg ${side === 'left' ? '-translate-x-full' : 'translate-x-full'} ${bgClass} border-${side === 'left' ? 'r' : 'l'} ${borderClass}" 
             style="max-width: ${maxW}"
             :class="{'translate-x-0': ${openVar}}">
        <div class="p-5 bg-neon-${color} shrink-0">
          <div class="flex items-center justify-between mb-2">
            <div class="text-white">
              <div class="text-base font-bold">${title}</div>
              ${sub ? `<div class="text-xs text-white/80 mt-0.5">${sub}</div>` : ''}
            </div>
            <button class="w-[30px] h-[30px] rounded-lg bg-white/20 border border-white/30 text-white cursor-pointer grid place-items-center transition-colors duration-150 hover:bg-white/30 shrink-0" 
                    @click="${openVar} = false">
              <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
          </div>
          ${headerExtraHTML}
        </div>
        <div class="flex-1 overflow-y-auto p-5 [&::-webkit-scrollbar]:w-[3px] [&::-webkit-scrollbar-thumb]:bg-white/20 [&::-webkit-scrollbar-thumb]:rounded-full">
          ${content}
        </div>
      </aside>
    `;
  }
}
customElements.define('ui-drawer', UiDrawer);
export default UiDrawer;
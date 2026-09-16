// UiModal Web Component - Moved from scripts.js
class UiModal extends HTMLElement {
  connectedCallback() {
    const id = this.getAttribute('id') || '';
    const size = this.getAttribute('size') || 'md';
    const title = this.getAttribute('title') || '';
    const sub = this.getAttribute('sub') || '';
    const color = this.getAttribute('color') || 'cyan';
    const content = this.innerHTML;
    
    this.innerHTML = `
      <div class="modal-overlay fixed inset-0 z-[100] bg-slate-950/80 backdrop-blur-[8px] flex items-center justify-center p-4 transition-all duration-250" 
           :class="{'opacity-100 pointer-events-auto': activeModal === '${id}', 'opacity-0 pointer-events-none': activeModal !== '${id}'}" 
           x-show="activeModal === '${id}'" 
           @click.self="closeModal()" 
           x-transition.opacity>
        <div class="modal bg-bg-card border border-bg-border rounded-3xl w-full max-h-[90vh] overflow-hidden flex flex-col shadow-xl transition-all duration-300 transform"
             :class="{
               'max-w-[420px]': '${size}' === 'sm',
               'max-w-[560px]': '${size}' === 'md',
               'max-w-[720px]': '${size}' === 'lg'
             }"
             x-show="activeModal === '${id}'" 
             x-transition.scale>
          
          ${title ? `
            <div class="p-5 flex items-center gap-4 bg-neon-${color} text-white shrink-0">
              <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0 bg-white/20">
                <svg class="w-5.5 h-5.5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414A1 1 0 0119 9.414V19a2 2 0 01-2 2z"/></svg>
              </div>
              <div class="flex-1">
                <div class="text-[17px] font-bold">${title}</div>
                ${sub ? `<div class="text-[13px] text-white/80 mt-0.5">${sub}</div>` : ''}
              </div>
              <button class="w-9 h-9 rounded-lg border-none bg-white/20 text-white cursor-pointer grid place-items-center transition-colors duration-150 hover:bg-white/30 shrink-0" @click="closeModal()">
                <svg class="w-4.5 h-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
              </button>
            </div>
          ` : ''}
          
          <div class="p-6 overflow-y-auto flex-1 text-text-2 leading-relaxed text-sm md:text-base bg-bg-card">
            ${content}
          </div>
          
        </div>
      </div>
    `;
  }
}
customElements.define('ui-modal', UiModal);
export default UiModal;
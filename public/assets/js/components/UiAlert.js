// UiAlert Web Component - Moved from scripts.js
class UiAlert extends HTMLElement {
  connectedCallback() {
    const type = this.getAttribute('type') || 'cyan';
    const title = this.getAttribute('title') || '';
    const content = this.innerHTML;
    
    this.innerHTML = `
      <div class="rounded-xl p-4 flex items-start gap-3.5 mb-3 border-2 border-neon-${type} bg-neon-${type}/10 text-neon-${type} shadow-neon-${type}" 
           x-data="alert(true)" 
           x-show="show" 
           x-transition>
        <div class="w-5.5 h-5.5 shrink-0 mt-0.5">
          <svg class="w-5.5 h-5.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
        </div>
        <div class="flex-1 min-w-0">
          <div class="text-sm md:text-base font-bold mb-0.5">${title}</div>
          <div class="text-xs md:text-sm leading-relaxed">${content}</div>
        </div>
        <button class="w-6 h-6 rounded-md border-none bg-transparent cursor-pointer text-current flex items-center justify-center opacity-60 transition-opacity duration-150 hover:opacity-100 shrink-0" @click="close()">
          <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
      </div>
    `;
  }
}
customElements.define('ui-alert', UiAlert);
export default UiAlert;
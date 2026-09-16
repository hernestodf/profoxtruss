// UiAccordion Web Component - Moved from scripts.js
class UiAccordion extends HTMLElement {
  connectedCallback() {
    const title = this.getAttribute('title') || '';
    const color = this.getAttribute('color') || 'cyan';
    const isOpen = this.hasAttribute('open');
    const content = this.innerHTML;
    
    this.className = "block mb-3";
    this.innerHTML = `
      <div class="border-2 border-bg-border-sub rounded-xl overflow-hidden bg-bg-card" x-data="accordion(${isOpen})" :class="{'border-neon-${color}': open}">
        <div class="flex items-center justify-between p-4 bg-bg-surface hover:bg-bg-hover cursor-pointer transition-colors duration-150" @click="toggle()">
          <div class="text-sm font-semibold flex items-center gap-2.5">
            <div class="w-[22px] h-[22px] rounded-md grid place-items-center shrink-0" style="background:var(--neon-${color})">
              <svg class="w-3.5 h-3.5 text-white" fill="none" viewBox="0 0 24 24" stroke="white" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
            </div>
            <span>${title}</span>
          </div>
          <svg class="w-5 h-5 text-text-3 transition-transform duration-250" :class="{'rotate-180': open}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
          </svg>
        </div>
        <div class="bg-bg-card" x-show="open" x-transition>
          <div class="p-5 text-text-2 leading-relaxed text-sm md:text-base">${content}</div>
        </div>
      </div>
    `;
  }
}
customElements.define('ui-accordion', UiAccordion);
export default UiAccordion;
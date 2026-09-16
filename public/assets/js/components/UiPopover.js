// UiPopover Web Component - Moved from scripts.js
class UiPopover extends HTMLElement {
  connectedCallback() {
    const trigger = this.getAttribute('trigger') || 'Gatilho';
    const color = this.getAttribute('color') || 'cyan';
    const isGhost = this.hasAttribute('ghost');
    const content = this.innerHTML;
    
    this.className = "relative inline-flex";
    this.innerHTML = `
      <div x-data="popover" @click.outside="close()" class="relative inline-flex">
        <button class="btn ${isGhost ? 'btn-ghost' : 'btn-' + color}" @click="toggle()">${trigger}</button>
        <div class="popover pop-bottom absolute z-40 bg-bg-elevated border-2 border-neon-cyan rounded-xl p-4 shadow-neon-cyan min-w-[240px] max-w-[320px] left-1/2 -translate-x-1/2 transition-all duration-200 ease-[cubic-bezier(.34,1.4,.64,1)]" 
              style="top: calc(100% + 12px)" 
             :class="{'opacity-100 pointer-events-auto scale-100 translate-y-0': open, 'opacity-0 pointer-events-none scale-95 translate-y-2': !open}"
             x-show="open" 
             x-transition>
          ${content}
        </div>
      </div>
    `;
  }
}
customElements.define('ui-popover', UiPopover);
export default UiPopover;
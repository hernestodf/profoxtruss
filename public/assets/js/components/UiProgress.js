// UiProgress Web Component - Moved from scripts.js
class UiProgress extends HTMLElement {
  connectedCallback() {
    const value = this.getAttribute('value') || '0';
    const color = this.getAttribute('color') || 'cyan';
    const title = this.getAttribute('title') || '';
    
    this.innerHTML = `
      <div class="mb-4">
        <div class="flex justify-between text-sm text-text-2 mb-2 font-medium">
          <span>${title}</span>
          <span class="text-neon-${color} font-bold">${value}%</span>
        </div>
        <div class="h-2.5 bg-bg-surface rounded-full overflow-hidden border border-bg-border-sub">
          <div class="h-full rounded-full bg-neon-${color} shadow-neon-${color} transition-all duration-[1.3s] ease-[cubic-bezier(.4,0,.2,1)]" 
               :style="animateProgress ? 'width: ${value}%' : 'width: 0%'">
          </div>
        </div>
      </div>
    `;
  }
}
customElements.define('ui-progress', UiProgress);
export default UiProgress;
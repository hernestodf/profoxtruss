// UiStatCard Web Component - Moved from scripts.js
class UiStatCard extends HTMLElement {
  connectedCallback() {
    const value = this.getAttribute('value') || '';
    const label = this.getAttribute('label') || '';
    const color = this.getAttribute('color') || 'cyan';
    const icon = this.innerHTML;
    
    this.innerHTML = `
      <div class="bg-bg-card border-2 border-bg-border-sub rounded-2xl p-6 text-center shadow-sm h-full">
        <div class="w-14 h-14 rounded-2xl grid place-items-center bg-neon-${color} shadow-neon-${color} text-white mx-auto mb-3.5">
          ${icon}
        </div>
        <div class="text-3xl font-extrabold text-text-1 tracking-tight leading-none mb-1">${value}</div>
        <div class="text-xs text-text-3 font-semibold">${label}</div>
      </div>
    `;
  }
}
customElements.define('ui-stat-card', UiStatCard);
export default UiStatCard;
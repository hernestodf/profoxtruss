// UiCard Web Component - Moved from scripts.js
class UiCard extends HTMLElement {
  connectedCallback() {
    const title = this.getAttribute('title') || '';
    const color = this.getAttribute('color') || '';
    const glow = this.hasAttribute('glow');
    
    const footerEl = this.querySelector('[slot="footer"]');
    const footerHTML = footerEl ? footerEl.outerHTML : '';
    if (footerEl) footerEl.remove();
    
    const content = this.innerHTML;
    
    const borderColorClass = glow && color ? `border-neon-${color}` : 'border-bg-border-sub';
    const shadowClass = glow && color ? `shadow-neon-${color}` : 'shadow-sm';
    const titleColorClass = color ? `text-neon-${color}` : 'text-text-1';
    
    this.innerHTML = `
      <div class="bg-bg-card border-2 ${borderColorClass} rounded-2xl overflow-hidden ${shadowClass} flex flex-col h-full">
        ${title ? `
          <div class="px-5 py-4 bg-bg-surface border-b border-bg-border-sub font-bold ${titleColorClass}">
            ${title}
          </div>
        ` : ''}
        <div class="p-5 flex-1 text-text-2 leading-relaxed text-sm md:text-base">
          ${content}
        </div>
        ${footerHTML ? `
          <div class="px-5 py-3.5 bg-bg-surface border-t border-bg-border-sub flex gap-2.5">
            ${footerHTML}
          </div>
        ` : ''}
      </div>
    `;
  }
}
customElements.define('ui-card', UiCard);
export default UiCard;
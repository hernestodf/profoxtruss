// UiListItem Web Component - Moved from scripts.js
class UiListItem extends HTMLElement {
  connectedCallback() {
    const title = this.getAttribute('title') || '';
    const desc = this.getAttribute('desc') || '';
    const color = this.getAttribute('color') || 'cyan';
    const badgeText = this.getAttribute('badge-text') || '';
    const badgeColor = this.getAttribute('badge-color') || 'cyan';
    const badgeSize = this.getAttribute('badge-size') || 'sm';
    const clickAction = this.getAttribute('@click') || '';
    const icon = this.innerHTML;

    this.innerHTML = `
      <div class="flex items-center gap-3.5 p-4 border-l-4 border-transparent hover:bg-bg-surface hover:border-neon-cyan cursor-pointer transition-all duration-150"
           ${clickAction ? `@click="${clickAction}"` : ''}>
        <div class="w-10.5 h-10.5 rounded-xl grid place-items-center bg-neon-${color} shadow-neon-${color} text-white shrink-0">
          ${icon}
        </div>
        <div class="flex-1 min-w-0">
          <div class="text-[14.5px] font-semibold text-text-1 mb-0.5">${title}</div>
          <div class="text-xs text-text-3">${desc}</div>
        </div>
        ${badgeText ? `
          <div class="shrink-0">
            <span class="badge badge-${badgeSize} ${badgeColor}">
              ${badgeText}
            </span>
          </div>
        ` : ''}
      </div>
    `;
  }
}
customElements.define('ui-list-item', UiListItem);
export default UiListItem;
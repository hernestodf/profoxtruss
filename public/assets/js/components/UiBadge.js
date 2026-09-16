// UiBadge Web Component - Moved from scripts.js
class UiBadge extends HTMLElement {
  connectedCallback() {
    const color = this.getAttribute('color') || 'cyan';
    const size = this.getAttribute('size') || ''; 
    const content = this.innerHTML;
    
    const sizeClass = size ? `badge-${size}` : '';
    this.innerHTML = `
      <span class="badge ${sizeClass} ${color}">
        ${content}
      </span>
    `;
  }
}
customElements.define('ui-badge', UiBadge);
export default UiBadge;
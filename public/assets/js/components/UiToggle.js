// UiToggle Web Component - Moved from scripts.js
class UiToggle extends HTMLElement {
  connectedCallback() {
    const label = this.getAttribute('label') || '';
    const color = this.getAttribute('color') || 'cyan';
    const size = this.getAttribute('size') || 's'; 
    const border = this.hasAttribute('border-bottom');
    const textLight = this.hasAttribute('text-light');
    
    let attrs = '';
    for (let i = 0; i < this.attributes.length; i++) {
      const attr = this.attributes[i];
      if (!['label', 'color', 'size', 'border-bottom', 'text-light', 'class', 'style'].includes(attr.name)) {
        attrs += ` ${attr.name}="${attr.value}"`;
      }
    }
    
    const containerClass = border ? `flex items-center justify-between py-2.5 border-b border-bg-border-sub` : `flex items-center justify-between py-2.5`;
    const labelColorClass = textLight ? 'text-white' : 'text-text-2';
    
    this.innerHTML = `
      <div class="${containerClass}">
        ${label ? `<div class="text-sm font-medium ${labelColorClass}">${label}</div>` : ''}
        <label class="${size === 's' ? 'tog-s' : 'tog'} ${color}">
          <input type="checkbox" ${attrs}/>
          <div class="tog-track"></div>
          <div class="tog-thumb"></div>
        </label>
      </div>
    `;
  }
}
customElements.define('ui-toggle', UiToggle);
export default UiToggle;
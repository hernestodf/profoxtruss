// UiCheckbox Web Component - Moved from scripts.js
class UiCheckbox extends HTMLElement {
  connectedCallback() {
    const title = this.getAttribute('title') || '';
    const desc = this.getAttribute('desc') || '';
    const model = this.getAttribute('x-model') || '';
    
    let attrs = '';
    for (let i = 0; i < this.attributes.length; i++) {
      const attr = this.attributes[i];
      if (!['title', 'desc', 'class', 'style'].includes(attr.name)) {
        attrs += ` ${attr.name}="${attr.value}"`;
      }
    }
    
    this.innerHTML = `
      <label class="flex items-start gap-3.5 cursor-pointer select-none">
        <input type="checkbox" class="hidden" ${attrs}/>
        <div class="w-5 h-5 rounded-md border-2 border-bg-border bg-bg-surface flex items-center justify-center shrink-0 mt-0.5 transition-all" 
             :class="{'border-neon-cyan bg-neon-cyan shadow-neon-cyan': ${model}}">
          <svg class="w-3.5 h-3.5 text-white transition-opacity" 
               :class="{'opacity-100': ${model}, 'opacity-0': !${model}}" 
               fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
          </svg>
        </div>
        <div class="text-[14.5px] text-text-2 leading-tight">
          ${title ? `<strong class="text-text-1 font-semibold block">${title}</strong>` : ''}
          ${desc ? `<span class="text-xs text-text-4">${desc}</span>` : ''}
        </div>
      </label>
    `;
  }
}
customElements.define('ui-checkbox', UiCheckbox);
export default UiCheckbox;
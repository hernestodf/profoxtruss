// UiRadio Web Component - Moved from scripts.js
class UiRadio extends HTMLElement {
  connectedCallback() {
    const title = this.getAttribute('title') || '';
    const desc = this.getAttribute('desc') || '';
    const model = this.getAttribute('x-model') || '';
    const value = this.getAttribute('value') || '';
    
    let attrs = '';
    for (let i = 0; i < this.attributes.length; i++) {
      const attr = this.attributes[i];
      if (!['title', 'desc', 'class', 'style'].includes(attr.name)) {
        attrs += ` ${attr.name}="${attr.value}"`;
      }
    }
    
    this.innerHTML = `
      <label class="flex items-start gap-3.5 cursor-pointer select-none">
        <input type="radio" class="hidden" ${attrs}/>
        <div class="w-5 h-5 rounded-full border-2 border-bg-border bg-bg-surface flex items-center justify-center shrink-0 mt-0.5 transition-all" 
             :class="{'border-neon-cyan bg-neon-cyan/20 shadow-neon-cyan': ${model} === '${value}'}">
          <div class="w-2.5 h-2.5 rounded-full bg-neon-cyan transition-all" 
               :class="{'opacity-100 scale-100': ${model} === '${value}', 'opacity-0 scale-40': ${model} !== '${value}'}"></div>
        </div>
        <div class="text-[14.5px] text-text-2 leading-tight">
          ${title ? `<strong class="text-text-1 font-semibold block">${title}</strong>` : ''}
          ${desc ? `<span class="text-xs text-text-4">${desc}</span>` : ''}
        </div>
      </label>
    `;
  }
}
customElements.define('ui-radio', UiRadio);
export default UiRadio;
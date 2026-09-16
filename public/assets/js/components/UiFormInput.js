// UiFormInput Web Component - Moved from scripts.js
class UiFormInput extends HTMLElement {
  connectedCallback() {
    const label = this.getAttribute('label') || '';
    const type = this.getAttribute('type') || 'text';
    const placeholder = this.getAttribute('placeholder') || '';
    const value = this.getAttribute('value') || '';
    const disabled = this.hasAttribute('disabled') ? 'disabled' : '';
    const rows = this.getAttribute('rows') || '';
    const validationState = this.getAttribute('validation-state') || ''; 
    const validationMsg = this.getAttribute('validation-msg') || '';
    const helpMsg = this.getAttribute('help-msg') || '';
    const dark = this.hasAttribute('dark');
    
    const iconEl = this.querySelector('[slot="icon"]');
    const iconHTML = iconEl ? iconEl.outerHTML : '';
    
    let controlHTML = '';
    const isSelect = type === 'select';
    const isTextarea = type === 'textarea';
    
    let attrs = '';
    for (let i = 0; i < this.attributes.length; i++) {
      const attr = this.attributes[i];
      if (!['label', 'type', 'placeholder', 'value', 'disabled', 'rows', 'validation-state', 'validation-msg', 'help-msg', 'dark', 'class', 'style'].includes(attr.name)) {
        attrs += ` ${attr.name}="${attr.value}"`;
      }
    }
    
    let borderStyle = '';
    let validationMsgHTML = '';
    
    if (validationState === 'success') {
      borderStyle = `!border-neon-green focus:!ring-neon-green/20`;
      validationMsgHTML = `<div class="text-[12.5px] mt-1.5 flex items-center gap-1 text-neon-green"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" class="w-3.5 h-3.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>${validationMsg}</div>`;
    } else if (validationState === 'error') {
      borderStyle = `!border-neon-red focus:!ring-neon-red/20`;
      validationMsgHTML = `<div class="text-[12.5px] mt-1.5 flex items-center gap-1 text-neon-red"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" class="w-3.5 h-3.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>${validationMsg}</div>`;
    } else if (helpMsg) {
      validationMsgHTML = `<div class="text-[12.5px] mt-1.5 flex items-center gap-1 text-text-4"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" class="w-3.5 h-3.5"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>${helpMsg}</div>`;
    }
    
    let inlineStyle = '';
    if (validationState === 'success') {
      inlineStyle = `style="box-shadow:0 0 0 4px var(--color-neon-green-glow)"`;
    } else if (validationState === 'error') {
      inlineStyle = `style="box-shadow:0 0 0 4px var(--color-neon-red-glow)"`;
    }
    
    const flClass = dark ? 'fl !text-white/50' : 'fl';
    const fiClass = dark ? `fi !bg-white/10 !border-white/20 !text-white placeholder:text-white/40 ${borderStyle}` : `fi ${borderStyle}`;
    
    if (isSelect) {
      const options = this.querySelectorAll('option');
      let optionsHTML = '';
      options.forEach(opt => {
        optionsHTML += `<option value="${opt.value}" ${opt.hasAttribute('selected') ? 'selected' : ''}>${opt.textContent}</option>`;
      });
      controlHTML = `<select class="${fiClass}" ${disabled} ${attrs}>${optionsHTML}</select>`;
    } else if (isTextarea) {
      controlHTML = `<textarea class="${fiClass}" rows="${rows || 4}" placeholder="${placeholder}" ${disabled} ${attrs}>${value}</textarea>`;
    } else {
      controlHTML = `
        <div class="relative">
          ${iconHTML ? `<div class="absolute left-3.5 top-1/2 -translate-y-1/2 text-text-3">${iconHTML}</div>` : ''}
          <input class="${fiClass} ${iconHTML ? '!pl-11' : ''}" 
                 type="${type}" 
                 placeholder="${placeholder}" 
                 value="${value}" 
                 ${disabled} 
                 ${inlineStyle}
                 ${attrs} />
        </div>
      `;
    }
    
    this.innerHTML = `
      <div class="fg">
        ${label ? `<label class="${flClass}">${label}</label>` : ''}
        ${controlHTML}
        ${validationMsgHTML}
      </div>
    `;
  }
}
customElements.define('ui-form-input', UiFormInput);
export default UiFormInput;
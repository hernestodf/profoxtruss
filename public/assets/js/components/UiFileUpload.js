// UiFileUpload Web Component - Moved from scripts.js
class UiFileUpload extends HTMLElement {
  connectedCallback() {
    const initialFilesStr = this.getAttribute('files') || '[]';
    
    this.innerHTML = `
      <div class="bg-bg-card border border-bg-border-sub rounded-2xl shadow-sm mb-4" 
           x-data="fileUpload(${initialFilesStr})">
        <div class="p-5">
          <div class="border-3 border-dashed border-neon-cyan rounded-2xl p-10 text-center cursor-pointer transition-all bg-bg-surface hover:border-neon-blue hover:bg-bg-hover hover:shadow-neon-cyan" 
               @click="$refs.fileInput.click()">
            <div class="w-14 h-14 rounded-2xl bg-neon-cyan flex items-center justify-center mx-auto mb-4 shadow-neon-cyan text-white">
              <svg class="w-6.5 h-6.5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
              </svg>
            </div>
            <div class="text-base font-bold mb-1.5 text-text-1">Arraste arquivos aqui ou clique para selecionar</div>
            <div class="text-xs text-text-3">PNG, JPG, PDF até 10MB <span class="text-neon-cyan font-bold underline cursor-pointer">ou clique para navegar</span></div>
            <input type="file" x-ref="fileInput" class="hidden" multiple @change="addFiles($event)"/>
          </div>
          <div class="flex flex-wrap gap-2.5 mt-4" x-show="filesList.length > 0">
            <template x-for="(file, index) in filesList" :key="file.name">
              <div class="flex items-center gap-2.5 p-2.5 bg-bg-surface border border-bg-border-sub rounded-xl" x-transition>
                <div class="w-9 h-9 rounded-lg grid place-items-center bg-neon-cyan text-white shadow-neon-cyan shrink-0" 
                     :class="{'!bg-neon-blue': file.type === 'img'}">
                  <template x-if="file.type === 'pdf'">
                    <svg class="w-[18px] h-[18px] text-white" fill="none" viewBox="0 0 24 24" stroke="white" stroke-width="2">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414A1 1 0 0119 9.414V19a2 2 0 01-2 2z"/>
                    </svg>
                  </template>
                  <template x-if="file.type === 'img'">
                    <svg class="w-[18px] h-[18px] text-white" fill="none" viewBox="0 0 24 24" stroke="white" stroke-width="2">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                  </template>
                </div>
                <div>
                  <div class="text-[13.5px] font-semibold text-text-1" x-text="file.name"></div>
                  <div class="text-[11.5px] text-text-3 font-mono" x-text="file.size"></div>
                </div>
                <button class="w-6 h-6 rounded-md bg-transparent border border-bg-border text-text-3 cursor-pointer grid place-items-center transition-colors duration-150 hover:bg-neon-red hover:border-neon-red hover:text-white shrink-0 ml-auto" 
                        @click="removeFile(index)">
                  <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                  </svg>
                </button>
              </div>
            </template>
          </div>
        </div>
      </div>
    `;
  }
}
customElements.define('ui-file-upload', UiFileUpload);
export default UiFileUpload;
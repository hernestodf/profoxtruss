// UiToastContainer Web Component - Moved from scripts.js
class UiToastContainer extends HTMLElement {
  connectedCallback() {
    this.innerHTML = `
      <div id="toast-container" 
           class="fixed bottom-6 right-6 z-50 flex flex-col-reverse gap-3 pointer-events-none" 
           x-show="toasts.length > 0" 
           x-transition>
        <template x-for="toast in toasts" :key="toast.id">
          <div class="toast flex items-start gap-3.5 min-w-[320px] max-w-[440px] p-4 rounded-xl bg-bg-elevated border-2 shadow-lg pointer-events-auto cursor-pointer animate-[toastIn_0.35s_cubic-bezier(.34,1.56,.64,1)_both]" 
               :class="{
                 'border-neon-red text-neon-red': toast.type === 'red',
                 'border-neon-green text-neon-green': toast.type === 'green',
                 'border-neon-cyan text-neon-cyan': toast.type === 'cyan'
               }"
               @click="removeToast(toast.id)" 
               x-transition>
            <div class="w-9 h-9 rounded-lg grid place-items-center shrink-0" 
                 :class="{
                   'bg-neon-red text-white': toast.type === 'red',
                   'bg-neon-green text-white': toast.type === 'green',
                   'bg-neon-cyan text-white': toast.type === 'cyan'
                 }">
              <template x-if="toast.type === 'red'">
                <svg class="w-4.5 h-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
              </template>
              <template x-if="toast.type === 'green'">
                <svg class="w-4.5 h-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
              </template>
              <template x-if="toast.type === 'cyan'">
                <svg class="w-4.5 h-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
              </template>
            </div>
            <div class="flex-1">
              <div class="text-sm font-bold mb-0.5 text-text-1" x-text="toast.title"></div>
              <div class="text-xs text-text-2" x-show="toast.msg" x-text="toast.msg"></div>
            </div>
            <button class="w-6 h-6 rounded-md flex items-center justify-center shrink-0 mt-0.5 text-text-3 hover:bg-bg-hover hover:text-text-1 border-none bg-transparent cursor-pointer transition-colors duration-150" @click.stop="removeToast(toast.id)">
              <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
          </div>
        </template>
      </div>
    `;
  }
}
customElements.define('ui-toast-container', UiToastContainer);
export default UiToastContainer;
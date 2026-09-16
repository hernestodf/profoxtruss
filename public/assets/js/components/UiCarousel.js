// UiCarousel Web Component - Moved from scripts.js
class UiCarousel extends HTMLElement {
  connectedCallback() {
    this.innerHTML = `
      <div class="carousel relative overflow-hidden rounded-2xl border-2 border-bg-border-sub" x-data="carousel(3)">
        <div class="carousel-track flex transition-transform duration-400 ease-[cubic-bezier(.4,0,.2,1)]" :style="'transform: translateX(-' + (index * 100) + '%)'">
          <div class="carousel-slide min-w-full p-6 md:p-10 flex items-center justify-center text-center bg-neon-red">
            <div class="max-w-[500px]">
              <div class="text-xl md:text-3xl font-bold text-white mb-3">Alerta Vermelho</div>
              <div class="text-sm md:text-base text-white/80 leading-relaxed mb-5">Use para notificações críticas que requerem atenção imediata.</div>
              <button class="btn btn-red" @click="showToast('red', 'Crítico', 'Ação no Slide 1')">Ver Detalhes</button>
            </div>
          </div>
          <div class="carousel-slide min-w-full p-6 md:p-10 flex items-center justify-center text-center bg-neon-cyan">
            <div class="max-w-[500px]">
              <div class="text-xl md:text-3xl font-bold text-white mb-3">Informação Cyan</div>
              <div class="text-sm md:text-base text-white/80 leading-relaxed mb-5">Indicadores de progresso e métricas importantes.</div>
              <button class="btn btn-cyan" @click="showToast('cyan', 'Informação', 'Ação no Slide 2')">Explorar</button>
            </div>
          </div>
          <div class="carousel-slide min-w-full p-6 md:p-10 flex items-center justify-center text-center bg-neon-green">
            <div class="max-w-[500px]">
              <div class="text-xl md:text-3xl font-bold text-white mb-3">Sucesso Verde</div>
              <div class="text-sm md:text-base text-white/80 leading-relaxed mb-5">Confirme operações completadas.</div>
              <button class="btn btn-green" @click="showToast('green', 'Sucesso', 'Ação no Slide 3')">Continuar</button>
            </div>
          </div>
        </div>
        <button class="carousel-nav carousel-prev absolute top-1/2 left-4 -translate-y-1/2 w-11 h-11 rounded-full bg-white/20 border-2 border-white/30 text-white cursor-pointer grid place-items-center transition-colors duration-150 hover:bg-white/30 z-5 backdrop-blur-[4px]" @click="prev()">
          <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
        </button>
        <button class="carousel-nav carousel-next absolute top-1/2 right-4 -translate-y-1/2 w-11 h-11 rounded-full bg-white/20 border-2 border-white/30 text-white cursor-pointer grid place-items-center transition-colors duration-150 hover:bg-white/30 z-5 backdrop-blur-[4px]" @click="next()">
          <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
        </button>
        <div class="carousel-dots flex justify-center gap-2 mt-4 absolute bottom-4 left-1/2 -translate-x-1/2">
          <button class="carousel-dot w-2.5 h-2.5 rounded-full bg-white/40 cursor-pointer border-none transition-all duration-200" :class="{'!bg-white shadow-[0_0_15px_#fff] scale-130': index === 0}" @click="goTo(0)"></button>
          <button class="carousel-dot w-2.5 h-2.5 rounded-full bg-white/40 cursor-pointer border-none transition-all duration-200" :class="{'!bg-white shadow-[0_0_15px_#fff] scale-130': index === 1}" @click="goTo(1)"></button>
          <button class="carousel-dot w-2.5 h-2.5 rounded-full bg-white/40 cursor-pointer border-none transition-all duration-200" :class="{'!bg-white shadow-[0_0_15px_#fff] scale-130': index === 2}" @click="goTo(2)"></button>
        </div>
      </div>
    `;
  }
}
customElements.define('ui-carousel', UiCarousel);
export default UiCarousel;
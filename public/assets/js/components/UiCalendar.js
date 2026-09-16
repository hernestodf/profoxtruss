// UiCalendar Web Component - Moved from scripts.js
class UiCalendar extends HTMLElement {
  connectedCallback() {
    const month = this.getAttribute('month') || 'Março 2026';
    const offset = parseInt(this.getAttribute('offset') || '6');
    const days = parseInt(this.getAttribute('days') || '14');
    const prevMonthStart = parseInt(this.getAttribute('prev-month-start') || '23');
    const today = parseInt(this.getAttribute('today') || '10');
    const eventsData = JSON.parse(this.getAttribute('events') || '[]');

    let prevMonthCells = '';
    for (let i = 0; i < offset; i++) {
      prevMonthCells += `<div class="cal-cell cal-other"><div class="cal-day">${prevMonthStart + i}</div></div>`;
    }

    let currentMonthCells = '';
    for (let d = 1; d <= days; d++) {
      const isToday = d === today;
      const dayEvents = eventsData.filter(e => e.day === d);
      
      let eventsHTML = '';
      let clickAction = `showToast('cyan', 'Calendário', 'Selecionado Dia ${d}')`;
      
      if (dayEvents.length > 0) {
        eventsHTML = '<div class="cal-events">';
        dayEvents.forEach(e => {
          eventsHTML += `<div class="cal-event bg-neon-${e.type} text-white">${e.label}</div>`;
        });
        eventsHTML += '</div>';
        
        const mainEvent = dayEvents[0];
        clickAction = `showToast('${mainEvent.type}', '${mainEvent.title || 'Calendário'}', '${mainEvent.toast || 'Dia ' + d}')`;
      }
      
      const todayClass = isToday ? 'cal-today' : '';
      currentMonthCells += `
        <div class="cal-cell ${todayClass}" @click="${clickAction}">
          <div class="cal-day">${d}</div>
          ${eventsHTML}
        </div>
      `;
    }

    this.innerHTML = `
      <div class="bg-bg-card border border-bg-border-sub rounded-2xl shadow-sm overflow-hidden">
        <div class="p-5 bg-neon-cyan flex items-center justify-between">
          <div class="text-base md:text-lg font-bold text-white">${month}</div>
          <div class="flex gap-1.5 shrink-0">
            <button class="w-[34px] h-[34px] rounded-lg bg-white/20 border border-white/30 text-white cursor-pointer grid place-items-center hover:bg-white/30" 
                    @click="showToast('cyan', 'Calendário', 'Mês anterior')">
              <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
              </svg>
            </button>
            <button class="w-[34px] h-[34px] rounded-lg bg-white/20 border border-white/30 text-white cursor-pointer grid place-items-center hover:bg-white/30" 
                    @click="showToast('cyan', 'Calendário', 'Próximo mês')">
              <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
              </svg>
            </button>
          </div>
        </div>
        <div class="grid grid-cols-7 border-t border-l border-bg-border-sub">
          <div class="p-2 text-center text-xs font-bold uppercase tracking-wider text-text-3 bg-bg-dark border-r border-b border-bg-border-sub">Dom</div>
          <div class="p-2 text-center text-xs font-bold uppercase tracking-wider text-text-3 bg-bg-dark border-r border-b border-bg-border-sub">Seg</div>
          <div class="p-2 text-center text-xs font-bold uppercase tracking-wider text-text-3 bg-bg-dark border-r border-b border-bg-border-sub">Ter</div>
          <div class="p-2 text-center text-xs font-bold uppercase tracking-wider text-text-3 bg-bg-dark border-r border-b border-bg-border-sub">Qua</div>
          <div class="p-2 text-center text-xs font-bold uppercase tracking-wider text-text-3 bg-bg-dark border-r border-b border-bg-border-sub">Qui</div>
          <div class="p-2 text-center text-xs font-bold uppercase tracking-wider text-text-3 bg-bg-dark border-r border-b border-bg-border-sub">Sex</div>
          <div class="p-2 text-center text-xs font-bold uppercase tracking-wider text-text-3 bg-bg-dark border-r border-b border-bg-border-sub">Sáb</div>
          
          ${prevMonthCells}
          ${currentMonthCells}
        </div>
      </div>
    `;
  }
}
customElements.define('ui-calendar', UiCalendar);
export default UiCalendar;
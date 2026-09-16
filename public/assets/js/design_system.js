// ============ COMPONENTES REUTILIZÁVEIS E WEB COMPONENTS ============

document.addEventListener('alpine:init', () => {

  // 1. GERENCIADOR DE LAYOUT GLOBAL (Gavetas, Sidebar e Toasts)
  Alpine.data('layout', () => ({
    // ---- SIDEBAR STATES ----
    sidebarOpen: false,   // Mobile off-canvas menu drawer open state
    sidebarMini: false,   // Desktop collapsed sidebar state
    activeSubmenu: null,  // Tracks which sidebar submenu is expanded ('ui', 'forms', etc.)
    
    // ---- MAIN NAVIGATION ----
    activeSection: 'dashboard',
    breadcrumbText: 'Dashboard',
    
    // ---- LEFT DRAWER (Filters) ----
    leftCanvasOpen: false,
    filterPeriod: 'Hoje',
    filterPrice: 2500,
    filterActiveRentals: true,
    filterAvailable: false,
    
    // ---- RIGHT DRAWER (Account / Profile) ----
    rightCanvasOpen: false,
    activeProfileTab: 'perfil',
    
    // ---- MODALS ----
    activeModal: null,    // Stores ID of current open modal (e.g. 'modal-danger')
    
    // ---- TOAST NOTIFICATIONS ----
    toasts: [],
    
    // ---- PROGRESS BARS ANIMATION ----
    animateProgress: false,
    
    // ---- THEME ----
    theme: localStorage.getItem('theme') || '',
    
    // ---- INITIALIZATION ----
    init() {
      // Expor funções globalmente para views que chamam window.openModal etc.
      window.openModal  = this.openModal.bind(this);
      window.closeModal = this.closeModal.bind(this);
      window.showToast  = this.showToast.bind(this);
      
      document.documentElement.dataset.theme = this.theme;
      this.$watch('theme', value => {
        document.documentElement.dataset.theme = value;
      });
      // Trigger progress bar animations after layout renders
      setTimeout(() => {
        this.animateProgress = true;
      }, 300);

      // Watcher to keep the breadcrumb updated reactively when sections change
      this.$watch('activeSection', value => {
        const titles = {
          dashboard: 'Dashboard',
          accordions: 'Accordions',
          alerts: 'Alerts',
          buttons: 'Buttons',
          badges: 'Badges',
          cards: 'Cards',
          carousel: 'Carousel',
          icons: 'Icons',
          listitems: 'List Items',
          modals: 'Modals',
          progress: 'Progress',
          popovers: 'Popovers',
          tabs: 'Tabs',
          tables: 'Tables',
          tooltips: 'Tooltips',
          typography: 'Typography',
          mobilemenu: 'Mobile Bottom Menu',
          forminputs: 'Form Inputs',
          checkboxradio: 'Checkbox & Radio',
          fileinput: 'File Input',
          validations: 'Validations',
          datetime: 'Date Time',
          invoice: 'Invoice',
          calendar: 'Calendar'
        };
        this.breadcrumbText = titles[value] || value;
      });
    },
    
    // ---- SIDEBAR ACTIONS ----
    toggleSidebar() {
      if (window.innerWidth < 1024) {
        this.sidebarOpen = !this.sidebarOpen;
      } else {
        this.sidebarMini = !this.sidebarMini;
      }
    },
    
    toggleSubmenu(id) {
      if (this.sidebarMini && window.innerWidth >= 1024) return;
      this.activeSubmenu = this.activeSubmenu === id ? null : id;
    },
    
    showSection(id) {
      this.activeSection = id;
      if (window.innerWidth < 1024) {
        this.sidebarOpen = false; // Close drawer on mobile upon selection
      }
    },
    
    // ---- MODAL ACTIONS ----
    openModal(id) {
      this.activeModal = id;
      document.body.style.overflow = 'hidden';
    },
    
    closeModal() {
      this.activeModal = null;
      document.body.style.overflow = '';
    },
    
    // ---- TOAST ACTIONS ----
    showToast(type, title, msg = '') {
      const id = Date.now() + Math.random().toString(36).substr(2, 9);
      this.toasts.push({ id, type, title, msg });
      
      // Auto close toast after 4 seconds
      setTimeout(() => {
        this.removeToast(id);
      }, 4000);
    },
    
    removeToast(id) {
      this.toasts = this.toasts.filter(t => t.id !== id);
    },

    setTheme(name) {
      this.theme = name;
      localStorage.setItem('theme', name);
    }
  }));

  // 2. ALPINE DATA CONSTRUCTORS (COMPORTAMENTO DOS COMPONENTES)
  Alpine.data('accordion', (initialOpen = false) => ({
    open: initialOpen,
    toggle() { this.open = !this.open; }
  }));

  Alpine.data('alert', (initialShow = true) => ({
    show: initialShow,
    close() { this.show = false; }
  }));

  Alpine.data('popover', () => ({
    open: false,
    toggle() { this.open = !this.open; },
    close() { this.open = false; }
  }));

  Alpine.data('carousel', (slidesCount = 3) => ({
    index: 0,
    count: slidesCount,
    next() { this.index = (this.index + 1) % this.count; },
    prev() { this.index = (this.index - 1 + this.count) % this.count; },
    goTo(i) { this.index = i; }
  }));

  Alpine.data('coloredTabs', (initialTab = 0, initialColor = 'red') => ({
    activeTab: initialTab,
    tabColor: initialColor,
    switchTab(index, color) {
      this.activeTab = index;
      this.tabColor = color;
    }
  }));

  Alpine.data('fileUpload', (initialFiles = []) => ({
    filesList: initialFiles,
    addFiles(event) {
      Array.from(event.target.files).forEach(f => {
        this.filesList.push({
          name: f.name,
          size: (f.size / (1024*1024)).toFixed(1) + ' MB',
          type: f.type.includes('pdf') ? 'pdf' : 'img'
        });
      });
    },
    removeFile(index) {
      this.filesList.splice(index, 1);
    }
  }));

});

// ============ 3. WEB COMPONENTS (CUSTOM ELEMENTS SEM SHADOW DOM) ============

// Helper to initialize Alpine on dynamically created content
function initAlpineElement(el) {
  if (window.Alpine && Alpine.version) {
    Alpine.initTree(el);
  } else {
    const check = () => {
      if (window.Alpine && Alpine.version) {
        Alpine.initTree(el);
      } else {
        requestAnimationFrame(check);
      }
    };
    check();
  }
}

// Helper to access layout Alpine data from custom elements
function withLayout(fn) {
  const tryIt = () => {
    if (window.Alpine && Alpine.version && Alpine.$data(document.body)) {
      fn(Alpine.$data(document.body));
    } else {
      requestAnimationFrame(tryIt);
    }
  };
  requestAnimationFrame(tryIt);
}

// A. COMPONENTE SIDEBAR DA APLICAÇÃO
class AppSidebar extends HTMLElement {
  connectedCallback() {
    this.render();
    this._onReady(() => {
      this._attachBehaviors();
    });
  }

  render() {
    this.innerHTML = `
      <aside id="sidebar" 
             class="fixed top-0 bottom-0 left-0 w-[260px] bg-slate-950 z-50 flex flex-col overflow-hidden transition-all duration-300 shadow-[4px_0_20px_rgba(0,0,0,0.3)]">
        <!-- Sidebar Head -->
        <div class="px-3.5 h-[60px] border-b border-white/10 flex items-center justify-between shrink-0">
          <div class="flex items-center gap-2.5 whitespace-nowrap overflow-visible">
            <div class="w-[34px] h-[34px] rounded-lg bg-neon-cyan flex items-center justify-center shadow-neon-cyan shrink-0 cursor-pointer" data-as-toggle-logo>
              <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0H5m5-4h4"/></svg>
            </div>
            <div class="transition-all duration-200" data-as-brand>
              <div class="text-sm font-bold text-white leading-none">SisLoc</div>
              <div class="text-[10px] text-white/50 font-mono mt-0.5">v3.0 · White</div>
            </div>
          </div>
          <button class="w-7 h-7 rounded-md bg-neon-cyan border-none text-white cursor-pointer grid place-items-center shadow-neon-cyan transition-all duration-150 hover:brightness-110 shrink-0" data-as-toggle>
            <svg class="w-3.5 h-3.5 transition-transform duration-320" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M11 19l-7-7 7-7M18 19l-7-7 7-7"/></svg>
          </button>
        </div>

        <!-- Sidebar Scroll Navigation -->
        <div class="flex-1 overflow-y-auto overflow-x-hidden py-2 [&::-webkit-scrollbar]:w-[3px] [&::-webkit-scrollbar-thumb]:bg-white/20 [&::-webkit-scrollbar-thumb]:rounded-full">
          <nav class="py-2" data-as-nav>
            <!-- Dashboard Item -->
            <div class="flex items-center justify-between px-3.5 h-11 rounded-lg mx-2 my-0.5 cursor-pointer text-white/70 text-sm font-medium transition-all duration-150 border border-transparent hover:bg-white/10 hover:text-white"
                 data-as-section="dashboard">
              <div class="flex items-center gap-2.5 min-w-0">
                <svg class="w-[17px] h-[17px] shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/></svg>
                <span class="whitespace-nowrap transition-all duration-200">Dashboard</span>
              </div>
            </div>

            <!-- UI Elements Header -->
            <div class="text-[10px] font-bold tracking-widest text-white/40 uppercase px-4 py-3 whitespace-nowrap overflow-hidden transition-all duration-200" data-as-section-header>UI Elements</div>
            
            <!-- UI Elements Expandable Submenu -->
            <div class="relative">
              <div class="flex items-center justify-between px-3.5 h-11 rounded-lg mx-2 my-0.5 cursor-pointer text-white/70 text-sm font-medium transition-all duration-150 border border-transparent hover:bg-white/10 hover:text-white"
                   data-as-submenu="ui">
                <div class="flex items-center gap-2.5 min-w-0">
                  <svg class="w-[17px] h-[17px] shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                  <span class="whitespace-nowrap transition-all duration-200">UI Elements</span>
                </div>
                <svg class="w-3 h-3 shrink-0 text-white/40 transition-transform duration-250" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
              </div>
              <div class="overflow-hidden transition-all duration-300" data-as-submenu-body="ui">
                <div class="si flex items-center gap-2.5 pl-11 pr-3.5 h-[38px] rounded-lg mx-2 my-0.5 text-white/60 text-[13.5px] cursor-pointer transition-all duration-150 hover:bg-white/10 hover:text-white" data-as-section="accordions"><div class="w-1.5 h-1.5 rounded-full bg-white/40 shrink-0"></div>Accordions</div>
                <div class="si flex items-center gap-2.5 pl-11 pr-3.5 h-[38px] rounded-lg mx-2 my-0.5 text-white/60 text-[13.5px] cursor-pointer transition-all duration-150 hover:bg-white/10 hover:text-white" data-as-section="alerts"><div class="w-1.5 h-1.5 rounded-full bg-white/40 shrink-0"></div>Alerts</div>
                <div class="si flex items-center gap-2.5 pl-11 pr-3.5 h-[38px] rounded-lg mx-2 my-0.5 text-white/60 text-[13.5px] cursor-pointer transition-all duration-150 hover:bg-white/10 hover:text-white" data-as-section="buttons"><div class="w-1.5 h-1.5 rounded-full bg-white/40 shrink-0"></div>Buttons</div>
                <div class="si flex items-center gap-2.5 pl-11 pr-3.5 h-[38px] rounded-lg mx-2 my-0.5 text-white/60 text-[13.5px] cursor-pointer transition-all duration-150 hover:bg-white/10 hover:text-white" data-as-section="badges"><div class="w-1.5 h-1.5 rounded-full bg-white/40 shrink-0"></div>Badges</div>
                <div class="si flex items-center gap-2.5 pl-11 pr-3.5 h-[38px] rounded-lg mx-2 my-0.5 text-white/60 text-[13.5px] cursor-pointer transition-all duration-150 hover:bg-white/10 hover:text-white" data-as-section="cards"><div class="w-1.5 h-1.5 rounded-full bg-white/40 shrink-0"></div>Cards</div>
                <div class="si flex items-center gap-2.5 pl-11 pr-3.5 h-[38px] rounded-lg mx-2 my-0.5 text-white/60 text-[13.5px] cursor-pointer transition-all duration-150 hover:bg-white/10 hover:text-white" data-as-section="carousel"><div class="w-1.5 h-1.5 rounded-full bg-white/40 shrink-0"></div>Carousel</div>
                <div class="si flex items-center gap-2.5 pl-11 pr-3.5 h-[38px] rounded-lg mx-2 my-0.5 text-white/60 text-[13.5px] cursor-pointer transition-all duration-150 hover:bg-white/10 hover:text-white" data-as-section="icons"><div class="w-1.5 h-1.5 rounded-full bg-white/40 shrink-0"></div>Icons</div>
                <div class="si flex items-center gap-2.5 pl-11 pr-3.5 h-[38px] rounded-lg mx-2 my-0.5 text-white/60 text-[13.5px] cursor-pointer transition-all duration-150 hover:bg-white/10 hover:text-white" data-as-section="listitems"><div class="w-1.5 h-1.5 rounded-full bg-white/40 shrink-0"></div>List Items</div>
                <div class="si flex items-center gap-2.5 pl-11 pr-3.5 h-[38px] rounded-lg mx-2 my-0.5 text-white/60 text-[13.5px] cursor-pointer transition-all duration-150 hover:bg-white/10 hover:text-white" data-as-section="modals"><div class="w-1.5 h-1.5 rounded-full bg-white/40 shrink-0"></div>Modals</div>
                <div class="si flex items-center gap-2.5 pl-11 pr-3.5 h-[38px] rounded-lg mx-2 my-0.5 text-white/60 text-[13.5px] cursor-pointer transition-all duration-150 hover:bg-white/10 hover:text-white" data-as-section="progress"><div class="w-1.5 h-1.5 rounded-full bg-white/40 shrink-0"></div>Progress</div>
                <div class="si flex items-center gap-2.5 pl-11 pr-3.5 h-[38px] rounded-lg mx-2 my-0.5 text-white/60 text-[13.5px] cursor-pointer transition-all duration-150 hover:bg-white/10 hover:text-white" data-as-section="popovers"><div class="w-1.5 h-1.5 rounded-full bg-white/40 shrink-0"></div>Popovers</div>
                <div class="si flex items-center gap-2.5 pl-11 pr-3.5 h-[38px] rounded-lg mx-2 my-0.5 text-white/60 text-[13.5px] cursor-pointer transition-all duration-150 hover:bg-white/10 hover:text-white" data-as-section="tabs"><div class="w-1.5 h-1.5 rounded-full bg-white/40 shrink-0"></div>Tabs</div>
                <div class="si flex items-center gap-2.5 pl-11 pr-3.5 h-[38px] rounded-lg mx-2 my-0.5 text-white/60 text-[13.5px] cursor-pointer transition-all duration-150 hover:bg-white/10 hover:text-white" data-as-section="tables"><div class="w-1.5 h-1.5 rounded-full bg-white/40 shrink-0"></div>Tables</div>
                <div class="si flex items-center gap-2.5 pl-11 pr-3.5 h-[38px] rounded-lg mx-2 my-0.5 text-white/60 text-[13.5px] cursor-pointer transition-all duration-150 hover:bg-white/10 hover:text-white" data-as-section="tooltips"><div class="w-1.5 h-1.5 rounded-full bg-white/40 shrink-0"></div>Tooltips</div>
                <div class="si flex items-center gap-2.5 pl-11 pr-3.5 h-[38px] rounded-lg mx-2 my-0.5 text-white/60 text-[13.5px] cursor-pointer transition-all duration-150 hover:bg-white/10 hover:text-white" data-as-section="typography"><div class="w-1.5 h-1.5 rounded-full bg-white/40 shrink-0"></div>Typography</div>
                <div class="si flex items-center gap-2.5 pl-11 pr-3.5 h-[38px] rounded-lg mx-2 my-0.5 text-white/60 text-[13.5px] cursor-pointer transition-all duration-150 hover:bg-white/10 hover:text-white" data-as-section="mobilemenu"><div class="w-1.5 h-1.5 rounded-full bg-white/40 shrink-0"></div>Mobile Menu</div>
              </div>
            </div>

            <!-- Forms Header -->
            <div class="text-[10px] font-bold tracking-widest text-white/40 uppercase px-4 py-3 whitespace-nowrap overflow-hidden transition-all duration-200" data-as-section-header>Forms</div>
            
            <!-- Forms Expandable Submenu -->
            <div class="relative">
              <div class="flex items-center justify-between px-3.5 h-11 rounded-lg mx-2 my-0.5 cursor-pointer text-white/70 text-sm font-medium transition-all duration-150 border border-transparent hover:bg-white/10 hover:text-white"
                   data-as-submenu="forms">
                <div class="flex items-center gap-2.5 min-w-0">
                  <svg class="w-[17px] h-[17px] shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                  <span class="whitespace-nowrap transition-all duration-200">Forms</span>
                </div>
                <svg class="w-3 h-3 shrink-0 text-white/40 transition-transform duration-250" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
              </div>
              <div class="overflow-hidden transition-all duration-300" data-as-submenu-body="forms">
                <div class="si flex items-center gap-2.5 pl-11 pr-3.5 h-[38px] rounded-lg mx-2 my-0.5 text-white/60 text-[13.5px] cursor-pointer transition-all duration-150 hover:bg-white/10 hover:text-white" data-as-section="forminputs"><div class="w-1.5 h-1.5 rounded-full bg-white/40 shrink-0"></div>Form Inputs</div>
                <div class="si flex items-center gap-2.5 pl-11 pr-3.5 h-[38px] rounded-lg mx-2 my-0.5 text-white/60 text-[13.5px] cursor-pointer transition-all duration-150 hover:bg-white/10 hover:text-white" data-as-section="checkboxradio"><div class="w-1.5 h-1.5 rounded-full bg-white/40 shrink-0"></div>Checkbox & Radio</div>
                <div class="si flex items-center gap-2.5 pl-11 pr-3.5 h-[38px] rounded-lg mx-2 my-0.5 text-white/60 text-[13.5px] cursor-pointer transition-all duration-150 hover:bg-white/10 hover:text-white" data-as-section="fileinput"><div class="w-1.5 h-1.5 rounded-full bg-white/40 shrink-0"></div>File Input</div>
                <div class="si flex items-center gap-2.5 pl-11 pr-3.5 h-[38px] rounded-lg mx-2 my-0.5 text-white/60 text-[13.5px] cursor-pointer transition-all duration-150 hover:bg-white/10 hover:text-white" data-as-section="validations"><div class="w-1.5 h-1.5 rounded-full bg-white/40 shrink-0"></div>Validations</div>
                <div class="si flex items-center gap-2.5 pl-11 pr-3.5 h-[38px] rounded-lg mx-2 my-0.5 text-white/60 text-[13.5px] cursor-pointer transition-all duration-150 hover:bg-white/10 hover:text-white" data-as-section="datetime"><div class="w-1.5 h-1.5 rounded-full bg-white/40 shrink-0"></div>Date Time</div>
              </div>
            </div>

            <!-- Extras Header -->
            <div class="text-[10px] font-bold tracking-widest text-white/40 uppercase px-4 py-3 whitespace-nowrap overflow-hidden transition-all duration-200" data-as-section-header>Extras</div>
            
            <div class="flex items-center justify-between px-3.5 h-11 rounded-lg mx-2 my-0.5 cursor-pointer text-white/70 text-sm font-medium transition-all duration-150 border border-transparent hover:bg-white/10 hover:text-white"
                 data-as-section="invoice">
              <div class="flex items-center gap-2.5 min-w-0">
                <svg class="w-[17px] h-[17px] shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414A1 1 0 0119 9.414V19a2 2 0 01-2 2z"/></svg>
                <span class="whitespace-nowrap transition-all duration-200">Invoice</span>
              </div>
            </div>

            <div class="flex items-center justify-between px-3.5 h-11 rounded-lg mx-2 my-0.5 cursor-pointer text-white/70 text-sm font-medium transition-all duration-150 border border-transparent hover:bg-white/10 hover:text-white"
                 data-as-section="calendar">
              <div class="flex items-center gap-2.5 min-w-0">
                <svg class="w-[17px] h-[17px] shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                <span class="whitespace-nowrap transition-all duration-200">Calendar</span>
              </div>
            </div>
          </nav>
        </div>

        <!-- Theme Switcher -->
        <div class="text-[10px] font-bold tracking-widest text-white/40 uppercase px-4 py-2 whitespace-nowrap">Temas</div>
          <div class="flex flex-wrap gap-1.5 px-3.5 pb-3" data-as-themes>
            <div class="w-7 h-7 rounded-lg cursor-pointer border-2 border-transparent transition-all duration-150 hover:scale-110" data-theme="" title="Cyan (Padrão)" style="background:#0B6E8C"></div>
            <div class="w-7 h-7 rounded-lg cursor-pointer border-2 border-transparent transition-all duration-150 hover:scale-110" data-theme="blue" title="Azul" style="background:#1A44A0"></div>
            <div class="w-7 h-7 rounded-lg cursor-pointer border-2 border-transparent transition-all duration-150 hover:scale-110" data-theme="green" title="Verde" style="background:#1F7A45"></div>
            <div class="w-7 h-7 rounded-lg cursor-pointer border-2 border-transparent transition-all duration-150 hover:scale-110" data-theme="red" title="Vermelho" style="background:#C02020"></div>
            <div class="w-7 h-7 rounded-lg cursor-pointer border-2 border-transparent transition-all duration-150 hover:scale-110" data-theme="amber" title="Amarelo Queimado" style="background:#B87800"></div>
            <div class="w-7 h-7 rounded-lg cursor-pointer border-2 border-transparent transition-all duration-150 hover:scale-110" data-theme="orange" title="Laranja" style="background:#C04A10"></div>
            <div class="w-7 h-7 rounded-lg cursor-pointer border-2 border-transparent transition-all duration-150 hover:scale-110" data-theme="pink" title="Rosa" style="background:#D62B6B"></div>
            <div class="w-7 h-7 rounded-lg cursor-pointer border-2 border-transparent transition-all duration-150 hover:scale-110" data-theme="lime" title="Lima" style="background:#6BA82A"></div>
            <div class="w-7 h-7 rounded-lg cursor-pointer border-2 border-transparent transition-all duration-150 hover:scale-110" data-theme="teal" title="Teal" style="background:#1A8A7A"></div>
            <div class="w-7 h-7 rounded-lg cursor-pointer border-2 border-transparent transition-all duration-150 hover:scale-110" data-theme="indigo" title="Índigo" style="background:#3B2BA8"></div>
          </div>

        <!-- Sidebar Footer -->
        <div class="p-2.5 border-t border-white/10 shrink-0 overflow-hidden">
          <div class="flex items-center gap-2.5 p-2 rounded-lg cursor-pointer transition-colors duration-150 hover:bg-white/10" data-as-profile>
            <div class="w-[34px] h-[34px] rounded-full bg-neon-cyan flex items-center justify-center text-xs font-bold text-white shrink-0 shadow-neon-cyan border border-white/20">MA</div>
            <div class="flex-1 min-w-0 overflow-hidden transition-all duration-200">
              <div class="text-[13.5px] font-semibold text-white truncate">Marco Antônio</div>
              <div class="text-[11.5px] text-white/50 truncate">Administrador</div>
            </div>
          </div>
        </div>
      </aside>
    `;
  }

  _onReady(callback) {
    if (window.Alpine && Alpine.version && Alpine.$data(document.body)) {
      callback();
    } else {
      requestAnimationFrame(() => this._onReady(callback));
    }
  }

  _attachBehaviors() {
    const data = Alpine.$data(document.body);
    const aside = this.querySelector('#sidebar');
    const nav = this.querySelector('[data-as-nav]');

    this._syncUI(aside, data);
    Alpine.effect(() => this._syncUI(aside, data));

    nav.addEventListener('click', (e) => {
      const section = e.target.closest('[data-as-section]');
      if (section) {
        data.showSection(section.dataset.asSection);
        return;
      }
      const submenuTrigger = e.target.closest('[data-as-submenu]');
      if (submenuTrigger) {
        data.toggleSubmenu(submenuTrigger.dataset.asSubmenu);
      }
    });

    this.querySelector('[data-as-toggle]').addEventListener('click', () => data.toggleSidebar());
    this.querySelector('[data-as-toggle-logo]').addEventListener('click', () => data.toggleSidebar());
    this.querySelector('[data-as-profile]').addEventListener('click', () => { data.rightCanvasOpen = true; });

    this.querySelector('[data-as-themes]').addEventListener('click', (e) => {
      const swatch = e.target.closest('[data-theme]');
      if (swatch) data.setTheme(swatch.dataset.theme);
    });
  }

  _syncUI(aside, data) {
    const isMini = data.sidebarMini;
    const isOpen = data.sidebarOpen;
    const activeSection = data.activeSection;
    const activeSubmenu = data.activeSubmenu;

    aside.classList.toggle('translate-x-0', isOpen);
    aside.classList.toggle('-translate-x-full', !isOpen);
    aside.classList.toggle('lg:translate-x-0', !isOpen);
    aside.classList.toggle('lg:w-[64px]', isMini);

    const allSections = aside.querySelectorAll('[data-as-section]');
    allSections.forEach(el => {
      const isActive = el.dataset.asSection === activeSection;
      el.classList.toggle('!bg-neon-cyan', isActive);
      el.classList.toggle('!text-white', isActive);
      el.classList.toggle('!border-neon-cyan', isActive);
      el.classList.toggle('shadow-neon-cyan', isActive);
    });

    const allHeaders = aside.querySelectorAll('[data-as-submenu]');
    allHeaders.forEach(el => {
      const menu = el.dataset.asSubmenu;
      const body = aside.querySelector(`[data-as-submenu-body="${menu}"]`);
      if (body) {
        const isExpanded = activeSubmenu === menu && !isMini;
        body.classList.toggle('max-h-0', !isExpanded);
        body.classList.toggle('max-h-[800px]', isExpanded && menu === 'ui');
        body.classList.toggle('max-h-[300px]', isExpanded && menu === 'forms');
      }
      const arrow = el.querySelector('svg:last-child');
      if (arrow) {
        arrow.classList.toggle('rotate-90', activeSubmenu === menu);
        arrow.classList.toggle('lg:opacity-0', isMini);
      }
    });

    const toggleBtn = aside.querySelector('[data-as-toggle]');
    if (toggleBtn) toggleBtn.classList.toggle('lg:hidden', isMini);

    aside.querySelectorAll('[data-as-section] span, [data-as-submenu] span').forEach(el => {
      el.classList.toggle('lg:opacity-0', isMini);
      el.classList.toggle('lg:w-0', isMini);
    });

    const logoText = aside.querySelector('[data-as-brand]');
    if (logoText) {
      logoText.classList.toggle('lg:opacity-0', isMini);
      logoText.classList.toggle('lg:w-0', isMini);
      logoText.classList.toggle('lg:overflow-hidden', isMini);
    }

    aside.querySelectorAll('[data-as-section-header]').forEach(el => {
      el.classList.toggle('lg:opacity-0', isMini);
      el.classList.toggle('lg:h-0', isMini);
      el.classList.toggle('lg:py-0', isMini);
    });

    const profileText = aside.querySelector('[data-as-profile] .flex-1');
    if (profileText) {
      profileText.classList.toggle('lg:opacity-0', isMini);
      profileText.classList.toggle('lg:w-0', isMini);
    }

    aside.querySelectorAll('.si').forEach(el => {
      el.childNodes.forEach(node => {
        if (node.nodeType === 3 && node.textContent.trim()) {
          node.parentNode.classList.toggle('lg:opacity-0', isMini);
        }
      });
    });

    const themeSwatches = aside.querySelectorAll('[data-as-themes] [data-theme]');
    themeSwatches.forEach(el => {
      const isActive = el.dataset.theme === data.theme;
      el.classList.toggle('border-white', isActive);
      el.classList.toggle('scale-110', isActive);
      el.style.borderColor = isActive ? 'white' : 'transparent';
    });
  }
}
customElements.define('app-sidebar', AppSidebar);

// B. COMPONENTE HEADER TOPBAR DA APLICAÇÃO
class AppTopbar extends HTMLElement {
  connectedCallback() {
    this.render();
    this._onReady(() => this._attachBehaviors());
  }

  render() {
    this.innerHTML = `
      <header id="topbar" 
              class="fixed top-0 right-0 left-0 h-[60px] bg-neon-cyan flex items-center justify-between px-4 z-30 transition-all duration-300 shadow-neon-cyan lg:left-[260px]">
        <div class="flex items-center gap-2">
          <button class="grid place-items-center w-[38px] h-[38px] bg-white/20 border border-white/30 rounded-xl text-white cursor-pointer transition-colors duration-150 hover:bg-white/30 lg:hidden shrink-0" 
                  data-tb-hamburger
                  aria-label="Abrir Menu">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
          </button>
          <div class="hidden md:flex items-center gap-2 text-sm text-white/80">
            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
            <span>Início</span>
            <span class="text-white/50">/</span>
            <span class="text-white font-semibold" data-tb-breadcrumb></span>
          </div>
        </div>
        
        <div class="flex items-center gap-2.5 bg-white/20 border border-white/30 rounded-xl px-3.5 py-2 max-w-[400px] flex-1 mx-2 transition-all duration-200 focus-within:bg-white/30 focus-within:border-white/50 focus-within:ring-4 focus-within:ring-white/10 shrink-0">
          <svg class="w-3.5 h-3.5 text-white/80 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35"/></svg>
          <input type="text" placeholder="Buscar..." class="bg-transparent border-none outline-none text-sm text-white w-full placeholder:text-white/60 font-sans"/>
        </div>

        <div class="flex items-center gap-2.5">
          <button class="w-[38px] h-[38px] rounded-xl bg-white/20 border border-white/30 text-white cursor-pointer grid place-items-center transition-all duration-150 hover:bg-white/30 relative"
                  data-tb-notifications>
            <div class="absolute top-[7px] right-[7px] w-2 h-2 rounded-full bg-neon-red border border-neon-cyan shadow-[0_0_8px_var(--neon-red)]"></div>
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
          </button>
          <div class="w-[34px] h-[34px] rounded-full bg-neon-cyan flex items-center justify-center text-xs font-bold text-white shrink-0 shadow-neon-cyan border border-white/30 cursor-pointer" data-tb-profile>MA</div>
        </div>
      </header>
    `;
  }

  _onReady(callback) {
    if (window.Alpine && Alpine.version && Alpine.$data(document.body)) {
      callback();
    } else {
      requestAnimationFrame(() => this._onReady(callback));
    }
  }

  _attachBehaviors() {
    const data = Alpine.$data(document.body);
    const header = this.querySelector('#topbar');
    const breadcrumb = this.querySelector('[data-tb-breadcrumb]');

    this._syncUI(header, data, breadcrumb);
    Alpine.effect(() => this._syncUI(header, data, breadcrumb));

    this.querySelector('[data-tb-hamburger]').addEventListener('click', () => { data.sidebarOpen = true; });
    this.querySelector('[data-tb-notifications]').addEventListener('click', () => {
      data.showToast('cyan', 'Notificações', 'Você não tem novas notificações.');
    });
    this.querySelector('[data-tb-profile]').addEventListener('click', () => { data.rightCanvasOpen = true; });
  }

  _syncUI(header, data, breadcrumb) {
    header.classList.toggle('lg:left-[64px]', data.sidebarMini);
    if (breadcrumb) breadcrumb.textContent = data.breadcrumbText;
  }
}
customElements.define('app-topbar', AppTopbar);

// C. COMPONENTE REUTILIZÁVEL: ACCORDION ITEM
class UiAccordion extends HTMLElement {
  connectedCallback() {
    const title = this.getAttribute('title') || '';
    const color = this.getAttribute('color') || 'cyan';
    const isOpen = this.hasAttribute('open');
    const content = this.innerHTML;
    
    this.className = "block mb-3";
    this.innerHTML = `
      <div class="border-2 border-bg-border-sub rounded-xl overflow-hidden bg-bg-card" x-data="accordion(${isOpen})" :class="{'border-neon-${color}': open}">
        <div class="flex items-center justify-between p-4 bg-bg-surface hover:bg-bg-hover cursor-pointer transition-colors duration-150" @click="toggle()">
          <div class="text-sm font-semibold flex items-center gap-2.5">
            <div class="w-[22px] h-[22px] rounded-md grid place-items-center shrink-0" style="background:var(--neon-${color})">
              <svg class="w-3.5 h-3.5 text-white" fill="none" viewBox="0 0 24 24" stroke="white" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
            </div>
            <span>${title}</span>
          </div>
          <svg class="w-5 h-5 text-text-3 transition-transform duration-250" :class="{'rotate-180': open}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
          </svg>
        </div>
        <div class="bg-bg-card" x-show="open" x-transition>
          <div class="p-5 text-text-2 leading-relaxed text-sm md:text-base">${content}</div>
        </div>
      </div>
    `;
    initAlpineElement(this);
  }
}
customElements.define('ui-accordion', UiAccordion);

// D. COMPONENTE REUTILIZÁVEL: ALERTA
class UiAlert extends HTMLElement {
  connectedCallback() {
    const type = this.getAttribute('type') || 'cyan';
    const title = this.getAttribute('title') || '';
    const content = this.innerHTML;

    const ICONS = {
      red: '<svg class="w-[22px] h-[22px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>',
      green: '<svg class="w-[22px] h-[22px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>',
      yellow: '<svg class="w-[22px] h-[22px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>',
      cyan: '<svg class="w-[22px] h-[22px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>',
      blue: '<svg class="w-[22px] h-[22px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>',
      purple: '<svg class="w-[22px] h-[22px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>',
      pink: '<svg class="w-[22px] h-[22px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>',
      lime: '<svg class="w-[22px] h-[22px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>',
      teal: '<svg class="w-[22px] h-[22px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>',
      indigo: '<svg class="w-[22px] h-[22px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/></svg>'
    };

    this.innerHTML = `
      <div class="rounded-xl p-4 flex items-start gap-3.5 mb-3 border-2 border-neon-${type} bg-neon-${type}/10 text-neon-${type} shadow-neon-${type} alert-${type}">
        <div class="w-[22px] h-[22px] shrink-0 mt-0.5">
          ${ICONS[type] || ICONS.cyan}
        </div>
        <div class="flex-1 min-w-0">
          <div class="text-sm md:text-base font-bold mb-0.5">${title}</div>
          <div class="text-xs md:text-sm leading-relaxed">${content}</div>
        </div>
        <button class="w-6 h-6 rounded-md border-none bg-transparent cursor-pointer text-current flex items-center justify-center opacity-60 transition-opacity duration-150 hover:opacity-100 shrink-0" data-alert-close>
          <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
      </div>
    `;
    this.querySelector('[data-alert-close]').addEventListener('click', () => {
      this.querySelector('.rounded-xl').style.display = 'none';
    });
  }
}
customElements.define('ui-alert', UiAlert);

// E. COMPONENTE REUTILIZÁVEL: PROGRESS BAR
class UiProgress extends HTMLElement {
  connectedCallback() {
    const value = this.getAttribute('value') || '0';
    const color = this.getAttribute('color') || 'cyan';
    const title = this.getAttribute('title') || '';
    
    this.innerHTML = `
      <div class="mb-4">
        <div class="flex justify-between text-sm text-text-2 mb-2 font-medium">
          <span>${title}</span>
          <span class="text-neon-${color} font-bold">${value}%</span>
        </div>
        <div class="h-2.5 bg-bg-surface rounded-full overflow-hidden border border-bg-border-sub">
          <div class="h-full rounded-full bg-neon-${color} shadow-neon-${color} transition-all duration-[1.3s] ease-[cubic-bezier(.4,0,.2,1)]"
               style="width: 0%">
          </div>
        </div>
      </div>
    `;
    setTimeout(() => {
      const bar = this.querySelector('.h-full');
      if (bar) bar.style.width = value + '%';
    }, 300);
  }
}
customElements.define('ui-progress', UiProgress);

// F. COMPONENTE REUTILIZÁVEL: CAROUSEL
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
    initAlpineElement(this);
  }
}
customElements.define('ui-carousel', UiCarousel);

// G. COMPONENTE REUTILIZÁVEL: TOAST CONTAINER
class UiToastContainer extends HTMLElement {
  connectedCallback() {
    this.render();
    withLayout((data) => {
      this._attachBehaviors(data);
    });
  }

  render() {
    this.innerHTML = `
      <div id="toast-container" 
           class="fixed bottom-6 right-6 z-50 flex flex-col-reverse gap-3 pointer-events-none"
           style="display: none">
      </div>
    `;
  }

  _attachBehaviors(data) {
    const container = this.querySelector('#toast-container');
    const TOAST_ICONS = {
      red: '<svg class="w-4.5 h-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>',
      green: '<svg class="w-4.5 h-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>',
      cyan: '<svg class="w-4.5 h-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>',
      pink: '<svg class="w-4.5 h-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>',
      lime: '<svg class="w-4.5 h-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>',
      teal: '<svg class="w-4.5 h-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>',
      indigo: '<svg class="w-4.5 h-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/></svg>'
    };

    const renderToasts = () => {
      const toasts = data.toasts;
      container.style.display = toasts.length > 0 ? '' : 'none';
      const existing = container.querySelectorAll('.toast');
      existing.forEach(el => el.remove());

      toasts.forEach((toast, i) => {
        const el = document.createElement('div');
        el.className = `toast flex items-start gap-3.5 min-w-[320px] max-w-[440px] p-4 rounded-xl bg-bg-elevated border-2 shadow-lg pointer-events-auto cursor-pointer animate-[toastIn_0.35s_cubic-bezier(.34,1.56,.64,1)_both] border-neon-${toast.type} text-neon-${toast.type}`;
        el.innerHTML = `
          <div class="w-9 h-9 rounded-lg grid place-items-center shrink-0 bg-neon-${toast.type} text-white">
            ${TOAST_ICONS[toast.type] || ''}
          </div>
          <div class="flex-1">
            <div class="text-sm font-bold mb-0.5 text-text-1">${toast.title}</div>
            ${toast.msg ? `<div class="text-xs text-text-2">${toast.msg}</div>` : ''}
          </div>
          <button class="w-6 h-6 rounded-md flex items-center justify-center shrink-0 mt-0.5 text-text-3 hover:bg-bg-hover hover:text-text-1 border-none bg-transparent cursor-pointer transition-colors duration-150">
            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
          </button>
        `;
        el.addEventListener('click', () => data.removeToast(toast.id));
        el.querySelector('button').addEventListener('click', (e) => {
          e.stopPropagation();
          data.removeToast(toast.id);
        });
        container.appendChild(el);
      });
    };

    renderToasts();
    Alpine.effect(() => {
      data.toasts.length;
      data.toasts.forEach(() => {});
      renderToasts();
    });
  }
}
customElements.define('ui-toast-container', UiToastContainer);

// H. COMPONENTE REUTILIZÁVEL: POPOVER
class UiPopover extends HTMLElement {
  connectedCallback() {
    const trigger = this.getAttribute('trigger') || 'Gatilho';
    const color = this.getAttribute('color') || 'cyan';
    const isGhost = this.hasAttribute('ghost');
    const content = this.innerHTML;
    
    this.className = "relative inline-flex";
    this.innerHTML = `
      <div x-data="popover" @click.outside="close()" class="relative inline-flex">
        <button class="btn ${isGhost ? 'btn-ghost' : 'btn-' + color}" @click="toggle()">${trigger}</button>
        <div class="popover pop-bottom absolute z-50 bg-bg-elevated border-2 border-neon-cyan rounded-xl p-4 shadow-neon-cyan min-w-[240px] max-w-[320px] left-1/2 -translate-x-1/2 transition-all duration-200 ease-[cubic-bezier(.34,1.4,.64,1)]" 
              style="top: calc(100% + 12px)" 
             :class="{'opacity-100 pointer-events-auto scale-100 translate-y-0': open, 'opacity-0 pointer-events-none scale-95 translate-y-2': !open}"
             x-show="open" 
             x-transition>
          ${content}
        </div>
      </div>
    `;
    initAlpineElement(this);
  }
}
customElements.define('ui-popover', UiPopover);

// I. COMPONENTE REUTILIZÁVEL: MODAL OVERLAY & CONTENT CONTAINER
class UiModal extends HTMLElement {
  connectedCallback() {
    const id = this.getAttribute('id') || '';
    const size = this.getAttribute('size') || 'md';
    const title = this.getAttribute('title') || '';
    const sub = this.getAttribute('sub') || '';
    const color = this.getAttribute('color') || 'cyan';
    const content = this.innerHTML;
    
    this.innerHTML = `
      <div class="modal-overlay fixed inset-0 z-[100] bg-slate-950/80 backdrop-blur-[8px] flex items-center justify-center p-4 transition-all duration-250 opacity-0 pointer-events-none"
           data-modal-overlay="${id}">
        <div class="modal bg-bg-card border border-bg-border rounded-3xl w-full max-h-[90vh] overflow-hidden flex flex-col shadow-xl transition-all duration-300 transform ${size === 'sm' ? 'max-w-[420px]' : size === 'lg' ? 'max-w-[720px]' : 'max-w-[560px]'}">
          <div class="p-5 flex items-center gap-4 bg-neon-${color} text-white shrink-0">
            ${title ? `
              <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0 bg-white/20">
                <svg class="w-5.5 h-5.5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414A1 1 0 0119 9.414V19a2 2 0 01-2 2z"/></svg>
              </div>
              <div class="flex-1">
                <div class="text-[17px] font-bold">${title}</div>
                ${sub ? `<div class="text-[13px] text-white/80 mt-0.5">${sub}</div>` : ''}
              </div>
            ` : ''}
            <button class="w-9 h-9 rounded-lg border-none bg-white/20 text-white cursor-pointer grid place-items-center transition-colors duration-150 hover:bg-white/30 shrink-0" data-modal-close="${id}">
              <svg class="w-4.5 h-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
          </div>
          <div class="p-6 overflow-y-auto flex-1 text-text-2 leading-relaxed text-sm md:text-base bg-bg-card">
            ${content}
          </div>
        </div>
      </div>
    `;
    withLayout((data) => {
      const overlay = this.querySelector('[data-modal-overlay]');
      const closeBtn = this.querySelector('[data-modal-close]');
      const modal = overlay.querySelector('.modal');
      
      const update = () => {
        const isActive = data.activeModal === id;
        overlay.classList.toggle('opacity-100', isActive);
        overlay.classList.toggle('pointer-events-auto', isActive);
        overlay.classList.toggle('opacity-0', !isActive);
        overlay.classList.toggle('pointer-events-none', !isActive);
      };
      update();
      Alpine.effect(update);
      
      overlay.addEventListener('click', (e) => {
        if (e.target === overlay) data.closeModal();
      });
      if (closeBtn) closeBtn.addEventListener('click', () => data.closeModal());
    });
  }
}
customElements.define('ui-modal', UiModal);

// J. COMPONENTE REUTILIZÁVEL: DRAWER (GAVETA OFF-CANVAS)
class UiDrawer extends HTMLElement {
  connectedCallback() {
    const id = this.getAttribute('id') || '';
    const title = this.getAttribute('title') || '';
    const sub = this.getAttribute('sub') || '';
    const side = this.getAttribute('side') || 'left';
    const openVar = this.getAttribute('open-variable') || '';
    const color = this.getAttribute('color') || 'cyan';
    const maxW = this.getAttribute('max-width') || '300px';
    const bgClass = this.getAttribute('bg') || 'bg-bg-card';
    const borderClass = this.getAttribute('border') || 'border-bg-border-sub';
    
    // Parse custom header-extra slot if present
    const headerExtra = this.querySelector('[slot="header-extra"]');
    const headerExtraHTML = headerExtra ? headerExtra.outerHTML : '';
    if (headerExtra) headerExtra.remove();

    const content = this.innerHTML;

    this.innerHTML = `
      <div class="fixed inset-0 bg-slate-950/70 backdrop-blur-[4px] z-40 transition-opacity duration-300 opacity-0 pointer-events-none"
           data-drawer-backdrop="${id}">
      </div>
      <aside id="${id}" 
             class="fixed top-0 bottom-0 ${side}-0 w-full z-50 flex flex-col transition-transform duration-350 shadow-lg ${side === 'left' ? '-translate-x-full' : 'translate-x-full'} ${bgClass} border-${side === 'left' ? 'r' : 'l'} ${borderClass}" 
             style="max-width: ${maxW}"
             data-drawer-aside="${id}">
        <div class="p-5 bg-neon-${color} shrink-0">
          <div class="flex items-center justify-between mb-2">
            <div class="text-white">
              <div class="text-base font-bold">${title}</div>
              ${sub ? `<div class="text-xs text-white/80 mt-0.5">${sub}</div>` : ''}
            </div>
            <button class="w-[30px] h-[30px] rounded-lg bg-white/20 border border-white/30 text-white cursor-pointer grid place-items-center transition-colors duration-150 hover:bg-white/30 shrink-0" 
                    data-drawer-close="${id}">
              <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
          </div>
          ${headerExtraHTML}
        </div>
        <div class="flex-1 overflow-y-auto p-5 [&::-webkit-scrollbar]:w-[3px] [&::-webkit-scrollbar-thumb]:bg-white/20 [&::-webkit-scrollbar-thumb]:rounded-full">
          ${content}
        </div>
      </aside>
    `;
    withLayout((data) => {
      const backdrop = this.querySelector('[data-drawer-backdrop]');
      const aside = this.querySelector('[data-drawer-aside]');
      const closeBtn = this.querySelector('[data-drawer-close]');
      
      const update = () => {
        const isOpen = data[openVar];
        backdrop.classList.toggle('opacity-100', isOpen);
        backdrop.classList.toggle('pointer-events-auto', isOpen);
        backdrop.classList.toggle('opacity-0', !isOpen);
        backdrop.classList.toggle('pointer-events-none', !isOpen);
        aside.classList.toggle('translate-x-0', isOpen);
      };
      update();
      Alpine.effect(update);
      
      backdrop.addEventListener('click', () => { data[openVar] = false; });
      if (closeBtn) closeBtn.addEventListener('click', () => { data[openVar] = false; });
    });
  }
}
customElements.define('ui-drawer', UiDrawer);

// K. COMPONENTE REUTILIZÁVEL: FILE UPLOAD
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
    initAlpineElement(this);
  }
}
customElements.define('ui-file-upload', UiFileUpload);

// L. COMPONENTE REUTILIZÁVEL: CALENDÁRIO COM EVENTOS
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
      let toastData = { type: 'cyan', title: 'Calendário', msg: `Selecionado Dia ${d}` };
      
      if (dayEvents.length > 0) {
        eventsHTML = '<div class="cal-events">';
        dayEvents.forEach(e => {
          eventsHTML += `<div class="cal-event bg-neon-${e.type} text-white">${e.label}</div>`;
        });
        eventsHTML += '</div>';
        
        const mainEvent = dayEvents[0];
        toastData = { type: mainEvent.type, title: mainEvent.title || 'Calendário', msg: mainEvent.toast || `Dia ${d}` };
      }
      
      const todayClass = isToday ? 'cal-today' : '';
      currentMonthCells += `
        <div class="cal-cell ${todayClass}" data-cal-toast='${JSON.stringify(toastData)}'>
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
                    data-cal-nav="Mês anterior">
              <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
              </svg>
            </button>
            <button class="w-[34px] h-[34px] rounded-lg bg-white/20 border border-white/30 text-white cursor-pointer grid place-items-center hover:bg-white/30" 
                    data-cal-nav="Próximo mês">
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
    withLayout((data) => {
      this.querySelectorAll('[data-cal-toast]').forEach(el => {
        const toast = JSON.parse(el.dataset.calToast);
        el.addEventListener('click', () => data.showToast(toast.type, toast.title, toast.msg));
      });
      this.querySelectorAll('[data-cal-nav]').forEach(el => {
        el.addEventListener('click', () => data.showToast('cyan', 'Calendário', el.dataset.calNav));
      });
    });
  }
}
customElements.define('ui-calendar', UiCalendar);

// M. COMPONENTE REUTILIZÁVEL: FATURA (INVOICE)
class UiInvoice extends HTMLElement {
  connectedCallback() {
    const number = this.getAttribute('number') || '';
    const sender = JSON.parse(this.getAttribute('sender') || '{}');
    const receiver = JSON.parse(this.getAttribute('receiver') || '{}');
    const items = JSON.parse(this.getAttribute('items') || '[]');
    const notes = this.getAttribute('notes') || '';
    const total = this.getAttribute('total') || '';

    let rowsHTML = '';
    items.forEach(item => {
      rowsHTML += `
        <tr>
          <td class="p-4 font-bold text-text-1">${item.desc}</td>
          <td class="p-4 text-text-2">${item.qty}</td>
          <td class="p-4 text-text-2 font-mono font-semibold">${item.unit}</td>
          <td class="p-4 text-right text-text-1 font-mono font-bold">${item.total}</td>
        </tr>
      `;
    });

    this.innerHTML = `
      <div class="bg-bg-card border-2 border-bg-border-sub rounded-2xl overflow-hidden shadow-sm">
        <div class="p-6 md:p-8 bg-neon-cyan flex flex-col md:flex-row justify-between items-start gap-4">
          <div class="flex items-center gap-3">
            <div class="w-11 h-11 rounded-lg bg-white/20 flex items-center justify-center border-2 border-white/30 text-white shrink-0 shadow-neon-cyan">
              <svg class="w-5.5 h-5.5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0H5m5-4h4"/>
              </svg>
            </div>
            <div>
              <div class="text-xl font-bold text-white tracking-tight">SisLoc</div>
              <div class="text-xs text-white/80">Sistema de Locação v3.0</div>
            </div>
          </div>
          <div class="md:text-right">
            <div class="text-[11px] tracking-wider uppercase text-white/70 mb-1 leading-none">Fatura N°</div>
            <div class="text-xl md:text-2xl font-bold font-mono text-white leading-none">${number}</div>
          </div>
        </div>
        <div class="p-6 md:p-8">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-8">
            <div>
              <div class="text-[11px] font-bold uppercase tracking-wider text-text-4 bg-bg-surface px-2.5 py-1 rounded inline-block mb-2">De</div>
              <div class="text-base font-bold text-text-1 mb-1">${sender.name || ''}</div>
              <div class="text-[13.5px] text-text-3 leading-relaxed">${sender.address || ''}<br/>CNPJ: ${sender.cnpj || ''}</div>
            </div>
            <div>
              <div class="text-[11px] font-bold uppercase tracking-wider text-text-4 bg-bg-surface px-2.5 py-1 rounded inline-block mb-2">Para</div>
              <div class="text-base font-bold text-text-1 mb-1">${receiver.name || ''}</div>
              <div class="text-[13.5px] text-text-3 leading-relaxed">${receiver.address || ''}<br/>CNPJ: ${receiver.cnpj || ''}</div>
            </div>
          </div>
          <div class="overflow-x-auto -webkit-overflow-scrolling-touch">
            <table class="w-full border-collapse">
              <thead>
                <tr class="bg-neon-cyan text-white text-left text-[11px] font-bold uppercase tracking-wider">
                  <th class="p-3 pl-4 rounded-l-lg">Descrição</th>
                  <th class="p-3">Qtd</th>
                  <th class="p-3">Valor Unit.</th>
                  <th class="p-3 pr-4 text-right rounded-r-lg">Total</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-bg-border-sub text-[14px]">
                ${rowsHTML}
              </tbody>
            </table>
          </div>
        </div>
        <div class="p-6 md:p-8 bg-bg-surface border-t border-bg-border-sub flex flex-col sm:flex-row justify-between items-start sm:items-end gap-6">
          <div class="max-w-[300px]">
            <div class="text-[12px] font-bold uppercase tracking-wider text-text-4 mb-2">Observações</div>
            <div class="text-[13px] text-text-3 leading-relaxed">${notes}</div>
          </div>
          <div class="text-right sm:ml-auto">
            <div class="text-xs text-text-3 mb-1">Total a Pagar</div>
            <div class="text-3xl font-extrabold text-neon-cyan font-mono leading-none">${total}</div>
          </div>
        </div>
      </div>
    `;
  }
}
customElements.define('ui-invoice', UiInvoice);

// N. COMPONENTE REUTILIZÁVEL: CARD DE MÉTRICAS (STAT CARD)
class UiStatCard extends HTMLElement {
  connectedCallback() {
    const value = this.getAttribute('value') || '';
    const label = this.getAttribute('label') || '';
    const color = this.getAttribute('color') || 'cyan';
    const icon = this.innerHTML;
    
    this.innerHTML = `
      <div class="bg-bg-card border-2 border-bg-border-sub rounded-2xl p-6 text-center shadow-sm h-full">
        <div class="w-14 h-14 rounded-2xl grid place-items-center bg-neon-${color} shadow-neon-${color} text-white mx-auto mb-3.5">
          ${icon}
        </div>
        <div class="text-3xl font-extrabold text-text-1 tracking-tight leading-none mb-1">${value}</div>
        <div class="text-xs text-text-3 font-semibold">${label}</div>
      </div>
    `;
  }
}
customElements.define('ui-stat-card', UiStatCard);

// O. COMPONENTE REUTILIZÁVEL: BADGE (INCLUINDO PULSE E TAMANHOS)
class UiBadge extends HTMLElement {
  connectedCallback() {
    const color = this.getAttribute('color') || 'cyan';
    const pulse = this.hasAttribute('pulse');
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

// P. COMPONENTE REUTILIZÁVEL: ITEM DE LISTA (LIST ITEM ROW)
class UiListItem extends HTMLElement {
  connectedCallback() {
    const title = this.getAttribute('title') || '';
    const desc = this.getAttribute('desc') || '';
    const color = this.getAttribute('color') || 'cyan';
    const badgeText = this.getAttribute('badge-text') || '';
    const badgeColor = this.getAttribute('badge-color') || 'cyan';
    const badgeSize = this.getAttribute('badge-size') || 'sm';
    const clickExpr = this.getAttribute('@click') || '';
    const icon = this.innerHTML;

    this.innerHTML = `
      <div class="flex items-center gap-3.5 p-4 border-l-4 border-transparent hover:bg-bg-surface hover:border-neon-cyan cursor-pointer transition-all duration-150">
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
    if (clickExpr) {
      const wrapper = this.querySelector('.flex');
      const fn = new Function('d', clickExpr.replace(/(\w+)\s*\(/, 'd.$1('));
      wrapper.addEventListener('click', () => fn(Alpine.$data(document.body)));
    }
  }
}
customElements.define('ui-list-item', UiListItem);

// Q. COMPONENTE REUTILIZÁVEL: CARD DE CONTEÚDO (INCLUINDO GLOW)
class UiCard extends HTMLElement {
  connectedCallback() {
    const title = this.getAttribute('title') || '';
    const color = this.getAttribute('color') || '';
    const glow = this.hasAttribute('glow');
    
    const footerEl = this.querySelector('[slot="footer"]');
    const footerHTML = footerEl ? footerEl.outerHTML : '';
    if (footerEl) footerEl.remove();
    
    const content = this.innerHTML;
    
    const borderColorClass = glow && color ? `border-neon-${color}` : 'border-bg-border-sub';
    const shadowClass = glow && color ? `shadow-neon-${color}` : 'shadow-sm';
    const titleColorClass = color ? `text-neon-${color}` : 'text-text-1';
    
    this.innerHTML = `
      <div class="bg-bg-card border-2 ${borderColorClass} rounded-2xl ${shadowClass} flex flex-col h-full">
        ${title ? `
          <div class="px-5 py-4 bg-bg-surface border-b border-bg-border-sub font-bold ${titleColorClass} overflow-hidden">
            ${title}
          </div>
        ` : ''}
        <div class="p-5 flex-1 text-text-2 leading-relaxed text-sm md:text-base">
          ${content}
        </div>
        ${footerHTML ? `
          <div class="px-5 py-3.5 bg-bg-surface border-t border-bg-border-sub flex gap-2.5 overflow-hidden">
            ${footerHTML}
          </div>
        ` : ''}
      </div>
    `;
  }
}
customElements.define('ui-card', UiCard);

// R. COMPONENTE REUTILIZÁVEL: INPUT DE FORMULÁRIO (LABEL, VALIDAÇÃO E ÍCONES)
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
      validationMsgHTML = `
        <div class="text-[12.5px] mt-1.5 flex items-center gap-1 text-neon-green">
          <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" class="w-3.5 h-3.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
          ${validationMsg}
        </div>
      `;
    } else if (validationState === 'error') {
      borderStyle = `!border-neon-red focus:!ring-neon-red/20`;
      validationMsgHTML = `
        <div class="text-[12.5px] mt-1.5 flex items-center gap-1 text-neon-red">
          <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" class="w-3.5 h-3.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
          ${validationMsg}
        </div>
      `;
    } else if (helpMsg) {
      validationMsgHTML = `
        <div class="text-[12.5px] mt-1.5 flex items-center gap-1 text-text-4">
          <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" class="w-3.5 h-3.5"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
          ${helpMsg}
        </div>
      `;
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
          ${iconHTML ? `
            <div class="absolute left-3.5 top-1/2 -translate-y-1/2 text-text-3">
              ${iconHTML}
            </div>
          ` : ''}
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

// S. COMPONENTE REUTILIZÁVEL: CHECKBOX CUSTOMIZADO
class UiCheckbox extends HTMLElement {
  connectedCallback() {
    const title = this.getAttribute('title') || '';
    const desc = this.getAttribute('desc') || '';
    const model = this.getAttribute('x-model') || '';
    
    this.innerHTML = `
      <label class="flex items-start gap-3.5 cursor-pointer select-none">
        <input type="checkbox" class="hidden"/>
        <div class="w-5 h-5 rounded-md border-2 border-bg-border bg-bg-surface flex items-center justify-center shrink-0 mt-0.5 transition-all">
          <svg class="w-3.5 h-3.5 text-white opacity-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
          </svg>
        </div>
        <div class="text-[14.5px] text-text-2 leading-tight">
          ${title ? `<strong class="text-text-1 font-semibold block">${title}</strong>` : ''}
          ${desc ? `<span class="text-xs text-text-4">${desc}</span>` : ''}
        </div>
      </label>
    `;
    if (model) {
      withLayout((data) => {
        const input = this.querySelector('input');
        const box = this.querySelector('.w-5');
        const svg = this.querySelector('svg');
        input.checked = !!data[model];
        const update = () => {
          const val = !!data[model];
          input.checked = val;
          box.classList.toggle('border-neon-cyan', val);
          box.classList.toggle('bg-neon-cyan', val);
          box.classList.toggle('shadow-neon-cyan', val);
          svg.classList.toggle('opacity-100', val);
          svg.classList.toggle('opacity-0', !val);
        };
        update();
        Alpine.effect(update);
        input.addEventListener('change', (e) => { data[model] = e.target.checked; });
      });
    }
  }
}
customElements.define('ui-checkbox', UiCheckbox);

// T. COMPONENTE REUTILIZÁVEL: RADIO BUTTON CUSTOMIZADO
class UiRadio extends HTMLElement {
  connectedCallback() {
    const title = this.getAttribute('title') || '';
    const desc = this.getAttribute('desc') || '';
    const model = this.getAttribute('x-model') || '';
    const value = this.getAttribute('value') || '';
    
    this.innerHTML = `
      <label class="flex items-start gap-3.5 cursor-pointer select-none">
        <input type="radio" class="hidden" name="${model}"/>
        <div class="w-5 h-5 rounded-full border-2 border-bg-border bg-bg-surface flex items-center justify-center shrink-0 mt-0.5 transition-all">
          <div class="w-2.5 h-2.5 rounded-full bg-neon-cyan opacity-0 scale-40"></div>
        </div>
        <div class="text-[14.5px] text-text-2 leading-tight">
          ${title ? `<strong class="text-text-1 font-semibold block">${title}</strong>` : ''}
          ${desc ? `<span class="text-xs text-text-4">${desc}</span>` : ''}
        </div>
      </label>
    `;
    if (model && value) {
      withLayout((data) => {
        const dot = this.querySelector('.w-2\\.5');
        const outer = this.querySelector('.rounded-full');
        const input = this.querySelector('input');
        const update = () => {
          const isActive = data[model] === value;
          outer.classList.toggle('border-neon-cyan', isActive);
          outer.classList.toggle('bg-neon-cyan/20', isActive);
          outer.classList.toggle('shadow-neon-cyan', isActive);
          dot.classList.toggle('opacity-100', isActive);
          dot.classList.toggle('scale-100', isActive);
          dot.classList.toggle('opacity-0', !isActive);
          dot.classList.toggle('scale-40', !isActive);
        };
        update();
        Alpine.effect(update);
        input.addEventListener('change', () => { data[model] = value; });
      });
    }
  }
}
customElements.define('ui-radio', UiRadio);

// U. COMPONENTE REUTILIZÁVEL: TOGGLE SWITCH (INTERRUPTOR)
class UiToggle extends HTMLElement {
  connectedCallback() {
    const label = this.getAttribute('label') || '';
    const color = this.getAttribute('color') || 'cyan';
    const size = this.getAttribute('size') || 's'; 
    const border = this.hasAttribute('border-bottom');
    const textLight = this.hasAttribute('text-light');
    
    const containerClass = border ? `flex items-center justify-between py-2.5 border-b border-bg-border-sub` : `flex items-center justify-between py-2.5`;
    const labelColorClass = textLight ? 'text-white' : 'text-text-2';
    const model = this.getAttribute('x-model') || '';
    
    this.innerHTML = `
      <div class="${containerClass}">
        ${label ? `<div class="text-sm font-medium ${labelColorClass}">${label}</div>` : ''}
        <label class="${size === 's' ? 'tog-s' : 'tog'} ${color}">
          <input type="checkbox"/>
          <div class="tog-track"></div>
          <div class="tog-thumb"></div>
        </label>
      </div>
    `;
    if (model) {
      withLayout((data) => {
        const input = this.querySelector('input');
        const update = () => { input.checked = !!data[model]; };
        update();
        Alpine.effect(update);
        input.addEventListener('change', (e) => { data[model] = e.target.checked; });
      });
    }
  }
}
customElements.define('ui-toggle', UiToggle);

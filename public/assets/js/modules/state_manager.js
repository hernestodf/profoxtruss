// Alpine Data State Manager - Moved from scripts.js
// Contains all Alpine.js reactive state patterns

export function registerLayoutState(Alpine) {
  Alpine.data('layout', () => ({
    sidebarOpen: false,
    sidebarMini: false,
    activeSubmenu: null,
    activeSection: 'dashboard',
    breadcrumbText: 'Dashboard',
    leftCanvasOpen: false,
    filterPeriod: 'Hoje',
    filterPrice: 2500,
    filterActiveRentals: true,
    filterAvailable: false,
    rightCanvasOpen: false,
    activeProfileTab: 'perfil',
    activeModal: null,
    toasts: [],
    animateProgress: false,
    theme: localStorage.getItem('theme') || '',

    init() {
      window.openModal  = this.openModal.bind(this);
      window.closeModal = this.closeModal.bind(this);
      window.showToast  = this.showToast.bind(this);
      
      document.documentElement.dataset.theme = this.theme;
      this.$watch('theme', value => {
        document.documentElement.dataset.theme = value;
      });
      setTimeout(() => { this.animateProgress = true; }, 300);
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
          tooltips: 'Tooltips',
          typography: 'Typography',
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

    toggleSidebar() {
      if (window.innerWidth < 1024) { this.sidebarOpen = !this.sidebarOpen; }
      else { this.sidebarMini = !this.sidebarMini; }
    },

    toggleSubmenu(id) {
      if (this.sidebarMini && window.innerWidth >= 1024) return;
      this.activeSubmenu = this.activeSubmenu === id ? null : id;
    },

    showSection(id) {
      this.activeSection = id;
      if (window.innerWidth < 1024) this.sidebarOpen = false;
    },

    openModal(id) {
      this.activeModal = id;
      document.body.style.overflow = 'hidden';
    },

    closeModal() {
      this.activeModal = null;
      document.body.style.overflow = '';
    },

    showToast(type, title, msg = '') {
      const id = Date.now() + Math.random().toString(36).substr(2, 9);
      this.toasts.push({ id, type, title, msg });
      setTimeout(() => { this.removeToast(id); }, 4000);
    },

    removeToast(id) {
      this.toasts = this.toasts.filter(t => t.id !== id);
    },

    setTheme(name) {
      this.theme = name;
      localStorage.setItem('theme', name);
    }
  }));
}

export function registerAccordionState(Alpine) {
  Alpine.data('accordion', (initialOpen = false) => ({
    open: initialOpen,
    toggle() { this.open = !this.open; }
  }));
}

export function registerAlertState(Alpine) {
  Alpine.data('alert', (initialShow = true) => ({
    show: initialShow,
    close() { this.show = false; }
  }));
}

export function registerPopoverState(Alpine) {
  Alpine.data('popover', () => ({
    open: false,
    toggle() { this.open = !this.open; },
    close() { this.open = false; }
  }));
}

export function registerCarouselState(Alpine) {
  Alpine.data('carousel', (slidesCount = 3) => ({
    index: 0,
    count: slidesCount,
    next() { this.index = (this.index + 1) % this.count; },
    prev() { this.index = (this.index - 1 + this.count) % this.count; },
    goTo(i) { this.index = i; }
  }));
}

export function registerColoredTabsState(Alpine) {
  Alpine.data('coloredTabs', (initialTab = 0, initialColor = 'red') => ({
    activeTab: initialTab,
    tabColor: initialColor,
    switchTab(index, color) {
      this.activeTab = index;
      this.tabColor = color;
    }
  }));
}

export function registerFileUploadState(Alpine) {
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
    removeFile(index) { this.filesList.splice(index, 1); }
  }));
}
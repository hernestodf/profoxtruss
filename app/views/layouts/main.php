<!DOCTYPE html>
<html lang="pt-BR" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php Layout::slot('title', 'ProFoxTruss') ?> — ProFoxTruss</title>
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet"/>
    
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'bg-darkest': 'var(--color-bg-darkest)',
                        'bg-dark': 'var(--color-bg-dark)',
                        'bg-surface': 'var(--color-bg-surface)',
                        'bg-card': 'var(--color-bg-card)',
                        'bg-elevated': 'var(--color-bg-elevated)',
                        'bg-hover': 'var(--color-bg-hover)',
                        'bg-border': 'var(--color-bg-border)',
                        'text-1': 'var(--color-text-1)',
                        'text-2': 'var(--color-text-2)',
                        'text-3': 'var(--color-text-3)',
                        'text-4': 'var(--color-text-4)',
                        'neon-cyan': 'var(--color-neon-cyan)',
                        'neon-green': 'var(--color-neon-green)',
                        'neon-red': 'var(--color-neon-red)',
                        'neon-purple': 'var(--color-neon-purple)',
                        'neon-amber': 'var(--color-neon-amber)'
                    }
                }
            }
        }
    </script>
    
    <!-- Design System Styles -->
    <link rel="stylesheet" href="<?= url('assets/css/styles.css') ?>">
    
    <!-- Design System Scripts -->
    <script src="<?= url('assets/js/design_system.js') ?>" defer></script>
    
    <!-- Alpine.js CDN -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <!-- Custom Head Slot -->
    <?php Layout::slot('head') ?>
</head>
<body x-data="layout" :class="{'mini': sidebarMini}" class="bg-bg-darkest text-text-1 font-['Plus_Jakarta_Sans'] min-h-screen flex flex-col antialiased">

<!-- DYNAMIC TOAST CONTAINER COMPONENT -->
<ui-toast-container></ui-toast-container>

<!-- SIDEBAR BACKDROP OVERLAY FOR MOBILE -->
<div class="fixed inset-0 bg-slate-950/70 backdrop-blur-[4px] z-40 transition-opacity duration-300 lg:hidden" x-show="sidebarOpen" @click="sidebarOpen = false" x-transition.opacity></div>

<!-- SYSTEM HEADER / TOP NAVBAR -->
<header class="bg-bg-dark border-b border-bg-border h-16 flex items-center justify-between px-6 sticky top-0 z-30">
    <div class="flex items-center gap-4">
        <!-- Mobile Toggle Button -->
        <button class="lg:hidden p-2 rounded-lg text-text-3 hover:bg-bg-surface hover:text-text-1" @click="sidebarOpen = true">
            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
        </button>
        <!-- Desktop Sidebar Toggle Button -->
        <button class="hidden lg:block p-2 rounded-lg text-text-3 hover:bg-bg-surface hover:text-text-1" @click="sidebarMini = !sidebarMini">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h12M4 18h16"/></svg>
        </button>
        
        <!-- Breadcrumb / Section Name -->
        <div class="flex items-center gap-2 text-sm font-semibold">
            <span class="text-text-3">ProFoxTruss</span>
            <span class="text-text-4">/</span>
            <span class="text-neon-cyan"><?php Layout::slot('title', 'Sistema') ?></span>
        </div>
    </div>
    
    <div class="flex items-center gap-4">
        <?php if (isLoggedIn()): ?>
            <!-- User Status & Menu Trigger -->
            <div class="flex items-center gap-3">
                <div class="text-right hidden sm:block">
                    <div class="text-xs font-bold text-text-1"><?= htmlspecialchars($_SESSION['user_name'] ?? '') ?></div>
                    <div class="text-[10px] font-semibold text-text-3 uppercase tracking-wider"><?= htmlspecialchars($_SESSION['user_role'] ?? '') ?></div>
                </div>
                <!-- Avatar Button -->
                <button class="w-10 h-10 rounded-full bg-neon-cyan/20 border border-neon-cyan flex items-center justify-center text-neon-cyan font-bold shadow-sm shadow-neon-cyan/20 hover:brightness-110" @click="rightCanvasOpen = true">
                    <?= strtoupper(substr($_SESSION['user_name'] ?? '', 0, 2)) ?>
                </button>
            </div>
        <?php else: ?>
            <a href="<?= url('/login') ?>" class="btn btn-cyan btn-sm">Entrar</a>
        <?php endif; ?>
    </div>
</header>

<!-- MAIN CONTAINER (Sidebar + Content) -->
<div class="flex flex-1 relative">

    <!-- LEFT SIDEBAR -->
    <aside class="sidebar-wrapper fixed lg:sticky top-16 left-0 h-[calc(100vh-64px)] z-40 bg-bg-dark border-r border-bg-border flex flex-col transition-all duration-300 overflow-y-auto"
           :class="sidebarOpen ? 'translate-x-0 w-[240px]' : (sidebarMini ? '-translate-x-full lg:translate-x-0 lg:w-[70px]' : '-translate-x-full lg:translate-x-0 lg:w-[240px]')">
        
        <div class="p-4 flex flex-col gap-1.5 flex-1">
            <!-- Navigation Items -->
            <a href="<?= url('/') ?>" class="flex items-center gap-3 px-3.5 py-3 rounded-lg text-sm font-medium border border-transparent transition-colors duration-150 <?= $_SERVER['REQUEST_URI'] === BASE_PATH . '/' ? 'bg-neon-cyan/10 text-neon-cyan border-neon-cyan/20 shadow-sm shadow-neon-cyan/5' : 'text-text-2 hover:bg-bg-surface hover:text-text-1' ?>">
                <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 002 2h2a2 2 0 002-2z"/></svg>
                <span class="lg-label transition-opacity" :class="sidebarMini ? 'lg:opacity-0 lg:w-0 overflow-hidden' : ''">Calculadora & Grid</span>
            </a>
            
            <a href="<?= url('/pecas') ?>" class="flex items-center gap-3 px-3.5 py-3 rounded-lg text-sm font-medium border border-transparent transition-colors duration-150 <?= str_starts_with($_SERVER['REQUEST_URI'], BASE_PATH . '/pecas') ? 'bg-neon-cyan/10 text-neon-cyan border-neon-cyan/20 shadow-sm' : 'text-text-2 hover:bg-bg-surface hover:text-text-1' ?>">
                <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                <span class="lg-label transition-opacity" :class="sidebarMini ? 'lg:opacity-0 lg:w-0 overflow-hidden' : ''">Peças & Estoque</span>
            </a>
            
            <?php if (isLoggedIn() && userRole() === 'admin'): ?>
                <div class="h-px bg-bg-border my-2" :class="sidebarMini ? 'mx-2' : ''"></div>
                
                <a href="<?= url('/admin/users') ?>" class="flex items-center gap-3 px-3.5 py-3 rounded-lg text-sm font-medium border border-transparent transition-colors duration-150 <?= str_starts_with($_SERVER['REQUEST_URI'], BASE_PATH . '/admin') ? 'bg-neon-cyan/10 text-neon-cyan border-neon-cyan/20 shadow-sm' : 'text-text-2 hover:bg-bg-surface hover:text-text-1' ?>">
                    <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/></svg>
                    <span class="lg-label transition-opacity" :class="sidebarMini ? 'lg:opacity-0 lg:w-0 overflow-hidden' : ''">Administração / Usuários</span>
                </a>
            <?php endif; ?>
        </div>
        
        <!-- Bottom Sidebar Section -->
        <div class="p-4 border-t border-bg-border">
            <?php if (isLoggedIn()): ?>
                <a href="<?= url('/logout') ?>" class="flex items-center gap-3 px-3.5 py-3 rounded-lg text-sm font-medium text-neon-red hover:bg-neon-red/10 transition-colors duration-150">
                    <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                    <span class="lg-label transition-opacity" :class="sidebarMini ? 'lg:opacity-0 lg:w-0 overflow-hidden' : ''">Sair</span>
                </a>
            <?php endif; ?>
        </div>
    </aside>

    <!-- RIGHT DRAWER (Minha Conta / Detalhes de Perfil) -->
    <ui-drawer id="right-canvas" title="Minha Conta" side="right" open-variable="rightCanvasOpen" color="cyan" max-width="340px" bg="bg-bg-dark" border="border-bg-border">
        <?php if (isLoggedIn()): ?>
            <div class="flex flex-col items-center gap-2.5 py-4 pb-6 border-b border-bg-border mb-5">
                <div class="w-16 h-16 rounded-full bg-neon-cyan flex items-center justify-center text-2xl font-bold text-white shadow-neon-cyan border-2 border-bg-dark">
                    <?= strtoupper(substr($_SESSION['user_name'] ?? '', 0, 2)) ?>
                </div>
                <div class="text-base font-bold text-text-1"><?= htmlspecialchars($_SESSION['user_name'] ?? '') ?></div>
                <ui-badge color="cyan" size="sm" class="shadow-neon-cyan"><?= htmlspecialchars($_SESSION['user_role'] ?? '') ?></ui-badge>
                <div class="text-xs text-text-3"><?= htmlspecialchars($_SESSION['user_email'] ?? '') ?></div>
            </div>
            
            <div class="space-y-4">
                <div class="p-4 bg-bg-surface rounded-xl border border-bg-border">
                    <div class="text-xs font-bold text-text-3 uppercase tracking-wider mb-2">Informações da Conta</div>
                    <div class="space-y-2.5 text-sm">
                        <div class="flex justify-between"><span class="text-text-3">Usuário:</span><span class="text-text-1 font-semibold"><?= htmlspecialchars($_SESSION['user_name'] ?? '') ?></span></div>
                        <div class="flex justify-between"><span class="text-text-3">E-mail:</span><span class="text-text-1 font-semibold"><?= htmlspecialchars($_SESSION['user_email'] ?? '') ?></span></div>
                        <div class="flex justify-between"><span class="text-text-3">Função:</span><span class="text-text-1 font-semibold"><?= htmlspecialchars($_SESSION['user_role'] ?? '') ?></span></div>
                    </div>
                </div>
                
                <a href="<?= url('/logout') ?>" class="btn btn-red w-full flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                    Encerrar Sessão
                </a>
            </div>
        <?php endif; ?>
    </ui-drawer>

    <!-- CONTENT WRAPPER -->
    <main class="flex-1 min-w-0 p-6 lg:p-8 flex flex-col gap-6">
        <?php Layout::slot('content') ?>
    </main>

</div>

<!-- Custom Scripts Slot -->
<?php Layout::slot('scripts') ?>
</body>
</html>

<?php
Layout::set('main');
Layout::block('title', 'Dashboard Geral');
Layout::start('content');
?>

<div class="flex flex-col gap-6">
    <!-- Welcome Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-extrabold text-text-1 tracking-tight">Bem-vindo ao ProFoxTruss</h1>
            <p class="text-sm text-text-3 mt-1">Painel geral de monitoramento de projetos de estruturas e controle de estoque de treliças.</p>
        </div>
        
        <div class="flex items-center gap-2">
            <a href="<?= url('/') ?>" class="btn btn-cyan shadow-neon-cyan/20">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                Nova Estrutura (Canvas)
            </a>
        </div>
    </div>
    
    <!-- STATS CARDS -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        
        <!-- Total Projects Card -->
        <div class="bg-bg-card border border-bg-border rounded-2xl p-5 shadow-lg flex items-center gap-4 hover:border-neon-cyan/40 transition-colors">
            <div class="w-12 h-12 rounded-xl bg-neon-cyan/10 border border-neon-cyan/20 flex items-center justify-center text-neon-cyan shrink-0">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            </div>
            <div>
                <div class="text-2xl font-extrabold text-text-1 font-mono"><?= $totalProjetos ?></div>
                <div class="text-xs font-semibold text-text-3 uppercase tracking-wider mt-0.5">Projetos Salvos</div>
            </div>
        </div>
        
        <!-- Unique Catalog Parts -->
        <div class="bg-bg-card border border-bg-border rounded-2xl p-5 shadow-lg flex items-center gap-4 hover:border-neon-purple/40 transition-colors">
            <div class="w-12 h-12 rounded-xl bg-neon-purple/10 border border-neon-purple/20 flex items-center justify-center text-neon-purple shrink-0">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
            </div>
            <div>
                <div class="text-2xl font-extrabold text-text-1 font-mono"><?= $totalPecas ?></div>
                <div class="text-xs font-semibold text-text-3 uppercase tracking-wider mt-0.5">Itens no Catálogo</div>
            </div>
        </div>
        
        <!-- Total Stock Count -->
        <div class="bg-bg-card border border-bg-border rounded-2xl p-5 shadow-lg flex items-center gap-4 hover:border-neon-green/40 transition-colors">
            <div class="w-12 h-12 rounded-xl bg-neon-green/10 border border-neon-green/20 flex items-center justify-center text-neon-green shrink-0">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/></svg>
            </div>
            <div>
                <div class="text-2xl font-extrabold text-text-1 font-mono"><?= $totalEstoque ?></div>
                <div class="text-xs font-semibold text-text-3 uppercase tracking-wider mt-0.5">Total em Estoque</div>
            </div>
        </div>
        
        <!-- Out of Stock Warnings -->
        <div class="bg-bg-card border border-bg-border rounded-2xl p-5 shadow-lg flex items-center gap-4 hover:border-neon-red/40 transition-colors">
            <div class="w-12 h-12 rounded-xl bg-neon-red/10 border border-neon-red/20 flex items-center justify-center text-neon-red shrink-0">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            </div>
            <div>
                <div class="text-2xl font-extrabold text-text-1 font-mono"><?= $itensFalta ?></div>
                <div class="text-xs font-semibold text-text-3 uppercase tracking-wider mt-0.5">Itens Zerados</div>
            </div>
        </div>
        
    </div>
    
    <!-- TABLES / GRID DATA -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        
        <!-- Left: Recent Projects -->
        <div class="bg-bg-card border border-bg-border rounded-2xl p-5 shadow-lg flex flex-col">
            <h2 class="text-base font-bold text-text-1 mb-4 flex items-center gap-2">
                <svg class="w-5 h-5 text-neon-cyan" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Projetos Recentes
            </h2>
            
            <div class="overflow-x-auto flex-1">
                <table class="w-full text-xs text-left">
                    <thead>
                        <tr class="border-b border-bg-border text-text-3">
                            <th class="py-2.5 font-bold uppercase tracking-wider text-[10px]">Nome do Projeto</th>
                            <th class="py-2.5 font-bold uppercase tracking-wider text-[10px] text-center">Medidas</th>
                            <th class="py-2.5 font-bold uppercase tracking-wider text-[10px] text-center">Criado em</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-bg-border/60">
                        <?php foreach ($projetosRecentes as $proj): ?>
                            <tr class="hover:bg-bg-surface/30">
                                <td class="py-3 font-semibold text-text-1"><?= htmlspecialchars($proj['name']) ?></td>
                                <td class="py-3 text-center text-text-2 font-mono"><?= $proj['length'] ?>m x <?= $proj['height'] ?>m x <?= $proj['width'] ?>m</td>
                                <td class="py-3 text-center text-text-3 font-mono"><?= date('d/m/Y H:i', strtotime($proj['created_at'])) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (count($projetosRecentes) === 0): ?>
                            <tr>
                                <td colspan="3" class="py-8 text-center text-text-3 font-medium">Nenhum projeto salvo.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- Right: Low Stock Alert -->
        <div class="bg-bg-card border border-bg-border rounded-2xl p-5 shadow-lg flex flex-col">
            <h2 class="text-base font-bold text-text-1 mb-4 flex items-center gap-2">
                <svg class="w-5 h-5 text-neon-red" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                Estoque Crítico (Abaixo de 15 pçs)
            </h2>
            
            <div class="overflow-x-auto flex-1">
                <table class="w-full text-xs text-left">
                    <thead>
                        <tr class="border-b border-bg-border text-text-3">
                            <th class="py-2.5 font-bold uppercase tracking-wider text-[10px]">Peça / Componente</th>
                            <th class="py-2.5 font-bold uppercase tracking-wider text-[10px] text-center">Código</th>
                            <th class="py-2.5 font-bold uppercase tracking-wider text-[10px] text-center">Tipo</th>
                            <th class="py-2.5 font-bold uppercase tracking-wider text-[10px] text-center">Disponível</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-bg-border/60">
                        <?php foreach ($pecasEstoqueBaixo as $peca): ?>
                            <tr class="hover:bg-bg-surface/30">
                                <td class="py-3 font-semibold text-text-1"><?= htmlspecialchars($peca['nome']) ?></td>
                                <td class="py-3 text-center text-text-3 font-mono"><?= htmlspecialchars($peca['codigo']) ?></td>
                                <td class="py-3 text-center">
                                    <span class="px-2 py-0.5 rounded-full text-[9px] font-bold bg-bg-surface border border-bg-border text-text-2">
                                        <?= htmlspecialchars($peca['tipo']) ?>
                                    </span>
                                </td>
                                <td class="py-3 text-center font-bold font-mono <?= $peca['estoque'] == 0 ? 'text-neon-red' : 'text-neon-cyan' ?>">
                                    <?= $peca['estoque'] ?> pçs
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (count($pecasEstoqueBaixo) === 0): ?>
                            <tr>
                                <td colspan="4" class="py-8 text-center text-neon-green font-bold">Excelente! Todo o estoque está acima de 15 peças.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
    </div>
</div>

<?php Layout::end(); ?>

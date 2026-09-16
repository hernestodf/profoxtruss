<?php
Layout::set('main');
Layout::block('title', 'Peças & Estoque');
Layout::start('content');
?>

<div class="space-y-6" x-data="pecasManager()">
    <!-- Header Page Toolbar -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-bg-card border border-bg-border rounded-2xl p-6 shadow-lg">
        <div>
            <h1 class="text-2xl font-black text-text-1 flex items-center gap-2">
                <svg class="w-7 h-7 text-neon-cyan" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                Catálogo de Peças & Estoque
            </h1>
            <p class="text-xs text-text-3 mt-1">Gerencie componentes, pesos, dimensões e níveis de inventário em tempo real.</p>
        </div>
        
        <button @click="openNewForm()" class="btn btn-cyan shadow-neon-cyan/25 flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
            Cadastrar Componente
        </button>
    </div>

    <!-- Stats Bar -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Stat 1 -->
        <div class="bg-bg-card border border-bg-border rounded-2xl p-5 shadow-lg flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-neon-cyan/15 flex items-center justify-center text-neon-cyan">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/></svg>
            </div>
            <div>
                <div class="text-[10px] text-text-3 font-bold uppercase tracking-wider">Total de Peças</div>
                <div class="text-2xl font-black text-text-1 font-mono" x-text="totalPecas">0</div>
            </div>
        </div>

        <!-- Stat 2 -->
        <div class="bg-bg-card border border-bg-border rounded-2xl p-5 shadow-lg flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-neon-green/15 flex items-center justify-center text-neon-green">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
            </div>
            <div>
                <div class="text-[10px] text-text-3 font-bold uppercase tracking-wider">Estoque Geral</div>
                <div class="text-2xl font-black text-text-1 font-mono" x-text="totalEstoque">0</div>
            </div>
        </div>

        <!-- Stat 3 -->
        <div class="bg-bg-card border border-bg-border rounded-2xl p-5 shadow-lg flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-neon-red/15 flex items-center justify-center text-neon-red">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            </div>
            <div>
                <div class="text-[10px] text-text-3 font-bold uppercase tracking-wider">Sem Estoque</div>
                <div class="text-2xl font-black text-text-1 font-mono" x-text="semEstoque">0</div>
            </div>
        </div>

        <!-- Stat 4 -->
        <div class="bg-bg-card border border-bg-border rounded-2xl p-5 shadow-lg flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-neon-purple/15 flex items-center justify-center text-neon-purple">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/></svg>
            </div>
            <div>
                <div class="text-[10px] text-text-3 font-bold uppercase tracking-wider">Categorias / Tipos</div>
                <div class="text-2xl font-black text-text-1 font-mono" x-text="tiposCount">0</div>
            </div>
        </div>
    </div>

    <!-- Filters & List Section -->
    <div class="bg-bg-card border border-bg-border rounded-2xl shadow-lg overflow-hidden flex flex-col">
        <!-- Toolbar Filters -->
        <div class="p-5 border-b border-bg-border flex flex-col md:flex-row gap-4 items-center justify-between bg-bg-surface/30">
            <!-- Search field -->
            <div class="flex items-center gap-2.5 bg-bg-surface border border-bg-border rounded-xl px-4 py-2.5 w-full md:max-w-md focus-within:border-neon-cyan transition-colors">
                <svg class="w-4 h-4 text-text-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35"/></svg>
                <input type="text" x-model="searchQuery" placeholder="Buscar por código ou nome..." class="bg-transparent border-none outline-none text-sm text-text-1 w-full placeholder:text-text-4 font-sans" />
            </div>

            <!-- Filters dropdowns -->
            <div class="flex flex-wrap items-center gap-3 w-full md:w-auto">
                <div class="flex items-center gap-2 w-full sm:w-auto">
                    <span class="text-xs font-semibold text-text-3">Tipo:</span>
                    <select x-model="filterTipo" class="bg-bg-surface border border-bg-border rounded-xl px-3 py-2 text-xs text-text-1 focus:outline-none focus:border-neon-cyan w-full sm:w-36">
                        <option value="">Todos</option>
                        <option value="Q30">Box Truss Q30</option>
                        <option value="plana">Treliça Plana Q30</option>
                        <option value="braco">Braço (barra)</option>
                        <option value="cubo">Cubo Conector 0,30m</option>
                        <option value="grepo">Grepo</option>
                        <option value="sapata">Sapata (base de apoio)</option>
                        <option value="sleeve">Sleeve</option>
                        <option value="montante">Montante (Perfil PM5)</option>
                        <option value="travessa">Travessa (Perfil PM5)</option>
                    </select>
                </div>

                <div class="flex items-center gap-2 w-full sm:w-auto">
                    <span class="text-xs font-semibold text-text-3">Status:</span>
                    <select x-model="filterStatus" class="bg-bg-surface border border-bg-border rounded-xl px-3 py-2 text-xs text-text-1 focus:outline-none focus:border-neon-cyan w-full sm:w-36">
                        <option value="">Todos</option>
                        <option value="1">Ativos</option>
                        <option value="0">Inativos</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Parts Table -->
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm whitespace-nowrap">
                <thead>
                    <tr class="bg-bg-surface/10 border-b border-bg-border/60 text-text-3 font-semibold text-xs tracking-wider uppercase">
                        <th class="px-6 py-4">Código</th>
                        <th class="px-6 py-4">Nome da Peça</th>
                        <th class="px-6 py-4 text-center">Tipo</th>
                        <th class="px-6 py-4 text-right">Comprimento</th>
                        <th class="px-6 py-4 text-right">Peso</th>
                        <th class="px-6 py-4 text-center">Estoque</th>
                        <th class="px-6 py-4 text-center">Status</th>
                        <th class="px-6 py-4 text-center">Ações</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-bg-border/50">
                    <template x-for="peca in filteredPecas" :key="peca.id">
                        <tr class="hover:bg-bg-surface/25 transition-colors group">
                            <!-- Code -->
                            <td class="px-6 py-4.5 font-bold font-mono text-neon-cyan" x-text="peca.codigo"></td>
                            
                            <!-- Name -->
                            <td class="px-6 py-4.5 font-semibold text-text-1" x-text="peca.nome"></td>
                            
                            <!-- Type badge -->
                            <td class="px-6 py-4.5 text-center">
                                 <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold"
                                      x-show="peca.tipo !== 'montante' && peca.tipo !== 'travessa'"
                                      :class="{
                                          'bg-neon-cyan/15 text-neon-cyan border border-neon-cyan/25': peca.tipo === 'Q30',
                                          'bg-orange-500/15 text-orange-400 border border-orange-500/25': peca.tipo === 'cubo',
                                          'bg-rose-500/15 text-rose-400 border border-rose-500/25': peca.tipo === 'grepo',
                                          'bg-neon-purple/15 text-neon-purple border border-neon-purple/25': peca.tipo === 'sleeve',
                                          'bg-amber-600/15 text-amber-400 border border-amber-600/25': peca.tipo === 'sapata'
                                      }"
                                      x-text="peca.tipo === 'Q30' ? 'Box Truss Q30' : (peca.tipo === 'cubo' ? 'Cubo 0,30m' : (peca.tipo === 'grepo' ? 'Grepo' : (peca.tipo === 'sapata' ? 'Sapata' : (peca.tipo === 'sleeve' ? 'Sleeve' : peca.tipo))))">
                                </span>
                                <!-- Montante/Travessa: cor real do modelo (varia por comprimento), não uma cor fixa por tipo -->
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold border"
                                      x-show="peca.tipo === 'montante' || peca.tipo === 'travessa'"
                                      :style="`background-color:${peca.cor}26; color:${peca.cor}; border-color:${peca.cor}40`"
                                      x-text="peca.tipo === 'montante' ? 'Montante' : 'Travessa'">
                                </span>
                            </td>

                            <!-- Length / Size -->
                            <td class="px-6 py-4.5 text-right font-mono font-medium text-text-2">
                                <span x-text="peca.tipo === 'Q30' || peca.tipo === 'grepo' || peca.tipo === 'sapata' || peca.tipo === 'montante' || peca.tipo === 'travessa' ? parseFloat(peca.comprimento).toFixed(2) + ' m' : (peca.tipo === 'cubo' ? '0,30 m' : '—')"></span>
                            </td>

                            <!-- Weight -->
                            <td class="px-6 py-4.5 text-right font-mono font-medium text-text-2" x-text="parseFloat(peca.peso).toFixed(2) + ' kg'"></td>

                            <!-- Stock level with coloring -->
                            <td class="px-6 py-4.5 text-center font-mono font-bold">
                                <span class="px-3 py-1 rounded-xl text-xs"
                                      :class="parseInt(peca.estoque) > 5 ? 'bg-neon-green/10 text-neon-green' : (parseInt(peca.estoque) > 0 ? 'bg-neon-purple/10 text-neon-purple' : 'bg-neon-red/10 text-neon-red')"
                                      x-text="peca.estoque">
                                </span>
                            </td>

                            <!-- Active status badge -->
                            <td class="px-6 py-4.5 text-center">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold"
                                      :class="peca.ativo == 1 ? 'bg-neon-green/10 text-neon-green' : 'bg-neon-red/10 text-neon-red'"
                                      x-text="peca.ativo == 1 ? 'ATIVO' : 'INATIVO'">
                                </span>
                            </td>

                            <!-- Actions -->
                            <td class="px-6 py-4.5 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <button @click="openEditForm(peca)" class="btn btn-ghost btn-xs text-neon-cyan border-neon-cyan/20 hover:bg-neon-cyan/10" title="Editar Peça">
                                        Editar
                                    </button>
                                    <button @click="deletePeca(peca.id, peca.nome)" class="btn btn-ghost btn-xs text-neon-red border-neon-red/20 hover:bg-neon-red/10" title="Excluir Peça">
                                        Excluir
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </template>

                    <template x-if="filteredPecas.length === 0">
                        <tr>
                            <td colspan="9" class="py-12 text-center text-text-3 font-semibold">
                                <span x-show="loading" class="btn-spin inline-block mr-2"></span>
                                <span x-text="loading ? 'Carregando catálogo...' : 'Nenhum componente cadastrado ou encontrado.'"></span>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>

    <!-- MODAL: Create / Edit Piece Form -->
    <ui-modal id="modal-peca-form" size="md" :title="form.id ? 'Editar Componente' : 'Cadastrar Componente'" sub="Preencha os campos para salvar a peça no catálogo" color="cyan">
        <div class="space-y-4 py-1" @keydown.enter="savePeca()">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-text-3 uppercase tracking-wider mb-2">Código da Peça</label>
                    <input type="text" x-model="form.codigo" :disabled="form.id !== null" class="w-full bg-bg-surface border border-bg-border rounded-xl px-4 py-2.5 text-text-1 focus:outline-none focus:border-neon-cyan font-mono disabled:opacity-50" placeholder="Ex: Q30-100" />
                </div>
                <div>
                    <label class="block text-xs font-bold text-text-3 uppercase tracking-wider mb-2">Tipo / Categoria</label>
                    <select x-model="form.tipo" class="w-full bg-bg-surface border border-bg-border rounded-xl px-4 py-2.5 text-text-1 focus:outline-none focus:border-neon-cyan font-semibold">
                        <option value="Q30">Box Truss Q30</option>
                        <option value="plana">Treliça Plana Q30</option>
                        <option value="braco">Braço (barra)</option>
                        <option value="cubo">Cubo Conector 0,30m</option>
                        <option value="grepo">Grepo</option>
                        <option value="sapata">Sapata (base de apoio)</option>
                        <option value="sleeve">Sleeve</option>
                        <option value="montante">Montante (Perfil PM5, vertical)</option>
                        <option value="travessa">Travessa (Perfil PM5, horizontal)</option>
                    </select>
                </div>

            <div>
                <label class="block text-xs font-bold text-text-3 uppercase tracking-wider mb-2">Nome do Componente</label>
                <input type="text" x-model="form.nome" class="w-full bg-bg-surface border border-bg-border rounded-xl px-4 py-2.5 text-text-1 focus:outline-none focus:border-neon-cyan font-semibold" placeholder="Ex: Box Truss Q30 1.00m" />
            </div>

            <div class="grid grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-bold text-text-3 uppercase tracking-wider mb-2">Comprimento (m)</label>
                    <input type="number" step="0.01" min="0" x-model.number="form.comprimento" :disabled="form.tipo !== 'Q30' && form.tipo !== 'cubo' && form.tipo !== 'grepo' && form.tipo !== 'sapata' && form.tipo !== 'montante' && form.tipo !== 'travessa'" class="w-full bg-bg-surface border border-bg-border rounded-xl px-4 py-2.5 text-text-1 focus:outline-none focus:border-neon-cyan font-mono disabled:opacity-50" />
                </div>
                <div>
                    <label class="block text-xs font-bold text-text-3 uppercase tracking-wider mb-2">Peso (kg)</label>
                    <input type="number" step="0.01" min="0" x-model.number="form.peso" class="w-full bg-bg-surface border border-bg-border rounded-xl px-4 py-2.5 text-text-1 focus:outline-none focus:border-neon-cyan font-mono" />
                </div>
                <div>
                    <label class="block text-xs font-bold text-text-3 uppercase tracking-wider mb-2">Preço (R$)</label>
                    <input type="number" step="0.01" min="0" x-model.number="form.preco" class="w-full bg-bg-surface border border-bg-border rounded-xl px-4 py-2.5 text-text-1 focus:outline-none focus:border-neon-cyan font-mono" />
                </div>
            </div>

            <div x-show="form.tipo === 'montante' || form.tipo === 'travessa'">
                <label class="block text-xs font-bold text-text-3 uppercase tracking-wider mb-2">Cor de Identificação (2D/3D)</label>
                <div class="flex items-center gap-3">
                    <input type="color" x-model="form.cor" class="w-14 h-10 bg-bg-surface border border-bg-border rounded-xl cursor-pointer" />
                    <span class="text-xs text-text-3 font-mono" x-text="form.cor"></span>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4 border-t border-bg-border/60 pt-4 mt-2">
                <div>
                    <label class="block text-xs font-bold text-text-3 uppercase tracking-wider mb-2">Quantidade em Estoque</label>
                    <input type="number" min="0" x-model.number="form.estoque" class="w-full bg-bg-surface border border-bg-border rounded-xl px-4 py-2.5 text-text-1 focus:outline-none focus:border-neon-cyan font-mono font-bold" />
                </div>
                <div>
                    <label class="block text-xs font-bold text-text-3 uppercase tracking-wider mb-2">Status da Peça</label>
                    <select x-model.number="form.ativo" class="w-full bg-bg-surface border border-bg-border rounded-xl px-4 py-2.5 text-text-1 focus:outline-none focus:border-neon-cyan font-semibold">
                        <option value="1">Ativo (visível no catálogo)</option>
                        <option value="0">Inativo (oculto)</option>
                    </select>
                </div>
            </div>

            <div class="flex justify-end gap-2.5 mt-8 border-t border-bg-border/60 pt-4">
                <button class="btn btn-ghost" @click="closeModal()">Cancelar</button>
                <button class="btn btn-cyan px-6" @click="savePeca()">
                    <span x-show="!saving">Salvar Peça</span>
                    <span x-show="saving" class="btn-spin"></span>
                </button>
            </div>
        </div>
    </ui-modal>
</div>

<script>
function pecasManager() {
    return {
        pecas: [],
        searchQuery: '',
        filterTipo: '',
        filterStatus: '',
        loading: false,
        saving: false,
        
        form: {
            id: null,
            codigo: '',
            nome: '',
            tipo: 'Q30',
            comprimento: 0.00,
            peso: 0.00,
            preco: 0.00,
            estoque: 0,
            ativo: 1,
            cor: '#38bdf8'
        },
        
        init() {
            this.carregarPecas();
        },
        
        carregarPecas() {
            this.loading = true;
            fetch('<?= url("/api/pecas") ?>')
                .then(r => r.json())
                .then(res => {
                    this.loading = false;
                    if (res.ok) {
                        this.pecas = res.data;
                    }
                })
                .catch(err => {
                    this.loading = false;
                    console.error(err);
                });
        },
        
        get filteredPecas() {
            return this.pecas.filter(p => {
                const query = this.searchQuery.toLowerCase();
                const matchesSearch = p.nome.toLowerCase().includes(query) || 
                                      p.codigo.toLowerCase().includes(query);
                const matchesTipo = !this.filterTipo || p.tipo === this.filterTipo;
                const matchesStatus = this.filterStatus === '' || p.ativo == this.filterStatus;
                return matchesSearch && matchesTipo && matchesStatus;
            });
        },
        
        get totalPecas() {
            return this.pecas.length;
        },
        
        get totalEstoque() {
            return this.pecas.reduce((acc, p) => acc + (parseInt(p.estoque) || 0), 0);
        },
        
        get semEstoque() {
            return this.pecas.filter(p => p.ativo == 1 && (parseInt(p.estoque) || 0) === 0).length;
        },
        
        get tiposCount() {
            const unique = new Set(this.pecas.map(p => p.tipo));
            return unique.size;
        },
        
        openNewForm() {
            this.form = {
                id: null,
                codigo: '',
                nome: '',
                tipo: 'Q30',
                comprimento: 0.00,
                peso: 0.00,
                preco: 0.00,
                estoque: 0,
                ativo: 1,
                cor: '#38bdf8'
            };
            if (window.openModal) {
                openModal('modal-peca-form');
            }
        },

        openEditForm(peca) {
            this.form = {
                id: peca.id,
                codigo: peca.codigo,
                nome: peca.nome,
                tipo: peca.tipo,
                comprimento: parseFloat(peca.comprimento),
                peso: parseFloat(peca.peso),
                preco: parseFloat(peca.preco),
                estoque: parseInt(peca.estoque),
                ativo: parseInt(peca.ativo),
                cor: peca.cor || '#38bdf8'
            };
            if (window.openModal) {
                openModal('modal-peca-form');
            }
        },
        
        savePeca() {
            if (!this.form.codigo || !this.form.nome || !this.form.tipo) {
                if (window.showToast) {
                    showToast('red', 'Campos Obrigatórios', 'Código, Nome e Tipo são obrigatórios.');
                } else {
                    alert('Código, Nome e Tipo são obrigatórios.');
                }
                return;
            }
            
            // Ajusta comprimento conforme tipo
            if (this.form.tipo === 'cubo') {
                this.form.comprimento = 0.30;
            } else if (this.form.tipo === 'sapata') {
                if (!this.form.comprimento || this.form.comprimento <= 0) {
                    this.form.comprimento = 0.50;
                }
            } else if (this.form.tipo === 'montante' || this.form.tipo === 'travessa') {
                if (!this.form.comprimento || this.form.comprimento <= 0) {
                    if (window.showToast) {
                        showToast('red', 'Comprimento Obrigatório', 'Montante/Travessa precisam de um comprimento maior que zero.');
                    } else {
                        alert('Montante/Travessa precisam de um comprimento maior que zero.');
                    }
                    return;
                }
            } else if (this.form.tipo !== 'Q30' && this.form.tipo !== 'grepo') {
                this.form.comprimento = 0;
            }

            this.saving = true;
            fetch('<?= url("/api/pecas") ?>', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', ...window.csrfHeader() },
                body: JSON.stringify(this.form)
            })
            .then(r => r.json())
            .then(res => {
                this.saving = false;
                if (res.ok) {
                    if (window.closeModal) closeModal();
                    this.carregarPecas();
                    if (window.showToast) {
                        showToast('green', 'Sucesso', 'Peça salva com sucesso!');
                    }
                } else {
                    if (window.showToast) {
                        showToast('red', 'Erro', res.error || 'Erro ao salvar peça.');
                    } else {
                        alert('Erro: ' + res.error);
                    }
                }
            })
            .catch(err => {
                this.saving = false;
                console.error(err);
                if (window.showToast) {
                    showToast('red', 'Erro', 'Erro de conexão.');
                }
            });
        },
        
        deletePeca(id, nome) {
            if (!confirm(`Tem certeza que deseja excluir a peça "${nome}"?`)) return;
            
            fetch(`<?= url("/api/pecas") ?>/${id}`, {
                method: 'DELETE',
                headers: window.csrfHeader()
            })
            .then(r => r.json())
            .then(res => {
                if (res.ok) {
                    this.carregarPecas();
                    if (window.showToast) {
                        showToast('green', 'Sucesso', 'Peça excluída com sucesso!');
                    }
                } else {
                    if (window.showToast) {
                        showToast('red', 'Erro', res.error || 'Erro ao excluir peça.');
                    } else {
                        alert('Erro: ' + res.error);
                    }
                }
            })
            .catch(err => {
                console.error(err);
                if (window.showToast) {
                    showToast('red', 'Erro', 'Erro de conexão.');
                }
            });
        }
    };
}
</script>

<?php Layout::end(); ?>

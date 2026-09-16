<?php
Layout::set('main');
Layout::block('title', 'Calculadora Box Truss Q30');
Layout::start('content');
?>

<div class="grid grid-cols-1 xl:grid-cols-12 gap-6" x-data="trussCalculator()">
    
    <!-- LEFT PANEL: Configuration & Draggable/Clickable Components -->
    <div class="xl:col-span-3 flex flex-col gap-6">
        
        <!-- Component Catalog Card -->
        <div class="bg-bg-card border border-bg-border rounded-2xl p-5 shadow-lg flex-1 flex flex-col min-h-0">
            <h2 class="text-base font-bold text-text-1 mb-1 flex items-center gap-2 shrink-0">
                <svg class="w-5 h-5 text-neon-cyan" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                Componentes Disponíveis
            </h2>
            <p class="text-xs text-text-3 mb-4 shrink-0">Clique para adicionar ou arraste para o canvas.</p>

            <div class="grid grid-cols-2 auto-rows-max content-start gap-2 overflow-y-auto flex-1 min-h-0 pr-1">
                <template x-for="peca in catalog" :key="peca.id">
                    <div draggable="true"
                         @dragstart="onDragStart($event, peca)"
                         @click="addComponentToCenter(peca)"
                         class="flex flex-col items-center justify-between p-3 bg-bg-surface border border-bg-border rounded-xl cursor-pointer hover:border-neon-cyan hover:bg-bg-hover group transition-all duration-150">
                        
                        <!-- Mini Icon representation based on type -->
                        <div class="w-8 h-8 rounded flex items-center justify-center mb-2 font-black text-[10px]"
                             :class="{
                                 'bg-neon-cyan/15 text-neon-cyan border border-neon-cyan/25': peca.tipo === 'Q30',
                                 'bg-lime-500/15 text-lime-400 border border-lime-500/25': peca.tipo === 'plana',
                                 'bg-teal-500/15 text-teal-400 border border-teal-500/25': peca.tipo === 'braco',
                                 'bg-neon-purple/15 text-neon-purple border border-neon-purple/25': peca.tipo === 'sleeve' || peca.tipo === 'sleeve_4faces',
                                 'bg-orange-500/15 text-orange-400 border border-orange-500/25': peca.tipo === 'cubo',
                                 'bg-rose-500/15 text-rose-400 border border-rose-500/25': peca.tipo === 'grepo',
                                 'bg-amber-600/15 text-amber-400 border border-amber-600/25': peca.tipo === 'sapata'
                             }"
                             :style="(peca.tipo === 'montante' || peca.tipo === 'travessa') ? `background-color:${peca.cor}26; color:${peca.cor}; border:1px solid ${peca.cor}40` : ''">
                             <span x-text="(peca.tipo === 'Q30' || peca.tipo === 'plana' || peca.tipo === 'braco' || peca.tipo === 'montante' || peca.tipo === 'travessa') ? peca.comprimento + 'm' : (peca.tipo === 'sleeve_4faces' ? 'SF' : (peca.tipo === 'sleeve' ? 'SL' : (peca.tipo === 'cubo' ? 'CB' : (peca.tipo === 'grepo' ? 'GR' : (peca.tipo === 'sapata' ? 'SP' : '')))))"></span>
                            <template x-if="peca.tipo === 'sleeve_4faces'">
                                <span class="ml-1 px-1 py-0.5 rounded text-[8px] font-bold bg-amber-500/20 text-amber-500">MÓVEL</span>
                            </template>
                        </div>
                        
                        <div class="text-[11px] font-bold text-text-1 text-center group-hover:text-neon-cyan" x-text="peca.nome"></div>
                        <div class="text-[9px] text-text-3 mt-1" x-text="peca.peso + ' kg'"></div>
                        
                        <!-- Stock info -->
                        <div class="mt-2 text-[9px] font-semibold px-2 py-0.5 rounded-full"
                             :class="peca.estoque > 0 ? 'bg-neon-green/10 text-neon-green' : 'bg-neon-red/10 text-neon-red'">
                            Estoque: <span x-text="peca.estoque"></span>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>

    <!-- CENTER PANEL: Interactive Canvas Area -->
    <div class="xl:col-span-6 flex flex-col gap-4">
        
        <!-- Canvas Toolbar -->
        <div class="bg-bg-card border border-bg-border rounded-2xl p-4 shadow-lg flex flex-wrap gap-3 items-center justify-between">
            <div class="flex items-center gap-3">
                <!-- Scale Selector -->
                <div class="flex items-center gap-2" x-show="viewMode !== '3d'">
                    <span class="text-xs font-semibold text-text-3">Escala:</span>
                    <select x-model.number="canvasScale" @change="updateCanvasScale()" class="bg-bg-surface border border-bg-border rounded-xl px-2.5 py-1.5 text-xs text-text-1 focus:outline-none focus:border-neon-cyan">
                        <option value="5">1m = 5px (estruturas muito grandes)</option>
                        <option value="10">1m = 10px</option>
                        <option value="15">1m = 15px</option>
                        <option value="20">1m = 20px</option>
                        <option value="30">1m = 30px</option>
                        <option value="50">1m = 50px</option>
                        <option value="75">1m = 75px</option>
                        <option value="100">1m = 100px</option>
                        <option value="150">1m = 150px</option>
                        <option value="200">1m = 200px (detalhe)</option>
                    </select>
                </div>
                
                <!-- Grid Toggle Button -->
                <button x-show="viewMode !== '3d'" @click="toggleGrid()" :class="gridEnabled ? 'bg-neon-cyan/20 border-neon-cyan text-neon-cyan' : 'bg-bg-surface border-bg-border text-text-3'" class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl border text-xs font-bold transition-all">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                    Grade
                </button>

                <!-- Undo/Redo Buttons -->
                <div class="flex items-center gap-1 bg-bg-surface border border-bg-border rounded-xl px-2 py-1">
                    <button @click="undo()" :disabled="!canUndo()" :class="canUndo() ? 'text-text-1 hover:text-neon-cyan' : 'text-text-3/40 cursor-not-allowed'" class="rounded px-1.5 py-0.5 font-bold text-sm leading-none transition-colors" title="Desfazer (Ctrl+Z)">↶</button>
                    <button @click="redo()" :disabled="!canRedo()" :class="canRedo() ? 'text-text-1 hover:text-neon-cyan' : 'text-text-3/40 cursor-not-allowed'" class="rounded px-1.5 py-0.5 font-bold text-sm leading-none transition-colors" title="Refazer (Ctrl+Y)">↷</button>
                </div>
                
                <!-- Sketch Mode Button -->
                <button x-show="viewMode !== '3d'" @click="toggleSketchMode()"
                        :style="sketchMode ? 'background:rgba(245,158,11,0.2);border-color:#f59e0b;color:#f59e0b' : 'background:var(--color-bg-surface);border-color:var(--color-bg-border);color:var(--color-text-3)'"
                        class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl border text-xs font-bold transition-all"
                        title="Modo Sketch — desenhe pontos para gerar estrutura">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                    Sketch
                    <span x-show="sketchMode" class="text-[9px] font-bold px-1.5 py-0.5 rounded-full" style="background:#f59e0b;color:#000">ON</span>
                </button>

                <!-- Zoom Controls -->
                <div class="flex items-center gap-1 bg-bg-surface border border-bg-border rounded-xl px-2 py-1" x-show="viewMode !== '3d'">
                    <button @click="zoomOut()" class="text-text-3 hover:text-text-1 hover:bg-bg-hover rounded px-1.5 py-0.5 font-bold text-sm leading-none transition-colors" title="Reduzir zoom">−</button>
                    <span class="text-xs font-mono text-text-2 min-w-[3rem] text-center select-none" x-text="Math.round(canvasZoom * 100) + '%'"></span>
                    <button @click="zoomIn()" class="text-text-3 hover:text-text-1 hover:bg-bg-hover rounded px-1.5 py-0.5 font-bold text-sm leading-none transition-colors" title="Aumentar zoom">+</button>
                    <button @click="resetZoom()" class="text-text-3 hover:text-text-1 hover:bg-bg-hover rounded px-1.5 py-0.5 text-xs leading-none transition-colors ml-1" title="Resetar zoom (100%)">⟲</button>
                    <button @click="fitToView()" class="text-text-3 hover:text-text-1 hover:bg-bg-hover rounded px-1.5 py-0.5 text-xs leading-none transition-colors ml-1" title="Ajustar à tela">⊡</button>
                </div>
            </div>

            <!-- Quebra de linha forçada para a barra de Acessórios ficar abaixo -->
            <div class="basis-full h-0" x-show="viewMode !== '3d'"></div>

            <!-- Acessórios Visuais (Lona / Painel de LED) — não entram no quantitativo -->
            <div class="flex items-center gap-1 bg-bg-surface border border-bg-border rounded-xl px-2 py-1" x-show="viewMode !== '3d'">
                <span class="text-xs font-semibold text-text-3 mr-1">Acessórios:</span>
                <input type="number" x-model.number="accessoryWidth" min="0.1" step="0.1" class="bg-bg-card border border-bg-border rounded-lg px-1 py-1 text-xs text-text-1 w-11 text-center focus:outline-none focus:border-neon-cyan" title="Largura (m)">
                <span class="text-text-3 text-xs">×</span>
                <input type="number" x-model.number="accessoryHeight" min="0.1" step="0.1" class="bg-bg-card border border-bg-border rounded-lg px-1 py-1 text-xs text-text-1 w-11 text-center focus:outline-none focus:border-neon-cyan" title="Altura (m)">
                <input type="color" x-model="accessoryColor" @change="applyAccessoryColor()" class="w-6 h-6 rounded-lg cursor-pointer border border-bg-border bg-bg-card p-0.5" title="Cor da Lona: usada nas próximas lonas; com lona(s) selecionada(s), recolore na hora">
                <input type="range" min="10" max="100" step="5" x-model.number="accessoryOpacity" @change="applyAccessoryOpacity()" class="w-14 cursor-pointer accent-cyan-400" :title="'Transparência da Lona: ' + accessoryOpacity + '% (100% = opaca). Vale para cor e imagem; com lona(s) selecionada(s), aplica na hora'">
                <input type="file" accept="image/png,image/jpeg" class="hidden" x-ref="lonaImageInput" @change="applyLonaImage($event)">
                <button @click="$refs.lonaImageInput.click()" class="px-1.5 py-1 rounded-lg border text-xs font-bold transition-all bg-bg-card border-bg-border text-text-3 hover:text-text-1" title="Preencher a(s) Lona(s) selecionada(s) com uma imagem PNG/JPG">📷</button>
                <button @click="removeLonaImage()" class="px-1.5 py-1 rounded-lg border text-xs font-bold transition-all bg-bg-card border-bg-border text-text-3 hover:text-text-1" title="Remover a imagem da(s) Lona(s) selecionada(s)">📷🚫</button>
                <button @click="addAccessory('lona', accessoryWidth, accessoryHeight)" class="px-1.5 py-1 rounded-lg border text-xs font-bold transition-all bg-bg-card border-bg-border text-text-3 hover:text-text-1" title="Adicionar Lona com as dimensões ao lado (centro da tela)">🖼️</button>
                <button @click="toggleAccessorySketch('lona')" :style="accessorySketchType === 'lona' ? 'background:rgba(245,158,11,0.2);border-color:#f59e0b;color:#f59e0b' : ''" class="px-1.5 py-1 rounded-lg border text-xs font-bold transition-all bg-bg-card border-bg-border text-text-3 hover:text-text-1" title="Desenhar Lona: clique em 2 pontos (cantos opostos)">🖼️✏️</button>
                <button @click="addAccessory('painel_led', accessoryWidth, accessoryHeight)" class="px-1.5 py-1 rounded-lg border text-xs font-bold transition-all bg-bg-card border-bg-border text-text-3 hover:text-text-1" title="Adicionar Painel de LED com as dimensões ao lado (centro da tela)">💡</button>
                <button @click="toggleAccessorySketch('painel_led')" :style="accessorySketchType === 'painel_led' ? 'background:rgba(34,211,238,0.2);border-color:#22d3ee;color:#22d3ee' : ''" class="px-1.5 py-1 rounded-lg border text-xs font-bold transition-all bg-bg-card border-bg-border text-text-3 hover:text-text-1" title="Desenhar Painel de LED: clique em 2 pontos (cantos opostos)">💡✏️</button>
                <button @click="addAccessory('parled')" class="px-1.5 py-1 rounded-lg border text-xs font-bold transition-all bg-bg-card border-bg-border text-text-3 hover:text-text-1" title="Adicionar PAR LED (refletor, tamanho fixo ~0.25m — centro da tela)">🔦</button>

                <span class="h-5 w-px bg-bg-border mx-1"></span>

                <input type="text" x-model="labelText" maxlength="40" placeholder="Texto" class="bg-bg-card border border-bg-border rounded-lg px-2 py-1 text-xs text-text-1 w-24 focus:outline-none focus:border-neon-cyan" title="Texto do label">
                <button @click="addLabel(labelText)" class="px-1.5 py-1 rounded-lg border text-xs font-bold transition-all bg-bg-card border-bg-border text-text-3 hover:text-text-1" title="Adicionar Label com o texto ao lado (centro da tela)">🏷️</button>
            </div>

            <div class="flex items-center gap-2">
                <!-- Delete Component -->
                <button x-show="viewMode !== '3d'" @click="deleteSelected()" class="btn btn-ghost btn-sm text-neon-red border-neon-red/25 hover:bg-neon-red/10" title="Deletar componente selecionado (Teclado: Del/Backspace)">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    Remover
                </button>
                
                <!-- Clear Canvas -->
                <button @click="clearCanvas()" class="btn btn-ghost btn-sm">
                    Limpar Tudo
                </button>
                
                <div class="h-5 w-px bg-bg-border mx-1"></div>
                
                <!-- Project Name Input & Direct Save -->
                <div class="flex items-center gap-2">
                    <span class="text-xs font-semibold text-text-3">Projeto:</span>
                    <input type="text" x-model="projectName" class="bg-bg-surface border border-bg-border rounded-xl px-3 py-1.5 text-xs text-text-1 focus:outline-none focus:border-neon-cyan w-40 font-semibold" placeholder="Nome do Projeto" />
                    <span x-show="currentProjectId && savedProjectName && projectName.trim() !== savedProjectName.trim()"
                          class="text-[10px] font-bold text-neon-cyan whitespace-nowrap"
                          :title="`Nome alterado: ao salvar, será criada uma cópia. O projeto '${savedProjectName}' será preservado.`">
                        ⎘ salvará como cópia
                    </span>
                </div>

                <!-- Save Layout -->
                <button @click="saveProject()" class="btn btn-cyan btn-sm shadow-neon-cyan/20">
                    <span x-show="!saving && !(currentProjectId && savedProjectName && projectName.trim() !== savedProjectName.trim())">Salvar</span>
                    <span x-show="!saving && currentProjectId && savedProjectName && projectName.trim() !== savedProjectName.trim()">Salvar Cópia</span>
                    <span x-show="saving" class="btn-spin"></span>
                </button>
                
                <!-- Load Layout -->
                <button @click="openLoadModal()" class="btn btn-ghost btn-sm border-bg-border">
                    Projetos
                </button>
            </div>
        </div>
        
        <!-- Canvas Board Card -->
        <div class="bg-bg-card border border-bg-border rounded-2xl overflow-hidden shadow-lg flex flex-col items-center justify-center p-4">
            <div class="w-full flex items-center justify-between text-xs text-text-3 font-semibold mb-3">
                <div class="flex items-center gap-1.5 bg-bg-surface border border-bg-border rounded-xl p-1 shrink-0">
                    <button @click="switchView('2d_frontal')" :class="viewMode === '2d_frontal' ? 'bg-neon-cyan/10 text-neon-cyan border-neon-cyan/20' : 'text-text-3 border-transparent hover:text-text-1'" class="px-3.5 py-1.5 rounded-lg border text-xs font-bold transition-all flex items-center gap-1">
                        🎨 Frente
                    </button>
                    <button @click="switchView('2d_fundo')" :class="viewMode === '2d_fundo' ? 'bg-neon-cyan/10 text-neon-cyan border-neon-cyan/20' : 'text-text-3 border-transparent hover:text-text-1'" class="px-3.5 py-1.5 rounded-lg border text-xs font-bold transition-all flex items-center gap-1">
                        🎨 Fundo
                    </button>
                    <button @click="switchView('2d_lateral')" :class="viewMode === '2d_lateral' ? 'bg-neon-cyan/10 text-neon-cyan border-neon-cyan/20' : 'text-text-3 border-transparent hover:text-text-1'" class="px-3.5 py-1.5 rounded-lg border text-xs font-bold transition-all flex items-center gap-1">
                        📐 Lado Esq
                    </button>
                    <button @click="switchView('2d_lateral_dir')" :class="viewMode === '2d_lateral_dir' ? 'bg-neon-cyan/10 text-neon-cyan border-neon-cyan/20' : 'text-text-3 border-transparent hover:text-text-1'" class="px-3.5 py-1.5 rounded-lg border text-xs font-bold transition-all flex items-center gap-1">
                        📐 Lado Dir
                    </button>
                    <button @click="switchViewPlanta('cima')" :class="(viewMode === '2d_superior' && plantaSide === 'cima') ? 'bg-neon-cyan/10 text-neon-cyan border-neon-cyan/20' : 'text-text-3 border-transparent hover:text-text-1'" class="px-3.5 py-1.5 rounded-lg border text-xs font-bold transition-all flex items-center gap-1">
                        📐 Planta Cima
                    </button>
                    <button @click="switchViewPlanta('baixo')" :class="(viewMode === '2d_superior' && plantaSide === 'baixo') ? 'bg-neon-cyan/10 text-neon-cyan border-neon-cyan/20' : 'text-text-3 border-transparent hover:text-text-1'" class="px-3.5 py-1.5 rounded-lg border text-xs font-bold transition-all flex items-center gap-1">
                        📐 Planta Baixo
                    </button>
                    <button @click="switchView('3d')" :class="viewMode === '3d' ? 'bg-neon-purple/10 text-neon-purple border-neon-purple/20' : 'text-text-3 border-transparent hover:text-text-1'" class="px-3.5 py-1.5 rounded-lg border text-xs font-bold transition-all flex items-center gap-1">
                        🚀 Vista 3D Preview
                    </button>
                </div>
                
                <span class="font-mono text-neon-cyan text-right shrink-0" x-show="viewMode === '2d_frontal'">Vista Frontal (olhando de frente)</span>
                <span class="font-mono text-neon-cyan text-right shrink-0" x-show="viewMode === '2d_fundo'">Vista de Fundo (olhando de trás)</span>
                <span class="font-mono text-neon-cyan text-right shrink-0" x-show="viewMode === '2d_lateral'">Vista Lateral Esquerda</span>
                <span class="font-mono text-neon-cyan text-right shrink-0" x-show="viewMode === '2d_lateral_dir'">Vista Lateral Direita</span>
                <span class="font-mono text-neon-cyan text-right shrink-0" x-show="viewMode === '2d_superior' && plantaSide === 'cima'">Planta — Metade de Cima editável (Y maior)</span>
                <span class="font-mono text-neon-cyan text-right shrink-0" x-show="viewMode === '2d_superior' && plantaSide === 'baixo'">Planta — Metade de Baixo editável (Y menor)</span>
                <span class="font-mono text-neon-purple text-right shrink-0" x-show="viewMode === '3d'">Visualizador 3D Interativo</span>
            </div>
            
            <div class="w-full text-xs text-text-3 font-semibold mb-2" x-show="viewMode === '2d_frontal' || viewMode === '2d_fundo'">
                <span>🖱️ Arraste componentes · Duplo clique rotaciona · Scroll zoom · <b>Arraste o fundo</b> panorâmica · <b>Shift+arrasto</b> seleção múltipla (só a metade atual) · <b>Ctrl+Shift+arrasto</b> seleciona também a metade travada · <b>Ctrl+C/V</b> copia/cola · <b>R</b> 45°</span>
            </div>
            <div class="w-full text-xs text-text-3 font-semibold mb-2" x-show="viewMode === '2d_lateral' || viewMode === '2d_lateral_dir'">
                <span>📐 Scroll zoom · <b>Arraste o fundo</b> panorâmica · <b>Shift+arrasto</b> seleção múltipla (só a metade atual) · <b>Ctrl+Shift+arrasto</b> seleciona também a metade travada · <b>R</b> rotaciona 45° (eixo X).</span>
            </div>
            <div class="w-full text-xs text-text-3 font-semibold mb-2" x-show="viewMode === '2d_superior'">
                <span>📐 Scroll zoom · <b>Arraste o fundo</b> panorâmica · <b>Shift+arrasto</b> seleção múltipla (só a metade atual) · <b>Ctrl+Shift+arrasto</b> seleciona também a metade travada · <b>R</b> rotaciona 45° (eixo Y).</span>
            </div>

            <div class="w-full flex items-center justify-end mb-2" x-show="viewMode === '2d_frontal' || viewMode === '2d_fundo' || viewMode === '2d_superior' || viewMode === '2d_lateral' || viewMode === '2d_lateral_dir'">
                <!-- Posição X/Y/Z da peça selecionada foi pro painel "Propriedades" na
                     coluna esquerda — fica só o Raio-X aqui (é um modo de visualização
                     da view, não uma propriedade de peça). -->
                <button @click="toggleXray()"
                        :class="xrayMode ? 'bg-neon-purple/15 text-neon-purple border-neon-purple/30' : 'border-bg-border text-text-3 hover:text-text-1'"
                        class="btn btn-ghost btn-xs text-xs border" title="Raio-X: deixa as peças mais transparentes pra ver o que está coberto na mesma posição de tela">
                    👻 Raio-X
                </button>
            </div>

            <div x-show="viewMode === '2d_frontal' || viewMode === '2d_fundo' || viewMode === '2d_superior' || viewMode === '2d_lateral' || viewMode === '2d_lateral_dir'" class="border border-bg-border rounded-xl bg-slate-950 relative overflow-hidden"
                 @dragover.prevent=""
                 @drop="onDrop($event)">
                <canvas id="trussCanvas" width="750" height="500"></canvas>
            </div>
            
            <!-- Sketch Mode Action Buttons -->
            <div x-show="sketchMode" class="flex items-center gap-2 mt-2">
                <span class="text-xs font-semibold" style="color:#f59e0b">✏️ Clique no canvas para adicionar pontos · <b>Enter</b> p/ finalizar</span>
                <div class="flex-1"></div>
                <button @click="undoSketchPoint()" class="btn btn-ghost btn-sm border-bg-border text-xs" title="Desfazer último ponto">↩ Desfazer</button>
                <button @click="clearSketch()" class="btn btn-ghost btn-sm border-bg-border text-xs" title="Limpar todos os pontos">🗑 Limpar</button>
                <button @click="generateFromSketch()" class="btn btn-cyan btn-sm text-xs shadow-neon-cyan/20" title="Gerar estrutura a partir dos pontos desenhados">🔧 Gerar Estrutura</button>
                <button @click="finishSketch()" class="btn btn-ghost btn-sm border-bg-border text-xs" title="Finalizar (Enter / Esc)">✓ Finalizar</button>
            </div>
            
            <div x-show="viewMode === '3d'" class="w-full flex items-center gap-2 mb-2 flex-wrap">
                <span class="text-xs text-text-3 font-semibold mr-auto">🖱️ Arraste gira · Scroll zoom · Botão direito move</span>
                <div class="flex items-center gap-1 bg-bg-surface border border-bg-border rounded-xl p-1">
                    <button @click="setIsometricView(0)" class="btn btn-ghost btn-xs text-xs" title="Vista isométrica — canto 1">↗ Iso 1</button>
                    <button @click="setIsometricView(1)" class="btn btn-ghost btn-xs text-xs" title="Vista isométrica — canto 2">↖ Iso 2</button>
                    <button @click="setIsometricView(2)" class="btn btn-ghost btn-xs text-xs" title="Vista isométrica — canto 3">↘ Iso 3</button>
                    <button @click="setIsometricView(3)" class="btn btn-ghost btn-xs text-xs" title="Vista isométrica — canto 4">↙ Iso 4</button>
                </div>
                <button @click="toggleXray()"
                        :class="xrayMode ? 'bg-neon-purple/15 text-neon-purple border-neon-purple/30' : 'border-bg-border text-text-3 hover:text-text-1'"
                        class="btn btn-ghost btn-xs text-xs border" title="Raio-X: deixa as peças transparentes pra ver o que está atrás">
                    👻 Raio-X
                </button>
            </div>

            <div x-show="viewMode === '3d'" class="border border-bg-border rounded-xl bg-slate-950 relative overflow-hidden w-[750px] h-[500px]" id="canvas3DContainer">
                <!-- Three.js will append the renderer canvas here -->
            </div>
            
            <div class="w-full flex items-center justify-between text-[11px] text-text-4 font-mono mt-2.5">
                <span>Resolução: 750px x 500px</span>
                <span x-show="viewMode === '2d_frontal' || viewMode === '2d_fundo' || viewMode === '2d_superior' || viewMode === '2d_lateral' || viewMode === '2d_lateral_dir'">Escala Atual: 1 metro = <span x-text="canvasScale"></span> pixels</span>
                <span x-show="viewMode === '3d'">Renderizado via Three.js (WebGL)</span>
            </div>
        </div>

        <!-- Properties Panel: peça única selecionada — info + posição X/Y/Z.
             Reaproveita selectedComp/xInput/heightInput/zInput já usados no
             topo do canvas; consolida tudo sobre "a peça selecionada" num só
             lugar em vez de espalhado pela toolbar. -->
        <div class="bg-bg-card border border-bg-border rounded-2xl p-5 shadow-lg shrink-0" x-show="selectedComp">
            <h2 class="text-base font-bold text-text-1 mb-3 flex items-center gap-2">
                <svg class="w-5 h-5 text-neon-cyan" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                Propriedades
            </h2>
            <template x-if="selectedComp">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-x-6 gap-y-2.5 text-xs items-start">
                    <div class="flex justify-between items-center gap-2">
                        <span class="text-text-3 font-medium">Nome</span>
                        <span class="font-bold text-text-1 text-right" x-text="selectedComp.nome"></span>
                    </div>
                    <div class="flex justify-between items-center gap-2">
                        <span class="text-text-3 font-medium">Código</span>
                        <span class="font-mono font-bold text-neon-cyan" x-text="selectedComp.codigo"></span>
                    </div>
                    <div class="flex justify-between items-center gap-2" x-show="selectedComp.length">
                        <span class="text-text-3 font-medium">Comprimento</span>
                        <span class="font-mono text-text-1" x-text="parseFloat(selectedComp.length || 0).toFixed(2) + 'm'"></span>
                    </div>
                    <div class="flex justify-between items-center gap-2" x-show="selectedComp.peso">
                        <span class="text-text-3 font-medium">Peso</span>
                        <span class="font-mono text-text-1" x-text="parseFloat(selectedComp.peso || 0).toFixed(2) + 'kg'"></span>
                    </div>
                    <div class="flex justify-between items-center gap-2" x-show="selectedComp.cor">
                        <span class="text-text-3 font-medium">Cor</span>
                        <span class="w-5 h-5 rounded-full border border-bg-border" :style="`background-color:${selectedComp.cor}`"></span>
                    </div>

                    <div class="col-span-2 md:col-span-4 border-t border-bg-border pt-3 mt-1">
                        <div class="text-[10px] font-bold text-text-3 uppercase tracking-wider mb-2">Posição (m)</div>
                        <div class="flex flex-wrap items-center gap-3">
                            <div class="flex items-center gap-1.5">
                                <span class="text-[10px] text-text-4 font-mono font-bold w-2.5">X</span>
                                <button @click="nudgeSelectedAxis('x', 'xInput', -0.05)" class="btn btn-ghost btn-xs text-xs px-1.5" title="X -5cm">▼</button>
                                <input type="number" step="0.05" x-model.number="xInput" @keydown.enter="applyAxisInput('x', 'xInput')"
                                       class="w-16 bg-bg-surface border border-bg-border rounded-lg px-1 py-1 text-xs text-text-1 font-mono text-center focus:outline-none focus:border-neon-cyan" />
                                <button @click="nudgeSelectedAxis('x', 'xInput', 0.05)" class="btn btn-ghost btn-xs text-xs px-1.5" title="X +5cm">▲</button>
                            </div>
                            <div class="flex items-center gap-1.5" x-show="selectedComp.tipo !== 'sapata'">
                                <span class="text-[10px] text-text-4 font-mono font-bold w-2.5">Y</span>
                                <button @click="nudgeSelectedAxis('y', 'heightInput', -0.05)" class="btn btn-ghost btn-xs text-xs px-1.5" title="Y -5cm">▼</button>
                                <input type="number" step="0.05" x-model.number="heightInput" @keydown.enter="applyAxisInput('y', 'heightInput')"
                                       class="w-16 bg-bg-surface border border-bg-border rounded-lg px-1 py-1 text-xs text-text-1 font-mono text-center focus:outline-none focus:border-neon-cyan" />
                                <button @click="nudgeSelectedAxis('y', 'heightInput', 0.05)" class="btn btn-ghost btn-xs text-xs px-1.5" title="Y +5cm">▲</button>
                            </div>
                            <div class="flex items-center gap-1.5">
                                <span class="text-[10px] text-text-4 font-mono font-bold w-2.5">Z</span>
                                <button @click="nudgeSelectedAxis('z', 'zInput', -0.05)" class="btn btn-ghost btn-xs text-xs px-1.5" title="Z -5cm">▼</button>
                                <input type="number" step="0.05" x-model.number="zInput" @keydown.enter="applyAxisInput('z', 'zInput')"
                                       class="w-16 bg-bg-surface border border-bg-border rounded-lg px-1 py-1 text-xs text-text-1 font-mono text-center focus:outline-none focus:border-neon-cyan" />
                                <button @click="nudgeSelectedAxis('z', 'zInput', 0.05)" class="btn btn-ghost btn-xs text-xs px-1.5" title="Z +5cm">▲</button>
                            </div>
                            <button @click="applyAllAxesInput()" class="btn btn-cyan btn-xs text-xs">Aplicar</button>
                        </div>
                    </div>
                </div>
            </template>
        </div>
    </div>

    <!-- RIGHT PANEL: Live Quantitative & Stock Matching -->
    <div class="xl:col-span-3 flex flex-col gap-6">
        
        <!-- Inventory / Stock Check Card -->
        <div class="bg-bg-card border border-bg-border rounded-2xl p-5 shadow-lg flex-1 flex flex-col">
            <h2 class="text-base font-bold text-text-1 mb-1 flex items-center gap-2">
                <svg class="w-5 h-5 text-neon-cyan" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                Quantitativo & Estoque
            </h2>
            <p class="text-xs text-text-3 mb-4">Verificação em tempo real de materiais e estoque.</p>
            
            <div class="flex-1 overflow-y-auto max-h-[400px]">
                <table class="w-full text-xs text-left">
                    <thead>
                        <tr class="border-b border-bg-border text-text-3 pb-2">
                            <th class="py-2.5 font-bold uppercase tracking-wider text-[10px]">Componente</th>
                            <th class="py-2.5 font-bold uppercase tracking-wider text-[10px] text-center">Proj.</th>
                            <th class="py-2.5 font-bold uppercase tracking-wider text-[10px] text-center">Estoque</th>
                            <th class="py-2.5 font-bold uppercase tracking-wider text-[10px] text-center">Falta</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-bg-border/60">
                        <template x-for="item in quantitative" :key="item.codigo">
                            <tr class="hover:bg-bg-surface/30">
                                <td class="py-2.5">
                                    <div class="font-bold text-text-1" x-text="item.nome"></div>
                                    <div class="text-[9px] text-text-3 font-mono" x-text="item.codigo"></div>
                                </td>
                                <td class="py-2.5 text-center font-bold text-text-1" x-text="item.projeto"></td>
                                <td class="py-2.5 text-center font-mono text-text-2" x-text="item.estoque"></td>
                                <td class="py-2.5 text-center">
                                    <template x-if="item.diferenca <= 0">
                                        <span class="px-2 py-0.5 rounded-full text-[9px] font-bold bg-neon-green/10 text-neon-green">
                                            OK
                                        </span>
                                    </template>
                                    <template x-if="item.diferenca > 0">
                                        <span class="px-2 py-0.5 rounded-full text-[9px] font-bold bg-neon-red/10 text-neon-red" x-text="'-' + item.diferenca">
                                        </span>
                                    </template>
                                </td>
                            </tr>
                        </template>
                        <template x-if="quantitative.length === 0">
                            <tr>
                                <td colspan="4" class="py-8 text-center text-text-3 font-medium">Nenhum componente no projeto.</td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
            
            <div class="border-t border-bg-border pt-4 mt-4 space-y-3">
                <div class="flex justify-between text-xs">
                    <span class="text-text-3 font-medium">Peso Total da Estrutura:</span>
                    <span class="font-bold text-neon-cyan font-mono" x-text="totalWeight.toFixed(1) + ' kg'"></span>
                </div>

                <div class="flex justify-between text-xs">
                    <span class="text-text-3 font-medium">Metros Lineares (Box Truss):</span>
                    <span class="font-bold text-neon-purple font-mono" x-text="totalLinearMeters.toFixed(2) + ' m'"></span>
                </div>

                <div class="flex justify-between text-xs" x-show="totalPm5LinearMeters > 0">
                    <span class="text-text-3 font-medium">Metros Lineares (Perfil PM5):</span>
                    <span class="font-bold text-orange-400 font-mono" x-text="totalPm5LinearMeters.toFixed(2) + ' m'"></span>
                </div>

                <div class="flex justify-between text-xs" x-show="totalSquareMeters > 0">
                    <span class="text-text-3 font-medium">Metros Quadrados (Lona/LED):</span>
                    <span class="font-bold text-amber-400 font-mono" x-text="totalSquareMeters.toFixed(2) + ' m²'"></span>
                </div>

                <div class="grid grid-cols-2 gap-2">
                    <button @click="exportToPDF()" class="btn btn-ghost text-neon-cyan border-neon-cyan/20 hover:bg-neon-cyan/15 btn-sm justify-center w-full">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                        PDF
                    </button>
                    <button @click="exportToExcel()" class="btn btn-ghost text-neon-green border-neon-green/20 hover:bg-neon-green/15 btn-sm justify-center w-full">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        Excel
                    </button>
                </div>
            </div>
        </div>
    </div>
    

    
    <!-- MODAL 2: Load Project List -->
    <ui-modal id="modal-load-projects" size="md" title="Projetos Salvos" sub="Selecione um projeto para carregar no canvas" color="cyan">
        <div class="space-y-3 py-2 max-h-[350px] overflow-y-auto pr-1">
            <template x-for="proj in savedProjects" :key="proj.id">
                <div class="flex items-center justify-between p-4 bg-bg-surface border border-bg-border rounded-2xl hover:border-neon-cyan/60 transition-colors">
                    <div>
                        <div class="font-bold text-text-1 text-sm" x-text="proj.name"></div>
                        <div class="text-[10px] text-text-3 mt-1">
                            Dimensões: <span x-text="proj.width"></span>m x <span x-text="proj.height"></span>m x <span x-text="proj.length"></span>m | Escala: 1m = <span x-text="proj.scale"></span>px
                        </div>
                        <div class="text-[9px] text-text-4 mt-0.5 font-mono" x-text="new Date(proj.created_at).toLocaleString('pt-BR')"></div>
                    </div>
                    <div class="flex items-center gap-2">
                        <button @click="loadProject(proj)" class="btn btn-cyan btn-sm">Carregar</button>
                        <button @click="deleteProject(proj.id)" class="btn btn-ghost text-neon-red border-neon-red/25 hover:bg-neon-red/10 btn-sm" title="Excluir do banco">
                            Excluir
                        </button>
                    </div>
                </div>
            </template>
            <template x-if="savedProjects.length === 0">
                <div class="py-12 text-center text-text-3 font-medium">Nenhum projeto salvo encontrado no banco SQLite.</div>
            </template>
        </div>
        <div class="flex justify-end mt-4">
            <button class="btn btn-ghost" @click="closeModal()">Fechar</button>
        </div>
    </ui-modal>
    
</div>

<?php Layout::slot('scripts') ?>
<!-- Fabric.js Canvas Library -->
<script src="<?= url('assets/js/fabric.min.js') ?>"></script>
<!-- SheetJS (Excel) -->
<script src="<?= url('assets/js/xlsx.full.min.js') ?>"></script>
<!-- jsPDF -->
<script src="<?= url('assets/js/jspdf.umd.min.js') ?>"></script>
<!-- jsPDF AutoTable Plugin (for dynamic table export) -->
<script src="<?= url('assets/js/jspdf.plugin.autotable.min.js') ?>"></script>
<!-- Three.js (3D Rendering) -->
<script src="<?= url('assets/js/three.min.js') ?>"></script>
<!-- Three.js OrbitControls -->
<script src="<?= url('assets/js/OrbitControls.js') ?>"></script>



<script>
// DEBUG WRAPPERS FOR FABRIC.JS TO DETECT INVALID/PROXIED OBJECTS
(function() {
    if (window.fabric) {
        const targetProto = fabric.Canvas.prototype;
        const originalRenderObjects = targetProto._renderObjects;
        if (originalRenderObjects) {
            targetProto._renderObjects = function(ctx, activeGroup) {
                if (this._objects) {
                    for (let i = 0; i < this._objects.length; i++) {
                        const obj = this._objects[i];
                        if (obj && typeof obj.render !== 'function') {
                            console.error("ERRO: Objeto inválido no canvas, sem método .render no índice " + i, obj);
                            console.log("Propriedades do objeto:", Object.keys(obj));
                            console.log("Representação em string:", String(obj));
                            console.trace();
                        }
                    }
                }
                return originalRenderObjects.call(this, ctx, activeGroup);
            };
        }
    }
})();

function trussCalculator() {
    let canvas = null;
    // Cache de HTMLImageElement das imagens de Lona (comp.image dataURL).
    // Fica na closure (fora do Alpine) para não passar pela reatividade.
    const lonaImgCache = {};
    return {
        catalog: [],
        savedProjects: [],
        projectName: 'Projeto Sem Nome',
        currentProjectId: null,
        savedProjectName: null, // nome do projeto quando foi salvo/carregado (detecta renomeio → salvar como cópia)
        saving: false,
        canvasScale: 50,
        gridEnabled: true,
        xrayMode: false,
        selectedCompUid: null,
        heightInput: 0,
        xInput: 0,
        zInput: 0,
        totalWeight: 0,
        totalLinearMeters: 0,
        totalPm5LinearMeters: 0,
        totalSquareMeters: 0,
        quantitative: [],
        canvasZoom: 1.0,
        history: [],
        historyIndex: -1,
        _historySuppress: false,
        viewMode: '2d_frontal',
        // 'cima' ou 'baixo' — qual metade (eixo Y) fica editável na vista Planta.
        // Não é um viewMode novo de propósito: a projeção da Planta (X horizontal,
        // Z vertical) é IDÊNTICA nos dois casos, só muda qual metade fica travada
        // (ver _isLockedForView) — reaproveitar '2d_superior' evita duplicar toda a
        // lógica de mapeamento de tela/rotação que já existe pra essa view.
        plantaSide: 'cima',
        components3D: [],

        dimensions: {
            length: 6.0,
            height: 3.0,
            width: 4.0
        },
        
        canvasComponents: [], // tracks active components on canvas: { fabricObj, catalogId, type, length }
        
        sketchMode: false,
        sketchPoints: [],
        sketchFabricObjects: [],
        _previewLine: null,
        _previewLabel: null,

        accessoryWidth: 3,
        accessoryHeight: 2,
        accessorySketchType: null,
        _accessoryCorner: null,
        labelText: 'TEXTO',
        _uidCounter: 0,
        _clipboard: null,
        accessoryColor: '#e2e8f0',
        accessoryOpacity: 55,
        
        init() {
            // Popula o campo "Altura (Y)" com o valor real toda vez que a seleção
            // muda, pra não sobrescrever o que o usuário está digitando a cada
            // re-render e sempre abrir já mostrando a altura atual da peça.
            this.$watch('selectedCompUid', () => {
                const comp = this.selectedComp;
                this.xInput = comp ? comp.x : 0;
                this.heightInput = comp ? comp.y : 0;
                this.zInput = comp ? comp.z : 0;
            });

            // Initialize Fabric.js Canvas
            canvas = new fabric.Canvas('trussCanvas', {
                selection: true,
                preserveObjectStacking: true,
                backgroundColor: '#070a13'
            });
            // Referência de depuração/E2E (Playwright lê o estado real do Fabric.js
            // pra simular drags precisos e checar posições) — não afeta produção.
            window.__trussCanvas = canvas;

            // Mouse wheel zoom (at cursor position)
            canvas.on('mouse:wheel', (opt) => {
                const delta = opt.e.deltaY;
                let zoom = canvas.getZoom();
                zoom *= 0.999 ** delta;
                zoom = Math.max(0.2, Math.min(5, zoom));
                this.canvasZoom = zoom;
                canvas.zoomToPoint({ x: opt.e.offsetX, y: opt.e.offsetY }, zoom);
                opt.e.preventDefault();
                opt.e.stopPropagation();
            });
            
            // Pan: botão do meio, Espaço+arrasto, ou clique-e-arrasto no fundo
            // (área vazia, sem peça sob o cursor). Shift+arrasto no fundo mantém
            // a seleção retangular múltipla NATIVA do Fabric.js — que ignora
            // automaticamente qualquer objeto com selectable:false, então só
            // pega peças da metade atual (não travada, ver _isLockedForView).
            // Desativado nos modos Sketch (clique no fundo adiciona pontos/cantos).
            var _panning = false, _panStart = { x: 0, y: 0 }, _panVpt = null;
            canvas.on('mouse:down', (opt) => {
                const leftOnEmpty = opt.e.button === 0 && !opt.target;
                const dragPan = leftOnEmpty && !opt.e.shiftKey
                    && !this.sketchMode && !this.accessorySketchType;
                if (opt.e.button === 1 || (leftOnEmpty && _spaceHeld) || dragPan) {
                    _panning = true;
                    _panStart = { x: opt.e.clientX, y: opt.e.clientY };
                    _panVpt = canvas.viewportTransform ? [...canvas.viewportTransform] : null;
                    canvas.selection = false;
                    // O Fabric já iniciou o retângulo de seleção neste mousedown
                    // (nosso handler roda depois): cancela para não selecionar ao soltar.
                    canvas._groupSelector = null;
                    canvas.setCursor('grabbing');
                    opt.e.preventDefault();
                }
            });
            canvas.on('mouse:move', (opt) => {
                if (_panning && _panVpt) {
                    var dx = opt.e.clientX - _panStart.x;
                    var dy = opt.e.clientY - _panStart.y;
                    var vpt = [..._panVpt];
                    vpt[4] += dx;
                    vpt[5] += dy;
                    canvas.setViewportTransform(vpt);
                    canvas._groupSelector = null;
                    canvas.setCursor('grabbing');
                    opt.e.preventDefault();
                }
            });
            canvas.on('mouse:up', (opt) => {
                if (_panning) {
                    _panning = false;
                    _panVpt = null;
                    canvas.selection = !this.sketchMode;
                    canvas.setCursor('default');
                    opt.e.preventDefault();
                }
            });
            // Track Space key for pan mode
            var _spaceHeld = false;
            const _isEditableTarget = (el) => el && (el.tagName === 'INPUT' || el.tagName === 'TEXTAREA' || el.isContentEditable);
            window.addEventListener('keydown', (e) => { if (e.key === ' ' && !e.repeat && !_isEditableTarget(e.target)) { _spaceHeld = true; e.preventDefault(); } });
            window.addEventListener('keyup', (e) => { if (e.key === ' ') { _spaceHeld = false; } });

            // Ctrl+Shift: em vez de tentar reproduzir a seleção retangular do
            // Fabric.js na mão (tentativa anterior — funcionava mas era frágil,
            // dependia de detalhes internos de como o Fabric.js liga seus próprios
            // listeners de mouse), a solução mais simples e robusta é DESTRAVAR
            // tudo temporariamente enquanto as duas teclas estão seguradas — o
            // Shift+arrasto/clique NATIVO do Fabric.js (que já funciona
            // perfeitamente pra metade não-travada) passa a enxergar TODA peça
            // como selecionável, sem precisar de nenhum código de seleção próprio.
            var _ctrlShiftHeld = false;
            window.addEventListener('keydown', (e) => {
                if (_ctrlShiftHeld || _isEditableTarget(e.target)) return;
                if (e.ctrlKey && e.shiftKey && this.viewMode !== '3d' && !this.sketchMode && !this.accessorySketchType) {
                    _ctrlShiftHeld = true;
                    this._unlockAllForSelection();
                }
            });
            window.addEventListener('keyup', (e) => {
                if (_ctrlShiftHeld && (!e.ctrlKey || !e.shiftKey)) {
                    _ctrlShiftHeld = false;
                    // NÃO re-trava aqui de propósito: soltar as teclas costuma
                    // acontecer bem antes de arrastar a seleção recém-montada pro
                    // lugar certo (ex: "juntei as 4 peças, agora vou soltar Ctrl+
                    // Shift e mover elas") — re-travar agora bloquearia esse
                    // arrasto seguinte. O re-travamento correto já acontece
                    // sozinho em `selection:cleared` (usuário desmarcou tudo) ou
                    // no próximo resync completo do canvas (syncCanvasFrom3D, que
                    // roda depois de qualquer arrasto/edição).
                }
            });

            // Draw grid lines
            this.drawGrid();
            
            // Register canvas selection/modification listeners
            canvas.on('object:moving', (options) => {
                const target = options.target;
                if (!target) return;
                
                const scale = this.canvasScale;
                const cx = canvas.width / 2;
                const cy = canvas.height / 2;
                const depth = this.dimensions.width;
                
                if (target.type === 'activeSelection') {
                    return;
                }
                
                const comp = this.components3D.find(c => c.uid === target.data.uid);
                if (!comp) return;
                
                if (this.viewMode === '2d_frontal') {
                    const floorY = cy + (this.dimensions.height * scale) / 2 - 40;
                    const newX = (target.left - cx) / scale;
                    const newY = (floorY - target.top) / scale;
                    const dx = newX - comp.x;
                    const dy = newY - comp.y;
                    
                    canvas.getObjects().forEach(o => {
                        if (o !== target && o.data && o.data.uid) {
                            const otherComp = this.components3D.find(c => c.uid === o.data.uid);
                            // Tolerância apertada (era 0.05m = largura do Perfil PM5) —
                            // ver comentário completo no object:modified, mesmo fix. O
                            // eixo Z (colapsado nesta view) TEM que bater também: sem
                            // isso, uma peça travada/esmaecida do outro lado (outra
                            // "view", ver _isLockedForView) que só coincide em X/Y na
                            // projeção 2D era arrastada junto às escondidas — bug real
                            // reportado pelo usuário ("pegar itens que estão em outras
                            // views"), corrigido 2026-09-16.
                            if (otherComp && Math.abs(otherComp.x - comp.x) < 0.01 && Math.abs(otherComp.y - comp.y) < 0.01 && Math.abs(otherComp.z - comp.z) < 0.01) {
                                o.set({
                                    left: cx + (otherComp.x + dx) * scale,
                                    top: floorY - (otherComp.y + dy) * scale
                                });
                                o.setCoords();
                            }
                        }
                    });
                } else if (this.viewMode === '2d_superior') {
                    const newX = (target.left - cx) / scale;
                    const newZ = (target.top - cy) / scale - depth / 2;
                    const dx = newX - comp.x;
                    const dz = newZ - comp.z;
                    
                    canvas.getObjects().forEach(o => {
                        if (o !== target && o.data && o.data.uid) {
                            const otherComp = this.components3D.find(c => c.uid === o.data.uid);
                            // Eixo Y (colapsado nesta view) tem que bater também — ver
                            // comentário completo no bloco 2d_frontal acima, mesmo fix.
                            if (otherComp && Math.abs(otherComp.x - comp.x) < 0.01 && Math.abs(otherComp.z - comp.z) < 0.01 && Math.abs(otherComp.y - comp.y) < 0.01) {
                                o.set({
                                    left: cx + (otherComp.x + dx) * scale,
                                    top: cy + (otherComp.z + dz + depth / 2) * scale
                                });
                                o.setCoords();
                            }
                        }
                    });
                } else if (this.viewMode === '2d_lateral') {
                    const floorY = cy + (this.dimensions.height * scale) / 2 - 40;
                    const newZ = (target.left - cx) / scale - depth / 2;
                    const newY = (floorY - target.top) / scale;
                    const dz = newZ - comp.z;
                    const dy = newY - comp.y;
                    
                    canvas.getObjects().forEach(o => {
                        if (o !== target && o.data && o.data.uid) {
                            const otherComp = this.components3D.find(c => c.uid === o.data.uid);
                            // Eixo X (colapsado nesta view) tem que bater também — ver
                            // comentário completo no bloco 2d_frontal acima, mesmo fix.
                            if (otherComp && Math.abs(otherComp.z - comp.z) < 0.01 && Math.abs(otherComp.y - comp.y) < 0.01 && Math.abs(otherComp.x - comp.x) < 0.01) {
                                o.set({
                                    left: cx + (otherComp.z + dz + depth / 2) * scale,
                                    top: floorY - (otherComp.y + dy) * scale
                                });
                                o.setCoords();
                            }
                        }
                    });
                } else if (this.viewMode === '2d_fundo') {
                    const floorY = cy + (this.dimensions.height * scale) / 2 - 40;
                    const newX = -(target.left - cx) / scale;
                    const newY = (floorY - target.top) / scale;
                    const dx = newX - comp.x;
                    const dy = newY - comp.y;
                    
                    canvas.getObjects().forEach(o => {
                        if (o !== target && o.data && o.data.uid) {
                            const otherComp = this.components3D.find(c => c.uid === o.data.uid);
                            // Tolerância apertada (era 0.05m = largura do Perfil PM5) —
                            // ver comentário completo no bloco 2d_frontal acima. Eixo Z
                            // (colapsado nesta view) tem que bater também, mesmo fix.
                            if (otherComp && Math.abs(otherComp.x - comp.x) < 0.01 && Math.abs(otherComp.y - comp.y) < 0.01 && Math.abs(otherComp.z - comp.z) < 0.01) {
                                o.set({
                                    left: cx - (otherComp.x + dx) * scale,
                                    top: floorY - (otherComp.y + dy) * scale
                                });
                                o.setCoords();
                            }
                        }
                    });
                } else if (this.viewMode === '2d_lateral_dir') {
                    const floorY = cy + (this.dimensions.height * scale) / 2 - 40;
                    const newZ = -(target.left - cx) / scale - depth / 2;
                    const newY = (floorY - target.top) / scale;
                    const dz = newZ - comp.z;
                    const dy = newY - comp.y;
                    
                    canvas.getObjects().forEach(o => {
                        if (o !== target && o.data && o.data.uid) {
                            const otherComp = this.components3D.find(c => c.uid === o.data.uid);
                            // Eixo X (colapsado nesta view) tem que bater também — ver
                            // comentário completo no bloco 2d_frontal acima, mesmo fix.
                            if (otherComp && Math.abs(otherComp.z - comp.z) < 0.01 && Math.abs(otherComp.y - comp.y) < 0.01 && Math.abs(otherComp.x - comp.x) < 0.01) {
                                o.set({
                                    left: cx - (otherComp.z + dz + depth / 2) * scale,
                                    top: floorY - (otherComp.y + dy) * scale
                                });
                                o.setCoords();
                            }
                        }
                    });
                }
                
            });

            canvas.on('object:modified', (options) => {
                const target = options.target;
                if (!target) return;

                // Feedback de arrasto (label de posição + linha-guia de snap) é só
                // durante o gesto — some ao soltar, senão fica grudado na tela.
                this._clearDragLabel();
                this._clearSnapGuide();

                const scale = this.canvasScale;
                const cx = canvas.width / 2;
                const cy = canvas.height / 2;
                const depth = this.dimensions.width;

                // Colisão: snapshot de tudo ANTES de mexer, pra poder reverter em
                // bloco se a posição/rotação final colidir com outra peça. movedUids
                // junta as peças que este gesto move (a própria seleção múltipla, ou
                // o "grupo" de peças que compartilhavam a posição exata da arrastada)
                // — elas não colidem entre si aqui porque se moveram juntas, rígidas.
                const _preSnapshot = JSON.stringify(this.components3D);
                const movedUids = [];

                if (target.type === 'activeSelection') {
                    target.forEachObject(o => {
                        if (o.data && o.data.uid) {
                            const comp = this.components3D.find(c => c.uid === o.data.uid);
                            if (comp) {
                                movedUids.push(comp.uid);
                                const matrix = o.calcTransformMatrix();
                                const opt = fabric.util.qrDecompose(matrix);
                                const absLeft = opt.translateX;
                                const absTop = opt.translateY;
                                
            if (this.viewMode === '2d_frontal') {
                                    const floorY = cy + (this.dimensions.height * scale) / 2 - 40;
                                    comp.x = (absLeft - cx) / scale;
                                    comp.y = (floorY - absTop) / scale;
                                    if (comp.tipo === 'Q30' || comp.tipo === 'plana' || comp.tipo === 'braco') this.applyQ30Rotation(comp, '2d_frontal', opt.angle);
                                    else comp.rotationZ = opt.angle;
                                } else if (this.viewMode === '2d_fundo') {
                                    const floorY = cy + (this.dimensions.height * scale) / 2 - 40;
                                    comp.x = -(absLeft - cx) / scale;
                                    comp.y = (floorY - absTop) / scale;
                                    if (comp.tipo === 'Q30' || comp.tipo === 'plana' || comp.tipo === 'braco') this.applyQ30Rotation(comp, '2d_fundo', opt.angle);
                                    else comp.rotationZ = opt.angle;
                                } else if (this.viewMode === '2d_superior') {
                                    comp.x = (absLeft - cx) / scale;
                                    comp.z = (absTop - cy) / scale - depth / 2;
                                    if (comp.tipo === 'Q30' || comp.tipo === 'plana' || comp.tipo === 'braco') this.applyQ30Rotation(comp, '2d_superior', opt.angle);
                                    else comp.rotationY = opt.angle;
                                } else if (this.viewMode === '2d_lateral') {
                                    const floorY = cy + (this.dimensions.height * scale) / 2 - 40;
                                    comp.z = (absLeft - cx) / scale - depth / 2;
                                    comp.y = (floorY - absTop) / scale;
                                    if (comp.tipo === 'Q30' || comp.tipo === 'plana' || comp.tipo === 'braco') this.applyQ30Rotation(comp, '2d_lateral', opt.angle);
                                    else comp.rotationX = opt.angle;
                                } else if (this.viewMode === '2d_lateral_dir') {
                                    const floorY = cy + (this.dimensions.height * scale) / 2 - 40;
                                    comp.z = -(absLeft - cx) / scale - depth / 2;
                                    comp.y = (floorY - absTop) / scale;
                                    if (comp.tipo === 'Q30' || comp.tipo === 'plana' || comp.tipo === 'braco') this.applyQ30Rotation(comp, '2d_lateral_dir', opt.angle);
                                    else comp.rotationX = opt.angle;
                                }
                            }
                        }
                    });
                } else {
                    const comp = this.components3D.find(c => c.uid === target.data.uid);
                    if (comp) {
                        if (this.viewMode === '2d_frontal' || this.viewMode === '2d_fundo') {
                            const floorY = cy + (this.dimensions.height * scale) / 2 - 40;
                            const xSign = this.viewMode === '2d_fundo' ? -1 : 1;
                            const newX = xSign * (target.left - cx) / scale;
                            const newY = (floorY - target.top) / scale;
                            const dx = newX - comp.x;
                            const dy = newY - comp.y;
                            const oldX = comp.x;
                            const oldY = comp.y;
                            
                            // Tolerância de "mesma posição" (agrupa peças coincidentes,
                            // ex: Q30 + conector no mesmo ponto, pra mover junto) —
                            // era 0.05m (5cm), que é a LARGURA INTEIRA de uma peça
                            // Perfil PM5. Duas peças PM5 vizinhas de verdade (só
                            // encostadas, não empilhadas) caíam nesse raio e eram
                            // tratadas como "a mesma peça se movendo junto" — e como
                            // collidesWithOthers EXCLUI o grupo movido da checagem,
                            // isso deixava uma atravessar a outra sem barreira nenhuma
                            // (corrigido 2026-08-18, mesma peça reportou "entra dentro
                            // da outra"). 0.01m (1cm) fica bem abaixo do menor lado de
                            // qualquer peça do catálogo (Grepo, 3cm), então só agrupa
                            // quem está genuinamente no mesmo ponto. Eixo Z (colapsado
                            // nesta view) TEM que bater também — sem isso, uma peça
                            // travada/esmaecida do outro lado (outra "view", ver
                            // _isLockedForView) que só coincide em X/Y na projeção 2D
                            // era arrastada e GRAVADA junto com a peça visível — bug
                            // real reportado pelo usuário ("pegar itens que estão em
                            // outras views"), corrigido 2026-09-16.
                            this.components3D.forEach(c => {
                                if (Math.abs(c.x - oldX) < 0.01 && Math.abs(c.y - oldY) < 0.01 && Math.abs(c.z - comp.z) < 0.01) {
                                    movedUids.push(c.uid);
                                    c.x += dx;
                                    c.y += dy;
                                    if (c.uid === target.data.uid) {
                                        if (c.tipo === 'Q30' || c.tipo === 'plana' || c.tipo === 'braco') this.applyQ30Rotation(c, this.viewMode, target.angle);
                                        else c.rotationZ = target.angle;
                                    }
                                }
                            });
                        } else if (this.viewMode === '2d_superior') {
                            const newX = (target.left - cx) / scale;
                            const newZ = (target.top - cy) / scale - depth / 2;
                            const dx = newX - comp.x;
                            const dz = newZ - comp.z;
                            const oldX = comp.x;
                            const oldZ = comp.z;
                            
                            // Mesmo fix de tolerância que na Frontal/Fundo acima (era
                            // 0.05m, a largura inteira do Perfil PM5) — ver comentário lá.
                            // Eixo Y (colapsado nesta view) tem que bater também — mesmo
                            // fix de "pegar itens de outras views" (2026-09-16).
                            this.components3D.forEach(c => {
                                if (Math.abs(c.x - oldX) < 0.01 && Math.abs(c.z - oldZ) < 0.01 && Math.abs(c.y - comp.y) < 0.01) {
                                    movedUids.push(c.uid);
                                    c.x += dx;
                                    c.z += dz;
                                    if (c.uid === target.data.uid) {
                                        if (c.tipo === 'Q30' || c.tipo === 'plana' || c.tipo === 'braco') this.applyQ30Rotation(c, this.viewMode, target.angle);
                                        else c.rotationY = target.angle;
                                    }
                                }
                            });
                         } else if (this.viewMode === '2d_lateral' || this.viewMode === '2d_lateral_dir') {
                            const floorY = cy + (this.dimensions.height * scale) / 2 - 40;
                            const zSign = this.viewMode === '2d_lateral_dir' ? -1 : 1;
                            const newZ = zSign * (target.left - cx) / scale - depth / 2;
                            const newY = (floorY - target.top) / scale;
                            const dz = newZ - comp.z;
                            const dy = newY - comp.y;
                            const oldZ = comp.z;
                            const oldY = comp.y;
                            
                            // Mesmo fix de tolerância que na Frontal/Fundo (era 0.05m, a
                            // largura inteira do Perfil PM5) — ver comentário lá.
                            // Eixo X (colapsado nesta view) tem que bater também — mesmo
                            // fix de "pegar itens de outras views" (2026-09-16).
                            this.components3D.forEach(c => {
                                if (Math.abs(c.z - oldZ) < 0.01 && Math.abs(c.y - oldY) < 0.01 && Math.abs(c.x - comp.x) < 0.01) {
                                    movedUids.push(c.uid);
                                    c.z += dz;
                                    c.y += dy;
                                    if (c.uid === target.data.uid) {
                                        if (c.tipo === 'Q30' || c.tipo === 'plana' || c.tipo === 'braco') this.applyQ30Rotation(c, this.viewMode, target.angle);
                                        else c.rotationX = target.angle;
                                    }
                                }
                            });
                        }
                    }
                }

                // SAPATA: enforce ground constraint (base at Y=0, centered origin at Y=height/2)
                this.components3D.forEach(c => {
                    if (c.tipo === 'sapata') {
                        c.y = 0.0175;
                    }
                });

                // Perfil PM5: mesma trava de orientação do addComponent — montante NUNCA
                // deita, travessa NUNCA empina, mesmo manipulado pelo handle de rotação.
                this.components3D.forEach(c => {
                    if (c.tipo === 'montante') {
                        c.rotationX = 0; c.rotationY = 0; c.rotationZ = 0;
                    } else if (c.tipo === 'travessa') {
                        c.rotationX = 0; c.rotationZ = 0;
                    }
                });

                // Fechamento de laço (ver _closeLoopIfBridging) — só faz sentido pra
                // arrasto de peça única, e roda ANTES do bloqueio de colisão logo
                // abaixo: se a peça realmente não cabe no vão como está (ex: as duas
                // pontas de um "U" não batem exatamente com o comprimento dela), é
                // isto que resolve — ajustando só as duas peças vizinhas diretamente
                // tocadas — antes que o "encostou no limite" bloqueie o gesto à toa.
                if (movedUids.length === 1) {
                    const bridgeComp = this.components3D.find(c => c.uid === movedUids[0]);
                    if (bridgeComp) this._closeLoopIfBridging(bridgeComp, movedUids);
                }

                // Colisão: se a posição/rotação final de alguma peça movida colide com
                // uma peça de FORA do grupo movido, NÃO desfaz o gesto inteiro — isso
                // fazia qualquer excesso de 1mm ao tentar encostar duas peças cancelar
                // o arrasto todo e voltar pro ponto de partida, dando a impressão de
                // que a peça precisava de um espaço bem maior que o real ("não
                // encosta"). Em vez disso, desliza a peça pela MESMA trajetória do
                // gesto até a fração mais próxima do alvo que ainda não sobrepõe nada
                // (busca binária), como uma parede física que você encosta e para, não
                // um teleporte de volta ao início.
                const targetByUid = {};
                movedUids.forEach(uid => {
                    const comp = this.components3D.find(c => c.uid === uid);
                    if (comp) {
                        targetByUid[uid] = {
                            x: comp.x, y: comp.y, z: comp.z,
                            rotationX: comp.rotationX, rotationY: comp.rotationY, rotationZ: comp.rotationZ
                        };
                    }
                });
                const collided = movedUids.some(uid => {
                    const comp = this.components3D.find(c => c.uid === uid);
                    return comp && this.collidesWithOthers(comp, movedUids);
                });
                if (collided) {
                    const preByUid = {};
                    JSON.parse(_preSnapshot).forEach(c => { preByUid[c.uid] = c; });

                    // Aplica a fração `t` do deslocamento (0 = posição de antes do
                    // gesto, sempre segura; 1 = alvo, onde colidiu) — a rotação vai
                    // direto pro alvo final (não interpola, só a posição desliza).
                    const applyFraction = (t) => {
                        movedUids.forEach(uid => {
                            const comp = this.components3D.find(c => c.uid === uid);
                            const pre = preByUid[uid];
                            const tgt = targetByUid[uid];
                            if (!comp || !pre || !tgt) return;
                            comp.x = pre.x + (tgt.x - pre.x) * t;
                            comp.y = pre.y + (tgt.y - pre.y) * t;
                            comp.z = pre.z + (tgt.z - pre.z) * t;
                            comp.rotationX = tgt.rotationX;
                            comp.rotationY = tgt.rotationY;
                            comp.rotationZ = tgt.rotationZ;
                        });
                        return movedUids.some(uid => {
                            const comp = this.components3D.find(c => c.uid === uid);
                            return comp && this.collidesWithOthers(comp, movedUids);
                        });
                    };

                    // A precisão da busca é PROPORCIONAL à distância total do gesto
                    // (cada iteração divide o intervalo restante por 2), não um valor
                    // fixo em metros — 8 iterações bastam pra um arrasto curto, mas
                    // arrastar de longe (alguns metros, comum ao trazer peça nova até
                    // a estrutura) sobrava ~1cm de folga visível no 3D antes de
                    // detectar a colisão. 24 iterações dão precisão < 1mm mesmo num
                    // arrasto de 10m (10/2^24 ≈ 0,0006mm) — custo desprezível, roda só
                    // uma vez ao soltar, não durante o arrasto.
                    let lo = 0, hi = 1;
                    for (let i = 0; i < 24; i++) {
                        const mid = (lo + hi) / 2;
                        if (applyFraction(mid)) { hi = mid; } else { lo = mid; }
                    }
                    // Se mesmo na fração segura (lo) ainda colide — caso de rotação
                    // pura (delta de posição zero, giro é que causou a sobreposição) —
                    // não tem "deslizar" possível: desfaz rotação e posição por completo.
                    if (applyFraction(lo)) {
                        movedUids.forEach(uid => {
                            const comp = this.components3D.find(c => c.uid === uid);
                            const pre = preByUid[uid];
                            if (comp && pre) Object.assign(comp, pre);
                        });
                    }

                    if (window.showToast) {
                        showToast('red', 'Encostou no limite', 'Levada o mais perto possível sem sobrepor outra peça.');
                    }
                } else if (movedUids.length === 1) {
                    // Não colidiu — mas pode ter sobrado uma folga pequena por
                    // imprecisão do mouse ao tentar encostar duas peças. Essa folga é
                    // a MESMA em metros não importa o zoom, mas fica mais VISÍVEL
                    // quanto mais se dá zoom (o mesmo 1cm vira mais pixels na tela) —
                    // por isso "piorava" ao aumentar a escala, mesmo sendo sempre a
                    // mesma distância real. Busca a peça vizinha mais próxima e a
                    // direção GEOMÉTRICA exata do vão entre as duas caixas (não a
                    // direção do arrasto do mouse — testado com dados reais de um
                    // projeto do usuário: a direção do arrasto pode não apontar pro
                    // vão de verdade quando as peças são grandes/descentradas, então
                    // calcular o vão pela geometria é mais confiável). Raio pequeno e
                    // FIXO EM METROS (não pixels — funciona igual em qualquer zoom).
                    // Só pra arrasto de peça única — seleção múltipla mantém a
                    // formação relativa como está.
                    const uid = movedUids[0];
                    const comp = this.components3D.find(c => c.uid === uid);
                    if (comp) {
                        const GAP_CLOSE = this._touchToleranceFor(comp);
                        const nearest = this._nearestGap(comp, [uid]);
                        if (nearest && nearest.dist > 1e-6 && nearest.dist <= GAP_CLOSE) {
                            const origX = comp.x, origY = comp.y, origZ = comp.z;
                            // `t` é a fração do vetor de vão exato (nearest.dx/dy/dz) —
                            // t=1 é o ponto de toque exato (calculado pela própria
                            // geometria das caixas, não uma direção estimada), t>1 já
                            // sobrepõe de leve (usado só pra garantir uma colisão no
                            // limite superior da busca binária).
                            const testAt = (t) => {
                                comp.x = origX + nearest.dx * t;
                                comp.y = origY + nearest.dy * t;
                                comp.z = origZ + nearest.dz * t;
                                return this._touchesExactly(comp, [uid]);
                            };
                            let lo = 0, hi = 1.02;
                            if (testAt(hi)) {
                                for (let i = 0; i < 24; i++) {
                                    const mid = (lo + hi) / 2;
                                    if (testAt(mid)) { hi = mid; } else { lo = mid; }
                                }
                                testAt(lo);
                                if (lo > 0.02 && window.showToast) {
                                    showToast('cyan', 'Encostado', 'Peça ajustada pra encostar exatamente na vizinha.');
                                }
                            } else {
                                testAt(0); // segurança: geometria não fechou como esperado, mantém onde soltou
                            }
                        }
                    }
                }

                this.syncCanvasFrom3D();
                this.calculateQuantitative();
                this.pushHistory();
            });

            // Rastreia a peça única selecionada (não seleção múltipla) pro painel
            // de altura exata (campo numérico + botões +/-) na lateral do canvas.
            const _syncSelectedComp = () => {
                const active = canvas.getActiveObject();
                if (active && active.type !== 'activeSelection' && active.data && active.data.uid) {
                    this.selectedCompUid = active.data.uid;
                } else {
                    this.selectedCompUid = null;
                }
            };
            canvas.on('selection:created', _syncSelectedComp);
            canvas.on('selection:updated', _syncSelectedComp);
            canvas.on('selection:cleared', () => {
                this.selectedCompUid = null;
                // Re-trava qualquer peça que uma seleção via Ctrl+Shift tenha
                // destravado temporariamente (ver mouse:up acima) — sem isto,
                // continuaria clicável fora da metade certa desta view até o
                // próximo resync completo do canvas.
                this._applyViewLocks();
                canvas.requestRenderAll();
            });

            // Keyboard Delete/Rotation/Arrow Nudge
            window.addEventListener('keydown', (e) => {
                if (this.accessorySketchType) {
                    if (e.key === 'Escape') {
                        this._exitAccessorySketch();
                        e.preventDefault();
                    }
                    return;
                }
                if (this.sketchMode) {
                    if (e.key === 'Enter' || e.key === 'Escape') {
                        this.finishSketch();
                        e.preventDefault();
                    }
                    return;
                }
                if (this.viewMode !== '2d_frontal' && this.viewMode !== '2d_fundo' && this.viewMode !== '2d_superior' && this.viewMode !== '2d_lateral' && this.viewMode !== '2d_lateral_dir') return;
                if (this.isEditingInput(e)) return;
                
                if (e.key === 'Delete' || e.key === 'Backspace') {
                    this.deleteSelected();
                    return;
                }
                if (e.key === 'r' || e.key === 'R') {
                    this.rotateSelected(45);
                    return;
                }
                if ((e.ctrlKey || e.metaKey) && !e.shiftKey && e.key.toLowerCase() === 'z') {
                    e.preventDefault();
                    this.undo();
                    return;
                }
                if ((e.ctrlKey || e.metaKey) && (e.key.toLowerCase() === 'y' || (e.shiftKey && e.key.toLowerCase() === 'z'))) {
                    e.preventDefault();
                    this.redo();
                    return;
                }
                // Copiar/Colar/Duplicar componentes selecionados. Só intercepta o
                // Ctrl+C/V do navegador quando há de fato algo copiado/colado.
                if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'c') {
                    if (this.copySelected()) e.preventDefault();
                    return;
                }
                if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'v') {
                    if (this.pasteClipboard()) e.preventDefault();
                    return;
                }
                if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'd') {
                    e.preventDefault();
                    if (this.copySelected()) this.pasteClipboard();
                    return;
                }

                const arrows = ['ArrowUp', 'ArrowDown', 'ArrowLeft', 'ArrowRight'];
                if (!arrows.includes(e.key)) return;
                e.preventDefault();

                const zoom = canvas.getZoom();
                const worldStep = zoom >= 2 ? 0.01 : 0.05;
                const step = (e.shiftKey ? worldStep * 10 : worldStep) * this.canvasScale;

                const active = canvas.getActiveObject();
                if (!active) return;

                if (active.type === 'activeSelection') {
                    active.forEachObject(o => {
                        if (o.data && o.data.uid) this.nudgeObject(o, e.key, step);
                    });
                } else if (active.data && active.data.uid) {
                    this.nudgeObject(active, e.key, step);
                }
                this.calculateQuantitative();
                this.pushHistory();
            });
            
            // Double click alterna entre Horizontal (0°) e Vertical (90°) —
            // únicas duas posições usadas nas estruturas.
            canvas.on('mouse:dblclick', (options) => {
                if (this.viewMode !== '2d_frontal' && this.viewMode !== '2d_fundo' && this.viewMode !== '2d_superior' && this.viewMode !== '2d_lateral' && this.viewMode !== '2d_lateral_dir') return;
                if (options.target && options.target.data && options.target.data.uid) {
                    const comp = this.components3D.find(c => c.uid === options.target.data.uid);
                    if (comp) {
                        if (comp.tipo === 'label') return;
                        // Perfil PM5: montante nunca gira (sempre vertical, sem estado
                        // alternativo válido); travessa só tem o toggle 0°/90° com sentido
                        // na Vista Superior (rotationY, direção no plano horizontal) — nas
                        // demais views o duplo-clique giraria rotationX/Z, que precisam
                        // ficar travados em 0 (nunca pode "empinar").
                        if (comp.tipo === 'montante') return;
                        if (comp.tipo === 'travessa' && this.viewMode !== '2d_superior') return;
                        const preRot = { rotationX: comp.rotationX, rotationY: comp.rotationY, rotationZ: comp.rotationZ };
                        const normalize = (deg) => ((deg % 180) + 180) % 180;
                        const toggle = (deg) => normalize(deg) === 90 ? 0 : 90;
                        const isQ30Like = comp.tipo === 'Q30' || comp.tipo === 'plana' || comp.tipo === 'braco';
                        if (isQ30Like) {
                            // Peças lineares usam a orientação canônica (isQ30AlongZ/
                            // isQ30Column): alternar o campo cru da view não funciona —
                            // na lateral, p.ex., horizontal/vertical = rotationY/rotationZ,
                            // e rotationX não muda nada. Calcula o ângulo atual NA VIEW e
                            // aplica o toggle via applyQ30Rotation (mesma convenção da
                            // rotação por alça).
                            let curAngle;
                            if (this.viewMode === '2d_frontal' || this.viewMode === '2d_fundo') {
                                const xSign = this.viewMode === '2d_fundo' ? -1 : 1;
                                curAngle = this.isQ30AlongZ(comp) ? 0
                                    : normalize(xSign === -1 ? (180 - comp.rotationZ) : comp.rotationZ);
                            } else if (this.viewMode === '2d_superior') {
                                curAngle = this.isQ30Column(comp) ? 0 : (this.isQ30AlongZ(comp) ? 90 : 0);
                            } else {
                                curAngle = this.isQ30Column(comp) ? 90 : 0;
                            }
                            this.applyQ30Rotation(comp, this.viewMode, toggle(curAngle));
                        } else if (this.viewMode === '2d_frontal' || this.viewMode === '2d_fundo') {
                            comp.rotationZ = toggle(comp.rotationZ);
                        } else if (this.viewMode === '2d_superior') {
                            comp.rotationY = toggle(comp.rotationY);
                        } else if (this.viewMode === '2d_lateral' || this.viewMode === '2d_lateral_dir') {
                            comp.rotationX = toggle(comp.rotationX);
                        }

                        // Colisão: girar no lugar pode invadir o volume de uma peça
                        // vizinha sem mover o centro — se colidir, desfaz só a rotação.
                        if (this.collidesWithOthers(comp, [comp.uid])) {
                            Object.assign(comp, preRot);
                            if (window.showToast) {
                                showToast('red', 'Rotação bloqueada', 'Colide com outra peça nesta posição.');
                            }
                        }

                        this.syncCanvasFrom3D();
                        this.calculateQuantitative();
                        this.pushHistory();
                    }
                }
            });

            // Watch dimensions width to automatically update depth components and side connections
            this.$watch('dimensions.width', (newWidth, oldWidth) => {
                const newD = parseFloat(newWidth) || 0;
                const oldD = parseFloat(oldWidth) || 0;
                if (newD === oldD) return;
                
                // Remove existing connecting beams running along depth (Z axis)
                this.components3D = this.components3D.filter(c => !(c.tipo === 'Q30' && c.rotationY === 90));
                
                // Shift all components that were at the back frame (z === -oldD) to -newD
                this.components3D.forEach(c => {
                    if (Math.abs(c.z - (-oldD)) < 0.05 && c.z !== 0) {
                        c.z = -newD;
                    }
                });
                
                // Recreate side connecting beams at all sleeve positions
                const sleeves = this.components3D.filter(c => (c.tipo === 'sleeve' || c.tipo === 'sleeve_4faces') && c.z === 0);
                sleeves.forEach(s => {
                    this.addSideConnectingBeams(s.x, s.y);
                });
                
                this.syncCanvasFrom3D();
                this.calculateQuantitative();
            });
            
            this.$watch('dimensions.length', () => {
                this.syncCanvasFrom3D();
                this.calculateQuantitative();
            });
            
            this.$watch('dimensions.height', () => {
                this.syncCanvasFrom3D();
                this.calculateQuantitative();
            });
            
            // Load Catalog from API
            this.loadCatalog();

            // Undo/Redo: histórico inicial
            this.resetHistory();
        },
        
        isEditingInput(e) {
            const tag = e.target.tagName.toLowerCase();
            return tag === 'input' || tag === 'textarea' || tag === 'select';
        },
        
        loadCatalog() {
            fetch('<?= url("/api/pecas") ?>')
                .then(r => r.json())
                .then(res => {
                    if (res.ok) {
                        this.catalog = res.data.filter(i => i.ativo == 1);
                    } else {
                        console.error('Erro ao carregar catálogo: ', res.error);
                    }
                })
                .catch(err => console.error('Erro de API:', err));
        },
        
        drawGrid() {
            if (!this.gridEnabled) return;
            const gridSize = 10; // 10px grid
            const width = canvas.width;
            const height = canvas.height;
            
            // Draw horizontal and vertical gridlines
            for (let i = gridSize; i < width; i += gridSize) {
                canvas.add(new fabric.Line([i, 0, i, height], {
                    stroke: '#1e293b',
                    strokeWidth: 1,
                    selectable: false,
                    evented: false,
                    isGridLine: true
                }));
            }
            for (let i = gridSize; i < height; i += gridSize) {
                canvas.add(new fabric.Line([0, i, width, i], {
                    stroke: '#1e293b',
                    strokeWidth: 1,
                    selectable: false,
                    evented: false,
                    isGridLine: true
                }));
            }
            const gridLines = canvas.getObjects().filter(o => o.isGridLine);
            gridLines.forEach(l => {
                const idx = canvas._objects.indexOf(l);
                if (idx > -1) {
                    canvas._objects.splice(idx, 1);
                    canvas._objects.unshift(l);
                }
            });
        },
        
        toggleGrid() {
            this.gridEnabled = !this.gridEnabled;
            // Remove existing gridlines
            const gridLines = canvas.getObjects().filter(o => o.isGridLine);
            gridLines.forEach(l => canvas.remove(l));
            this.drawGrid();
            canvas.requestRenderAll();
        },
        
        updateCanvasScale() {
            // Remove existing gridlines
            const gridLines = canvas.getObjects().filter(o => o.isGridLine);
            gridLines.forEach(l => canvas.remove(l));
            this.drawGrid();
            
            this.syncCanvasFrom3D();
            this.calculateQuantitative();
            
            if (window.showToast) {
                showToast('cyan', 'Escala Ajustada', `Nova escala: 1m = ${this.canvasScale}px`);
            }
        },
        
        addComponentToCenter(peca) {
            const x = canvas.width / 2;
            const y = canvas.height / 2;
            this.addComponent(peca, x, y);
        },

        // ── Colisão real entre peças (caixa orientada → AABB no mundo) ──────────
        // Cada tipo tem uma caixa local FIXA (metades de largura/altura/comprimento,
        // já na mesma convenção de eixo usada pelos create*Model: comprimento ao
        // longo de X, salvo montante que já nasce vertical/Y). Aplicamos a MESMA
        // rotação usada no render 3D (rotationX, -rotationY, -rotationZ, ordem
        // XYZ do Three.js) para achar os 8 cantos no mundo e daí a AABB (caixa
        // alinhada aos eixos). É uma aproximação conservadora para peças
        // diagonais (Sketch): a AABB de uma peça a 45° é maior que sua caixa
        // real, então preferimos bloquear demais o caso raro de diagonal a
        // deixar passar uma sobreposição real.
        _pieceHalfExtents(comp) {
            const t = comp.tipo;
            const len = comp.length || 0;
            if (t === 'Q30') return [len / 2, 0.15, 0.15];
            if (t === 'plana') return [len / 2, 0.15, 0.025];
            if (t === 'braco') return [len / 2, 0.025, 0.025];
            if (t === 'sapata') return [0.15, 0.0175, len / 2];
            if (t === 'montante') return [0.025, len / 2, 0.025];
            if (t === 'travessa') return [len / 2, 0.025, 0.025];
            if (t === 'cubo') return [0.15, 0.15, 0.15];
            // Sleeve: createSleeveModel() = createColoredTruss(0.42) = MESMA
            // createTrussSection do Q30/Cubo (seção 0,30x0,30 fixa) só que com
            // 0,42m no eixo do comprimento — NÃO é um cubo uniforme de 0,42m.
            // Era `[0.21,0.21,0.21]` (cubo) até 2026-08-18: sobredimensionava a
            // seção em 2 dos 3 eixos (0,21 em vez do real 0,15), bloqueando
            // peças bem mais longe do que o Sleeve realmente ocupa.
            if (t === 'sleeve' || t === 'sleeve_4faces') return [0.21, 0.15, 0.15];
            // Grepo: perfil "U" de aço, chassi real 300x42x30mm (ver
            // createGrepoModel: length=0.30 fixo, channelWidth=0.042,
            // channelHeight=0.030) — NÃO é um cubo de 0,30m. Era
            // `[0.15,0.15,0.15]` (mesmo footprint do Cubo) até 2026-08-18:
            // ~7x maior que o chassi real nos eixos de altura/largura,
            // empurrando peças vizinhas bem além do necessário.
            if (t === 'grepo') return [0.015, 0.15, 0.021];
            return null; // acessórios (lona/painel_led/parled/label) não colidem
        },

        _pieceRotationRad(comp) {
            const d = Math.PI / 180;
            if (comp.tipo === 'montante') return [0, 0, 0];
            if (comp.tipo === 'travessa') return [0, -comp.rotationY * d, 0];
            return [comp.rotationX * d, -comp.rotationY * d, -comp.rotationZ * d];
        },

        _worldAABB(comp, margin = 0.002) {
            const half = this._pieceHalfExtents(comp);
            if (!half) return null;
            // Margem de tolerância pra colisão NÃO bloquear peças genuinamente
            // encostadas — mas ela também define quanto de sobreposição REAL passa
            // sem disparar a colisão, e isso precisa ser mínimo ("um corpo não pode
            // entrar em outro corpo", ditado pelo usuário). Um cap de 40% do
            // half-extent (usado até 2026-08-18) permitia até 1,6cm de sobreposição
            // real numa peça PM5 de 5cm sem soar alarme — visível como a peça
            // "entrando um quarto" na vizinha, no 2D E no 3D (os dois desenham a
            // MESMA posição de dados). Reduzido pra base=2mm / cap=20%: dá conta de
            // ruído de ponto flutuante/arredondamento do snap sem abrir espaço real
            // de sobreposição perceptível. O "deslizar até o limite"
            // (object:modified) e o "fechar folga" (_touchesExactly) já usam
            // margem zero à parte — não dependem deste valor pra fechar o toque
            // exato, só esta checagem "colide sim/não" fica mais rigorosa.
            const shrink = (h) => Math.max(0, h - Math.min(margin, h * 0.2));
            const hx = shrink(half[0]);
            const hy = shrink(half[1]);
            const hz = shrink(half[2]);
            const [rx, ry, rz] = this._pieceRotationRad(comp);
            const cx = Math.cos(rx), sx = Math.sin(rx);
            const cy = Math.cos(ry), sy = Math.sin(ry);
            const cz = Math.cos(rz), sz = Math.sin(rz);
            // R = Rz * Ry * Rx aplicado aos 3 eixos locais unitários (mesma ordem
            // 'XYZ' do Three.js Euler) — dá as 3 direções da caixa no mundo.
            const rot = (vx, vy, vz) => {
                const y1 = vy * cx - vz * sx, z1 = vy * sx + vz * cx, x1 = vx;
                const x2 = x1 * cy + z1 * sy, z2 = -x1 * sy + z1 * cy;
                const x3 = x2 * cz - y1 * sz, y3 = x2 * sz + y1 * cz;
                return [x3, y3, z2];
            };
            const axisX = rot(1, 0, 0), axisY = rot(0, 1, 0), axisZ = rot(0, 0, 1);
            let minX = Infinity, minY = Infinity, minZ = Infinity;
            let maxX = -Infinity, maxY = -Infinity, maxZ = -Infinity;
            for (const sX of [-1, 1]) for (const sY of [-1, 1]) for (const sZ of [-1, 1]) {
                const px = comp.x + sX * hx * axisX[0] + sY * hy * axisY[0] + sZ * hz * axisZ[0];
                const py = comp.y + sX * hx * axisX[1] + sY * hy * axisY[1] + sZ * hz * axisZ[1];
                const pz = comp.z + sX * hx * axisX[2] + sY * hy * axisY[2] + sZ * hz * axisZ[2];
                minX = Math.min(minX, px); maxX = Math.max(maxX, px);
                minY = Math.min(minY, py); maxY = Math.max(maxY, py);
                minZ = Math.min(minZ, pz); maxZ = Math.max(maxZ, pz);
            }
            return { minX, maxX, minY, maxY, minZ, maxZ };
        },

        _aabbOverlap(a, b) {
            return a.minX < b.maxX && a.maxX > b.minX &&
                   a.minY < b.maxY && a.maxY > b.minY &&
                   a.minZ < b.maxZ && a.maxZ > b.minZ;
        },

        // Verifica se `comp` (posição/rotação candidatas) colide com alguma OUTRA
        // peça real de components3D. `excludeUids` evita autocolisão (a própria
        // peça, ou o grupo inteiro numa seleção múltipla/grupo que se move junto).
        // A margem de tolerância (ver _worldAABB) é PROPOSITAL aqui — sem ela,
        // duas peças genuinamente encostadas (ex: Q30 na face de um cubo)
        // seriam bloqueadas de ficar juntas.
        collidesWithOthers(comp, excludeUids = []) {
            const box = this._worldAABB(comp);
            if (!box) return false;
            return this.components3D.some(other => {
                if (excludeUids.includes(other.uid)) return false;
                const otherBox = this._worldAABB(other);
                if (!otherBox) return false;
                return this._aabbOverlap(box, otherBox);
            });
        },

        // Igual collidesWithOthers, mas SEM a margem de tolerância (margin=0) —
        // usado só pra "fechar folga" (ver object:modified): ali queremos achar
        // o ponto de TOQUE REAL exato, não o limite tolerante da colisão normal
        // (que de propósito permite ficar um pouco mais perto que o toque real,
        // pra não bloquear peças já encostadas). Com a margem de 8mm, "fechar
        // folga" convergia pra ~1,6cm ALÉM do toque real (sobrepondo de leve em
        // vez de só encostar) — corrigido usando margem zero aqui.
        _touchesExactly(comp, excludeUids = []) {
            const box = this._worldAABB(comp, 0);
            if (!box) return false;
            return this.components3D.some(other => {
                if (excludeUids.includes(other.uid)) return false;
                const otherBox = this._worldAABB(other, 0);
                if (!otherBox) return false;
                return this._aabbOverlap(box, otherBox);
            });
        },

        // Raio de captura do "fechar folga" (ver object:modified), proporcional
        // ao tamanho REAL da peça em vez de um valor fixo pra qualquer uma.
        // Piso e teto RECALIBRADOS em 2026-08-18 com dados reais do usuário
        // (projeto "BALCAO COM TESTEIRA": 28 pares próximos de Montante/
        // Travessa, folgas reais de arrasto manual entre 11,9mm e 56,2mm,
        // mediana 35,8mm) — um piso de 1cm (tentativa inicial) só pegava 2/28
        // casos reais; a maioria do erro humano de mouse fica na faixa de
        // 2-5cm, não milímetros. Piso subiu pra 4cm (pega ~70% dos casos reais
        // de primeira) e teto pra 6cm (pega 100% da amostra real). Ainda usa a
        // MENOR dimensão da peça (seção transversal) × 60% como referência,
        // então peças maiores que o Q30 (seção 30cm) continuam limitadas pelo
        // teto, e só usam o piso quando a peça é pequena o bastante pra isso
        // dominar (ex: Perfil PM5, grepo).
        _touchToleranceFor(comp) {
            const half = this._pieceHalfExtents(comp);
            if (!half) return 0.06;
            const smallest = Math.min(...half);
            return Math.min(0.06, Math.max(0.04, smallest * 0.6));
        },

        // Acha a peça mais próxima de `comp` e o vetor EXATO (por eixo) que fecha
        // o vão entre as duas caixas (margem zero — geometria real). Cada eixo é
        // 0 se as caixas já se sobrepõem naquele eixo, ou a distância assinalada
        // pra fechar. Usado pelo "fechar folga" em vez da direção do arrasto do
        // mouse — mais confiável quando a peça vizinha é grande/descentrada (a
        // direção até o centro dela pode não apontar pro vão de verdade).
        _nearestGap(comp, excludeUids = []) {
            const box = this._worldAABB(comp, 0);
            if (!box) return null;
            const gapAxis = (aMin, aMax, bMin, bMax) => {
                if (aMax < bMin) return bMin - aMax;
                if (bMax < aMin) return -(aMin - bMax);
                return 0;
            };
            let best = null, bestDist = Infinity;
            this.components3D.forEach(other => {
                if (excludeUids.includes(other.uid)) return;
                const otherBox = this._worldAABB(other, 0);
                if (!otherBox) return;
                const dx = gapAxis(box.minX, box.maxX, otherBox.minX, otherBox.maxX);
                const dy = gapAxis(box.minY, box.maxY, otherBox.minY, otherBox.maxY);
                const dz = gapAxis(box.minZ, box.maxZ, otherBox.minZ, otherBox.maxZ);
                const dist = Math.sqrt(dx * dx + dy * dy + dz * dz);
                if (dist > 0 && dist < bestDist) {
                    bestDist = dist;
                    best = { dx, dy, dz, dist };
                }
            });
            return best;
        },

        // Eixo do MUNDO (x/y/z) ao longo do qual o comprimento de `comp` corre,
        // depois de aplicar sua rotação — usado por _closeLoopIfBridging pra saber
        // em que direção "empurrar" as peças vizinhas. Só retorna um eixo pra
        // peças-barra de verdade (comprimento claramente dominante — pelo menos
        // 3x as outras duas dimensões; cubo/sapata/conectores não tem "eixo de
        // comprimento" e retornam null) E cuja rotação está alinhada aos eixos do
        // mundo (múltiplo de 90° — ângulos quebrados não têm um único eixo reto
        // pra empurrar peças ao longo dele).
        _worldLengthAxis(comp) {
            const half = this._pieceHalfExtents(comp);
            if (!half) return null;
            let lenIdx = 0;
            for (let i = 1; i < 3; i++) if (half[i] > half[lenIdx]) lenIdx = i;
            const others = [0, 1, 2].filter(i => i !== lenIdx).map(i => half[i]);
            if (half[lenIdx] < others[0] * 3 || half[lenIdx] < others[1] * 3) return null;

            const local = [0, 0, 0];
            local[lenIdx] = 1;
            const [rx, ry, rz] = this._pieceRotationRad(comp);
            const cx = Math.cos(rx), sx = Math.sin(rx);
            const cy = Math.cos(ry), sy = Math.sin(ry);
            const cz = Math.cos(rz), sz = Math.sin(rz);
            // Mesma composição de rotação (R = Rz*Ry*Rx) usada em _worldAABB.
            const y1 = local[1] * cx - local[2] * sx, z1 = local[1] * sx + local[2] * cx, x1 = local[0];
            const x2 = x1 * cy + z1 * sy, z2 = -x1 * sy + z1 * cy;
            const x3 = x2 * cz - y1 * sz, y3 = x2 * sz + y1 * cz;
            const world = [x3, y3, z2];
            const abs = world.map(Math.abs);
            let dom = 0;
            for (let i = 1; i < 3; i++) if (abs[i] > abs[dom]) dom = i;
            if (abs[dom] < 0.98) return null; // não alinhado a um eixo do mundo
            return { axisKey: ['x', 'y', 'z'][dom], halfLen: half[lenIdx] };
        },

        // Fecha um "laço" (ex: duas peças formando um U + uma peça de fechamento
        // entre elas): quando a peça arrastada tem as DUAS pontas perto de peças
        // DIFERENTES e perpendiculares a ela — não uma continuação reta, que já é
        // resolvida pelo "fechar folga" de uma ponta só em object:modified — mas o
        // vão entre essas duas peças vizinhas não bate exatamente com o
        // comprimento fixo da peça arrastada (peça de catálogo não estica), desliza
        // AS DUAS peças vizinhas (só elas — por decisão explícita do usuário, não
        // cascateia pra mais nada conectado a elas) ao longo do eixo da peça
        // arrastada até o vão bater exatamente, e encosta a peça arrastada bem no
        // meio, flush nas duas pontas ao mesmo tempo.
        _closeLoopIfBridging(comp, excludeUids) {
            const axisInfo = this._worldLengthAxis(comp);
            if (!axisInfo) return false;
            const { axisKey, halfLen } = axisInfo;

            const end1 = { x: comp.x, y: comp.y, z: comp.z };
            const end2 = { x: comp.x, y: comp.y, z: comp.z };
            end1[axisKey] -= halfLen;
            end2[axisKey] += halfLen;

            // 30cm: raio de captura de um GESTO ("aproximei pra fechar o laço"),
            // bem maior que a tolerância de toque preciso (_touchToleranceFor,
            // teto 6cm) usada no resto do app — aqui não estamos testando se já
            // encostou, estamos perguntando "o usuário está tentando ligar esta
            // ponta a esta peça?".
            const CAPTURE = 0.30;
            const findNeighborNear = (pt) => {
                let best = null, bestDist = CAPTURE;
                this.components3D.forEach(o => {
                    if (excludeUids.includes(o.uid)) return;
                    const oAxis = this._worldLengthAxis(o);
                    if (oAxis && oAxis.axisKey === axisKey) return; // continuação reta, não laço
                    const box = this._worldAABB(o, 0);
                    if (!box) return;
                    const dx = Math.max(box.minX - pt.x, 0, pt.x - box.maxX);
                    const dy = Math.max(box.minY - pt.y, 0, pt.y - box.maxY);
                    const dz = Math.max(box.minZ - pt.z, 0, pt.z - box.maxZ);
                    const dist = Math.sqrt(dx * dx + dy * dy + dz * dz);
                    if (dist < bestDist) { bestDist = dist; best = o; }
                });
                return best;
            };

            const neighborA = findNeighborNear(end1);
            const neighborB = findNeighborNear(end2);
            if (!neighborA || !neighborB || neighborA.uid === neighborB.uid) return false;

            let lo = neighborA, hi = neighborB;
            if (lo[axisKey] > hi[axisKey]) { lo = neighborB; hi = neighborA; }

            // Vão real ENTRE AS FACES (não centro-a-centro) — reaproveita _worldAABB
            // pra achar a face de cada vizinha voltada pra a outra, então funciona
            // certo não importa a espessura/rotação de cada uma (ex: montante 5cm
            // vs Q30 30cm têm faces bem diferentes do centro).
            const boxLo = this._worldAABB(lo, 0);
            const boxHi = this._worldAABB(hi, 0);
            const faceMaxKey = axisKey === 'x' ? 'maxX' : axisKey === 'y' ? 'maxY' : 'maxZ';
            const faceMinKey = axisKey === 'x' ? 'minX' : axisKey === 'y' ? 'minY' : 'minZ';
            const currentGap = boxHi[faceMinKey] - boxLo[faceMaxKey];
            const requiredGap = halfLen * 2;
            const delta = requiredGap - currentGap;

            // Isto é um AJUSTE FINO de imprecisão de montagem, não licença pra
            // deformar a estrutura pra caber qualquer peça errada escolhida. 15cm
            // ~ 2,5x o teto de tolerância de toque já usado em todo o resto do
            // app — dá folga real sem aceitar qualquer coisa.
            const MAX_ADJUST = 0.15;
            if (Math.abs(delta) < 1e-6 || Math.abs(delta) > MAX_ADJUST) return false;

            const preLo = lo[axisKey], preHi = hi[axisKey];
            lo[axisKey] = preLo - delta / 2;
            hi[axisKey] = preHi + delta / 2;

            // Segurança: se mover qualquer uma das duas vizinhas criar uma
            // colisão NOVA com outra peça fora do laço, desfaz tudo — o usuário
            // decidiu que isto só pode mexer nessas 2 peças, nunca cascatear pra
            // mais nada, então se não der pra fazer isolado, melhor não fazer
            // nada do que criar uma sobreposição em outro lugar da estrutura.
            const excl = excludeUids.concat([lo.uid, hi.uid]);
            if (this.collidesWithOthers(lo, excl) || this.collidesWithOthers(hi, excl)) {
                lo[axisKey] = preLo;
                hi[axisKey] = preHi;
                return false;
            }

            comp[axisKey] = (lo[axisKey] + hi[axisKey]) / 2;

            if (window.showToast) {
                showToast('cyan', 'Laço fechado', 'As duas peças vizinhas se ajustaram pra encaixar esta peça certinho.');
            }
            return true;
        },

        addComponent(peca, x, y, rotation = 0) {
            const scale = this.canvasScale;
            const cx = canvas.width / 2;
            const cy = canvas.height / 2;
            const depth = this.dimensions.width;
            
            let compX = 0;
            let compY = 0;
            let compZ = 0;
            let rotX = 0;
            let rotY = 0;
            let rotZ = 0;
            
            if (this.viewMode === '2d_frontal' || this.viewMode === '2d_fundo') {
                const xSign = this.viewMode === '2d_fundo' ? -1 : 1;
                const floorY = cy + (this.dimensions.height * scale) / 2 - 40;
                compX = xSign * (x - cx) / scale;
                compY = (floorY - y) / scale;
                compZ = 0;
                rotZ = rotation;
            } else if (this.viewMode === '2d_superior') {
                compX = (x - cx) / scale;
                compY = this.dimensions.height;
                compZ = (y - cy) / scale - depth / 2;
                rotY = rotation;
            } else if (this.viewMode === '2d_lateral' || this.viewMode === '2d_lateral_dir') {
                const zSign = this.viewMode === '2d_lateral_dir' ? -1 : 1;
                const floorY = cy + (this.dimensions.height * scale) / 2 - 40;
                compX = -this.dimensions.length / 2;
                compY = (floorY - y) / scale;
                compZ = zSign * (x - cx) / scale - depth / 2;
                rotX = rotation;
            }

            // Perfil PM5 — Montante/Travessa: restrição de orientação (ditada pelo
            // usuário): montante NUNCA deita (só ao longo de Y), travessa NUNCA
            // empina (só no plano horizontal X-Z). Sobrescreve o que a view tenha
            // calculado acima, então vale para qualquer view/forma de adicionar.
            if (peca.tipo === 'montante') {
                rotX = 0; rotY = 0; rotZ = 0;
            } else if (peca.tipo === 'travessa') {
                rotX = 0; rotZ = 0; // só rotY (direção no plano horizontal) fica livre
            }

            // SAPATA: sempre apoiada no solo (Y = altura/2 = 0.0175) — precisa
            // estar certo ANTES de testar colisão, senão a caixa candidata é
            // testada na altura errada.
            if (peca.tipo === 'sapata') {
                compY = 0.0175;
            }

            // Evita empilhar/sobrepor peças (ex: clicar várias vezes no catálogo
            // sempre adiciona no centro do canvas): "cascateia" a nova peça ao longo
            // do eixo de cascata da view atual até achar um espaço REALMENTE livre,
            // verificado por colisão real (caixa orientada, ver collidesWithOthers)
            // — antes disso a checagem era só heurística de posição/comprimento e
            // falhava com peças rotacionadas/desalinhadas (obs-011 em
            // .ai/memory/observations.md). Se não achar espaço em 60 tentativas,
            // bloqueia a adição em vez de deixar a peça nova invadir outra.
            const gap = 0.10;
            const cascadeAxis = (this.viewMode === '2d_lateral' || this.viewMode === '2d_lateral_dir') ? 'z' : 'x';
            const buildCandidate = () => ({
                tipo: peca.tipo,
                length: peca.comprimento || 0,
                x: compX, y: compY, z: compZ,
                rotationX: rotX, rotationY: rotY, rotationZ: rotZ
            });
            let candidate = buildCandidate();
            let candBox = this._worldAABB(candidate);
            const stepSize = candBox
                ? (cascadeAxis === 'x' ? (candBox.maxX - candBox.minX) : (candBox.maxZ - candBox.minZ)) + gap
                : 0.40;
            let attempts = 0;
            let placed = !candBox || !this.collidesWithOthers(candidate);
            while (!placed && attempts < 60) {
                if (cascadeAxis === 'z') { compZ += stepSize; } else { compX += stepSize; }
                candidate = buildCandidate();
                placed = !this.collidesWithOthers(candidate);
                attempts++;
            }
            if (!placed) {
                if (window.showToast) {
                    showToast('red', 'Sem espaço livre', 'Não foi possível adicionar a peça: colide com outras peças próximas.');
                }
                return;
            }

            const newComp = {
                uid: this._uid(),
                catalogId: peca.id,
                codigo: peca.codigo,
                tipo: peca.tipo,
                length: peca.comprimento || 0,
                nome: peca.nome,
                peso: peca.peso,
                x: compX,
                y: compY,
                z: compZ,
                rotationX: rotX,
                rotationY: rotY,
                rotationZ: rotZ
            };

            if (peca.tipo === 'sapata') {
                newComp.width = 0.30;
                newComp.height = 0.035;
            }

            if (peca.tipo === 'montante' || peca.tipo === 'travessa') {
                newComp.cor = peca.cor || '#94a3b8';
            }
            
            this.components3D.push(newComp);

            this.syncCanvasFrom3D();
            this.calculateQuantitative();
            this.pushHistory();
        },

        // ── Acessórios visuais (Lona / Painel de LED) ──────────────────────────
        // Itens meramente ilustrativos: não entram no quantitativo/orçamento
        // (excludeFromQuantitative: true). Renderizados como retângulo nas views
        // Frontal/Fundo e como plano translúcido no 3D.
        addAccessory(tipo, width, height) {
            // PAR LED é uma luminária de tamanho fixo (~0.25m), não uma
            // superfície dimensionável: ignora os inputs de largura/altura.
            if (tipo === 'parled') {
                width = 0.25;
                height = 0.30;
            }
            width = parseFloat(width) || 1;
            height = parseFloat(height) || 1;
            const isLed = tipo === 'painel_led';
            const depth = tipo === 'parled' ? 0.25 : (isLed ? 0.1 : 0.03);
            const scale = this.canvasScale;
            const cy = canvas.height / 2;
            const floorY = cy + (this.dimensions.height * scale) / 2 - 40;
            let x = 0, y = (floorY - cy) / scale, z = -depth / 2;
            if (this.viewMode === '2d_superior') {
                x = 0;
                y = this.dimensions.height / 2;
                z = -depth / 2;
            } else if (this.viewMode === '2d_lateral' || this.viewMode === '2d_lateral_dir') {
                x = 0;
                y = (floorY - cy) / scale;
                z = -depth / 2;
            }
            this._createAccessory(tipo, width, height, x, y, z);
        },

        // Converte um ponto de clique do canvas em coordenadas de mundo (a, b),
        // onde (a, b) representam os 2 eixos visíveis na view atual:
        // Frontal/Fundo -> (x, y) | Superior -> (x, z) | Lateral/Lateral Dir -> (z, y)
        _accessoryPointToWorld(px, py, depth) {
            const cx = canvas.width / 2;
            const cy = canvas.height / 2;
            const scale = this.canvasScale;
            const floorY = cy + (this.dimensions.height * scale) / 2 - 40;
            if (this.viewMode === '2d_superior') {
                return { a: (px - cx) / scale, b: (py - cy) / scale - depth / 2 };
            }
            if (this.viewMode === '2d_lateral' || this.viewMode === '2d_lateral_dir') {
                const zSign = this.viewMode === '2d_lateral_dir' ? -1 : 1;
                return { a: zSign * (px - cx) / scale - depth / 2, b: (floorY - py) / scale };
            }
            const xSign = this.viewMode === '2d_fundo' ? -1 : 1;
            return { a: xSign * (px - cx) / scale, b: (floorY - py) / scale };
        },

        toggleAccessorySketch(tipo) {
            if (this.accessorySketchType === tipo) {
                this._exitAccessorySketch();
                return;
            }
            if (this.accessorySketchType) {
                this._exitAccessorySketch();
            }
            if (this.sketchMode) {
                this.toggleSketchMode();
            }
            this.accessorySketchType = tipo;
            this._accessoryCorner = null;
            canvas.discardActiveObject();
            canvas.selection = false;
            this._accessoryClickHandler = (e) => {
                if (e.target) return;
                var ev = e.e || e;
                var pt = canvas.getPointer(ev);
                if (!pt) return;
                const isLed = this.accessorySketchType === 'painel_led';
                const depth = isLed ? 0.1 : 0.03;
                var world = this._accessoryPointToWorld(pt.x, pt.y, depth);
                if (isNaN(world.a) || isNaN(world.b)) return;
                if (!this._accessoryCorner) {
                    this._accessoryCorner = world;
                } else {
                    const spanA = Math.abs(world.a - this._accessoryCorner.a) || 0.5;
                    const spanB = Math.abs(world.b - this._accessoryCorner.b) || 0.5;
                    const centerA = (this._accessoryCorner.a + world.a) / 2;
                    const centerB = (this._accessoryCorner.b + world.b) / 2;
                    const accTipo = this.accessorySketchType;
                    let width, height, x, y, z;
                    if (this.viewMode === '2d_superior') {
                        // a=x, b=z — largura definida pelo arrasto, altura mantém o valor digitado
                        width = spanA;
                        height = this.accessoryHeight;
                        x = centerA; y = this.dimensions.height / 2; z = centerB;
                    } else if (this.viewMode === '2d_lateral' || this.viewMode === '2d_lateral_dir') {
                        // a=z, b=y — altura definida pelo arrasto, largura mantém o valor digitado
                        width = this.accessoryWidth;
                        height = spanB;
                        x = 0; y = centerB; z = centerA;
                    } else {
                        // Frontal/Fundo — a=x, b=y
                        width = spanA;
                        height = spanB;
                        x = centerA; y = centerB; z = -depth / 2;
                    }
                    this._exitAccessorySketch();
                    this._createAccessory(accTipo, width, height, x, y, z);
                }
            };
            canvas.on('mouse:down', this._accessoryClickHandler);
        },

        _exitAccessorySketch() {
            canvas.off('mouse:down', this._accessoryClickHandler);
            canvas.selection = true;
            this.accessorySketchType = null;
            this._accessoryCorner = null;
        },

        _createAccessory(tipo, width, height, x, y, z = 0) {
            const isLed = tipo === 'painel_led';
            const isParled = tipo === 'parled';
            const newComp = {
                uid: this._uid(),
                codigo: isParled ? 'PARLED' : (isLed ? 'PAINEL_LED' : 'LONA'),
                nome: isParled ? 'PAR LED' : (isLed ? 'Painel de LED' : 'Lona'),
                tipo: tipo,
                width: width,
                height: height,
                depth: isParled ? 0.25 : (isLed ? 0.1 : 0.03),
                x: x, y: y, z: z,
                rotationX: 0, rotationY: 0, rotationZ: 0,
                excludeFromQuantitative: true
            };
            if (tipo === 'lona') {
                newComp.color = this.accessoryColor;
                newComp.opacity = this.accessoryOpacity / 100;
            }
            this.components3D.push(newComp);
            this.syncCanvasFrom3D();
            this.calculateQuantitative();
            this.pushHistory();
            if (window.showToast) {
                const titulo = isParled ? 'PAR LED Adicionado' : (isLed ? 'Painel de LED Adicionado' : 'Lona Adicionada');
                const detalhe = isParled ? 'Refletor ilustrativo — não entra no quantitativo.' : `${width.toFixed(2)}m x ${height.toFixed(2)}m — não entra no quantitativo.`;
                showToast('green', titulo, detalhe);
            }
        },

        // uids das Lonas atualmente selecionadas no canvas
        _selectedLonaUids() {
            const active = canvas.getActiveObject();
            if (!active) return [];
            const uids = [];
            if (active.type === 'activeSelection') {
                active.forEachObject(o => { if (o.data && o.data.uid) uids.push(o.data.uid); });
            } else if (active.data && active.data.uid) {
                uids.push(active.data.uid);
            }
            return this.components3D.filter(c => uids.includes(c.uid) && c.tipo === 'lona').map(c => c.uid);
        },

        // Preenche a(s) Lona(s) selecionada(s) com a imagem escolhida (PNG/JPG).
        // A imagem é reduzida para no máx. 1024px e salva como dataURL dentro do
        // componente — vai junto no JSON do projeto (save/load/copiar/colar).
        applyLonaImage(ev) {
            const file = ev.target.files && ev.target.files[0];
            ev.target.value = '';
            if (!file) return;
            const lonaUids = this._selectedLonaUids();
            if (lonaUids.length === 0) {
                if (window.showToast) showToast('cyan', 'Selecione uma Lona', 'Clique numa lona no canvas antes de escolher a imagem.');
                return;
            }
            const reader = new FileReader();
            reader.onload = () => {
                const img = new Image();
                img.onload = () => {
                    const maxSide = 1024;
                    const f = Math.min(1, maxSide / Math.max(img.width, img.height));
                    const w = Math.round(img.width * f);
                    const h = Math.round(img.height * f);
                    const cnv = document.createElement('canvas');
                    cnv.width = w; cnv.height = h;
                    cnv.getContext('2d').drawImage(img, 0, 0, w, h);
                    const isPng = file.type === 'image/png';
                    const dataUrl = cnv.toDataURL(isPng ? 'image/png' : 'image/jpeg', 0.85);
                    this.components3D.forEach(c => {
                        if (lonaUids.includes(c.uid)) c.image = dataUrl;
                    });
                    this.syncCanvasFrom3D();
                    this.pushHistory();
                    if (window.showToast) showToast('green', 'Imagem Aplicada', `${lonaUids.length} lona(s) preenchida(s) com "${file.name}".`);
                };
                img.src = reader.result;
            };
            reader.readAsDataURL(file);
        },

        removeLonaImage() {
            const lonaUids = this._selectedLonaUids();
            let changed = 0;
            this.components3D.forEach(c => {
                if (lonaUids.includes(c.uid) && c.image) {
                    delete c.image;
                    changed++;
                }
            });
            if (changed > 0) {
                this.syncCanvasFrom3D();
                this.pushHistory();
                if (window.showToast) showToast('cyan', 'Imagem Removida', `${changed} lona(s) voltou(aram) à cor sólida.`);
            } else if (window.showToast) {
                showToast('cyan', 'Nada a remover', 'Selecione uma lona que tenha imagem aplicada.');
            }
        },

        // HTMLImageElement da imagem da Lona, via cache de closure. Se ainda não
        // carregou, devolve null e re-sincroniza o canvas quando carregar.
        _getLonaImage(comp) {
            if (!comp.image) return null;
            let entry = lonaImgCache[comp.uid];
            if (entry && entry.src === comp.image) {
                return entry.loaded ? entry.img : null;
            }
            const img = new Image();
            entry = { src: comp.image, img: img, loaded: false };
            lonaImgCache[comp.uid] = entry;
            img.onload = () => {
                entry.loaded = true;
                this.syncCanvasFrom3D();
            };
            img.src = comp.image;
            return null;
        },

        // Converte cor hex (#rrggbb) em rgba() com a transparência da Lona
        _hexToRgba(hex, alpha) {
            const m = /^#?([0-9a-f]{6})$/i.exec(hex || '');
            if (!m) return `rgba(248,250,252,${alpha})`;
            const n = parseInt(m[1], 16);
            return `rgba(${(n >> 16) & 255},${(n >> 8) & 255},${n & 255},${alpha})`;
        },

        // Aplica a transparência do slider à(s) Lona(s) selecionada(s)
        applyAccessoryOpacity() {
            const lonaUids = this._selectedLonaUids();
            let changed = 0;
            this.components3D.forEach(c => {
                if (lonaUids.includes(c.uid)) {
                    c.opacity = this.accessoryOpacity / 100;
                    changed++;
                }
            });
            if (changed > 0) {
                this.syncCanvasFrom3D();
                this.pushHistory();
            }
        },

        // Recolore a(s) Lona(s) selecionada(s) com a cor do seletor da toolbar
        applyAccessoryColor() {
            const active = canvas.getActiveObject();
            if (!active) return;
            const uids = [];
            if (active.type === 'activeSelection') {
                active.forEachObject(o => { if (o.data && o.data.uid) uids.push(o.data.uid); });
            } else if (active.data && active.data.uid) {
                uids.push(active.data.uid);
            }
            let changed = 0;
            this.components3D.forEach(c => {
                if (uids.includes(c.uid) && c.tipo === 'lona') {
                    c.color = this.accessoryColor;
                    changed++;
                }
            });
            if (changed > 0) {
                this.syncCanvasFrom3D();
                this.pushHistory();
                if (window.showToast) showToast('cyan', 'Cor Aplicada', `${changed} lona(s) recolorida(s).`);
            }
        },

        // Representação 2D do PAR LED (mesma em todas as 5 views): corpo
        // arredondado escuro + lente circular âmbar. Tamanho mínimo em pixels
        // para continuar visível/clicável em escalas pequenas.
        _createParLed2D(left, top, angle, uid) {
            const scale = this.canvasScale;
            const r = Math.max(0.10 * scale, 5);
            const bodyW = r * 2.2;
            const bodyH = r * 2.6;
            const body = new fabric.Rect({
                left: 0, top: 0,
                width: bodyW, height: bodyH,
                rx: r * 0.4, ry: r * 0.4,
                fill: 'rgba(15,23,42,0.9)',
                stroke: '#fbbf24', strokeWidth: 2,
                originX: 'center', originY: 'center'
            });
            const lens = new fabric.Circle({
                left: 0, top: bodyH / 2 - r * 0.9,
                radius: r * 0.8,
                fill: 'rgba(251,191,36,0.45)',
                stroke: '#fbbf24', strokeWidth: 2,
                originX: 'center', originY: 'center'
            });
            const obj = new fabric.Group([body, lens], {
                left: left, top: top,
                originX: 'center', originY: 'center',
                angle: angle || 0,
                lockScalingX: true, lockScalingY: true,
                lockSkewingX: true, lockSkewingY: true,
                padding: 8,
                cornerColor: '#fbbf24', cornerSize: 8,
                transparentCorners: false,
                data: { uid: uid }
            });
            obj.setControlsVisibility({
                mt: false, mb: false, ml: false, mr: false,
                bl: false, br: false, tl: false, tr: false,
                mtr: true
            });
            obj.on('moving', () => { this.magneticSnap(obj); });
            return obj;
        },

        // ── Label de texto (ex: nome do evento/marca) — não entra no quantitativo ──
        addLabel(text) {
            text = (text || '').trim() || 'TEXTO';
            const scale = this.canvasScale;
            const cy = canvas.height / 2;
            const floorY = cy + (this.dimensions.height * scale) / 2 - 40;
            let x = 0, y = (floorY - cy) / scale, z = 0;
            if (this.viewMode === '2d_superior') {
                x = 0; y = this.dimensions.height; z = 0;
            } else if (this.viewMode === '2d_lateral' || this.viewMode === '2d_lateral_dir') {
                x = 0; y = (floorY - cy) / scale; z = 0;
            }
            this._createLabel(text, x, y, z);
        },

        _createLabel(text, x, y, z = 0) {
            const newComp = {
                uid: this._uid(),
                codigo: 'LABEL',
                nome: 'Label',
                tipo: 'label',
                text: text,
                x: x, y: y, z: z,
                rotationX: 0, rotationY: 0, rotationZ: 0,
                excludeFromQuantitative: true
            };
            this.components3D.push(newComp);
            this.syncCanvasFrom3D();
            this.calculateQuantitative();
            this.pushHistory();
            if (window.showToast) {
                showToast('green', 'Label Adicionado', `"${text}" — não entra no quantitativo. Duplo-clique para editar o texto.`);
            }
        },

        addSideConnectingBeams(x, y, virtualStock = null) {
            const depth = this.dimensions.width;
            if (depth <= 0.5) return;
            const cuboHalf = 0.15;
            const beamDepth = depth - 2 * cuboHalf; // exclude cube volumes at both ends
            if (beamDepth <= 0.05) return;
            
            const q30s = this.catalog.filter(p => p.tipo === 'Q30').sort((a,b) => b.comprimento - a.comprimento);
            if (q30s.length === 0) return;
            const cuboPeca = this.catalog.find(p => p.tipo === 'cubo');

            if (!virtualStock) {
                virtualStock = {};
                this.catalog.forEach(p => {
                    if (p.tipo === 'Q30') {
                        virtualStock[p.id] = parseInt(p.estoque) || 0;
                    }
                });
                this.components3D.forEach(c => {
                    if (c.tipo === 'Q30' && virtualStock[c.catalogId]) {
                        virtualStock[c.catalogId]--;
                    }
                });
            }
            
            const allCombs = [];
            const findCombs = (target, index, currentComb) => {
                if (Math.abs(target) < 0.01) {
                    allCombs.push([...currentComb]);
                    return;
                }
                if (target < -0.01 || index >= q30s.length) {
                    return;
                }
                const pLen = q30s[index].comprimento;
                if (pLen <= target + 0.01) {
                    currentComb.push(q30s[index]);
                    findCombs(target - pLen, index, currentComb);
                    currentComb.pop();
                }
                findCombs(target, index + 1, currentComb);
            };
            
            findCombs(beamDepth, 0, []);
            
            let bestComb = null;
            if (allCombs.length > 0) {
                let bestScore = -Infinity;
                allCombs.forEach(comb => {
                    const usage = {};
                    comb.forEach(p => {
                        usage[p.id] = (usage[p.id] || 0) + 1;
                    });
                    
                    let outOfStockPenalty = 0;
                    Object.keys(usage).forEach(pid => {
                        const needed = usage[pid];
                        const available = virtualStock[pid] || 0;
                        if (needed > available) {
                            outOfStockPenalty += (needed - available) * 100;
                        }
                    });
                    
                    const score = -outOfStockPenalty - comb.length;
                    if (score > bestScore) {
                        bestScore = score;
                        bestComb = comb;
                    }
                });
            }
            
            if (!bestComb) {
                bestComb = [];
                let remaining = beamDepth;
                while (remaining > 0.05) {
                    let fitPeca = null;
                    for (let qi = 0; qi < q30s.length; qi++) {
                        if (q30s[qi].comprimento <= remaining + 0.01) {
                            fitPeca = q30s[qi];
                            break;
                        }
                    }
                    if (!fitPeca) break;
                    bestComb.push(fitPeca);
                    remaining -= fitPeca.comprimento;
                }
            }
            
            let curZ = -cuboHalf; // start from cube face, not center
            bestComb.forEach((fitPeca, idx) => {
                const zCenter = curZ - fitPeca.comprimento / 2;
                const newBeam = {
                    uid: this._uid(),
                    catalogId: fitPeca.id,
                    codigo: fitPeca.codigo,
                    tipo: fitPeca.tipo,
                    length: fitPeca.comprimento,
                    nome: fitPeca.nome,
                    peso: fitPeca.peso,
                    x: x,
                    y: y,
                    z: zCenter,
                    rotationX: 0,
                    rotationY: 90,
                    rotationZ: 0
                };
                this.components3D.push(newBeam);

                if (virtualStock[fitPeca.id]) {
                    virtualStock[fitPeca.id]--;
                }
                curZ -= fitPeca.comprimento;

                // Cubo connector at the joint between this segment and the next
                if (idx < bestComb.length - 1 && cuboPeca) {
                    this.components3D.push({
                        uid: this._uid(),
                        catalogId: cuboPeca.id,
                        codigo: cuboPeca.codigo,
                        tipo: cuboPeca.tipo,
                        length: cuboPeca.comprimento || 0,
                        nome: cuboPeca.nome,
                        peso: cuboPeca.peso,
                        x: x, y: y, z: curZ,
                        rotationX: 0, rotationY: 90, rotationZ: 0
                    });
                }
            });
        },

        onDragStart(e, peca) {
            e.dataTransfer.setData('text/plain', JSON.stringify(peca));
        },
        
        onDrop(e) {
            const rect = document.getElementById('trussCanvas').getBoundingClientRect();
            const x = e.clientX - rect.left;
            const y = e.clientY - rect.top;
            
            try {
                const peca = JSON.parse(e.dataTransfer.getData('text/plain'));
                if (peca && peca.id) {
                    this.addComponent(peca, x, y);
                }
            } catch (err) {
                console.error("Erro no drop:", err);
            }
        },
        
        deleteSelected() {
            const activeObject = canvas.getActiveObject();
            if (activeObject) {
                let uidsToDelete = [];
                if (activeObject.type === 'activeSelection') {
                    activeObject.forEachObject((obj) => {
                        if (obj.data && obj.data.uid) {
                            uidsToDelete.push(obj.data.uid);
                        }
                    });
                    canvas.discardActiveObject();
                } else {
                    if (activeObject.data && activeObject.data.uid) {
                        uidsToDelete.push(activeObject.data.uid);
                    }
                }
                
                if (uidsToDelete.length > 0) {
                    this.components3D = this.components3D.filter(c => !uidsToDelete.includes(c.uid));
                    this.syncCanvasFrom3D();
                    this.calculateQuantitative();
                    canvas.discardActiveObject();
                    canvas.requestRenderAll();
                    this.pushHistory();
                }
            }
        },
        
        rotateSelected(degrees) {
            const activeObject = canvas.getActiveObject();
            if (activeObject) {
                let uidsToRotate = [];
                if (activeObject.type === 'activeSelection') {
                    activeObject.forEachObject((obj) => {
                        if (obj.data && obj.data.uid) {
                            uidsToRotate.push(obj.data.uid);
                        }
                    });
                } else {
                    if (activeObject.data && activeObject.data.uid) {
                        uidsToRotate.push(activeObject.data.uid);
                    }
                }
                
                if (uidsToRotate.length > 0) {
                    this.components3D.forEach(c => {
                        if (uidsToRotate.includes(c.uid)) {
                            if (this.viewMode === '2d_frontal' || this.viewMode === '2d_fundo') {
                                c.rotationZ = (c.rotationZ + degrees) % 360;
                            } else if (this.viewMode === '2d_superior') {
                                c.rotationY = (c.rotationY + degrees) % 360;
                            } else if (this.viewMode === '2d_lateral' || this.viewMode === '2d_lateral_dir') {
                                c.rotationX = (c.rotationX + degrees) % 360;
                            }
                        }
                    });
                    this.syncCanvasFrom3D();
                    this.calculateQuantitative();
                    this.pushHistory();
                }
            }
        },

        // Peça única selecionada (não seleção múltipla) — usada pelo painel de
        // posição/altura exata. `xInput`/`heightInput`/`zInput` são populados
        // sempre que a seleção muda (ver watcher em init()), pra não sobrescrever
        // o que o usuário está digitando a cada re-render.
        get selectedComp() {
            if (!this.selectedCompUid) return null;
            return this.components3D.find(c => c.uid === this.selectedCompUid) || null;
        },

        // Campo numérico + botões ▲/▼ funcionam pros 3 eixos (X/Y/Z) do mesmo
        // jeito — só Y tem a trava extra da SAPATA (sempre no solo). `axis` é
        // 'x'/'y'/'z', `inputProp` é o nome do data property Alpine correspondente
        // ('xInput'/'heightInput'/'zInput').
        applyAxisInput(axis, inputProp) {
            const comp = this.selectedComp;
            if (!comp) return;
            if (axis === 'y' && comp.tipo === 'sapata') {
                if (window.showToast) {
                    showToast('red', 'Altura travada', 'Sapata sempre fica apoiada no solo (Y não é editável).');
                }
                this[inputProp] = comp.y;
                return;
            }
            const newVal = parseFloat(this[inputProp]);
            if (isNaN(newVal)) { this[inputProp] = comp[axis]; return; }
            this._setSelectedAxis(axis, inputProp, newVal);
        },

        // Botões ▲/▼: ajuste fino em passos de 5cm, sem precisar acertar de
        // primeira no campo numérico ou no arrasto.
        nudgeSelectedAxis(axis, inputProp, deltaM) {
            const comp = this.selectedComp;
            if (!comp) return;
            if (axis === 'y' && comp.tipo === 'sapata') {
                if (window.showToast) {
                    showToast('red', 'Altura travada', 'Sapata sempre fica apoiada no solo (Y não é editável).');
                }
                return;
            }
            this._setSelectedAxis(axis, inputProp, comp[axis] + deltaM);
        },

        _setSelectedAxis(axis, inputProp, newVal) {
            const comp = this.selectedComp;
            if (!comp) return;
            const oldVal = comp[axis];
            comp[axis] = newVal;
            if (this.collidesWithOthers(comp, [comp.uid])) {
                comp[axis] = oldVal;
                this[inputProp] = oldVal;
                if (window.showToast) {
                    showToast('red', 'Movimento bloqueado', 'Essa posição colide com outra peça.');
                }
                return;
            }
            this[inputProp] = newVal;
            this.syncCanvasFrom3D();
            this.calculateQuantitative();
            this.pushHistory();
            // syncCanvasFrom3D() faz canvas.clear() + recria os objetos do zero —
            // isso derruba a seleção do Fabric.js (o objeto antigo nem existe
            // mais). Sem reselecionar, o painel de posição (que só aparece com
            // uma peça selecionada) sumiria depois do 1º clique, parecendo que
            // os botões ▲/▼/Aplicar "pararam de funcionar".
            this._reselectComp(comp.uid);
        },

        // Botão "Aplicar" único: aplica X/Y/Z juntos como UMA posição final (em
        // vez de 3 chamadas independentes de applyAxisInput, que fariam 3
        // reconstruções de canvas e 3 checagens de colisão separadas — aqui é
        // uma checagem só, contra a posição realmente pretendida).
        applyAllAxesInput() {
            const comp = this.selectedComp;
            if (!comp) return;
            const newX = parseFloat(this.xInput);
            const newY = comp.tipo === 'sapata' ? comp.y : parseFloat(this.heightInput);
            const newZ = parseFloat(this.zInput);
            if (isNaN(newX) || isNaN(newY) || isNaN(newZ)) return;
            const oldX = comp.x, oldY = comp.y, oldZ = comp.z;
            comp.x = newX; comp.y = newY; comp.z = newZ;
            if (this.collidesWithOthers(comp, [comp.uid])) {
                comp.x = oldX; comp.y = oldY; comp.z = oldZ;
                this.xInput = oldX; this.heightInput = oldY; this.zInput = oldZ;
                if (window.showToast) {
                    showToast('red', 'Movimento bloqueado', 'Essa posição colide com outra peça.');
                }
                return;
            }
            this.xInput = newX; this.heightInput = newY; this.zInput = newZ;
            this.syncCanvasFrom3D();
            this.calculateQuantitative();
            this.pushHistory();
            this._reselectComp(comp.uid);
        },

        // Reencontra o objeto Fabric recém-recriado que corresponde ao mesmo
        // uid e o marca como selecionado de novo.
        _reselectComp(uid) {
            if (!uid) return;
            const obj = canvas.getObjects().find(o => o.data && o.data.uid === uid);
            if (obj) {
                canvas.setActiveObject(obj);
                canvas.requestRenderAll();
            }
        },

        nudgeObject(obj, key, pxStep) {
            const comp = this.components3D.find(c => c.uid === obj.data.uid);
            if (!comp) return;

            const scale = this.canvasScale;
            const worldStep = pxStep / scale;
            let dx = 0, dy = 0;

            if (key === 'ArrowUp') { dy = -1; }
            else if (key === 'ArrowDown') { dy = 1; }
            else if (key === 'ArrowLeft') { dx = -1; }
            else if (key === 'ArrowRight') { dx = 1; }

            const preLeft = obj.left, preTop = obj.top;
            const preX = comp.x, preY = comp.y, preZ = comp.z;

            obj.left += dx * pxStep;
            obj.top += dy * pxStep;
            obj.setCoords();

            if (this.viewMode === '2d_frontal' || this.viewMode === '2d_fundo') {
                var nSign = this.viewMode === '2d_fundo' ? -1 : 1;
                comp.x += nSign * dx * worldStep;
                comp.y -= dy * worldStep;
            } else if (this.viewMode === '2d_superior') {
                comp.x += dx * worldStep;
                comp.z += dy * worldStep;
            } else if (this.viewMode === '2d_lateral' || this.viewMode === '2d_lateral_dir') {
                var nSign2 = this.viewMode === '2d_lateral_dir' ? -1 : 1;
                comp.z += nSign2 * dx * worldStep;
                comp.y -= dy * worldStep;
            }

            // SAPATA: enforce ground constraint after nudge
            if (comp.tipo === 'sapata') {
                comp.y = 0.0175;
            }

            // Colisão: nudge que esbarra em outra peça é desfeito (posição e visual).
            if (this.collidesWithOthers(comp, [comp.uid])) {
                comp.x = preX; comp.y = preY; comp.z = preZ;
                obj.left = preLeft; obj.top = preTop;
                obj.setCoords();
            }

            canvas.requestRenderAll();
        },

        clearCanvas() {
            if (this.sketchMode) {
                if (!confirm("Limpar o esboço atual?")) return;
                this.clearSketch();
                this.syncCanvasFrom3D();
                return;
            }
            if (confirm("Deseja realmente limpar toda a estrutura do projeto?")) {
                this.components3D = [];
                this.syncCanvasFrom3D();
                this.calculateQuantitative();
                this.pushHistory();
                if (window.showToast) {
                    showToast('cyan', 'Canvas Limpo', 'Todos os componentes foram removidos.');
                }
            }
        },
        
        _uid() { return 'comp_' + (++this._uidCounter); },

        // ── Copiar/Colar/Duplicar (Ctrl+C / Ctrl+V / Ctrl+D) ──────────────────
        copySelected() {
            const active = canvas.getActiveObject();
            if (!active) return false;
            const uids = [];
            if (active.type === 'activeSelection') {
                active.forEachObject(o => { if (o.data && o.data.uid) uids.push(o.data.uid); });
            } else if (active.data && active.data.uid) {
                uids.push(active.data.uid);
            }
            const comps = this.components3D.filter(c => uids.includes(c.uid));
            if (comps.length === 0) return false;
            this._clipboard = JSON.parse(JSON.stringify(comps));
            if (window.showToast) {
                showToast('cyan', 'Copiado', `${comps.length} componente(s) copiado(s). Ctrl+V para colar.`);
            }
            return true;
        },

        pasteClipboard() {
            if (!this._clipboard || this._clipboard.length === 0) return false;
            // Desloca nos eixos visíveis da view atual para a cópia não nascer em
            // cima do original, cascateando o deslocamento (igual ao
            // addComponent) até achar uma posição sem colisão pro GRUPO inteiro
            // — antes colava sempre a 0,5m fixo sem checar nada, então colar
            // sobre uma estrutura cheia sobrepunha peças silenciosamente e dava
            // a sensação de "a colisão não funciona" (na real ela nunca rodava
            // nesse caminho).
            const step = 0.5;
            const maxAttempts = 20;
            const applyOffset = (c, off) => {
                const cand = { ...c };
                if (this.viewMode === '2d_superior') {
                    cand.x = c.x + off; cand.z = c.z + off;
                } else if (this.viewMode === '2d_lateral' || this.viewMode === '2d_lateral_dir') {
                    cand.z = c.z + off;
                } else {
                    cand.x = c.x + off;
                }
                return cand;
            };
            let off = step;
            let candidates = this._clipboard.map(c => applyOffset(c, off));
            let attempts = 0;
            while (candidates.some(cand => this.collidesWithOthers(cand)) && attempts < maxAttempts) {
                off += step;
                candidates = this._clipboard.map(c => applyOffset(c, off));
                attempts++;
            }
            if (attempts >= maxAttempts && window.showToast) {
                showToast('red', 'Espaço apertado', 'Não achei posição totalmente livre para colar — confira se alguma cópia ficou sobreposta.');
            }

            // Atualiza o clipboard com o offset final: colar de novo continua a
            // "escadinha" a partir daqui, não do offset original.
            this._clipboard = candidates;

            const newUids = [];
            candidates.forEach(cand => {
                const copy = JSON.parse(JSON.stringify(cand));
                copy.uid = this._uid();
                newUids.push(copy.uid);
                this.components3D.push(copy);
            });
            this.syncCanvasFrom3D();
            this.calculateQuantitative();
            this.pushHistory();
            // Deixa as cópias selecionadas: dá para arrastar ou colar de novo.
            // syncCanvasFrom3D() já rodou (linha acima) e travou + esmaeceu (ver
            // _applyViewLocks) qualquer cópia que a "escadinha" do offset tenha
            // empurrado pro lado de fora da view atual — comum quando o original
            // já estava perto do corte, ou quando se copia com Ctrl+Shift peças
            // dos dois lados do corte de uma vez. Restaura seleção E opacidade
            // originais (_viewLockBaseOpacity, guardada por _applyViewLocks) —
            // sem isso a cópia "do outro lado" ficava com ~30% de opacidade
            // (quase invisível no fundo escuro do canvas, fácil de parecer que
            // "não veio" mesmo estando lá) mesmo depois de destravada.
            const objs = canvas.getObjects().filter(o => o.data && newUids.includes(o.data.uid));
            objs.forEach(o => {
                if (o.selectable === false) {
                    o.set({
                        selectable: true,
                        evented: true,
                        hoverCursor: 'move',
                        opacity: o._viewLockBaseOpacity !== undefined ? o._viewLockBaseOpacity : o.opacity
                    });
                }
            });
            // NÃO chama discardActiveObject() aqui antes de trocar a seleção:
            // isso dispararia 'selection:cleared', que roda _applyViewLocks() e
            // RE-TRAVARIA (e re-esmaeceria) na hora as cópias recém destravadas
            // acima, antes mesmo da seleção final ser montada — mesmo bug já
            // corrigido no Ctrl+Shift (ver handler de seleção mais acima).
            // setActiveObject() já troca a seleção atomicamente.
            if (objs.length === 1) {
                canvas.setActiveObject(objs[0]);
            } else if (objs.length > 1) {
                canvas.setActiveObject(new fabric.ActiveSelection(objs, { canvas: canvas }));
            }
            canvas.requestRenderAll();
            if (window.showToast) {
                showToast('green', 'Colado', `${newUids.length} componente(s) duplicado(s).`);
            }
            return true;
        },

        // Undo/Redo: histórico de snapshots de components3D (pilha linear, máx 50 estados).
        resetHistory() {
            this.history = [JSON.stringify(this.components3D)];
            this.historyIndex = 0;
        },

        pushHistory() {
            if (this._historySuppress) return;
            const snapshot = JSON.stringify(this.components3D);
            if (this.historyIndex >= 0 && this.history[this.historyIndex] === snapshot) return;
            this.history = this.history.slice(0, this.historyIndex + 1);
            this.history.push(snapshot);
            if (this.history.length > 50) this.history.shift();
            this.historyIndex = this.history.length - 1;
        },

        canUndo() { return this.historyIndex > 0; },
        canRedo() { return this.historyIndex < this.history.length - 1; },

        undo() {
            if (!this.canUndo()) return;
            this.historyIndex--;
            this._restoreHistorySnapshot();
        },

        redo() {
            if (!this.canRedo()) return;
            this.historyIndex++;
            this._restoreHistorySnapshot();
        },

        _restoreHistorySnapshot() {
            this._historySuppress = true;
            canvas.discardActiveObject();
            this.components3D = JSON.parse(this.history[this.historyIndex]);
            this.syncCanvasFrom3D();
            this.calculateQuantitative();
            if (this.viewMode === '3d') {
                this.init3DView();
            }
            this._historySuppress = false;
        },
        
        pixelToMeters(px, py) {
            const cx = canvas.width / 2;
            const cy = canvas.height / 2;
            const scale = this.canvasScale;
            const floorY = cy + (this.dimensions.height * scale) / 2 - 40;
            return { x: (px - cx) / scale, y: (floorY - py) / scale };
        },
        
        toggleSketchMode() {
            if (this.accessorySketchType) this._exitAccessorySketch();
            if (this.sketchMode) {
                this.sketchMode = false;
                canvas.selection = true;
                canvas.off('mouse:down', this._sketchClickHandler);
                canvas.off('mouse:move', this._sketchMoveHandler);
                canvas.off('mouse:dblclick', this._sketchDblClickHandler);
                if (this._previewLine) { canvas.remove(this._previewLine); this._previewLine = null; }
                if (this._previewLabel) { canvas.remove(this._previewLabel); this._previewLabel = null; }
                this.clearSketch();
                this.syncCanvasFrom3D();
                this.calculateQuantitative();
            } else {
                this.sketchMode = true;
                canvas.selection = false;
                canvas.discardActiveObject();
                this._sketchClickHandler = (e) => {
                    if (e.target) return;
                    var ev = e.e || e;
                    var pt = canvas.getPointer(ev) || e.absolutePointer || e.pointer;
                    if (!pt) {
                        var el = canvas.getElement();
                        var r = el.getBoundingClientRect();
                        var z = canvas.getZoom();
                        pt = { x: (ev.clientX - r.left) / z, y: (ev.clientY - r.top) / z };
                    }
                    var world = this.pixelToMeters(pt.x, pt.y);
                    if (isNaN(world.x) || isNaN(world.y)) return;
                    this.sketchPoints.push({ x: world.x, y: world.y });
                    this.renderSketch();
                };
                this._sketchMoveHandler = (e) => {
                    if (this.sketchPoints.length === 0) return;
                    var ev = e.e || e;
                    var pt = canvas.getPointer(ev) || e.absolutePointer || e.pointer;
                    if (!pt) return;
                    var world = this.pixelToMeters(pt.x, pt.y);
                    if (isNaN(world.x) || isNaN(world.y)) return;
                    var last = this.sketchPoints[this.sketchPoints.length - 1];
                    var scale = this.canvasScale;
                    var cx = canvas.width / 2;
                    var cy = canvas.height / 2;
                    var floorY = cy + (this.dimensions.height * scale) / 2 - 40;
                    var px1 = cx + last.x * scale;
                    var py1 = floorY - last.y * scale;
                    var px2 = cx + world.x * scale;
                    var py2 = floorY - world.y * scale;
                    var dx = world.x - last.x;
                    var dy = world.y - last.y;
                    var dist = Math.sqrt(dx * dx + dy * dy);
                    if (this._previewLine && this._previewLabel) {
                        this._previewLine.set({ x1: px1, y1: py1, x2: px2, y2: py2 });
                        this._previewLabel.set({ text: dist.toFixed(2) + 'm', left: (px1 + px2) / 2, top: (py1 + py2) / 2 - 14 });
                        canvas.requestRenderAll();
                        return;
                    }
                    this._previewLine = new fabric.Line([px1, py1, px2, py2], {
                        stroke: '#fef08a', strokeWidth: 1.5, strokeDashArray: [4, 4],
                        selectable: false, evented: false
                    });
                    canvas.add(this._previewLine);
                    this._previewLabel = new fabric.Text(dist.toFixed(2) + 'm', {
                        left: (px1 + px2) / 2, top: (py1 + py2) / 2 - 14, fontSize: 11,
                        fontFamily: 'monospace', fontWeight: 'bold',
                        fill: '#fef08a', backgroundColor: '#070a13',
                        padding: 2, originX: 'center', originY: 'center',
                        selectable: false, evented: false
                    });
                    canvas.add(this._previewLabel);
                    canvas.requestRenderAll();
                };
                this._sketchDblClickHandler = () => {
                    this.finishSketch();
                };
                canvas.on('mouse:down', this._sketchClickHandler);
                canvas.on('mouse:move', this._sketchMoveHandler);
                canvas.on('mouse:dblclick', this._sketchDblClickHandler);
            }
        },
        
        renderSketch() {
            this.clearSketchFabricObjects();
            var scale = this.canvasScale;
            var cx = canvas.width / 2;
            var cy = canvas.height / 2;
            var floorY = cy + (this.dimensions.height * scale) / 2 - 40;
            
            for (var i = 0; i < this.sketchPoints.length; i++) {
                var p = this.sketchPoints[i];
                var px = cx + p.x * scale;
                var py = floorY - p.y * scale;
                
                var circle = new fabric.Circle({
                    left: px, top: py, radius: 6,
                    fill: '#06b6d4', stroke: '#ffffff', strokeWidth: 2,
                    originX: 'center', originY: 'center',
                    selectable: false, evented: false
                });
                canvas.add(circle);
                this.sketchFabricObjects.push(circle);
                
                if (i < this.sketchPoints.length - 1) {
                    var p2 = this.sketchPoints[i + 1];
                    var px2 = cx + p2.x * scale;
                    var py2 = floorY - p2.y * scale;
                    var line = new fabric.Line([px, py, px2, py2], {
                        stroke: '#22d3ee', strokeWidth: 2, strokeDashArray: [6, 4],
                        selectable: false, evented: false
                    });
                    canvas.add(line);
                    this.sketchFabricObjects.push(line);
                    
                    var dx = p2.x - p.x;
                    var dy = p2.y - p.y;
                    var dist = Math.sqrt(dx * dx + dy * dy);
                    var midX = (px + px2) / 2;
                    var midY = (py + py2) / 2;
                    var label = new fabric.Text(dist.toFixed(2) + 'm', {
                        left: midX, top: midY - 15, fontSize: 11,
                        fontFamily: 'monospace', fontWeight: 'bold',
                        fill: '#22d3ee', backgroundColor: '#070a13',
                        padding: 3, originX: 'center', originY: 'center',
                        selectable: false, evented: false
                    });
                    canvas.add(label);
                    this.sketchFabricObjects.push(label);
                }
            }
            
            canvas.requestRenderAll();
        },
        
        clearSketchFabricObjects() {
            this.sketchFabricObjects.forEach(function(obj) { canvas.remove(obj); });
            this.sketchFabricObjects = [];
            if (this._previewLine) { canvas.remove(this._previewLine); this._previewLine = null; }
            if (this._previewLabel) { canvas.remove(this._previewLabel); this._previewLabel = null; }
        },
        
        undoSketchPoint() {
            if (this.sketchPoints.length === 0) return;
            this.sketchPoints.pop();
            this.renderSketch();
        },
        
        finishSketch() {
            if (this.sketchPoints.length >= 2) {
                this.generateFromSketch();
            }
            this.toggleSketchMode();
        },
        
        clearSketch() {
            this.sketchPoints = [];
            this.clearSketchFabricObjects();
        },
        
        generateTrussLine(x1, y1, z1, x2, y2, z2, rotX, rotY, rotZ, q30s, virtualStock, cuboPeca) {
            const dx = x2 - x1;
            const dy = y2 - y1;
            const dz = z2 - z1;
            const totalDist = Math.sqrt(dx*dx + dy*dy + dz*dz);
            if (totalDist < 0.01) return;
            
            const allCombs = [];
            const findCombs = (target, index, currentComb) => {
                if (Math.abs(target) < 0.01) {
                    allCombs.push([...currentComb]);
                    return;
                }
                if (target < -0.01 || index >= q30s.length) return;
                const pLen = q30s[index].comprimento;
                if (pLen <= target + 0.01) {
                    currentComb.push(q30s[index]);
                    findCombs(target - pLen, index, currentComb);
                    currentComb.pop();
                }
                findCombs(target, index + 1, currentComb);
            };
            findCombs(totalDist, 0, []);
            
            let bestComb = null;
            if (allCombs.length > 0) {
                let bestScore = -Infinity;
                allCombs.forEach(comb => {
                    const usage = {};
                    comb.forEach(p => { usage[p.id] = (usage[p.id] || 0) + 1; });
                    let outOfStockPenalty = 0;
                    Object.keys(usage).forEach(pid => {
                        const needed = usage[pid];
                        const available = virtualStock[pid] || 0;
                        if (needed > available) outOfStockPenalty += (needed - available) * 100;
                    });
                    const score = -outOfStockPenalty - comb.length;
                    if (score > bestScore) { bestScore = score; bestComb = comb; }
                });
            }
            
            if (!bestComb) {
                bestComb = [];
                let remaining = totalDist;
                while (remaining > 0.05) {
                    // Find the longest Q30 that fits within remaining space (no overshoot)
                    let fitPeca = null;
                    for (let qi = 0; qi < q30s.length; qi++) {
                        if (q30s[qi].comprimento <= remaining + 0.01) {
                            fitPeca = q30s[qi];
                            break;
                        }
                    }
                    if (!fitPeca) break; // no Q30 fits, stop (leave gap)
                    bestComb.push(fitPeca);
                    remaining -= fitPeca.comprimento;
                }
            }
            
            let curX = x1;
            let curY = y1;
            let curZ = z1;
            const dirX = dx / totalDist || 0;
            const dirY = dy / totalDist || 0;
            const dirZ = dz / totalDist || 0;
            
            bestComb.forEach((fitPeca, idx) => {
                const pLen = fitPeca.comprimento;
                const cX = curX + (pLen / 2) * dirX;
                const cY = curY + (pLen / 2) * dirY;
                const cZ = curZ + (pLen / 2) * dirZ;
                
                this.components3D.push({
                    uid: this._uid(),
                    catalogId: fitPeca.id,
                    codigo: fitPeca.codigo,
                    tipo: fitPeca.tipo,
                    length: fitPeca.comprimento,
                    nome: fitPeca.nome,
                    peso: fitPeca.peso,
                    x: cX, y: cY, z: cZ,
                    rotationX: rotX, rotationY: rotY, rotationZ: rotZ
                });
                
                if (virtualStock[fitPeca.id]) virtualStock[fitPeca.id]--;
                curX += pLen * dirX;
                curY += pLen * dirY;
                curZ += pLen * dirZ;

                // Cubo connector at the joint between this segment and the next
                if (idx < bestComb.length - 1 && cuboPeca) {
                    this.components3D.push({
                        uid: this._uid(),
                        catalogId: cuboPeca.id,
                        codigo: cuboPeca.codigo,
                        tipo: cuboPeca.tipo,
                        length: cuboPeca.comprimento || 0,
                        nome: cuboPeca.nome,
                        peso: cuboPeca.peso,
                        x: curX, y: curY, z: curZ,
                        rotationX: rotX, rotationY: rotY, rotationZ: rotZ
                    });
                }
            });
        },
        
        generateFromSketch() {
            if (this.sketchPoints.length < 2) {
                alert("Adicione pelo menos 2 pontos no Sketch para gerar a estrutura.");
                return;
            }
            
            const q30s = this.catalog.filter(p => p.tipo === 'Q30').sort((a,b) => b.comprimento - a.comprimento);
            const cuboPeca = this.catalog.find(p => p.tipo === 'cubo');

            if (q30s.length === 0 || !cuboPeca) {
                alert("Catálogo incompleto! Vá em Peças & Estoque e cadastre barras Q30 e Cubo no estoque.");
                return;
            }
            
            const virtualStock = {};
            q30s.forEach(p => { virtualStock[p.id] = p.estoque; });
            this.components3D.forEach(c => {
                if (c.tipo === 'Q30' && virtualStock[c.catalogId] !== undefined) {
                    virtualStock[c.catalogId]--;
                }
            });

            const cuboHalf = 0.15;
            
            // Front frame
            for (let i = 0; i < this.sketchPoints.length - 1; i++) {
                const p1 = this.sketchPoints[i];
                const p2 = this.sketchPoints[i + 1];
                const dx = p2.x - p1.x;
                const dy = p2.y - p1.y;
                const totalDist = Math.sqrt(dx * dx + dy * dy);
                if (totalDist < 0.01) continue;
                const rotZ = Math.atan2(dy, dx) * (180 / Math.PI);
                const ratio = cuboHalf / totalDist;
                const sx = p1.x + dx * ratio;
                const sy = p1.y + dy * ratio;
                const ex = p2.x - dx * ratio;
                const ey = p2.y - dy * ratio;
                
                this.generateTrussLine(sx, sy, 0, ex, ey, 0, 0, 0, rotZ, q30s, virtualStock, cuboPeca);
                
                // Cubo connector at intermediate joints only
                if (i > 0) {
                    this.components3D.push({
                        uid: this._uid(),
                        catalogId: cuboPeca.id, codigo: cuboPeca.codigo, tipo: cuboPeca.tipo,
                        length: cuboPeca.comprimento || 0, nome: cuboPeca.nome, peso: cuboPeca.peso,
                        x: p1.x, y: p1.y, z: 0,
                        rotationX: 0, rotationY: 0, rotationZ: 0
                    });
                }
            }
            
            // Cubo at last point (extremity) if not at ground level
            const lastPt = this.sketchPoints[this.sketchPoints.length - 1];
            if (this.sketchPoints.length > 2 && lastPt.y > 0.1) {
                this.components3D.push({
                    uid: this._uid(),
                    catalogId: cuboPeca.id, codigo: cuboPeca.codigo, tipo: cuboPeca.tipo,
                    length: cuboPeca.comprimento || 0, nome: cuboPeca.nome, peso: cuboPeca.peso,
                    x: lastPt.x, y: lastPt.y, z: 0,
                    rotationX: 0, rotationY: 0, rotationZ: 0
                });
            }
            
            // Sapatas/Pé de Galinha não são mais geradas: a estrutura termina nas colunas
            // verticais (apenas Q30 + conectores CUBO/GREPO nas juntas).
            // Sketch gera apenas o frame plano (sem duplicar em profundidade).

            this.clearSketch();
            this.syncCanvasFrom3D();
            this.calculateQuantitative();
            this.pushHistory();

            if (window.showToast) {
                showToast('green', 'Estrutura Adicionada', 'Estrutura gerada a partir do Sketch. Continue desenhando ou finalize.');
            }
        },
        
        calculateQuantitative() {
            const counts = {};
            let weight = 0;
            let linearMeters = 0;
            let pm5LinearMeters = 0;
            let squareMeters = 0;

            this.components3D.forEach(comp => {
                if (comp.tipo === 'Q30' || comp.tipo === 'plana' || comp.tipo === 'braco' || comp.tipo === 'cubo' || comp.tipo === 'grepo' || comp.tipo === 'sapata') {
                    linearMeters += comp.length || 0.30;
                }
                if (comp.tipo === 'montante' || comp.tipo === 'travessa') {
                    pm5LinearMeters += comp.length || 0;
                }
                if (comp.tipo === 'lona' || comp.tipo === 'painel_led') {
                    squareMeters += (comp.width || 0) * (comp.height || 0);
                }

                if (comp.excludeFromQuantitative) return;

                const source = comp.quantOverride || comp;
                const code = source.codigo;
                if (!counts[code]) {
                    counts[code] = {
                        codigo: code,
                        nome: source.nome,
                        tipo: source.tipo,
                        peso: source.peso,
                        projeto: 0,
                        estoque: 0,
                        diferenca: 0
                    };
                }
                counts[code].projeto += 1;
                weight += source.peso;
            });

            this.catalog.forEach(catItem => {
                if (counts[catItem.codigo]) {
                    counts[catItem.codigo].estoque = catItem.estoque;
                    counts[catItem.codigo].diferenca = Math.max(0, counts[catItem.codigo].projeto - catItem.estoque);
                }
            });

            this.quantitative = Object.values(counts);
            this.totalWeight = weight;
            this.totalLinearMeters = linearMeters;
            this.totalPm5LinearMeters = pm5LinearMeters;
            this.totalSquareMeters = squareMeters;
        },

        
        saveProject() {
            this.saving = true;

            // Reaproveitamento de projetos: se o nome foi alterado em relação
            // ao projeto salvo/carregado, grava como um NOVO projeto (cópia),
            // preservando o original. Mesmo nome → atualiza o existente.
            const renamed = this.currentProjectId && this.savedProjectName
                && this.projectName.trim() !== this.savedProjectName.trim();
            const saveAsCopy = !this.currentProjectId || renamed;

            const payload = {
                id: saveAsCopy ? null : this.currentProjectId,
                name: this.projectName,
                width: this.dimensions.width,
                height: this.dimensions.height,
                length: this.dimensions.length,
                scale: this.canvasScale,
                components: JSON.stringify(this.components3D)
            };

            fetch('<?= url("/api/projects") ?>', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            })
            .then(r => r.json())
            .then(res => {
                this.saving = false;
                if (res.ok) {
                    const previousName = this.savedProjectName;
                    this.currentProjectId = res.data.id;
                    this.savedProjectName = this.projectName;
                    if (window.closeModal) closeModal();
                    if (window.showToast) {
                        if (renamed) {
                            showToast('cyan', 'Cópia Criada', `Novo projeto "${this.projectName}" criado a partir de "${previousName}". O original foi preservado.`);
                        } else {
                            showToast('green', 'Projeto Salvo', `O projeto "${this.projectName}" foi gravado.`);
                        }
                    }
                } else {
                    alert('Erro ao salvar projeto: ' + res.error);
                }
            })
            .catch(err => {
                this.saving = false;
                console.error(err);
                alert('Erro de conexão ao salvar.');
            });
        },
        
        openLoadModal() {
            fetch('<?= url("/api/projects") ?>')
                .then(r => r.json())
                .then(res => {
                    if (res.ok) {
                        this.savedProjects = res.data;
                        if (window.openModal) {
                            openModal('modal-load-projects');
                        }
                    }
                })
                .catch(err => console.error(err));
        },
        
        loadProject(proj) {
            this.currentProjectId = proj.id;
            this.projectName = proj.name;
            this.savedProjectName = proj.name;
            this.dimensions.width = parseFloat(proj.width);
            this.dimensions.height = parseFloat(proj.height);
            this.dimensions.length = parseFloat(proj.length);
            this.canvasScale = parseInt(proj.scale);
            
            const componentsList = JSON.parse(proj.components);
            const isOldFormat = componentsList.length > 0 && componentsList[0].z === undefined;
            
            if (isOldFormat) {
                this.components3D = [];
                let minY = -9999;
                componentsList.forEach(c => {
                    if (c.y > minY) minY = c.y;
                });
                
                const cx = canvas.width / 2;
                const cy = canvas.height / 2;
                const scale = this.canvasScale;
                const depth = this.dimensions.width;
                
                componentsList.forEach(c => {
                    const catalogItem = this.catalog.find(i => i.id == c.id || i.codigo == c.codigo);
                    if (catalogItem) {
                        const xM = (c.x - cx) / scale;
                        const yM = (minY - c.y) / scale;
                        
                        const newComp = {
                            uid: this._uid(),
                            catalogId: catalogItem.id,
                            codigo: catalogItem.codigo,
                            tipo: catalogItem.tipo,
                            length: catalogItem.comprimento || 0,
                            nome: catalogItem.nome,
                            peso: catalogItem.peso,
                            x: xM,
                            y: yM,
                            z: 0,
                            rotationX: 0,
                            rotationY: 0,
                            rotationZ: c.rotation || 0
                        };
                        this.components3D.push(newComp);
                        
                        if (depth > 0.5) {
                            const backComp = Object.assign({}, newComp, {
                                uid: this._uid(),
                                z: -depth
                            });
                            this.components3D.push(backComp);
                            
                            if (catalogItem.tipo === 'sleeve') {
                                this.addSideConnectingBeams(xM, yM);
                            }
                        }
                    }
                });
            } else {
                this.components3D = componentsList;
                // Os uids salvos vieram de outra sessão e _uidCounter zera a cada
                // recarga da página: sem reposicionar o contador, a próxima peça
                // adicionada repetiria um uid carregado (comp_1, comp_2...) e os
                // handlers por uid (mover/rotacionar/deletar) passariam a afetar
                // duas peças ao mesmo tempo.
                this.components3D.forEach(c => {
                    if (!c.uid) { c.uid = this._uid(); return; }
                    const n = parseInt(String(c.uid).replace('comp_', ''), 10);
                    if (!isNaN(n) && n > this._uidCounter) this._uidCounter = n;
                });
            }

            this.switchView('2d_frontal');
            this.resetHistory();
            if (window.closeModal) closeModal();
            if (window.showToast) {
                showToast('cyan', 'Projeto Carregado', `"${proj.name}" carregado com sucesso.`);
            }
        },
        
        deleteProject(id) {
            if (confirm("Deseja realmente excluir este projeto permanentemente?")) {
                fetch(`<?= url("/api/projects/") ?>${id}`, {
                    method: 'DELETE'
                })
                .then(r => r.json())
                .then(res => {
                    if (res.ok) {
                        this.savedProjects = this.savedProjects.filter(p => p.id !== id);
                        if (id === this.currentProjectId) {
                            this.currentProjectId = null;
                            this.projectName = 'Projeto Sem Nome';
                            this.savedProjectName = null;
                        }
                        if (window.showToast) {
                            showToast('red', 'Projeto Excluído', 'Projeto removido com sucesso.');
                        }
                    }
                })
                .catch(err => console.error(err));
            }
        },
        
        async exportToPDF() {
            if (this.components3D.length === 0) {
                alert("O canvas está vazio! Adicione peças antes de exportar.");
                return;
            }

            const { jsPDF } = window.jspdf;
            const doc = new jsPDF('l', 'mm', 'a4');

            // ── Captura as 5 vistas 2D como imagens ──
            // Salva o estado atual do canvas para restaurar ao final.
            const originalViewMode = this.viewMode;
            const originalVpt = canvas.viewportTransform ? [...canvas.viewportTransform] : null;
            const originalZoom = canvas.getZoom();

            const viewConfigs = [
                { mode: '2d_frontal',     title: 'Vista Frontal' },
                { mode: '2d_fundo',       title: 'Vista de Fundo' },
                { mode: '2d_superior',    title: 'Vista Superior (Planta)' },
                { mode: '2d_lateral',     title: 'Vista Lateral Esquerda' },
                { mode: '2d_lateral_dir', title: 'Vista Lateral Direita' }
            ];

            const captureView = (mode) => {
                this.viewMode = mode;
                this.resetZoom();
                this.syncCanvasFrom3D();
                const gridLines = canvas.getObjects().filter(o => o.isGridLine);
                gridLines.forEach(l => l.set({ opacity: 0 }));
                canvas.renderAll();
                const data = canvas.toDataURL({ format: 'png', quality: 1.0 });
                gridLines.forEach(l => l.set({ opacity: 1.0 }));
                canvas.renderAll();
                return data;
            };

            // Espera N frames de requestAnimationFrame — o loop de render 3D
            // (animate(), definido em init3DView) só desenha a nova posição de
            // câmera no PRÓXIMO frame; sem esperar, o toDataURL() capturaria o
            // frame antigo (câmera anterior) ou um canvas ainda em branco.
            const waitFrames = (n) => new Promise(resolve => {
                let count = 0;
                const step = () => { count++; if (count >= n) resolve(); else requestAnimationFrame(step); };
                requestAnimationFrame(step);
            });

            const isoTitles = ['Isométrica 1 (↗)', 'Isométrica 2 (↖)', 'Isométrica 3 (↘)', 'Isométrica 4 (↙)'];
            const captureIsoViews = async () => {
                this.viewMode = '3d';
                this.init3DView();
                // init3DView monta a cena dentro de um setTimeout(0) — espera o
                // renderer existir antes de tentar reposicionar a câmera.
                let waited = 0;
                while (!this._threeRenderer && waited < 2000) {
                    await new Promise(r => setTimeout(r, 20));
                    waited += 20;
                }
                if (!this._threeRenderer) return [];
                const images = [];
                for (let i = 0; i < 4; i++) {
                    this.setIsometricView(i);
                    await waitFrames(3);
                    images.push({ title: isoTitles[i], data: this._threeRenderer.domElement.toDataURL('image/png', 1.0) });
                }
                return images;
            };

            let viewImages = [];
            let isoImages = [];
            try {
                viewImages = viewConfigs.map(v => ({ ...v, data: captureView(v.mode) }));
                isoImages = await captureIsoViews();
            } catch (err) {
                console.error('Falha ao capturar as vistas para o PDF:', err);
            } finally {
                // Restaura o estado original do canvas
                this.viewMode = originalViewMode;
                if (originalViewMode === '3d') {
                    this.init3DView();
                } else {
                    this.syncCanvasFrom3D();
                    if (originalVpt) canvas.setViewportTransform(originalVpt);
                    canvas.setZoom(originalZoom);
                    this.canvasZoom = originalZoom;
                }
            }

            if (viewImages.length === 0) {
                alert("Não foi possível gerar as imagens das vistas para o PDF. Tente novamente.");
                return;
            }

            const canvasAspect = canvas.height / canvas.width;

            // ── Página 1: Cabeçalho + Resumo + Quantitativo ──
            doc.setFont("helvetica", "bold");
            doc.setFontSize(18);
            doc.setTextColor(11, 15, 25); // bg-darkest tone
            doc.text("ProFoxTruss — Relatório de Estrutura & Quantitativos", 15, 15);

            doc.setFontSize(11);
            doc.setFont("helvetica", "normal");
            doc.setTextColor(100, 100, 100);
            doc.text(`Projeto: ${this.projectName} | Dimensões: ${this.dimensions.length}m x ${this.dimensions.height}m x ${this.dimensions.width}m`, 15, 21);
            doc.text(`Data: ${new Date().toLocaleString('pt-BR')}`, 15, 26);

            // Resumo Geral e Ferragens lado a lado
            const { parafusos, arruelas, porcas } = this.calculateHardwareCounts();
            let sumY = 38;
            doc.setFontSize(13);
            doc.setFont("helvetica", "bold");
            doc.setTextColor(11, 15, 25);
            doc.text("Resumo Geral", 15, sumY);
            sumY += 8;
            doc.setFontSize(10);
            doc.setFont("helvetica", "normal");
            doc.setTextColor(60, 65, 75);
            const summaryLines = [
                `Peso Total da Estrutura: ${this.totalWeight.toFixed(1)} kg`,
                `Metros Lineares (Box Truss): ${this.totalLinearMeters.toFixed(2)} m`
            ];
            if (this.totalPm5LinearMeters > 0) {
                summaryLines.push(`Metros Lineares (Perfil PM5): ${this.totalPm5LinearMeters.toFixed(2)} m`);
            }
            if (this.totalSquareMeters > 0) {
                summaryLines.push(`Metros Quadrados (Lona/Painel LED): ${this.totalSquareMeters.toFixed(2)} m²`);
            }
            summaryLines.forEach(line => { doc.text(line, 15, sumY); sumY += 6; });

            let hwY = 38;
            doc.setFontSize(13);
            doc.setFont("helvetica", "bold");
            doc.setTextColor(11, 15, 25);
            doc.text("Ferragens (demonstrativo)", 140, hwY);
            hwY += 8;
            doc.setFontSize(10);
            doc.setFont("helvetica", "normal");
            doc.setTextColor(60, 65, 75);
            [
                `Parafusos: ${parafusos}`,
                `Arruelas: ${arruelas}`,
                `Porcas: ${porcas}`
            ].forEach(line => { doc.text(line, 140, hwY); hwY += 6; });

            // Materials table header
            const tableStartY = Math.max(sumY, hwY) + 8;
            doc.setFontSize(14);
            doc.setFont("helvetica", "bold");
            doc.setTextColor(11, 15, 25);
            doc.text("Quantitativo de Materiais & Status de Estoque", 15, tableStartY);

            // Prepare Table Data
            const rows = [];
            this.quantitative.forEach(item => {
                const statusStr = item.diferenca <= 0 ? "Suficiente" : `Falta ${item.diferenca} pç(s)`;
                rows.push([
                    item.nome,
                    item.codigo,
                    item.projeto.toString(),
                    item.estoque.toString(),
                    statusStr,
                    `${(item.projeto * item.peso).toFixed(1)} kg`
                ]);
            });

            doc.autoTable({
                startY: tableStartY + 5,
                head: [["Componente", "Código", "Necessário", "Em Estoque", "Status Estoque", "Peso Total"]],
                body: rows,
                theme: 'striped',
                headStyles: { fillColor: [6, 182, 212] }, // neon-cyan hex matching
                foot: [["Total", "", "", "", "", `${this.totalWeight.toFixed(1)} kg`]],
                footStyles: { fillColor: [11, 15, 25], textColor: [255, 255, 255] }
            });

            // ── Página 2: vistas 2D (Frontal, Fundo, Superior, Lateral Esq/Dir) ──
            doc.addPage();
            doc.setFontSize(14);
            doc.setFont("helvetica", "bold");
            doc.setTextColor(11, 15, 25);
            doc.text("Vistas 2D", 15, 15);

            const cellW = 85, cellGap = 6, cellH = cellW * canvasAspect, rowGap = 12;
            const cols = [15, 15 + cellW + cellGap, 15 + (cellW + cellGap) * 2];
            const rows2D = [22, 22 + cellH + rowGap];
            const positions = [
                { x: cols[0], y: rows2D[0] }, { x: cols[1], y: rows2D[0] }, { x: cols[2], y: rows2D[0] },
                { x: cols[0], y: rows2D[1] }, { x: cols[1], y: rows2D[1] }
            ];
            viewImages.forEach((view, i) => {
                const pos = positions[i];
                doc.setFontSize(11);
                doc.setFont("helvetica", "bold");
                doc.setTextColor(11, 15, 25);
                doc.text(view.title, pos.x, pos.y - 2);
                doc.setDrawColor(220, 225, 230);
                doc.setFillColor(7, 10, 19);
                doc.rect(pos.x, pos.y, cellW, cellH, "F");
                doc.addImage(view.data, 'PNG', pos.x, pos.y, cellW, cellH);
            });

            // ── Página 3: Vistas Isométricas 3D ──
            if (isoImages.length > 0) {
                doc.addPage();
                doc.setFontSize(14);
                doc.setFont("helvetica", "bold");
                doc.setTextColor(11, 15, 25);
                doc.text("Vistas Isométricas 3D", 15, 15);

                // canvas3DContainer é fixo em 750x500 (init3DView) — mesma proporção
                // usada para calcular a altura da célula a partir da largura.
                const isoAspect = 500 / 750;
                const isoCellW = 120, isoCellGap = 10, isoCellH = isoCellW * isoAspect, isoRowGap = 14;
                const isoCols = [15, 15 + isoCellW + isoCellGap];
                const isoRows = [22, 22 + isoCellH + isoRowGap];
                const isoPositions = [
                    { x: isoCols[0], y: isoRows[0] }, { x: isoCols[1], y: isoRows[0] },
                    { x: isoCols[0], y: isoRows[1] }, { x: isoCols[1], y: isoRows[1] }
                ];
                isoImages.forEach((view, i) => {
                    const pos = isoPositions[i];
                    doc.setFontSize(11);
                    doc.setFont("helvetica", "bold");
                    doc.setTextColor(11, 15, 25);
                    doc.text(view.title, pos.x, pos.y - 2);
                    doc.setDrawColor(220, 225, 230);
                    doc.setFillColor(7, 10, 19);
                    doc.rect(pos.x, pos.y, isoCellW, isoCellH, "F");
                    doc.addImage(view.data, 'PNG', pos.x, pos.y, isoCellW, isoCellH);
                });
            }

            doc.save(`ProFoxTruss_${this.projectName.replace(/\s+/g, '_')}.pdf`);
        },
        
        exportToExcel() {
            if (this.quantitative.length === 0) {
                alert("Nenhum componente no projeto para exportar.");
                return;
            }
            
            const wb = XLSX.utils.book_new();
            
            // Prepare rows array
            const data = [
                ["ProFoxTruss — Relatório de Estrutura"],
                ["Projeto:", this.projectName],
                ["Dimensões:", `${this.dimensions.length}m (Comp) x ${this.dimensions.height}m (Alt) x ${this.dimensions.width}m (Larg)`],
                ["Data de Exportação:", new Date().toLocaleString('pt-BR')],
                [],
                ["Componente", "Código", "Quantidade Projetada", "Estoque Disponível", "Falta no Estoque", "Peso Unitário (kg)", "Peso Total (kg)"]
            ];
            
            this.quantitative.forEach(item => {
                data.push([
                    item.nome,
                    item.codigo,
                    item.projeto,
                    item.estoque,
                    item.diferenca,
                    item.peso,
                    (item.projeto * item.peso)
                ]);
            });
            
            data.push([]);
            data.push(["Peso Total da Estrutura (kg):", "", "", "", "", "", this.totalWeight]);
            data.push(["Metros Lineares (Box Truss):", "", "", "", "", "", this.totalLinearMeters]);
            if (this.totalPm5LinearMeters > 0) {
                data.push(["Metros Lineares (Perfil PM5):", "", "", "", "", "", this.totalPm5LinearMeters]);
            }
            if (this.totalSquareMeters > 0) {
                data.push(["Metros Quadrados (Lona/Painel LED):", "", "", "", "", "", this.totalSquareMeters]);
            }

            const ws = XLSX.utils.aoa_to_sheet(data);
            XLSX.utils.book_append_sheet(wb, ws, "Quantitativo");
            
            XLSX.writeFile(wb, `ProFoxTruss_${this.projectName.replace(/\s+/g, '_')}.xlsx`);
        },

        // Raio-X: deixa toda peça estrutural (qualquer THREE.Mesh na cena)
        // semi-transparente, pra enxergar o que está atrás sem precisar girar a
        // câmera — resolve o caso de peças escondidas atrás de outras em
        // QUALQUER ângulo, ao contrário de só trocar o ponto de vista. Sprites
        // (labels) e Line (linhas de cota) não são Mesh, então continuam 100%
        // legíveis mesmo no modo raio-X.
        _applyXray(active) {
            if (!this._threeScene) return;
            this._threeScene.traverse(obj => {
                if (!obj.isMesh || !obj.material) return;
                const mats = Array.isArray(obj.material) ? obj.material : [obj.material];
                mats.forEach(m => {
                    if (active) {
                        if (m._xrayOrigOpacity === undefined) {
                            m._xrayOrigOpacity = m.opacity;
                            m._xrayOrigTransparent = m.transparent;
                        }
                        m.transparent = true;
                        m.opacity = 0.35;
                    } else if (m._xrayOrigOpacity !== undefined) {
                        m.opacity = m._xrayOrigOpacity;
                        m.transparent = m._xrayOrigTransparent;
                        delete m._xrayOrigOpacity;
                        delete m._xrayOrigTransparent;
                    }
                });
            });
        },

        toggleXray() {
            this.xrayMode = !this.xrayMode;
            if (this.viewMode === '3d') {
                this._applyXray(this.xrayMode);
            } else {
                // Nos modos 2D não há cena Three.js persistente pra alterar — o
                // canvas Fabric.js é reconstruído do zero (syncCanvasFrom3D), e a
                // opacidade do raio-X é aplicada ali (ver os 3 blocos de
                // finalização de objeto: Frontal/Fundo, Superior, Lateral).
                this.syncCanvasFrom3D();
            }
        },

        // Vistas isométricas rápidas: pula a câmera pra um dos 4 cantos
        // diagonais da estrutura em vez de depender só do arrasto manual do
        // OrbitControls — útil pra contornar uma peça que está bloqueando a
        // visão sem precisar "achar" o ângulo certo na mão. A distância se
        // adapta ao tamanho real do projeto (diagonal da caixa delimitadora),
        // então funciona tanto numa estrutura pequena quanto numa grande.
        setIsometricView(index) {
            if (!this._threeCamera || !this._threeControls) return;
            const length = this.dimensions.length || 3;
            const height = this.dimensions.height || 3;
            const depth = this.dimensions.width || 3;
            // Mesmo centro aproximado usado no resto da calculadora: X centrado
            // em 0, Y de 0 até height, Z de 0 até -depth.
            const target = { x: 0, y: height / 2, z: -depth / 2 };
            const diag = Math.sqrt(length * length + height * height + depth * depth) || 5;
            const dist = Math.max(diag * 0.85, 3);
            const presets = [
                { az: 45, el: 32 },
                { az: 135, el: 32 },
                { az: 225, el: 32 },
                { az: 315, el: 32 }
            ];
            const p = presets[index] || presets[0];
            const azRad = p.az * Math.PI / 180;
            const elRad = p.el * Math.PI / 180;
            this._threeCamera.position.set(
                target.x + dist * Math.cos(elRad) * Math.sin(azRad),
                target.y + dist * Math.sin(elRad),
                target.z + dist * Math.cos(elRad) * Math.cos(azRad)
            );
            this._threeControls.target.set(target.x, target.y, target.z);
            this._threeControls.update();
        },

        init3DView() {
            setTimeout(() => {
                const container = document.getElementById('canvas3DContainer');
                if (!container) return;
                
                // Clear container first
                container.innerHTML = '';
                
                // Initialize Three.js Scene, Camera, Renderer
                const width = 750;
                const height = 500;
                const scene = new THREE.Scene();
                scene.background = new THREE.Color('#070a13');
                
                // Fog to look premium
                scene.fog = new THREE.FogExp2('#070a13', 0.03);
                
                const camera = new THREE.PerspectiveCamera(40, width / height, 0.1, 1000);
                camera.position.set(0, 3.5, 9); // Position camera slightly up and back
                
                const renderer = new THREE.WebGLRenderer({ antialias: true, preserveDrawingBuffer: true });
                renderer.setSize(width, height);
                renderer.shadowMap.enabled = true;
                renderer.shadowMap.type = THREE.PCFSoftShadowMap;
                container.appendChild(renderer.domElement);

                // Mantém referências para captura de imagem (ex: exportToPDF)
                this._threeRenderer = renderer;
                this._threeScene = scene;
                this._threeCamera = camera;
                
                // OrbitControls
                const controls = new THREE.OrbitControls(camera, renderer.domElement);
                controls.enableDamping = true;
                controls.dampingFactor = 0.05;
                controls.maxPolarAngle = Math.PI / 2 + 0.05; // Don't go below ground
                // Referência guardada pra setIsometricView() poder reposicionar a
                // câmera/alvo de fora deste closure sem reconstruir a cena inteira.
                this._threeControls = controls;

                // Ground Grid / Stage Floor
                const gridHelper = new THREE.GridHelper(30, 30, '#06b6d4', '#1e293b');
                gridHelper.position.y = 0.001; // slightly above 0 to avoid z-fighting
                scene.add(gridHelper);
                
                // Stage floor surface
                const floorGeo = new THREE.PlaneGeometry(30, 30);
                const floorMat = new THREE.MeshStandardMaterial({ 
                    color: '#030712', 
                    roughness: 0.8,
                    metalness: 0.1
                });
                const floor = new THREE.Mesh(floorGeo, floorMat);
                floor.rotation.x = -Math.PI / 2;
                floor.receiveShadow = true;
                scene.add(floor);
                
                // Lights
                // HemisphereLight creates a natural gradient from sky to ground
                const hemiLight = new THREE.HemisphereLight('#ffffff', '#1e293b', 0.65);
                scene.add(hemiLight);
                
                // Main front directional key light
                const dirLight1 = new THREE.DirectionalLight('#ffffff', 1.2);
                dirLight1.position.set(5, 10, 7);
                dirLight1.castShadow = true;
                dirLight1.shadow.mapSize.width = 1024;
                dirLight1.shadow.mapSize.height = 1024;
                scene.add(dirLight1);
                
                // Back/side directional fill light to brighten shadows
                const dirLight2 = new THREE.DirectionalLight('#e2e8f0', 0.65);
                dirLight2.position.set(-5, 6, -5);
                scene.add(dirLight2);
                
                // Accent neon purple point light for premium look
                const pointLight = new THREE.PointLight('#c084fc', 1.5, 18);
                pointLight.position.set(0, 5, -3);
                scene.add(pointLight);
                
                // Silver/aluminum truss material (moderated metalness to avoid reflecting pitch black environment)
                const trussMat = new THREE.MeshStandardMaterial({
                    color: '#e2e8f0',
                    metalness: 0.55,
                    roughness: 0.2
                });
                
                // Slate/iron base material (lighter than pitch black)
                const baseMat = new THREE.MeshStandardMaterial({
                    color: '#334155',
                    metalness: 0.45,
                    roughness: 0.35
                });
                
                // Vibrant purple sleeve material
                const sleeveMat = new THREE.MeshStandardMaterial({
                    color: '#a855f7',
                    metalness: 0.5,
                    roughness: 0.25
                });

                // Brushed steel material for sapata base plates
                const sapataMat = new THREE.MeshStandardMaterial({
                    color: '#b8c5d6',
                    metalness: 0.92,
                    roughness: 0.38
                });
                
                // Helper to create a Q30 Box Truss section 3D model
                const createTrussSection = (length) => {
                    const group = new THREE.Group();
                    const size = 0.3; // 30cm width/height
                    const rMain = 0.016; // main tube radius (32mm diameter)
                    const rBrace = 0.007; // brace tube radius (14mm diameter)

                    // End cap material — subtle bright ring to show segment boundaries
                    const endCapMat = new THREE.MeshStandardMaterial({
                        color: '#94a3b8',
                        metalness: 0.6,
                        roughness: 0.15,
                        transparent: true,
                        opacity: 0.5
                    });
                    
                    // End cap plates at each end of the segment
                    const capGeo = new THREE.BoxGeometry(size, 0.005, size);
                    const cap1 = new THREE.Mesh(capGeo, endCapMat);
                    cap1.position.set(0, -length/2, 0);
                    group.add(cap1);
                    const cap2 = new THREE.Mesh(capGeo, endCapMat);
                    cap2.position.set(0, length/2, 0);
                    group.add(cap2);

                    // 4 Main corner tubes
                    const tubeGeo = new THREE.CylinderGeometry(rMain, rMain, length, 8);
                    
                    // Top-Left
                    const t1 = new THREE.Mesh(tubeGeo, trussMat);
                    t1.position.set(-size/2, 0, -size/2);
                    t1.castShadow = true;
                    t1.receiveShadow = true;
                    group.add(t1);
                    
                    // Top-Right
                    const t2 = new THREE.Mesh(tubeGeo, trussMat);
                    t2.position.set(size/2, 0, -size/2);
                    t2.castShadow = true;
                    t2.receiveShadow = true;
                    group.add(t2);
                    
                    // Bottom-Left
                    const t3 = new THREE.Mesh(tubeGeo, trussMat);
                    t3.position.set(-size/2, 0, size/2);
                    t3.castShadow = true;
                    t3.receiveShadow = true;
                    group.add(t3);
                    
                    // Bottom-Right
                    const t4 = new THREE.Mesh(tubeGeo, trussMat);
                    t4.position.set(size/2, 0, size/2);
                    t4.castShadow = true;
                    t4.receiveShadow = true;
                    group.add(t4);
                    
                    // Diagonal/horizontal bracing braces
                    const steps = Math.max(1, Math.round(length / 0.4)); // brace every ~40cm
                    const stepSize = length / steps;
                    
                    for (let s = 0; s <= steps; s++) {
                        const yPos = -length/2 + s * stepSize;
                        
                        // Horizontal braces
                        const horizGeo = new THREE.CylinderGeometry(rBrace, rBrace, size, 6);
                        
                        // Face 1 (Z = -size/2)
                        const h1 = new THREE.Mesh(horizGeo, trussMat);
                        h1.rotation.z = Math.PI / 2;
                        h1.position.set(0, yPos, -size/2);
                        h1.castShadow = true;
                        group.add(h1);
                        
                        // Face 2 (Z = size/2)
                        const h2 = new THREE.Mesh(horizGeo, trussMat);
                        h2.rotation.z = Math.PI / 2;
                        h2.position.set(0, yPos, size/2);
                        h2.castShadow = true;
                        group.add(h2);
                        
                        // Face 3 (X = -size/2)
                        const h3 = new THREE.Mesh(horizGeo, trussMat);
                        h3.rotation.x = Math.PI / 2;
                        h3.position.set(-size/2, yPos, 0);
                        h3.castShadow = true;
                        group.add(h3);
                        
                        // Face 4 (X = size/2)
                        const h4 = new THREE.Mesh(horizGeo, trussMat);
                        h4.rotation.x = Math.PI / 2;
                        h4.position.set(size/2, yPos, 0);
                        h4.castShadow = true;
                        group.add(h4);
                        
                        // Diagonal braces (zig-zag pattern)
                        if (s < steps) {
                            const diagLength = Math.sqrt(size * size + stepSize * stepSize);
                            const diagGeo = new THREE.CylinderGeometry(rBrace, rBrace, diagLength, 6);
                            const angle = Math.atan2(size, stepSize);
                            
                            // Diagonals on alternating sides
                            const d1 = new THREE.Mesh(diagGeo, trussMat);
                            d1.rotation.z = (s % 2 === 0 ? angle : -angle);
                            d1.position.set(0, yPos + stepSize/2, -size/2);
                            d1.castShadow = true;
                            group.add(d1);
                            
                            const d2 = new THREE.Mesh(diagGeo, trussMat);
                            d2.rotation.z = (s % 2 === 0 ? -angle : angle);
                            d2.position.set(0, yPos + stepSize/2, size/2);
                            d2.castShadow = true;
                            group.add(d2);
                        }
                    }
                    
                    // Make the cylinder lie along the X axis by default (horizontal in 2D editor)
                    group.rotation.z = -Math.PI / 2;
                    return group;
                };
                
                // Orange truss material for cubes (same truss structure as Q30)
                const cuboMat = new THREE.MeshStandardMaterial({
                    color: '#f97316', metalness: 0.35, roughness: 0.25
                });
                
                // Helper to make a truss section with custom color
                const createColoredTruss = (length, mat) => {
                    var g = createTrussSection(length);
                    g.traverse(function(child) {
                        if (child.isMesh) {
                            child.material = mat;
                        }
                    });
                    return g;
                };
                
                // Cubo uses the same truss structure as Q30 but orange and 0.30m
                const createCubeModel = () => createColoredTruss(0.30, cuboMat);

                // Helper to create a SAPATA 3D model: solid flat rectangular steel
                // plate (width: 0.30m, height: 0.035m, length varies by model).
                // Brushed steel finish (aço escovado).
                const createSapataModel = (length) => {
                    const group = new THREE.Group();
                    const w = 0.30;
                    const h = 0.035;
                    const l = length;
                    const box = new THREE.Mesh(
                        new THREE.BoxGeometry(w, h, l, 1, 1, 1),
                        sapataMat
                    );
                    box.castShadow = true;
                    box.receiveShadow = true;
                    group.add(box);

                    // Beveled/chamfered edge detail on top face: thin border ring
                    const edgeMat = new THREE.MeshStandardMaterial({
                        color: '#d1d5db',
                        metalness: 0.95,
                        roughness: 0.25
                    });
                    const edgeThick = 0.003;
                    // Top face edges
                    const topEdgeGeo = new THREE.BoxGeometry(w, edgeThick, edgeThick);
                    const edgeFront = new THREE.Mesh(topEdgeGeo, edgeMat);
                    edgeFront.position.set(0, h / 2, l / 2 - edgeThick / 2);
                    group.add(edgeFront);
                    const edgeBack = new THREE.Mesh(topEdgeGeo, edgeMat);
                    edgeBack.position.set(0, h / 2, -l / 2 + edgeThick / 2);
                    group.add(edgeBack);
                    const sideEdgeGeo = new THREE.BoxGeometry(edgeThick, edgeThick, l);
                    const edgeLeft = new THREE.Mesh(sideEdgeGeo, edgeMat);
                    edgeLeft.position.set(w / 2 - edgeThick / 2, h / 2, 0);
                    group.add(edgeLeft);
                    const edgeRight = new THREE.Mesh(sideEdgeGeo, edgeMat);
                    edgeRight.position.set(-w / 2 + edgeThick / 2, h / 2, 0);
                    group.add(edgeRight);

                    return group;
                };
                
                // Helper to create a Sleeve 3D model: mesmo formato treliçado do
                // cubo (createColoredTruss), porém roxo e maior (corre por fora
                // da estrutura).
                const createSleeveModel = () => {
                    const sleeveSize = 0.42; // maior que o cubo (0.30m)
                    return createColoredTruss(sleeveSize, sleeveMat);
                };

                // Helper to create a Grepo 3D model: perfil "U" de aço galvanizado
                // (chassi 42x30x300mm) usado para grampear/unir tubos de Q30,
                // com furos oblongos marcados nas abas laterais.
                const grepoMat = new THREE.MeshStandardMaterial({ color: '#cbd5e1', metalness: 0.85, roughness: 0.3 });
                const grepoSlotMat = new THREE.MeshStandardMaterial({ color: '#1e293b', metalness: 0.1, roughness: 0.9 });
                const createGrepoModel = () => {
                    const length = 0.30;
                    const wallThickness = 0.004;
                    const channelWidth = 0.042;  // largura do "U" (abertura)
                    const channelHeight = 0.030; // altura das abas do "U"
                    const group = new THREE.Group();

                    // Base do "U"
                    const base = new THREE.Mesh(new THREE.BoxGeometry(length, wallThickness, channelWidth), grepoMat);
                    base.position.set(0, -channelHeight / 2, 0);
                    base.castShadow = true;
                    base.receiveShadow = true;
                    group.add(base);

                    // Abas laterais do "U"
                    const wallGeo = new THREE.BoxGeometry(length, channelHeight, wallThickness);
                    [1, -1].forEach(side => {
                        const wall = new THREE.Mesh(wallGeo, grepoMat);
                        wall.position.set(0, 0, side * (channelWidth / 2 - wallThickness / 2));
                        wall.castShadow = true;
                        wall.receiveShadow = true;
                        group.add(wall);

                        // Furos oblongos (marcação visual) nas abas
                        [-1, 1].forEach(pos => {
                            const slot = new THREE.Mesh(new THREE.BoxGeometry(0.038, 0.017, wallThickness + 0.001), grepoSlotMat);
                            slot.position.set(pos * length / 4, 0, side * (channelWidth / 2 - wallThickness / 2));
                            group.add(slot);
                        });
                    });

                    // Modelo é construído deitado (comprimento ao longo de X);
                    // gira 90° em Z para ficar em pé (comprimento ao longo de Y),
                    // mesma convenção de eixo usada por createTrussSection/cubo.
                    group.rotation.z = Math.PI / 2;

                    return group;
                };

                // Helper: gera textura procedural simulando pixels de um painel de LED
                const createLedPanelTexture = () => {
                    const size = 256;
                    const cnv = document.createElement('canvas');
                    cnv.width = size; cnv.height = size;
                    const ctx = cnv.getContext('2d');
                    ctx.fillStyle = '#020617';
                    ctx.fillRect(0, 0, size, size);
                    const cell = 16;
                    const colors = ['#22d3ee', '#0ea5e9', '#a855f7', '#f472b6', '#facc15', '#34d399'];
                    for (let py = 0; py < size; py += cell) {
                        for (let px = 0; px < size; px += cell) {
                            ctx.fillStyle = colors[(px / cell + py / cell * 3) % colors.length | 0];
                            ctx.globalAlpha = 0.55 + 0.45 * (((px / cell + py / cell) % 3) / 2);
                            ctx.fillRect(px + 1, py + 1, cell - 2, cell - 2);
                        }
                    }
                    ctx.globalAlpha = 1;
                    const tex = new THREE.CanvasTexture(cnv);
                    tex.wrapS = THREE.RepeatWrapping;
                    tex.wrapT = THREE.RepeatWrapping;
                    return tex;
                };

                // Treliça Plana (Flat Truss): 2 faces paralelas (banzos superior e
                // inferior) ligadas por travessas e diagonais em zigue-zague, tudo em
                // UM único plano — não forma volume fechado como o Box Truss. Altura
                // da seção 0.30m; espessura apenas a dos tubos (~0.04m). Construída
                // ao longo de Y e rotacionada para X (mesma convenção do Q30).
                const planaMat = new THREE.MeshStandardMaterial({
                    color: '#a3e635', metalness: 0.35, roughness: 0.25
                });

                // Braço: 1 barra estrutural (elemento linear) — NÃO é treliça, não tem
                // faces. Apenas 1 tubo longitudinal; possui só comprimento e direção.
                // Hierarquia: Box Truss (4 faces) > Treliça Plana (2) > Braço (1 barra).
                const bracoMat = new THREE.MeshStandardMaterial({
                    color: '#2dd4bf', metalness: 0.5, roughness: 0.3
                });
                const createBracoModel = (length) => {
                    const group = new THREE.Group();
                    const bar = new THREE.Mesh(new THREE.CylinderGeometry(0.025, 0.025, length, 12), bracoMat);
                    bar.castShadow = true;
                    bar.receiveShadow = true;
                    group.add(bar);
                    // Deitado ao longo do eixo X por padrão (mesma convenção do Q30)
                    group.rotation.z = -Math.PI / 2;
                    return group;
                };

                // Perfil PM5 (montante/travessa): seção transversal cruciforme REAL do
                // perfil de alumínio extrudado 50x50mm (ver pm5.jpeg e
                // descricao_completa_perfil_aluminio_3d.pdf, na raiz do projeto):
                // cavidade central maior + 4 câmaras menores nos cantos, ligadas por
                // nervuras finas — não um prisma liso. Shape 2D montado uma única vez
                // (não depende do comprimento) e extrudado por peça via
                // THREE.ExtrudeGeometry. A cor é por item de catálogo (varia por
                // comprimento, cadastrada em Peças & Estoque), então o material é
                // criado por instância, não compartilhado.
                const _pm5CrossSection = (() => {
                    const S = 0.025;           // metade do lado externo (50mm)
                    const wall = 0.0025;        // parede externa / nervura (~2,5mm)
                    const centralHalf = 0.008;  // metade da cavidade central (maior, ~16mm)
                    const cornerHalf = 0.006;   // metade de cada câmara de canto (~12mm)
                    const cornerOffset = S - wall - cornerHalf; // centro de cada câmara de canto

                    const shape = new THREE.Shape();
                    shape.moveTo(-S, -S);
                    shape.lineTo(S, -S);
                    shape.lineTo(S, S);
                    shape.lineTo(-S, S);
                    shape.closePath();

                    // Buracos em winding OPOSTO ao contorno externo (sentido horário),
                    // como exige o THREE.js para o ExtrudeGeometry subtrair corretamente.
                    const squareHole = (cx, cy, half) => {
                        const p = new THREE.Path();
                        p.moveTo(cx - half, cy - half);
                        p.lineTo(cx - half, cy + half);
                        p.lineTo(cx + half, cy + half);
                        p.lineTo(cx + half, cy - half);
                        p.closePath();
                        return p;
                    };

                    shape.holes.push(squareHole(0, 0, centralHalf));
                    [[-1, -1], [1, -1], [-1, 1], [1, 1]].forEach(([sx, sy]) => {
                        shape.holes.push(squareHole(sx * cornerOffset, sy * cornerOffset, cornerHalf));
                    });
                    return shape;
                })();

                // ExtrudeGeometry nasce ao longo de Z (de 0 a `length`); centraliza em
                // -length/2..length/2 pra bater com a convenção de todas as outras
                // peças (origem no centro do comprimento).
                const _createPM5Mesh = (length, cor) => {
                    const geo = new THREE.ExtrudeGeometry(_pm5CrossSection, {
                        depth: length,
                        bevelEnabled: false,
                        curveSegments: 1
                    });
                    geo.translate(0, 0, -length / 2);
                    const mat = new THREE.MeshStandardMaterial({
                        color: cor || '#94a3b8', metalness: 0.65, roughness: 0.32
                    });
                    const mesh = new THREE.Mesh(geo, mat);
                    mesh.castShadow = true;
                    mesh.receiveShadow = true;
                    return mesh;
                };

                // Montante: SEMPRE vertical (ao longo de Y) — nunca deitado. A rotação de
                // base (Z da extrusão -> Y) fica no MESH filho, não no group: o dispatch
                // de render sobrescreve model.rotation.x/y com `=` (não `+=`), então uma
                // base no group seria apagada a cada redesenho.
                const createMontanteModel = (length, cor) => {
                    const group = new THREE.Group();
                    const mesh = _createPM5Mesh(length, cor);
                    mesh.rotation.x = -Math.PI / 2;
                    group.add(mesh);
                    return group;
                };

                // Travessa: SEMPRE no plano horizontal — nunca em pé (vertical).
                const createTravessaModel = (length, cor) => {
                    const group = new THREE.Group();
                    const mesh = _createPM5Mesh(length, cor);
                    mesh.rotation.y = Math.PI / 2;
                    group.add(mesh);
                    return group;
                };
                const createFlatTrussSection = (length) => {
                    const group = new THREE.Group();
                    const size = 0.3;
                    const rMain = 0.016;
                    const rBrace = 0.007;

                    // 2 banzos (tubos principais) — único par, sem as outras 2 faces
                    const tubeGeo = new THREE.CylinderGeometry(rMain, rMain, length, 8);
                    const b1 = new THREE.Mesh(tubeGeo, planaMat);
                    b1.position.set(-size / 2, 0, 0);
                    b1.castShadow = true;
                    group.add(b1);
                    const b2 = new THREE.Mesh(tubeGeo, planaMat);
                    b2.position.set(size / 2, 0, 0);
                    b2.castShadow = true;
                    group.add(b2);

                    // Travessas + diagonais em zigue-zague no plano dos banzos
                    const steps = Math.max(1, Math.round(length / 0.4));
                    const stepSize = length / steps;
                    for (let s = 0; s <= steps; s++) {
                        const yPos = -length / 2 + s * stepSize;
                        const rung = new THREE.Mesh(new THREE.CylinderGeometry(rBrace, rBrace, size, 6), planaMat);
                        rung.rotation.z = Math.PI / 2;
                        rung.position.set(0, yPos, 0);
                        rung.castShadow = true;
                        group.add(rung);
                        if (s < steps) {
                            const diagLength = Math.sqrt(size * size + stepSize * stepSize);
                            const angle = Math.atan2(size, stepSize);
                            const diag = new THREE.Mesh(new THREE.CylinderGeometry(rBrace, rBrace, diagLength, 6), planaMat);
                            diag.rotation.z = (s % 2 === 0 ? angle : -angle);
                            diag.position.set(0, yPos + stepSize / 2, 0);
                            diag.castShadow = true;
                            group.add(diag);
                        }
                    }

                    // Deitada ao longo do eixo X por padrão (igual createTrussSection);
                    // os banzos ficam em y = ±0.15 (plano vertical, "em pé").
                    group.rotation.z = -Math.PI / 2;
                    return group;
                };

                // Helper: refletor PAR LED procedural — corpo cilíndrico apontando
                // para +Z (público), lente emissiva âmbar e garra de fixação no topo.
                const createParLedModel = () => {
                    const group = new THREE.Group();
                    const bodyMat = new THREE.MeshStandardMaterial({ color: '#0f172a', metalness: 0.7, roughness: 0.35 });
                    const yokeMat = new THREE.MeshStandardMaterial({ color: '#334155', metalness: 0.6, roughness: 0.4 });
                    // Corpo: CylinderGeometry tem eixo em Y; rotation.x = 90° leva o
                    // topo (raio menor) para +Z, onde fica a lente.
                    const body = new THREE.Mesh(new THREE.CylinderGeometry(0.11, 0.13, 0.25, 24), bodyMat);
                    body.rotation.x = Math.PI / 2;
                    group.add(body);
                    const lens = new THREE.Mesh(
                        new THREE.CircleGeometry(0.095, 24),
                        new THREE.MeshStandardMaterial({ color: '#fbbf24', emissive: '#fbbf24', emissiveIntensity: 1.6, side: THREE.DoubleSide })
                    );
                    lens.position.z = 0.126;
                    group.add(lens);
                    // Garra em "U" invertido para pendurar no truss
                    const armL = new THREE.Mesh(new THREE.BoxGeometry(0.02, 0.16, 0.02), yokeMat);
                    armL.position.set(-0.14, 0.06, 0);
                    const armR = armL.clone();
                    armR.position.x = 0.14;
                    const yokeTop = new THREE.Mesh(new THREE.BoxGeometry(0.30, 0.02, 0.02), yokeMat);
                    yokeTop.position.y = 0.15;
                    group.add(armL, armR, yokeTop);
                    return group;
                };

                // READ ACTIVE 3D COMPONENTS AND DRAW IN 3D
                this.components3D.forEach(comp => {
                    let model;
                    if (comp.tipo === 'cubo') {
                        model = createCubeModel();
                        model.rotation.x = comp.rotationX * (Math.PI / 180);
                        model.rotation.y = -comp.rotationY * (Math.PI / 180);
                        model.rotation.z += -comp.rotationZ * (Math.PI / 180);
                    } else if (comp.tipo === 'Q30') {
                        model = createTrussSection(comp.length);
                        model.rotation.x = comp.rotationX * (Math.PI / 180);
                        model.rotation.y = -comp.rotationY * (Math.PI / 180);
                        model.rotation.z += -comp.rotationZ * (Math.PI / 180);
                    } else if (comp.tipo === 'plana') {
                        model = createFlatTrussSection(comp.length);
                        model.rotation.x = comp.rotationX * (Math.PI / 180);
                        model.rotation.y = -comp.rotationY * (Math.PI / 180);
                        model.rotation.z += -comp.rotationZ * (Math.PI / 180);
                    } else if (comp.tipo === 'braco') {
                        model = createBracoModel(comp.length);
                        model.rotation.x = comp.rotationX * (Math.PI / 180);
                        model.rotation.y = -comp.rotationY * (Math.PI / 180);
                        model.rotation.z += -comp.rotationZ * (Math.PI / 180);
                    } else if (comp.tipo === 'montante') {
                        model = createMontanteModel(comp.length, comp.cor);
                        model.rotation.x = comp.rotationX * (Math.PI / 180);
                        model.rotation.y = -comp.rotationY * (Math.PI / 180);
                        model.rotation.z += -comp.rotationZ * (Math.PI / 180);
                    } else if (comp.tipo === 'travessa') {
                        model = createTravessaModel(comp.length, comp.cor);
                        model.rotation.x = comp.rotationX * (Math.PI / 180);
                        model.rotation.y = -comp.rotationY * (Math.PI / 180);
                        model.rotation.z += -comp.rotationZ * (Math.PI / 180);
                    } else if (comp.tipo === 'sleeve' || comp.tipo === 'sleeve_4faces') {
                        model = createSleeveModel();
                        model.rotation.x = comp.rotationX * (Math.PI / 180);
                        model.rotation.y = -comp.rotationY * (Math.PI / 180);
                        // `+=` (não `=`): createSleeveModel() já nasce com uma rotação de
                        // base própria (deitar a treliça ao longo de X) — sobrescrever com
                        // `=` apagava essa base e desalinhava o modelo real (bug corrigido
                        // 2026-08-18, mesma classe do bug do Grepo abaixo).
                        model.rotation.z += -comp.rotationZ * (Math.PI / 180);
                    } else if (comp.tipo === 'grepo') {
                        model = createGrepoModel();
                        model.rotation.x = comp.rotationX * (Math.PI / 180);
                        model.rotation.y = -comp.rotationY * (Math.PI / 180);
                        // `+=`: mesmo motivo do Sleeve acima — createGrepoModel() também
                        // nasce com rotação de base própria (ver group.rotation.z lá dentro).
                        model.rotation.z += -comp.rotationZ * (Math.PI / 180);
                    } else if (comp.tipo === 'sapata') {
                        model = createSapataModel(comp.length);
                        model.rotation.x = comp.rotationX * (Math.PI / 180);
                        model.rotation.y = -comp.rotationY * (Math.PI / 180);
                        model.rotation.z += -comp.rotationZ * (Math.PI / 180);
                    } else if (comp.tipo === 'parled') {
                        model = createParLedModel();
                        model.rotation.x = comp.rotationX * (Math.PI / 180);
                        model.rotation.y = -comp.rotationY * (Math.PI / 180);
                        model.rotation.z = -comp.rotationZ * (Math.PI / 180);
                    } else if (comp.tipo === 'lona' || comp.tipo === 'painel_led') {
                        const isLed = comp.tipo === 'painel_led';
                        const accDepth = comp.depth || (isLed ? 0.1 : 0.03);
                        const accGeo = new THREE.BoxGeometry(comp.width, comp.height, accDepth);
                        if (isLed) {
                            const ledTexture = createLedPanelTexture();
                            ledTexture.repeat.set(Math.max(1, Math.round(comp.width / 0.5)), Math.max(1, Math.round(comp.height / 0.5)));
                            const faceMat = new THREE.MeshStandardMaterial({
                                map: ledTexture,
                                emissiveMap: ledTexture,
                                emissive: '#22d3ee',
                                emissiveIntensity: 1.1,
                                color: '#0f172a',
                                metalness: 0.2,
                                roughness: 0.4,
                                side: THREE.DoubleSide
                            });
                            const frameMat = new THREE.MeshStandardMaterial({ color: '#1e293b', metalness: 0.4, roughness: 0.6 });
                            // Ordem das faces do BoxGeometry: [+x, -x, +y, -y, +z, -z]
                            model = new THREE.Mesh(accGeo, [frameMat, frameMat, frameMat, frameMat, faceMat, faceMat]);
                        } else {
                            const lonaOp3 = (typeof comp.opacity === 'number') ? comp.opacity : null;
                            const accMat = new THREE.MeshStandardMaterial({
                                color: comp.color || '#f8fafc',
                                metalness: 0.1,
                                roughness: 0.6,
                                transparent: true,
                                opacity: lonaOp3 !== null ? lonaOp3 : 0.55,
                                side: THREE.DoubleSide
                            });
                            if (comp.image) {
                                // Imagem da lona como textura: branco para não tingir.
                                accMat.map = new THREE.TextureLoader().load(comp.image);
                                accMat.color = new THREE.Color('#ffffff');
                                accMat.opacity = lonaOp3 !== null ? lonaOp3 : 0.95;
                            }
                            model = new THREE.Mesh(accGeo, accMat);
                        }
                        model.rotation.x = comp.rotationX * (Math.PI / 180);
                        model.rotation.y = -comp.rotationY * (Math.PI / 180);
                        model.rotation.z += -comp.rotationZ * (Math.PI / 180);
                    } else if (comp.tipo === 'label') {
                        const labelText3 = comp.text || 'TEXTO';
                        const fontSize3 = 64;
                        const padding3 = 32;
                        const labelCanvas = document.createElement('canvas');
                        const lctx = labelCanvas.getContext('2d');
                        lctx.font = `bold ${fontSize3}px sans-serif`;
                        const textWidth3 = lctx.measureText(labelText3).width;
                        // Resizing the canvas resets the 2D context, so re-apply font/styles after.
                        labelCanvas.width = Math.ceil(textWidth3 + padding3 * 2);
                        labelCanvas.height = fontSize3 + padding3;
                        lctx.font = `bold ${fontSize3}px sans-serif`;
                        lctx.textAlign = 'center';
                        lctx.textBaseline = 'middle';
                        lctx.shadowColor = '#070a13';
                        lctx.shadowBlur = 8;
                        lctx.fillStyle = '#fbbf24';
                        lctx.fillText(labelText3, labelCanvas.width / 2, labelCanvas.height / 2);
                        const labelTexture = new THREE.CanvasTexture(labelCanvas);
                        const labelMat3 = new THREE.SpriteMaterial({ map: labelTexture, transparent: true, depthTest: false });
                        const labelSprite3 = new THREE.Sprite(labelMat3);
                        labelSprite3.position.set(comp.x, comp.y, comp.z);
                        const spriteHeight3 = 0.4;
                        labelSprite3.scale.set(spriteHeight3 * (labelCanvas.width / labelCanvas.height), spriteHeight3, 1);
                        scene.add(labelSprite3);
                    }

                    if (model) {
                        model.position.set(comp.x, comp.y, comp.z);
                        scene.add(model);
                    }

                    // Acessórios visuais (lona/painel/parled/label) não recebem etiqueta de medida
                    if (comp.tipo === 'lona' || comp.tipo === 'painel_led' || comp.tipo === 'parled' || comp.tipo === 'label') return;

                    // Component label
                    const isConector = comp.tipo === 'cubo' || comp.tipo === 'grepo';
                    const labelTxt = comp.tipo === 'grepo' ? 'GREPO' : (comp.tipo === 'cubo' ? 'CUBO' : (comp.tipo === 'sapata' ? 'SAPATA' : (comp.tipo === 'Q30' || comp.tipo === 'plana' || comp.tipo === 'braco' || comp.tipo === 'montante' || comp.tipo === 'travessa' ? `${comp.length.toFixed(1)}m` : (comp.tipo === 'sleeve_4faces' ? 'SF' : (comp.tipo === 'sleeve' ? 'SLEEVE' : '')))));
                    const labelC = document.createElement('canvas');
                    labelC.width = 128; labelC.height = 48;
                    const labelCtx = labelC.getContext('2d');
                    labelCtx.font = 'bold 22px monospace';
                    labelCtx.textAlign = 'center';
                    labelCtx.textBaseline = 'middle';
                    labelCtx.shadowColor = '#070a13';
                    labelCtx.shadowBlur = 5;
                    labelCtx.fillStyle = comp.tipo === 'cubo' ? '#f59e0b' : (comp.tipo === 'grepo' ? '#f43f5e' : (comp.tipo === 'sapata' ? '#d97706' : (comp.tipo === 'Q30' ? '#38bdf8' : (comp.tipo === 'plana' ? '#a3e635' : (comp.tipo === 'braco' ? '#2dd4bf' : (comp.tipo === 'montante' || comp.tipo === 'travessa' ? (comp.cor || '#94a3b8') : (comp.tipo === 'sleeve' || comp.tipo === 'sleeve_4faces' ? '#a855f7' : '#6366f1')))))));
                    labelCtx.fillText(labelTxt, 64, 26);
                    const labelTex = new THREE.CanvasTexture(labelC);
                    const labelMat = new THREE.SpriteMaterial({ map: labelTex, transparent: true, depthTest: false });
                    const labelSprite = new THREE.Sprite(labelMat);
                    labelSprite.position.set(comp.x, comp.y + 0.5, comp.z);
                    labelSprite.scale.set(0.6, 0.225, 1);
                    scene.add(labelSprite);
                });
                
                // ── Dimension helpers in 3D ──
                const L = this.dimensions.length;
                const H = this.dimensions.height;
                const D = this.dimensions.width;
                const neonColor = 0x06b6d4;
                
                const createDimLabel = (text, pos) => {
                    const c = document.createElement('canvas');
                    c.width = 256; c.height = 64;
                    const ctx = c.getContext('2d');
                    ctx.font = 'bold 30px monospace';
                    ctx.textAlign = 'center';
                    ctx.textBaseline = 'middle';
                    ctx.shadowColor = '#070a13';
                    ctx.shadowBlur = 6;
                    ctx.fillStyle = '#06b6d4';
                    ctx.fillText(text, 128, 34);
                    const tex = new THREE.CanvasTexture(c);
                    const mat = new THREE.SpriteMaterial({ map: tex, transparent: true, depthTest: false });
                    const sprite = new THREE.Sprite(mat);
                    sprite.position.copy(pos);
                    sprite.scale.set(1.2, 0.3, 1);
                    return sprite;
                };
                
                const makeLine = (a, b) => {
                    const g = new THREE.BufferGeometry().setFromPoints([
                        new THREE.Vector3(a.x, a.y, a.z),
                        new THREE.Vector3(b.x, b.y, b.z)
                    ]);
                    return new THREE.Line(g, new THREE.LineBasicMaterial({ color: neonColor, transparent: true, opacity: 0.6 }));
                };
                
                const makeTick = (pos, dir, size = 0.08) => {
                    const p = new THREE.Vector3(pos.x, pos.y, pos.z);
                    const e = p.clone().add(new THREE.Vector3(dir.x * size, dir.y * size, dir.z * size));
                    return makeLine(p, e);
                };
                
                const offset = 0.25;
                const frontZ = 0;
                const leftX = -L / 2;
                const rightX = L / 2;
                const baseY = -0.02;
                const topY = H;
                const backZ = -D;
                
                // Length (X) at bottom front
                const lxY = baseY - offset;
                const lxZ = frontZ - offset;
                scene.add(makeLine({ x: leftX, y: lxY, z: lxZ }, { x: rightX, y: lxY, z: lxZ }));
                scene.add(makeTick({ x: leftX, y: lxY, z: lxZ }, { x: 0, y: -1, z: 0 }));
                scene.add(makeTick({ x: rightX, y: lxY, z: lxZ }, { x: 0, y: -1, z: 0 }));
                scene.add(createDimLabel(`${L.toFixed(1)}m`, { x: 0, y: lxY - 0.2, z: lxZ }));
                
                // Height (Y) at left front
                const hyX = leftX - offset;
                const hyZ = frontZ - offset;
                scene.add(makeLine({ x: hyX, y: baseY, z: hyZ }, { x: hyX, y: topY, z: hyZ }));
                scene.add(makeTick({ x: hyX, y: baseY, z: hyZ }, { x: -1, y: 0, z: 0 }));
                scene.add(makeTick({ x: hyX, y: topY, z: hyZ }, { x: -1, y: 0, z: 0 }));
                scene.add(createDimLabel(`${H.toFixed(1)}m`, { x: hyX - 0.25, y: H / 2, z: hyZ }));
                
                // Depth (Z) at bottom left
                if (D > 0.01) {
                    const dzX = leftX - offset;
                    const dzY = baseY - offset;
                    scene.add(makeLine({ x: dzX, y: dzY, z: frontZ }, { x: dzX, y: dzY, z: backZ }));
                    scene.add(makeTick({ x: dzX, y: dzY, z: frontZ }, { x: -1, y: 0, z: 0 }));
                    scene.add(makeTick({ x: dzX, y: dzY, z: backZ }, { x: -1, y: 0, z: 0 }));
                    scene.add(createDimLabel(`${D.toFixed(1)}m`, { x: dzX - 0.25, y: dzY, z: -D / 2 }));
                }

                // A cena é reconstruída do zero toda vez que se entra na vista 3D
                // (materiais novos, sem histórico de opacidade) — reaplica o modo
                // raio-X aqui se ele já estava ativo antes de trocar de view.
                if (this.xrayMode) {
                    this._applyXray(true);
                }

                // Render Loop
                let reqId;
                const animate = () => {
                    reqId = requestAnimationFrame(animate);
                    controls.update();
                    renderer.render(scene, camera);
                };
                animate();
                
                // Watcher to clean up requestAnimationFrame if viewMode changes
                let unwatch;
                unwatch = this.$watch('viewMode', (val) => {
                    if (val !== '3d') {
                        cancelAnimationFrame(reqId);
                        if (typeof unwatch === 'function') {
                            unwatch();
                        }
                    }
                });
                
            }, 50);
        },
        
        switchView(newMode) {
            if (this.accessorySketchType) this._exitAccessorySketch();
            if (this.sketchMode) {
                this.sketchMode = false;
                canvas.selection = true;
                canvas.off('mouse:down', this._sketchClickHandler);
                canvas.off('mouse:move', this._sketchMoveHandler);
                canvas.off('mouse:dblclick', this._sketchDblClickHandler);
                if (this._previewLine) { canvas.remove(this._previewLine); this._previewLine = null; }
                if (this._previewLabel) { canvas.remove(this._previewLabel); this._previewLabel = null; }
                this.clearSketch();
            }
            this.viewMode = newMode;
            
            if (newMode === '2d_frontal' || newMode === '2d_fundo' || newMode === '2d_superior' || newMode === '2d_lateral' || newMode === '2d_lateral_dir') {
                this.resetZoom();
                this.syncCanvasFrom3D();
                this.calculateQuantitative();
            } else if (newMode === '3d') {
                this.init3DView();
            }
        },

        // Os botões "Planta Cima"/"Planta Baixo" chamam isto em vez de switchView()
        // direto: a projeção da Planta (X horizontal, Z vertical) não muda entre os
        // dois — só qual metade (eixo Y) fica travada (ver _isLockedForView) — então
        // os dois reaproveitam o MESMO viewMode '2d_superior', trocando apenas
        // `plantaSide` antes de re-sincronizar o canvas.
        switchViewPlanta(side) {
            this.plantaSide = side;
            this.switchView('2d_superior');
        },

        zoomIn() {
            let zoom = canvas.getZoom();
            zoom = Math.min(zoom * 1.2, 5.0);
            this.canvasZoom = zoom;
            const center = new fabric.Point(canvas.width / 2, canvas.height / 2);
            canvas.zoomToPoint(center, zoom);
            canvas.requestRenderAll();
        },
        
        zoomOut() {
            let zoom = canvas.getZoom();
            zoom = Math.max(zoom / 1.2, 0.2);
            this.canvasZoom = zoom;
            const center = new fabric.Point(canvas.width / 2, canvas.height / 2);
            canvas.zoomToPoint(center, zoom);
            canvas.requestRenderAll();
        },
        
        resetZoom() {
            const center = new fabric.Point(canvas.width / 2, canvas.height / 2);
            canvas.zoomToPoint(center, 1.0);
            var vpt = canvas.viewportTransform;
            vpt[4] = 0;
            vpt[5] = 0;
            canvas.setViewportTransform(vpt);
            this.canvasZoom = 1.0;
            canvas.requestRenderAll();
        },
        
        fitToView() {
            var objects = canvas.getObjects().filter(function(o) { return o.data && o.data.uid; });
            if (objects.length === 0) return;
            var minX = Infinity, minY = Infinity, maxX = -Infinity, maxY = -Infinity;
            objects.forEach(function(o) {
                var b = o.getBoundingRect();
                if (b.left < minX) minX = b.left;
                if (b.top < minY) minY = b.top;
                if (b.left + b.width > maxX) maxX = b.left + b.width;
                if (b.top + b.height > maxY) maxY = b.top + b.height;
            });
            var pad = 40, cw = canvas.width, ch = canvas.height;
            var zoomX = (cw - pad * 2) / (maxX - minX);
            var zoomY = (ch - pad * 2) / (maxY - minY);
            var zoom = Math.min(zoomX, zoomY, 3.0);
            if (zoom < 0.1) zoom = 0.1;
            var cx = (minX + maxX) / 2, cy = (minY + maxY) / 2;
            var vpt = canvas.viewportTransform;
            vpt[0] = zoom; vpt[3] = zoom;
            vpt[4] = cw / 2 - cx * zoom;
            vpt[5] = ch / 2 - cy * zoom;
            canvas.setViewportTransform(vpt);
            this.canvasZoom = zoom;
            canvas.requestRenderAll();
        },

        // Orientação canônica de uma peça Q30: usada por TODAS as views (2D e helpers de
        // edição) para que a classificação horizontal/vertical/profundidade seja sempre
        // a mesma, não importa em qual view a peça foi editada por último.
        // - "Ao longo de Z" (profundidade): rotationX=0 e rotationY≈90 (ou 270)
        // - "Coluna" (vertical, ao longo de Y): não está ao longo de Z e rotationZ≈90 (ou 270)
        // - Caso contrário: peça no plano X-Y, ângulo = rotationZ (pode ser diagonal, ex: Sketch)
        isQ30AlongZ(comp) {
            const ry = ((comp.rotationY % 360) + 360) % 360;
            return Math.abs(comp.rotationX) < 1 && ((ry > 45 && ry < 135) || (ry > 225 && ry < 315));
        },

        isQ30Column(comp) {
            if (this.isQ30AlongZ(comp)) return false;
            const rz = ((comp.rotationZ % 360) + 360) % 360;
            return (rz > 45 && rz < 135) || (rz > 225 && rz < 315);
        },

        // Aplica a rotação 2D resultante (target.angle) de uma peça Q30 de volta ao
        // componente 3D, escrevendo SEMPRE o trio canônico rotationX/Y/Z, para que
        // isQ30AlongZ/isQ30Column fiquem consistentes em todas as views após a edição.
        // - Frontal/Fundo: ângulo livre (preserva diagonais do Sketch); fundo é espelhado.
        // - Superior/Lateral/Lateral Dir: ângulo é "encaixado" (snap) em 0° ou 90°,
        //   pois nessas views uma peça Q30 só pode estar "de ponta" ou "deitada".
        applyQ30Rotation(comp, viewMode, targetAngle) {
            const norm = a => ((a % 360) + 360) % 360;
            const angDiff = (a, b) => Math.abs(norm(a - b + 180) - 180);

            if (viewMode === '2d_frontal' || viewMode === '2d_fundo') {
                const xSign = viewMode === '2d_fundo' ? -1 : 1;
                const curAngle = this.isQ30AlongZ(comp) ? 0 : norm(xSign === -1 ? (180 - comp.rotationZ) : comp.rotationZ);
                if (angDiff(targetAngle, curAngle) > 1) {
                    comp.rotationX = 0;
                    comp.rotationY = 0;
                    comp.rotationZ = xSign === -1 ? (180 - targetAngle) : targetAngle;
                }
            } else if (viewMode === '2d_superior') {
                const curAngle = this.isQ30Column(comp) ? 0 : (this.isQ30AlongZ(comp) ? 90 : 0);
                if (angDiff(targetAngle, curAngle) > 1) {
                    const a = norm(targetAngle);
                    if ((a > 45 && a < 135) || (a > 225 && a < 315)) {
                        comp.rotationX = 0; comp.rotationY = 90; comp.rotationZ = 0;
                    } else {
                        comp.rotationX = 0; comp.rotationY = 0; comp.rotationZ = 0;
                    }
                }
            } else {
                // 2d_lateral / 2d_lateral_dir
                const curAngle = this.isQ30Column(comp) ? 90 : 0;
                if (angDiff(targetAngle, curAngle) > 1) {
                    const a = norm(targetAngle);
                    if ((a > 45 && a < 135) || (a > 225 && a < 315)) {
                        comp.rotationX = 0; comp.rotationY = 0; comp.rotationZ = 90;
                    } else {
                        comp.rotationX = 0; comp.rotationY = 90; comp.rotationZ = 0;
                    }
                }
            }
        },

        // Extensão real (mín/máx/meio) das peças já colocadas no projeto, num eixo
        // do mundo ('x'|'y'|'z') — usado por _isLockedForView pra achar o ponto de
        // corte de cada vista dividida. Dinâmico (não usa dimensions.length/height/
        // width nominais do card do projeto) porque peças raramente ocupam
        // exatamente essas dimensões — o corte tem que refletir onde as peças
        // REALMENTE estão, senão trava a metade errada.
        _axisMidpoint(axis) {
            let min = Infinity, max = -Infinity;
            this.components3D.forEach(c => {
                const v = c[axis];
                if (typeof v !== 'number') return;
                if (v < min) min = v;
                if (v > max) max = v;
            });
            if (!isFinite(min) || !isFinite(max)) return null;
            return { min, max, mid: (min + max) / 2 };
        },

        // Resolve o problema de seleção incorreta nas views 2D que colapsam um eixo
        // do mundo pra desenhar na tela: Frontal/Fundo colapsam Z (profundidade),
        // Lateral Esq/Dir colapsam X (comprimento), Planta Cima/Baixo colapsam Y
        // (altura) — peças de lados opostos do eixo colapsado podem cair no mesmo
        // ponto da tela, e clicar ali podia selecionar/arrastar a peça errada (a que
        // está "atrás", não a que se vê). Cada vista trava (não seleciona/arrasta) a
        // metade que ela não representa; a outra metade continua visível (esmaecida
        // no syncCanvasFrom3D) só como referência espacial.
        // Convenção de eixos (mesma do resto da calculadora — ver setIsometricView/
        // drawDimensionLine): X cresce da esquerda pra direita (leftX=-L/2,
        // rightX=L/2), Y cresce de baixo pra cima, Z é 0 na frente e fica mais
        // negativo indo pro fundo (frontZ=0, backZ=-D).
        _isLockedForView(comp) {
            let axis, unlockedSide; // unlockedSide: 'high' (valor >= meio) ou 'low' (valor < meio)
            if (this.viewMode === '2d_frontal') { axis = 'z'; unlockedSide = 'high'; }
            else if (this.viewMode === '2d_fundo') { axis = 'z'; unlockedSide = 'low'; }
            else if (this.viewMode === '2d_lateral') { axis = 'x'; unlockedSide = 'low'; }
            else if (this.viewMode === '2d_lateral_dir') { axis = 'x'; unlockedSide = 'high'; }
            else if (this.viewMode === '2d_superior') { axis = 'y'; unlockedSide = this.plantaSide === 'baixo' ? 'low' : 'high'; }
            else return false;

            const range = this._axisMidpoint(axis);
            if (!range) return false;
            // Abaixo desta extensão real não há ambiguidade de fato nesse eixo
            // (projeto raso/plano) — travar tudo seria só atrapalhar sem resolver
            // seleção incorreta nenhuma (não existe "peça atrás" pra confundir).
            const EPS = 0.05;
            if (range.max - range.min < EPS) return false;

            const v = comp[axis];
            if (typeof v !== 'number') return false;
            return unlockedSide === 'high' ? v < range.mid : v >= range.mid;
        },

        // Aplica (ou desfaz) o travamento por metade em TODOS os objetos já no
        // canvas, sem reconstruir nada — chamado no fim de syncCanvasFrom3D()
        // (logo depois de recriar tudo do zero) e também depois que uma seleção
        // via Ctrl+Shift (ver o handler de mouse:down mais abaixo) destrava
        // temporariamente peças da metade travada: ao desmarcar a seleção
        // (selection:cleared), chama isto de novo pra re-travar tudo sem
        // precisar de um resync completo do canvas.
        _applyViewLocks() {
            canvas.getObjects().forEach(o => {
                if (!o.data || !o.data.uid) return;
                const comp = this.components3D.find(c => c.uid === o.data.uid);
                if (!comp) return;
                if (this._isLockedForView(comp)) {
                    if (o.selectable !== false) {
                        // Guarda a opacidade-base SÓ se ainda não tiver uma
                        // guardada — reusa o valor original mesmo que a peça já
                        // tenha passado por um ciclo de destrava/trava (Ctrl+Shift
                        // seguido de deselecionar), senão cada ciclo multiplicaria
                        // a opacidade por 0.3 de novo (esmaecendo cada vez mais).
                        if (o._viewLockBaseOpacity === undefined) {
                            o._viewLockBaseOpacity = (typeof o.opacity === 'number') ? o.opacity : 1;
                        }
                        o.set({
                            selectable: false,
                            evented: false,
                            hoverCursor: 'default',
                            opacity: o._viewLockBaseOpacity * 0.3
                        });
                    }
                } else if (o._viewLockBaseOpacity !== undefined) {
                    // Estava travado e foi destravado temporariamente
                    // (Ctrl+Shift) — restaura seleção normal e opacidade original.
                    o.set({ selectable: true, evented: true, hoverCursor: 'move', opacity: o._viewLockBaseOpacity });
                    delete o._viewLockBaseOpacity;
                }
            });
        },

        // Chamado ao segurar Ctrl+Shift (ver keydown mais acima): destrava TODA
        // peça travada da view atual, restaurando seleção e opacidade normais —
        // o Shift+arrasto/clique NATIVO do Fabric.js passa a alcançar qualquer
        // peça, sem precisar de nenhuma lógica de seleção própria. Reusa o mesmo
        // `_viewLockBaseOpacity` de `_applyViewLocks` (idempotente — não
        // recaptura de uma opacidade já esmaecida, então não há risco de
        // "esquecer" a opacidade real original mesmo passando por vários ciclos
        // de destrava/trava).
        _unlockAllForSelection() {
            canvas.getObjects().forEach(o => {
                if (o.selectable === false) {
                    if (o._viewLockBaseOpacity === undefined) {
                        o._viewLockBaseOpacity = (typeof o.opacity === 'number') ? o.opacity : 1;
                    }
                    o.set({ selectable: true, evented: true, hoverCursor: 'move', opacity: o._viewLockBaseOpacity });
                }
            });
            canvas.requestRenderAll();
        },

        syncCanvasFrom3D() {
            canvas.clear();
            canvas.backgroundColor = '#070a13';
            this.drawGrid();
            
            const cx = canvas.width / 2;
            const cy = canvas.height / 2;
            const scale = this.canvasScale;
            
            // Helper to determine component color based on Q30 length
            const isConector = (c) => c.tipo === 'cubo' || c.tipo === 'grepo';
            const getComponentColors = (comp) => {
                let strokeColor, fillColor;
                if (comp.tipo === 'cubo') {
                    strokeColor = '#f97316'; // Orange (conector cubo)
                    fillColor = 'rgba(249,115,22,0.25)';
                } else if (comp.tipo === 'grepo') {
                    strokeColor = '#f43f5e'; // Rose/red (grepo)
                    fillColor = 'rgba(244,63,94,0.25)';
                } else if (comp.tipo === 'Q30') {
                    const len = parseFloat(comp.length) || 0;
                    if (Math.abs(len - 3.0) < 0.05) {
                        strokeColor = '#38bdf8'; // Sky blue
                        fillColor = 'rgba(56,189,248,0.18)';
                    } else if (Math.abs(len - 2.0) < 0.05) {
                        strokeColor = '#10b981'; // Emerald
                        fillColor = 'rgba(16,185,129,0.18)';
                    } else if (Math.abs(len - 1.5) < 0.05) {
                        strokeColor = '#06b6d4'; // Cyan
                        fillColor = 'rgba(6,182,212,0.18)';
                    } else if (Math.abs(len - 1.0) < 0.05) {
                        strokeColor = '#fbbf24'; // Amber/yellow
                        fillColor = 'rgba(251,191,36,0.18)';
                    } else if (Math.abs(len - 0.5) < 0.05) {
                        strokeColor = '#ec4899'; // Pink
                        fillColor = 'rgba(236,72,153,0.18)';
                    } else {
                        strokeColor = '#6366f1'; // Indigo
                        fillColor = 'rgba(99,102,241,0.18)';
                    }
                } else if (comp.tipo === 'plana') {
                    strokeColor = '#a3e635'; // Lime (treliça plana, 2 faces)
                    fillColor = 'rgba(163,230,53,0.18)';
                } else if (comp.tipo === 'braco') {
                    strokeColor = '#2dd4bf'; // Teal (braço — barra Q30 de 3m)
                    fillColor = 'rgba(45,212,191,0.18)';
                } else if (comp.tipo === 'sleeve' || comp.tipo === 'sleeve_4faces') {
                    strokeColor = '#a855f7'; // Purple
                    fillColor = 'rgba(168,85,247,0.18)';
                } else if (comp.tipo === 'sapata') {
                    strokeColor = '#d97706'; // Amber (sapata — base de apoio)
                    fillColor = 'rgba(217,119,6,0.22)';
                } else if (comp.tipo === 'montante' || comp.tipo === 'travessa') {
                    // Perfil PM5: cor é por item de catálogo (varia por comprimento)
                    strokeColor = comp.cor || '#94a3b8';
                    fillColor = this._hexToRgba(comp.cor || '#94a3b8', 0.22);
                }
                return { strokeColor, fillColor };
            };
            
            // Helper to create grouped Q30 Fabric object with length text label
            const createFabricQ30Object = (comp, left, top, rectWidth, rectHeight, angle, strokeColor, fillColor) => {
                let obj;
                const q30Thickness = 0.30 * scale;
                const isLong = (rectWidth > q30Thickness + 5) || (rectHeight > q30Thickness + 5);
                // O Fabric.js desenha o contorno CENTRALIZADO na borda (metade pra
                // fora do retângulo nominal) — num Q30 (30cm, dezenas de px) isso é
                // irrelevante, mas numa peça fina como o Perfil PM5 (5cm, poucos px
                // na tela) o contorno de 2px quase dobra o tamanho VISUAL da peça,
                // fazendo duas peças corretamente encostadas nos dados 3D parecerem
                // sobrepostas no 2D. Subtrai a largura do contorno da dimensão
                // nominal aqui, uma vez só, pra todo tipo de peça: o resultado
                // (preenchimento + contorno) bate com o tamanho real pretendido, não
                // "tamanho real + contorno por fora" (2026-08-18).
                const STROKE_W = 2;
                const rw = Math.max(1, rectWidth - STROKE_W);
                const rh = Math.max(1, rectHeight - STROKE_W);

                if (isLong) {
                    let rect = new fabric.Rect({
                        left: 0,
                        top: 0,
                        width: rw,
                        height: rh,
                        fill: fillColor,
                        stroke: strokeColor,
                        strokeWidth: STROKE_W,
                        originX: 'center',
                        originY: 'center',
                        cornerColor: '#06b6d4',
                        cornerSize: 8,
                        transparentCorners: false
                    });
                    
                    let text = new fabric.Text(`${comp.length}m`, {
                        left: 0,
                        top: 0,
                        fontSize: 9,
                        fontFamily: 'monospace',
                        fontWeight: 'bold',
                        fill: '#ffffff',
                        originX: 'center',
                        originY: 'center'
                    });
                    
                    obj = new fabric.Group([rect, text], {
                        left: left,
                        top: top,
                        originX: 'center',
                        originY: 'center',
                        angle: angle,
                        selectable: true,
                        evented: true,
                        data: { uid: comp.uid }
                    });
                } else {
                    obj = new fabric.Rect({
                        left: left,
                        top: top,
                        width: rw,
                        height: rh,
                        fill: fillColor,
                        stroke: strokeColor,
                        strokeWidth: STROKE_W,
                        originX: 'center',
                        originY: 'center',
                        angle: angle,
                        selectable: true,
                        evented: true,
                        cornerColor: '#06b6d4',
                        cornerSize: 8,
                        transparentCorners: false,
                        data: { uid: comp.uid }
                    });
                }
                return obj;
            };

            // Helper to create a connector (cubo/grepo) with truss X-bracing pattern
            const createFabricConnectorObject = (comp, left, top, cubeSize, strokeColor, fillColor, angle) => {
                var d = cubeSize * 0.12;
                var w = 2.5;
                var bg = new fabric.Rect({
                    left: 0, top: 0, width: cubeSize, height: cubeSize,
                    fill: fillColor, stroke: strokeColor, strokeWidth: 4,
                    rx: 2, ry: 2, originX: 'center', originY: 'center'
                });
                var x1 = new fabric.Line([-cubeSize/2+d, -cubeSize/2+d, cubeSize/2-d, cubeSize/2-d], {
                    stroke: strokeColor, strokeWidth: w, selectable: false, evented: false
                });
                var x2 = new fabric.Line([cubeSize/2-d, -cubeSize/2+d, -cubeSize/2+d, cubeSize/2-d], {
                    stroke: strokeColor, strokeWidth: w, selectable: false, evented: false
                });
                return new fabric.Group([bg, x1, x2], {
                    left: left, top: top, originX: 'center', originY: 'center',
                    angle: angle, selectable: true, evented: true,
                    cornerColor: strokeColor, cornerSize: 8, transparentCorners: false,
                    data: { uid: comp.uid }
                });
            };
            
            if (this.viewMode === '2d_frontal' || this.viewMode === '2d_fundo') {
                const xSign = this.viewMode === '2d_fundo' ? -1 : 1;
                const floorY = cy + (this.dimensions.height * scale) / 2 - 40;
                const cuboPx = 0.30 * scale;
                const length = this.dimensions.length;
                const lengthPx = length * scale;
                const startX = cx - (lengthPx / 2);
                const endX = cx + (lengthPx / 2);
                const startXOuter = startX - cuboPx / 2;
                const endXOuter = endX + cuboPx / 2;
                
                // Draw ground line
                const groundLine = new fabric.Line([0, floorY, canvas.width, floorY], {
                    stroke: '#334155',
                    strokeWidth: 2,
                    selectable: false,
                    evented: false
                });
                canvas.add(groundLine);
                
                // Draw dimension lines (configured dimension — cubes and Q30s within)
                this.drawDimensionLine(startXOuter, floorY + 25, endXOuter, floorY + 25, `${length.toFixed(1)}m`, 'horizontal');
                this.drawDimensionLine(xSign === -1 ? endXOuter + 30 : startXOuter - 30, floorY - this.dimensions.height * scale, xSign === -1 ? endXOuter + 30 : startXOuter - 30, floorY, `${this.dimensions.height.toFixed(1)}m`, 'vertical');
                
                // Labels for orientation (swapped in fundo view)
                const labelEsquerda = new fabric.Text(xSign === -1 ? "DIREITA (RIGHT)" : "ESQUERDA (LEFT)", {
                    left: xSign === -1 ? endX : startX,
                    top: floorY + 10,
                    fontSize: 10,
                    fontFamily: 'monospace',
                    fontWeight: 'bold',
                    fill: '#64748b',
                    originX: 'center',
                    selectable: false,
                    evented: false
                });
                const labelDireita = new fabric.Text(xSign === -1 ? "ESQUERDA (LEFT)" : "DIREITA (RIGHT)", {
                    left: xSign === -1 ? startX : endX,
                    top: floorY + 10,
                    fontSize: 10,
                    fontFamily: 'monospace',
                    fontWeight: 'bold',
                    fill: '#64748b',
                    originX: 'center',
                    selectable: false,
                    evented: false
                });
                canvas.add(labelEsquerda, labelDireita);
                
                // Draw components
                this.components3D.forEach(comp => {
                    const { strokeColor, fillColor } = getComponentColors(comp);
                    const left = cx + xSign * comp.x * scale;
                    const top = floorY - comp.y * scale;
                    if (comp.tipo === 'lona' || comp.tipo === 'painel_led' || comp.tipo === 'parled') {
                        if (comp.tipo === 'parled') {
                            const obj = this._createParLed2D(left, top, comp.rotationZ, comp.uid);
                            canvas.add(obj);
                            return;
                        }
                        const isLed = comp.tipo === 'painel_led';
                        // Lona com imagem: preenche com fabric.Pattern esticado para o
                        // tamanho do retângulo (nesta view a face plena é visível; nas
                        // views de ponta/planta a lona é fina e mantém a cor sólida).
                        // comp.opacity (0.1–1.0) vale para cor E imagem; lonas antigas
                        // sem o campo mantêm o visual de antes (fallbacks).
                        const lonaOp = (typeof comp.opacity === 'number') ? comp.opacity : null;
                        let lonaFill = this._hexToRgba(comp.color || '#e2e8f0', lonaOp !== null ? lonaOp : 0.25);
                        const lonaImg = !isLed ? this._getLonaImage(comp) : null;
                        if (lonaImg) {
                            lonaFill = new fabric.Pattern({
                                source: lonaImg,
                                repeat: 'no-repeat',
                                patternTransform: [comp.width * scale / lonaImg.width, 0, 0, comp.height * scale / lonaImg.height, 0, 0]
                            });
                        }
                        const obj = new fabric.Rect({
                            left: left,
                            top: top,
                            width: comp.width * scale,
                            height: comp.height * scale,
                            fill: isLed ? 'rgba(15,23,42,0.85)' : lonaFill,
                            opacity: (!isLed && lonaImg && lonaOp !== null) ? lonaOp : 1,
                            stroke: isLed ? '#22d3ee' : (comp.color || '#e2e8f0'),
                            strokeWidth: 2,
                            strokeDashArray: isLed ? [6, 4] : [4, 3],
                            originX: 'center',
                            originY: 'center',
                            angle: comp.rotationZ,
                            lockScalingX: true,
                            lockScalingY: true,
                            lockSkewingX: true,
                            lockSkewingY: true,
                            padding: 8,
                            cornerColor: isLed ? '#22d3ee' : (comp.color || '#e2e8f0'),
                            cornerSize: 8,
                            transparentCorners: false,
                            data: { uid: comp.uid }
                        });
                        obj.setControlsVisibility({
                            mt: false, mb: false, ml: false, mr: false,
                            bl: false, br: false, tl: false, tr: false,
                            mtr: true
                        });
                        obj.on('moving', () => { this.magneticSnap(obj); });
                        canvas.add(obj);
                        return;
                    }

                    if (comp.tipo === 'label') {
                        const obj = new fabric.IText(comp.text || 'TEXTO', {
                            left: left,
                            top: top,
                            fontSize: 18,
                            fontFamily: 'Arial',
                            fontWeight: 'bold',
                            fill: '#fbbf24',
                            originX: 'center',
                            originY: 'center',
                            angle: comp.rotationZ,
                            padding: 6,
                            cornerColor: '#fbbf24',
                            cornerSize: 8,
                            transparentCorners: false,
                            data: { uid: comp.uid }
                        });
                        obj.on('moving', () => { this.magneticSnap(obj); });
                        obj.on('editing:exited', () => {
                            comp.text = obj.text || 'TEXTO';
                            this.calculateQuantitative();
                            this.pushHistory();
                        });
                        canvas.add(obj);
                        return;
                    }

                    let obj;
                    const q30Thickness = 0.30 * scale;

                    if (comp.tipo === 'Q30' || comp.tipo === 'plana' || comp.tipo === 'braco' || comp.tipo === 'cubo' || comp.tipo === 'grepo') {
                        if (isConector(comp)) {
                            const cubeSize = 0.30 * scale;
                            obj = createFabricConnectorObject(comp, left, top, cubeSize, strokeColor, fillColor, comp.rotationZ);
                        } else {
                            const alongZ = this.isQ30AlongZ(comp);
                            // Treliça Plana: 2 faces, plano sempre vertical — fina só na
                            // vista de ponta. Braço: 1 barra (elemento linear, sem faces)
                            // — fino em TODAS as direções transversais.
                            const thinT = Math.max(0.05 * scale, 3);
                            const isBraco = comp.tipo === 'braco';

                            let rectWidth, rectHeight, angle;
                            if (alongZ) {
                                // Peça de profundidade (eixo Z): vista de ponta nesta view
                                rectWidth = (comp.tipo === 'plana' || isBraco) ? thinT : q30Thickness;
                                rectHeight = isBraco ? thinT : q30Thickness;
                                angle = 0;
                            } else {
                                // Peça no plano X-Y: coluna vertical, viga horizontal ou diagonal
                                rectWidth = comp.length * scale;
                                rectHeight = isBraco ? thinT : q30Thickness;
                                angle = xSign === -1 ? (180 - comp.rotationZ) : comp.rotationZ;
                            }

                            obj = createFabricQ30Object(comp, left, top, rectWidth, rectHeight, angle, strokeColor, fillColor);
                        }
                    } else if (comp.tipo === 'montante') {
                        // Perfil PM5: SEMPRE vertical nesta view (rotationZ travado em 0).
                        const thinT = Math.max(0.05 * scale, 3);
                        obj = createFabricQ30Object(comp, left, top, thinT, comp.length * scale, 0, strokeColor, fillColor);
                    } else if (comp.tipo === 'travessa') {
                        // Perfil PM5: SEMPRE horizontal — rotationX/Z travados em 0; só
                        // rotationY (direção no plano X-Z) varia, então reaproveita a
                        // mesma classificação "de ponta" (isQ30AlongZ) usada pelo Q30.
                        const thinT = Math.max(0.05 * scale, 3);
                        const alongZ = this.isQ30AlongZ(comp);
                        const rectWidth = alongZ ? thinT : comp.length * scale;
                        obj = createFabricQ30Object(comp, left, top, rectWidth, thinT, 0, strokeColor, fillColor);
                    } else if (comp.tipo === 'sleeve' || comp.tipo === 'sleeve_4faces') {
                        // Medida real do Sleeve (createSleeveModel: 0,42m, ver
                        // sleeveSize em createSleeveModel) — era `(22/50)*scale`
                        // (0,44m aproximado/"olhômetro") até 2026-08-18.
                        const sleeveSize = 0.42 * scale;
                        obj = new fabric.Rect({
                            left: left,
                            top: top,
                            width: sleeveSize,
                            height: sleeveSize,
                            rx: 3,
                            ry: 3,
                            fill: fillColor,
                            stroke: strokeColor,
                            strokeWidth: 2,
                            originX: 'center',
                            originY: 'center',
                            angle: comp.rotationZ,
                            cornerColor: '#a855f7',
                            cornerSize: 8,
                            transparentCorners: false,
                            data: { uid: comp.uid }
                        });
                    } else if (comp.tipo === 'sapata') {
                        const sapataW = 0.30 * scale;
                        const sapataH = Math.max(0.035 * scale, 3);
                        const sapTop = floorY - (comp.height || 0.035) / 2 * scale;
                        obj = new fabric.Rect({
                            left: left,
                            top: sapTop,
                            width: sapataW,
                            height: sapataH,
                            rx: 1,
                            ry: 1,
                            fill: fillColor,
                            stroke: strokeColor,
                            strokeWidth: 2,
                            originX: 'center',
                            originY: 'center',
                            angle: comp.rotationZ,
                            cornerColor: '#d97706',
                            cornerSize: 8,
                            transparentCorners: false,
                            data: { uid: comp.uid }
                        });
                    }

                    if (obj) {
                        obj.set({
                            lockScalingX: true,
                            lockScalingY: true,
                            lockSkewingX: true,
                            lockSkewingY: true,
                            padding: 8
                        });
                        obj.setControlsVisibility({
                            mt: false,
                            mb: false,
                            ml: false,
                            mr: false,
                            bl: false,
                            br: false,
                            tl: false,
                            tr: false,
                            // Perfil PM5: rotationZ é travado em 0 nesta view (montante nunca
                            // deita, travessa nunca empina) — esconde o handle de rotação
                            // para não sugerir um giro que será descartado ao soltar.
                            mtr: comp.tipo !== 'montante' && comp.tipo !== 'travessa'
                        });
                        obj.on('moving', (options) => {
                            this.magneticSnap(obj);
                        });
                        // Raio-X (mesmo estado da Vista 3D): reduz a opacidade de toda
                        // peça estrutural, pra enxergar o que fica coberto por outra na
                        // mesma posição de tela (comum na Superior/Lateral quando peças
                        // diferem só na profundidade não representada nesta view).
                        if (this.xrayMode) {
                            obj.set({ opacity: 0.45 });
                        }
                        canvas.add(obj);
                    }
                });
            } else if (this.viewMode === '2d_superior') {
                const depth = this.dimensions.width;
                const length = this.dimensions.length;
                const lengthPx = length * scale;
                const depthPx = depth * scale;
                const hasDepth = depthPx > 1;
                const cuboPx = 0.30 * scale;

                const startX = cx - (lengthPx / 2);
                const endX = cx + (lengthPx / 2);
                const frontY = cy + (depthPx / 2);
                const backY = cy - (depthPx / 2);
                const startXOuter = startX - cuboPx / 2;
                const endXOuter = endX + cuboPx / 2;
                const frontYOuter = frontY + cuboPx / 2;
                const backYOuter = backY - cuboPx / 2;
                
                // Draw dimension lines (configured dimension)
                this.drawDimensionLine(startXOuter, frontYOuter + 30, endXOuter, frontYOuter + 30, `${length.toFixed(1)}m`, 'horizontal');
                if (hasDepth) {
                    this.drawDimensionLine(startXOuter - 30, backYOuter, startXOuter - 30, frontYOuter, `${depth.toFixed(1)}m`, 'vertical');
                }
                
                // Labels for orientation
                let labelFundo = null;
                if (hasDepth) {
                    labelFundo = new fabric.Text("FUNDO (BACK)", {
                        left: cx,
                        top: Math.max(15, backY - 35),
                        fontSize: 11,
                        fontFamily: 'monospace',
                        fontWeight: 'bold',
                        fill: '#64748b',
                        originX: 'center',
                        selectable: false,
                        evented: false
                    });
                }
                const labelFrente = new fabric.Text("FRENTE (FRONT)", {
                    left: cx,
                    top: Math.min(canvas.height - 20, frontY + 55),
                    fontSize: 11,
                    fontFamily: 'monospace',
                    fontWeight: 'bold',
                    fill: '#64748b',
                    originX: 'center',
                    selectable: false,
                    evented: false
                });
                if (labelFundo) canvas.add(labelFundo);
                canvas.add(labelFrente);
                
                // Draw components
                this.components3D.forEach(comp => {
                    const { strokeColor, fillColor } = getComponentColors(comp);
                    const left = cx + comp.x * scale;
                    const top = cy + (comp.z + depth / 2) * scale;

                    if (comp.tipo === 'lona' || comp.tipo === 'painel_led' || comp.tipo === 'parled') {
                        if (comp.tipo === 'parled') {
                            const obj = this._createParLed2D(left, top, comp.rotationY, comp.uid);
                            canvas.add(obj);
                            return;
                        }
                        const isLed = comp.tipo === 'painel_led';
                        const obj = new fabric.Rect({
                            left: left,
                            top: top,
                            width: comp.width * scale,
                            height: Math.max((comp.depth || 0) * scale, 2),
                            fill: isLed ? 'rgba(15,23,42,0.85)' : this._hexToRgba(comp.color || '#e2e8f0', typeof comp.opacity === 'number' ? comp.opacity : 0.25),
                            stroke: isLed ? '#22d3ee' : (comp.color || '#e2e8f0'),
                            strokeWidth: 2,
                            strokeDashArray: isLed ? [6, 4] : [4, 3],
                            originX: 'center',
                            originY: 'center',
                            angle: comp.rotationY,
                            lockScalingX: true,
                            lockScalingY: true,
                            lockSkewingX: true,
                            lockSkewingY: true,
                            padding: 8,
                            cornerColor: isLed ? '#22d3ee' : (comp.color || '#e2e8f0'),
                            cornerSize: 8,
                            transparentCorners: false,
                            data: { uid: comp.uid }
                        });
                        obj.setControlsVisibility({
                            mt: false, mb: false, ml: false, mr: false,
                            bl: false, br: false, tl: false, tr: false,
                            mtr: true
                        });
                        obj.on('moving', () => { this.magneticSnap(obj); });
                        canvas.add(obj);
                        return;
                    }

                    if (comp.tipo === 'label') {
                        const obj = new fabric.IText(comp.text || 'TEXTO', {
                            left: left,
                            top: top,
                            fontSize: 18,
                            fontFamily: 'Arial',
                            fontWeight: 'bold',
                            fill: '#fbbf24',
                            originX: 'center',
                            originY: 'center',
                            angle: comp.rotationY,
                            padding: 6,
                            cornerColor: '#fbbf24',
                            cornerSize: 8,
                            transparentCorners: false,
                            data: { uid: comp.uid }
                        });
                        obj.on('moving', () => { this.magneticSnap(obj); });
                        obj.on('editing:exited', () => {
                            comp.text = obj.text || 'TEXTO';
                            this.calculateQuantitative();
                            this.pushHistory();
                        });
                        canvas.add(obj);
                        return;
                    }

                    let obj;
                    const q30Thickness = 0.30 * scale;
                    // Medida real do Sleeve (createSleeveModel: 0,42m) — era
                    // `(22/50)*scale` (0,44m aproximado) até 2026-08-18.
                    const sleeveSize = 0.42 * scale;

                    if (comp.tipo === 'Q30' || comp.tipo === 'plana' || comp.tipo === 'braco' || comp.tipo === 'cubo' || comp.tipo === 'grepo') {
                        if (isConector(comp)) {
                            const cubeSize = 0.30 * scale;
                            obj = createFabricConnectorObject(comp, left, top, cubeSize, strokeColor, fillColor, comp.rotationY);
                        } else {
                            // Treliça Plana (plano vertical): vista de cima mostra só a
                            // espessura (~0.05m). Braço (1 barra): fino em tudo, inclusive
                            // na vista de ponta de coluna.
                            const thinT = Math.max(0.05 * scale, 3);
                            const isBraco = comp.tipo === 'braco';
                            const crossThickness = (comp.tipo === 'plana' || isBraco) ? thinT : q30Thickness;
                            let rectWidth, rectHeight, angle;
                            if (this.isQ30Column(comp)) {
                                // Coluna vertical (eixo Y): vista de ponta nesta view
                                rectWidth = isBraco ? thinT : q30Thickness;
                                rectHeight = crossThickness;
                                angle = 0;
                            } else if (this.isQ30AlongZ(comp)) {
                                // Peça de profundidade (eixo Z): vertical na planta
                                rectWidth = comp.length * scale;
                                rectHeight = crossThickness;
                                angle = 90;
                            } else {
                                // Peça horizontal/diagonal no plano X-Y: horizontal na planta
                                rectWidth = comp.length * scale;
                                rectHeight = crossThickness;
                                angle = 0;
                            }

                            obj = createFabricQ30Object(comp, left, top, rectWidth, rectHeight, angle, strokeColor, fillColor);
                        }
                    } else if (comp.tipo === 'montante') {
                        // Vista de cima: montante é sempre coluna vertical — aparece como
                        // um pequeno quadrado (footprint da seção), nunca gira nesta view.
                        const thinT = Math.max(0.05 * scale, 3);
                        obj = createFabricQ30Object(comp, left, top, thinT, thinT, 0, strokeColor, fillColor);
                    } else if (comp.tipo === 'travessa') {
                        // Vista de cima: travessa sempre deitada no plano horizontal —
                        // rotationY é a única rotação livre e representa a direção real.
                        const thinT = Math.max(0.05 * scale, 3);
                        obj = createFabricQ30Object(comp, left, top, comp.length * scale, thinT, comp.rotationY, strokeColor, fillColor);
                    } else if (comp.tipo === 'sleeve' || comp.tipo === 'sleeve_4faces') {
                        obj = new fabric.Rect({
                            left: left,
                            top: top,
                            width: sleeveSize,
                            height: sleeveSize,
                            rx: 3,
                            ry: 3,
                            fill: fillColor,
                            stroke: strokeColor,
                            strokeWidth: 2,
                            originX: 'center',
                            originY: 'center',
                            angle: comp.rotationY,
                            selectable: true,
                            evented: true,
                            cornerColor: '#a855f7',
                            cornerSize: 8,
                            transparentCorners: false,
                            data: { uid: comp.uid }
                        });
                    } else if (comp.tipo === 'sapata') {
                        const sapataLen = comp.length * scale;
                        const sapataW = 0.30 * scale;
                        obj = new fabric.Rect({
                            left: left,
                            top: top,
                            width: sapataW,
                            height: sapataLen,
                            rx: 1,
                            ry: 1,
                            fill: fillColor,
                            stroke: strokeColor,
                            strokeWidth: 2,
                            originX: 'center',
                            originY: 'center',
                            angle: comp.rotationY,
                            selectable: true,
                            evented: true,
                            cornerColor: '#d97706',
                            cornerSize: 8,
                            transparentCorners: false,
                            data: { uid: comp.uid }
                        });
                    }

                    if (obj) {
                        obj.set({
                            lockScalingX: true,
                            lockScalingY: true,
                            lockSkewingX: true,
                            lockSkewingY: true,
                            padding: 8
                        });
                        obj.setControlsVisibility({
                            mt: false,
                            mb: false,
                            ml: false,
                            mr: false,
                            bl: false,
                            br: false,
                            tl: false,
                            tr: false,
                            // Montante nunca gira (footprint simétrico); travessa mantém
                            // o handle aqui pois rotationY é sua única rotação livre.
                            mtr: comp.tipo !== 'montante'
                        });
                        obj.on('moving', (options) => {
                            this.magneticSnap(obj);
                        });
                        // Raio-X (mesmo estado da Vista 3D): reduz a opacidade de toda
                        // peça estrutural, pra enxergar o que fica coberto por outra na
                        // mesma posição de tela (comum na Superior/Lateral quando peças
                        // diferem só na profundidade não representada nesta view).
                        if (this.xrayMode) {
                            obj.set({ opacity: 0.45 });
                        }
                        canvas.add(obj);
                    }
                });
            } else if (this.viewMode === '2d_lateral' || this.viewMode === '2d_lateral_dir') {
                const zSign = this.viewMode === '2d_lateral_dir' ? -1 : 1;
                const floorY = cy + (this.dimensions.height * scale) / 2 - 40;
                const depth = this.dimensions.width;
                const depthPx = depth * scale;
                const hasDepth = depthPx > 1;
                
                const startZ = hasDepth ? cx - (depthPx / 2) : cx;
                const endZ = hasDepth ? cx + (depthPx / 2) : cx;
                
                // Draw ground line
                const groundLine = new fabric.Line([0, floorY, canvas.width, floorY], {
                    stroke: '#334155',
                    strokeWidth: 2,
                    selectable: false,
                    evented: false
                });
                canvas.add(groundLine);
                
                // Draw dimension lines (configured dimension)
                const cuboPx = 0.30 * scale;
                if (hasDepth) {
                    this.drawDimensionLine(startZ - cuboPx / 2, floorY + 25, endZ + cuboPx / 2, floorY + 25, `${depth.toFixed(1)}m`, 'horizontal');
                }
                this.drawDimensionLine(startZ - 30 - cuboPx / 2, floorY - this.dimensions.height * scale - cuboPx / 2, startZ - 30 - cuboPx / 2, floorY + cuboPx / 2, `${this.dimensions.height.toFixed(1)}m`, 'vertical');
                
                // Labels for orientation (swapped in lateral_dir view)
                if (hasDepth) {
                    const labelFundo = new fabric.Text(zSign === -1 ? "FRENTE (FRONT)" : "FUNDO (BACK)", {
                        left: zSign === -1 ? endZ : startZ,
                        top: floorY + 10,
                        fontSize: 10,
                        fontFamily: 'monospace',
                        fontWeight: 'bold',
                        fill: '#64748b',
                        originX: 'center',
                        selectable: false,
                        evented: false
                    });
                    canvas.add(labelFundo);
                    const labelFrente = new fabric.Text(zSign === -1 ? "FUNDO (BACK)" : "FRENTE (FRONT)", {
                        left: zSign === -1 ? startZ : endZ,
                        top: floorY + 10,
                        fontSize: 10,
                        fontFamily: 'monospace',
                        fontWeight: 'bold',
                        fill: '#64748b',
                        originX: 'center',
                        selectable: false,
                        evented: false
                    });
                    canvas.add(labelFrente);
                }
                
                // Draw components
                this.components3D.forEach(comp => {
                    const { strokeColor, fillColor } = getComponentColors(comp);
                    const left = cx + zSign * (comp.z + depth / 2) * scale;
                    const top = floorY - comp.y * scale;

                    if (comp.tipo === 'lona' || comp.tipo === 'painel_led' || comp.tipo === 'parled') {
                        if (comp.tipo === 'parled') {
                            const obj = this._createParLed2D(left, top, comp.rotationX, comp.uid);
                            canvas.add(obj);
                            return;
                        }
                        const isLed = comp.tipo === 'painel_led';
                        const obj = new fabric.Rect({
                            left: left,
                            top: top,
                            width: Math.max((comp.depth || 0) * scale, 2),
                            height: comp.height * scale,
                            fill: isLed ? 'rgba(15,23,42,0.85)' : this._hexToRgba(comp.color || '#e2e8f0', typeof comp.opacity === 'number' ? comp.opacity : 0.25),
                            stroke: isLed ? '#22d3ee' : (comp.color || '#e2e8f0'),
                            strokeWidth: 2,
                            strokeDashArray: isLed ? [6, 4] : [4, 3],
                            originX: 'center',
                            originY: 'center',
                            angle: comp.rotationX,
                            lockScalingX: true,
                            lockScalingY: true,
                            lockSkewingX: true,
                            lockSkewingY: true,
                            padding: 8,
                            cornerColor: isLed ? '#22d3ee' : (comp.color || '#e2e8f0'),
                            cornerSize: 8,
                            transparentCorners: false,
                            data: { uid: comp.uid }
                        });
                        obj.setControlsVisibility({
                            mt: false, mb: false, ml: false, mr: false,
                            bl: false, br: false, tl: false, tr: false,
                            mtr: true
                        });
                        obj.on('moving', () => { this.magneticSnap(obj); });
                        canvas.add(obj);
                        return;
                    }

                    if (comp.tipo === 'label') {
                        const obj = new fabric.IText(comp.text || 'TEXTO', {
                            left: left,
                            top: top,
                            fontSize: 18,
                            fontFamily: 'Arial',
                            fontWeight: 'bold',
                            fill: '#fbbf24',
                            originX: 'center',
                            originY: 'center',
                            angle: comp.rotationX,
                            padding: 6,
                            cornerColor: '#fbbf24',
                            cornerSize: 8,
                            transparentCorners: false,
                            data: { uid: comp.uid }
                        });
                        obj.on('moving', () => { this.magneticSnap(obj); });
                        obj.on('editing:exited', () => {
                            comp.text = obj.text || 'TEXTO';
                            this.calculateQuantitative();
                            this.pushHistory();
                        });
                        canvas.add(obj);
                        return;
                    }

                    let obj;
                    const q30Thickness = 0.30 * scale;
                    // Medida real do Sleeve (createSleeveModel: 0,42m) — era
                    // `(22/50)*scale` (0,44m aproximado) até 2026-08-18.
                    const sleeveSize = 0.42 * scale;

                    if (comp.tipo === 'Q30' || comp.tipo === 'plana' || comp.tipo === 'braco' || comp.tipo === 'cubo' || comp.tipo === 'grepo') {
                        if (isConector(comp)) {
                            const cubeSize = 0.30 * scale;
                            obj = createFabricConnectorObject(comp, left, top, cubeSize, strokeColor, fillColor, comp.rotationX);
                        } else {
                            // Treliça Plana (plano vertical): de lado, peça no plano X-Y
                            // ou coluna mostra a espessura fina; peça ao longo de Z mostra
                            // a face plena (0.30m de altura). Braço (1 barra): fino em
                            // todas as direções transversais.
                            const thinT = Math.max(0.05 * scale, 3);
                            const isBraco = comp.tipo === 'braco';
                            let rectWidth, rectHeight, angle;
                            if (this.isQ30Column(comp)) {
                                // Coluna vertical (eixo Y): vertical nesta view
                                rectWidth = comp.length * scale;
                                rectHeight = (comp.tipo === 'plana' || isBraco) ? thinT : q30Thickness;
                                angle = 90;
                            } else if (this.isQ30AlongZ(comp)) {
                                // Peça de profundidade (eixo Z): horizontal nesta view
                                rectWidth = comp.length * scale;
                                rectHeight = isBraco ? thinT : q30Thickness;
                                angle = 0;
                            } else {
                                // Peça horizontal/diagonal no plano X-Y: vista de ponta nesta view
                                rectWidth = (comp.tipo === 'plana' || isBraco) ? thinT : q30Thickness;
                                rectHeight = isBraco ? thinT : q30Thickness;
                                angle = 0;
                            }

                            obj = createFabricQ30Object(comp, left, top, rectWidth, rectHeight, angle, strokeColor, fillColor);
                        }
                    } else if (comp.tipo === 'montante') {
                        // SEMPRE vertical (coluna) nesta view também — mesma classificação
                        // de "coluna" do Q30 (angle=90, comprimento cheio).
                        const thinT = Math.max(0.05 * scale, 3);
                        obj = createFabricQ30Object(comp, left, top, comp.length * scale, thinT, 90, strokeColor, fillColor);
                    } else if (comp.tipo === 'travessa') {
                        // SEMPRE horizontal — aparece de ponta ou em comprimento total
                        // conforme rotationY (única rotação livre da travessa).
                        const thinT = Math.max(0.05 * scale, 3);
                        const alongZ = this.isQ30AlongZ(comp);
                        const rectWidth = alongZ ? comp.length * scale : thinT;
                        obj = createFabricQ30Object(comp, left, top, rectWidth, thinT, 0, strokeColor, fillColor);
                    } else if (comp.tipo === 'sleeve' || comp.tipo === 'sleeve_4faces') {
                        obj = new fabric.Rect({
                            left: left,
                            top: top,
                            width: sleeveSize,
                            height: sleeveSize,
                            rx: 3,
                            ry: 3,
                            fill: fillColor,
                            stroke: strokeColor,
                            strokeWidth: 2,
                            originX: 'center',
                            originY: 'center',
                            angle: comp.rotationX,
                            cornerColor: '#a855f7',
                            cornerSize: 8,
                            transparentCorners: false,
                            data: { uid: comp.uid }
                        });
                    } else if (comp.tipo === 'sapata') {
                        const sapataLen = comp.length * scale;
                        const sapataH = Math.max(0.035 * scale, 3);
                        const sapTop = floorY - (comp.height || 0.035) / 2 * scale;
                        obj = new fabric.Rect({
                            left: left,
                            top: sapTop,
                            width: sapataLen,
                            height: sapataH,
                            rx: 1,
                            ry: 1,
                            fill: fillColor,
                            stroke: strokeColor,
                            strokeWidth: 2,
                            originX: 'center',
                            originY: 'center',
                            angle: comp.rotationX,
                            cornerColor: '#d97706',
                            cornerSize: 8,
                            transparentCorners: false,
                            data: { uid: comp.uid }
                        });
                    }

                    if (obj) {
                        obj.set({
                            lockScalingX: true,
                            lockScalingY: true,
                            lockSkewingX: true,
                            lockSkewingY: true,
                            padding: 8
                        });
                        obj.setControlsVisibility({
                            mt: false,
                            mb: false,
                            ml: false,
                            mr: false,
                            bl: false,
                            br: false,
                            tl: false,
                            tr: false,
                            // Perfil PM5: rotationX é travado em 0 nesta view — esconde o
                            // handle para não sugerir um giro que será descartado.
                            mtr: comp.tipo !== 'montante' && comp.tipo !== 'travessa'
                        });
                        obj.on('moving', (options) => {
                            this.magneticSnap(obj);
                        });
                        // Raio-X (mesmo estado da Vista 3D): reduz a opacidade de toda
                        // peça estrutural, pra enxergar o que fica coberto por outra na
                        // mesma posição de tela (comum na Superior/Lateral quando peças
                        // diferem só na profundidade não representada nesta view).
                        if (this.xrayMode) {
                            obj.set({ opacity: 0.45 });
                        }
                        canvas.add(obj);
                    }
                });
            }

            // Move grid lines to back
            const gridLines = canvas.getObjects().filter(o => o.isGridLine);
            gridLines.forEach(l => {
                const idx = canvas._objects.indexOf(l);
                if (idx > -1) {
                    canvas._objects.splice(idx, 1);
                    canvas._objects.unshift(l);
                }
            });

            // Trava seleção/arrasto das peças da metade que esta view não representa
            // (ver _isLockedForView e _applyViewLocks).
            this._applyViewLocks();

            // Orientation/depth hints (top-left corner overlay)
            this.drawViewHints();

            canvas.requestRenderAll();
        },

        // Painel de orientação no canto superior esquerdo de cada vista 2D: mostra os
        // dois eixos representados na tela e a dimensão do eixo perpendicular (que entra/sai
        // da tela), para ajudar a interpretar profundidade e diferenciar peças horizontais
        // de verticais entre as views.
        // Ferragens necessárias (apenas demonstrativo, não entra no quantitativo):
        // cada FACE de CUBO conectada a um Q30 usa 4 parafusos + 8 arruelas + 4 porcas;
        // cada GREPO usa 2 parafusos + 4 arruelas + 2 porcas.
        // "Face conectada" = ponta de um Q30 que cai próxima ao centro de um cubo
        // (peças Q30 são geradas encostadas no cubo, encurtadas por cuboHalf=0.15m).
        calculateHardwareCounts() {
            const cuboHalf = 0.15;
            const tol = cuboHalf + 0.06;
            const cubos = this.components3D.filter(comp => comp.tipo === 'cubo');
            const q30s = this.components3D.filter(comp => comp.tipo === 'Q30');
            const q30Endpoints = [];
            q30s.forEach(comp => {
                const half = (comp.length || 0) / 2;
                let dir;
                if (this.isQ30Column(comp)) {
                    dir = { x: 0, y: 1, z: 0 };
                } else if (this.isQ30AlongZ(comp)) {
                    dir = { x: 0, y: 0, z: 1 };
                } else {
                    const rad = (comp.rotationZ || 0) * Math.PI / 180;
                    dir = { x: Math.cos(rad), y: Math.sin(rad), z: 0 };
                }
                q30Endpoints.push({ x: comp.x - dir.x * half, y: comp.y - dir.y * half, z: comp.z - dir.z * half });
                q30Endpoints.push({ x: comp.x + dir.x * half, y: comp.y + dir.y * half, z: comp.z + dir.z * half });
            });
            let totalCuboFaces = 0;
            cubos.forEach(cubo => {
                q30Endpoints.forEach(ep => {
                    const dx = ep.x - cubo.x, dy = ep.y - cubo.y, dz = ep.z - cubo.z;
                    if (dx * dx + dy * dy + dz * dz < tol * tol) totalCuboFaces++;
                });
            });
            const numGrepos = this.components3D.filter(comp => comp.tipo === 'grepo').length;
            return {
                parafusos: totalCuboFaces * 4 + numGrepos * 2,
                arruelas: totalCuboFaces * 8 + numGrepos * 4,
                porcas: totalCuboFaces * 4 + numGrepos * 2
            };
        },

        drawViewHints() {
            const cfg = {
                '2d_frontal':     { h: '→ Comprimento (X)', v: '↑ Altura (Y)', perpLabel: 'Profundidade (Z)', perpVal: this.dimensions.width, perpIcon: '⊗' },
                '2d_fundo':       { h: '← Comprimento (X)', v: '↑ Altura (Y)', perpLabel: 'Profundidade (Z)', perpVal: this.dimensions.width, perpIcon: '⊙' },
                '2d_superior':    { h: '→ Comprimento (X)', v: '↓ Profundidade (Z)', perpLabel: 'Altura (Y)', perpVal: this.dimensions.height, perpIcon: '⊗' },
                '2d_lateral':     { h: '→ Profundidade (Z)', v: '↑ Altura (Y)', perpLabel: 'Comprimento (X)', perpVal: this.dimensions.length, perpIcon: '⊗' },
                '2d_lateral_dir': { h: '← Profundidade (Z)', v: '↑ Altura (Y)', perpLabel: 'Comprimento (X)', perpVal: this.dimensions.length, perpIcon: '⊙' }
            };
            const c = cfg[this.viewMode];
            if (!c) return;

            const { parafusos: numParafusos, arruelas: numArruelas, porcas: numPorcas } = this.calculateHardwareCounts();

            const pad = 10;
            const panel = new fabric.Rect({
                left: pad, top: pad, width: 200, height: 109,
                fill: 'rgba(15,23,42,0.78)', stroke: '#334155', strokeWidth: 1,
                rx: 8, ry: 8, originX: 'left', originY: 'top',
                selectable: false, evented: false
            });
            const lineH = new fabric.Text(c.h, {
                left: pad + 10, top: pad + 7, fontSize: 11, fontFamily: 'monospace',
                fill: '#38bdf8', fontWeight: 'bold', selectable: false, evented: false
            });
            const lineV = new fabric.Text(c.v, {
                left: pad + 10, top: pad + 24, fontSize: 11, fontFamily: 'monospace',
                fill: '#34d399', fontWeight: 'bold', selectable: false, evented: false
            });
            const lineP = new fabric.Text(`${c.perpIcon} ${c.perpLabel}: ${c.perpVal.toFixed(1)}m`, {
                left: pad + 10, top: pad + 41, fontSize: 11, fontFamily: 'monospace',
                fill: '#f472b6', fontWeight: 'bold', selectable: false, evented: false
            });
            const sep = new fabric.Line([pad + 10, pad + 58, pad + 190, pad + 58], {
                stroke: '#334155', strokeWidth: 1, selectable: false, evented: false
            });
            const lineParafusos = new fabric.Text(`🔩 Parafusos: ${numParafusos}`, {
                left: pad + 10, top: pad + 63, fontSize: 11, fontFamily: 'monospace',
                fill: '#fbbf24', fontWeight: 'bold', selectable: false, evented: false
            });
            const lineArruelas = new fabric.Text(`⊙ Arruelas: ${numArruelas}`, {
                left: pad + 10, top: pad + 80, fontSize: 11, fontFamily: 'monospace',
                fill: '#fbbf24', fontWeight: 'bold', selectable: false, evented: false
            });
            const linePorcas = new fabric.Text(`⬡ Porcas: ${numPorcas}`, {
                left: pad + 10, top: pad + 97, fontSize: 11, fontFamily: 'monospace',
                fill: '#fbbf24', fontWeight: 'bold', selectable: false, evented: false
            });
            canvas.add(panel, lineH, lineV, lineP, sep, lineParafusos, lineArruelas, linePorcas);
        },

        drawDimensionLine(x1, y1, x2, y2, label, align = 'horizontal') {
            const line = new fabric.Line([x1, y1, x2, y2], {
                stroke: '#06b6d4',
                strokeWidth: 1.5,
                selectable: false,
                evented: false
            });
            canvas.add(line);
            
            const tickSize = 6;
            if (align === 'horizontal') {
                const tickStart = new fabric.Line([x1, y1 - tickSize, x1, y1 + tickSize], {
                    stroke: '#06b6d4',
                    strokeWidth: 1.5,
                    selectable: false,
                    evented: false
                });
                const tickEnd = new fabric.Line([x2, y2 - tickSize, x2, y2 + tickSize], {
                    stroke: '#06b6d4',
                    strokeWidth: 1.5,
                    selectable: false,
                    evented: false
                });
                canvas.add(tickStart, tickEnd);
            } else {
                const tickStart = new fabric.Line([x1 - tickSize, y1, x1 + tickSize, y1], {
                    stroke: '#06b6d4',
                    strokeWidth: 1.5,
                    selectable: false,
                    evented: false
                });
                const tickEnd = new fabric.Line([x2 - tickSize, y2, x2 + tickSize, y2], {
                    stroke: '#06b6d4',
                    strokeWidth: 1.5,
                    selectable: false,
                    evented: false
                });
                canvas.add(tickStart, tickEnd);
            }
            
            const text = new fabric.Text(label, {
                left: align === 'horizontal' ? (x1 + x2) / 2 : x1 - 10,
                top: align === 'horizontal' ? y1 - 15 : (y1 + y2) / 2,
                fontSize: 11,
                fontFamily: 'monospace',
                fill: '#06b6d4',
                originX: 'center',
                originY: 'center',
                backgroundColor: '#070a13',
                padding: 4,
                selectable: false,
                evented: false
            });
            if (align === 'vertical') {
                text.set({
                    angle: -90,
                    left: x1 - 15
                });
            }
            canvas.add(text);
        },
        
        getTrussSegmentsForDistance(distanceM) {
            const segments = [];
            const q30s = this.catalog.filter(p => p.tipo === 'Q30').sort((a,b) => b.comprimento - a.comprimento);
            if (q30s.length === 0) return segments;
            
            let remaining = distanceM;
            while (remaining > 0.05) {
                let fitPeca = q30s.find(p => p.comprimento <= remaining + 0.01);
                if (!fitPeca) fitPeca = q30s[q30s.length - 1];
                segments.push(fitPeca);
                remaining -= fitPeca.comprimento;
            }
            return segments;
        },

        magneticSnap(target) {
            // Tolerância em pixels de tela: 12px fixos garantem um alvo fácil de
            // acertar com o mouse em qualquer zoom (antes era `0.1 * scale`, que
            // dava só 5px na escala padrão — ver histórico 2026-08-18). MAS pra
            // peças pequenas (Perfil PM5, seção de 5cm) 12px podem representar
            // uma distância real enorme perto do tamanho da própria peça (ex:
            // 24cm na escala padrão — quase 5x a largura do PM5!), fazendo ela
            // "pular" pra vizinhos longe demais em vez de encaixar com precisão.
            // Usa o MENOR entre os 12px fixos (piso de precisão do mouse) e o
            // raio proporcional ao tamanho real da peça (_touchToleranceFor,
            // mesma lógica do "fechar folga"), convertido pra pixels na escala
            // atual — cada peça é imantada numa distância do tamanho dela, não
            // um valor genérico igual pra todas.
            const comp = target.data && target.data.uid
                ? this.components3D.find(c => c.uid === target.data.uid)
                : null;
            const sizeTolerancePx = comp ? this._touchToleranceFor(comp) * this.canvasScale : Infinity;
            const snapPx = Math.min(12, sizeTolerancePx);
            const tb = target.getBoundingRect();
            if (!tb.width || !tb.height) return;

            // Eixos X e Y resolvidos de forma INDEPENDENTE um do outro (pedido do
            // usuário: "ao encostar na coordenada x y z... marcar uma linha" — uma
            // peça pode estar alinhada só em X, só em Y, ou nos dois ao mesmo tempo).
            // Antes o snap exigia um PONTO inteiro (X e Y da mesma borda/canto) perto
            // o bastante, então uma peça alinhada em X mas longe em Y não recebia
            // nenhum feedback — o que é exatamente "smart guides" tipo Figma/SketchUp,
            // diferente do snap ponto-a-ponto de canto que já existia. Sem o centro:
            // incluir o centro no snap fazia a peça arrastada saltar pra cima da
            // vizinha (sobreposição total); bordas/meio-de-borda bastam pra encaixar
            // treliças ponta a ponta e ainda alinhar centro-com-centro (o meio da
            // borda de uma peça cai sobre o centro geométrico dela mesma no eixo
            // perpendicular). Cada view 2D mapeia X-da-tela/Y-da-tela pra 2 dos 3
            // eixos do mundo (X/Y/Z) — trocar de view cobre o terceiro eixo.
            const tXs = [tb.left, tb.left + tb.width / 2, tb.left + tb.width];
            const tYs = [tb.top, tb.top + tb.height / 2, tb.top + tb.height];

            let bestDx = 0, bestDxDist = snapPx, guideX = null;
            let bestDy = 0, bestDyDist = snapPx, guideY = null;

            canvas.getObjects().forEach(o => {
                if (o === target || o.isGridLine || (!o.selectable && !o.evented)) return;
                const ob = o.getBoundingRect();
                if (!ob.width || !ob.height) return;

                const oXs = [ob.left, ob.left + ob.width / 2, ob.left + ob.width];
                const oYs = [ob.top, ob.top + ob.height / 2, ob.top + ob.height];

                tXs.forEach(tx => oXs.forEach(ox => {
                    const dist = Math.abs(ox - tx);
                    if (dist < bestDxDist) { bestDxDist = dist; bestDx = ox - tx; guideX = ox; }
                }));
                tYs.forEach(ty => oYs.forEach(oy => {
                    const dist = Math.abs(oy - ty);
                    if (dist < bestDyDist) { bestDyDist = dist; bestDy = oy - ty; guideY = oy; }
                }));
            });

            if (bestDx !== 0 || bestDy !== 0) {
                target.set({ left: target.left + bestDx, top: target.top + bestDy });
                target.setCoords();
            }
            if (guideX !== null || guideY !== null) {
                this._showSnapGuide({ x: guideX, y: guideY });
            } else {
                this._clearSnapGuide();
            }

            // Feedback ao vivo: mostra a posição/altura real (em metros) da peça
            // enquanto ela está sendo arrastada, não só depois de soltar — pedido
            // do usuário ("não tem essa sensação de onde o objeto vai ficar"),
            // principalmente pra controlar altura (Y) nas views Frontal/Fundo/Lateral.
            this._updateDragLabel(target);
        },

        // Linhas-guia tracejadas nos eixos onde o snap magnético encontrou
        // alinhamento — sem isso o snap "acontece" mas fica invisível, parecendo
        // que não tem controle nenhum sobre onde a peça vai parar. `point.x`/
        // `point.y` podem vir null independentemente (eixos resolvidos à parte em
        // `magneticSnap`): só a linha do eixo realmente alinhado fica visível, a
        // outra some — evita mostrar uma "falsa" linha grudada em 0 quando só um
        // dos dois eixos está de fato alinhado com alguma peça vizinha.
        _showSnapGuide(point) {
            if (!this._snapGuideLines) {
                const mkLine = (coords) => new fabric.Line(coords, {
                    stroke: '#22d3ee',
                    strokeWidth: 1,
                    strokeDashArray: [5, 4],
                    selectable: false,
                    evented: false,
                    excludeFromExport: true
                });
                this._snapGuideLines = [
                    mkLine([0, 0, canvas.width, 0]),
                    mkLine([0, 0, 0, canvas.height])
                ];
                this._snapGuideLines.forEach(l => canvas.add(l));
            }
            const hasY = point.y !== null && point.y !== undefined;
            const hasX = point.x !== null && point.x !== undefined;
            this._snapGuideLines[0].set({ y1: point.y ?? 0, y2: point.y ?? 0, visible: hasY });
            this._snapGuideLines[1].set({ x1: point.x ?? 0, x2: point.x ?? 0, visible: hasX });
            canvas.requestRenderAll();
        },

        _clearSnapGuide() {
            if (this._snapGuideLines) {
                this._snapGuideLines.forEach(l => canvas.remove(l));
                this._snapGuideLines = null;
                canvas.requestRenderAll();
            }
        },

        // Converte a posição de tela do objeto arrastado nas 2 coordenadas do
        // mundo (em metros) que fazem sentido pra view atual — mesma lógica de
        // conversão usada no object:modified, só que chamada a cada frame do
        // arrasto em vez de só no final.
        _dragWorldLabel(target) {
            const scale = this.canvasScale;
            const cx = canvas.width / 2;
            const cy = canvas.height / 2;
            const depth = this.dimensions.width;
            const floorY = cy + (this.dimensions.height * scale) / 2 - 40;
            if (this.viewMode === '2d_frontal' || this.viewMode === '2d_fundo') {
                const xSign = this.viewMode === '2d_fundo' ? -1 : 1;
                const wx = xSign * (target.left - cx) / scale;
                const wy = (floorY - target.top) / scale;
                return `X ${wx.toFixed(2)}m   Y ${wy.toFixed(2)}m`;
            } else if (this.viewMode === '2d_superior') {
                const wx = (target.left - cx) / scale;
                const wz = (target.top - cy) / scale - depth / 2;
                return `X ${wx.toFixed(2)}m   Z ${wz.toFixed(2)}m`;
            } else {
                const zSign = this.viewMode === '2d_lateral_dir' ? -1 : 1;
                const wz = zSign * (target.left - cx) / scale - depth / 2;
                const wy = (floorY - target.top) / scale;
                return `Z ${wz.toFixed(2)}m   Y ${wy.toFixed(2)}m`;
            }
        },

        _updateDragLabel(target) {
            const text = this._dragWorldLabel(target);
            if (!this._dragLabel) {
                this._dragLabel = new fabric.Text(text, {
                    fontSize: 12,
                    fontFamily: 'monospace',
                    fontWeight: 'bold',
                    fill: '#0b0f19',
                    backgroundColor: '#22d3ee',
                    padding: 5,
                    selectable: false,
                    evented: false,
                    excludeFromExport: true
                });
                canvas.add(this._dragLabel);
            }
            this._dragLabel.set({ text, left: target.left + 24, top: target.top - 34 });
            this._dragLabel.setCoords();
            canvas.bringToFront(this._dragLabel);
            canvas.requestRenderAll();
        },

        _clearDragLabel() {
            if (this._dragLabel) {
                canvas.remove(this._dragLabel);
                this._dragLabel = null;
            }
        }
    };
}
</script>
<?php Layout::end(); ?>

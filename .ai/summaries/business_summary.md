# Business Summary — ProFoxTruss

## Domínio
Sistema de engenharia para cálculo e projeto de **treliças Box Truss** (perfil Q30). Utilizado por empresas de montagem de estruturas para palcos, stands, eventos e construções modulares.

## Entidades Principais
- **Peças (produtos)**: Tubos Box Truss Q30 em comprimentos variados (0.30m a 5.00m), sleeves conectores, sapatas de base e sapatas Pé de Galinha (1m/2m). Cada peça tem código, nome, tipo, comprimento, peso, preço e estoque.
- **Projetos**: Estruturas treliçadas definidas por width, height, length; componentes salvos como JSON; visualização 2D (Fabric.js) e 3D (Three.js).
- **Usuários**: 3 roles — admin (acesso total), editor (dashboard+relatórios), user (dashboard apenas).
- **Clientes** (desativado): Cadastro de pessoas físicas com CPF.
- **Pedidos** (desativado): Vendas com itens de produtos, descontos, status (rascunho/confirmado/cancelado).

## Fluxos de Negócio
1. **Projetar estrutura**: Usuário define dimensões (comprimento, altura, profundidade), escolhe tipo de sapata, adiciona peças do catálogo por drag-click ou seleção, sistema monta pórtico automaticamente.
2. **Visualizar**: Renderização 2D com zoom (20%-500%), snap magnético entre componentes (15px), linhas de cota (X/Y/Z) no 3D, medidas individuais por peça.
3. **Gerenciar estoque**: Cadastro e edição de peças no catálogo, controle de quantidade em estoque.
4. **Salvar projeto**: Projetos são persistidos em JSON no banco SQLite, permitindo recarregar e editar.

## Módulos Desativados
- **Clientes**: Cadastro completo com validação de CPF brasileiro, CRUD funcional mas rotas comentadas.
- **Pedidos**: CRUD completo com itens, cálculo de subtotais server-side, busca AJAX de clientes/produtos, rotas comentadas.

## Observações
- Não há integrações externas (pagamento, e-mail, MikroTik).
- Sistema é independente, sem API pública para terceiros.
- Terminologia de ISP (PPPoE, bloqueio, plano, vencimento) é placeholder herdado e não reflete a realidade do sistema.

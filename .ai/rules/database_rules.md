# Regras: Database

## Conexão
- Sempre usar `getPDO()` (singleton). Não criar conexões PDO adicionais.
- `getPDO()` está em `config.php` e já configura `PDO::ERRMODE_EXCEPTION`.
- WAL mode não está configurado — pode ser adicionado se necessário para concorrência.

## Queries
- Prepared statements obrigatórios (`$pdo->prepare()` + `execute()`). Nunca concatenar valores em SQL.
- `INSERT` com `lastInsertId()` para obter ID gerado.
- Transações para operações multi-tabela (ver `PedidoRepository::create()`).
- Usar `ON DELETE CASCADE` para dependências fracas, `ON DELETE RESTRICT` para fortes.

## Migrations
- Schema completo em `database/migration.sql` (aplicação manual, sem migration runner).
- Seed em `database/seed.sql` com dados iniciais.
- Novas tabelas ou colunas devem ser adicionadas como `ALTER TABLE` em arquivo separado ou editando `migration.sql` (para dev).

## Schema
- Tipos relevantes: `PRIMARY KEY AUTOINCREMENT`, `TEXT`, `REAL`, `INTEGER`.
- Datas como `TEXT` com `DEFAULT CURRENT_TIMESTAMP` (SQLite não tem tipo DATETIME nativo).
- JSON armazenado como `TEXT` (coluna `components` em `projects`).

## Performance
- Índices existentes para colunas mais consultadas (email, cpf, nome, tipo, status).
- Sem índices compostos atualmente.
- Monitorar queries lentas via `appLog()`.

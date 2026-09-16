# Deployment

## ⚠️ Dois ambientes distintos — não confundir
Este projeto roda em DUAS máquinas diferentes:
1. **Ambiente local (dev)** — a máquina onde a IA normalmente opera (Apache local,
   descrito abaixo em "Ambiente Local de Desenvolvimento").
2. **Produção real** — um VPS remoto, domínio público `sisloc.online/profoxtruss`,
   descoberto/confirmado em 2026-08-18 (antes desta data este arquivo só descrevia o
   ambiente local e estava incompleto/enganoso sobre onde o site realmente roda).
   **Editar o código local NÃO afeta produção** — é preciso sincronizar manualmente
   (ver "Deploy para Produção" abaixo).

## Produção — sisloc.online/profoxtruss (2026-08-18)
- **Domínio público**: `https://sisloc.online/profoxtruss` (HTTPS real, DNS aponta
  pra `143.95.219.84`)
- **Servidor**: VPS com **CyberPanel + OpenLiteSpeed** (NÃO é Apache — diferente do
  ambiente local). `openlitespeed` processos rodando como `lshttpd`; painel
  administrativo CyberPanel na porta 8090.
- **Caminho real do projeto**: `/home/sisloc.online/public_html/profoxtruss`
  (segue o padrão CyberPanel `/home/<domínio>/public_html/`, NÃO
  `/var/www/html/...` como no ambiente local).
- **PHP**: múltiplas versões via lsphp em `/usr/local/lsws/lsphp{74,80,81,82,83,84,85}/bin/php`.
  ⚠️ `php -l` (lint) SEGFAULTA nesse servidor (tanto o `php` genérico quanto os
  lsphp — parece ligado ao ionCube Loader instalado) — **não usar `php -l` pra
  validar sintaxe remotamente**; validar localmente antes de enviar, e usar execução
  normal (`php -r "..."`) pra rodar scripts, que funciona sem problema.
- **Banco**: SQLite, mesmo `database/database.sqlite` do projeto local (mesma
  estrutura relacional) — **não** foi migrado pra MySQL apesar do servidor também
  hospedar bancos MySQL de outros sistemas (SisLoc).
- **`.env` remoto**: igual ao local — `APP_ENV=development`, `APP_DEBUG=true` (ainda
  não preparado como "produção" de verdade; ninguém pediu essa troca ainda).
- **Servidor compartilhado**: a mesma máquina hospeda outros sites/sistemas
  completamente não relacionados — `adllocacoes.com.br`, `diamondxsistemas.com.br`,
  e a família SisLoc (`sisloc.online`, `profox.sisloc.online`, `profoxba.sisloc.online`,
  `profoxmg.sisloc.online`, `profoxmt.sisloc.online`, `capital.sisloc.online`,
  `innovar.sisloc.online`, `monitor.sisloc.online`) — **cuidado ao rodar comandos
  amplos/exploratórios via SSH, não mexer fora de `.../profoxtruss/`**.

### Acesso SSH
- `ssh -p 22022 -o IdentitiesOnly=yes -o IdentityAgent=none -i /home/hernesto/.ssh/sisloc_tunnel root@143.95.219.84`
- A chave `sisloc_tunnel` já é usada por um `autossh` de longa duração nesta máquina
  local (toolforward de MySQL pros bancos SisLoc, porta local 13306) — **funciona
  igual pra abrir uma sessão SSH nova** pro mesmo host, sem precisar de senha.
- Uma senha root também foi fornecida uma vez pelo usuário via chat (`sshpass`), mas
  a tentativa por senha travou/foi rejeitada ("Not allowed at this time") — a chave é
  o caminho confiável, preferir sempre ela.
- Não existe `sqlite3` CLI no servidor remoto — pra inspecionar/alterar o banco
  remoto, usar `php -r` com PDO (ex: `php -r '$p = new PDO("sqlite:...");'`).

### Deploy (não existe script — é manual, arquivo por arquivo)
Não há `scripts/deploy.sh` nem qualquer automação de deploy (nem local nem remoto).
Fluxo usado e validado em 2026-08-18 (repetir esse padrão):
1. **Backup no servidor** antes de tocar em qualquer arquivo, num diretório
   `.deploy_backup_AAAAMMDD_HHMMSS[_descrição]/` dentro do próprio
   `.../profoxtruss/` (não em `/tmp`, sobrevive entre sessões SSH):
   ```bash
   ssh ... 'cd .../profoxtruss && mkdir -p .deploy_backup_X && cp arquivo.php .deploy_backup_X/'
   ```
2. **Upload** arquivo por arquivo via `scp`, com o path COMPLETO de destino (nunca
   copiar 2 arquivos com o mesmo basename — ex: `home/index.php` e `pecas/index.php`
   — pra um mesmo diretório flat; um sobrescreve o outro).
3. **Verificar** `md5sum` local vs remoto batendo.
4. **Alterações de banco**: nunca substituir o `database.sqlite` inteiro (haveria
   risco de apagar dado real de produção) — aplicar só `ALTER TABLE`/`INSERT OR
   IGNORE` idempotentes via `php -r` + PDO, do mesmo jeito que seria feito local.
5. **Verificar o site ao vivo**: login real via curl (usuário seed
   `admin@exemplo.com` / `admin123` — ainda é o único usuário cadastrado em
   produção, é basicamente uma instância de demonstração, não tem dado de cliente
   real) + checar página carrega 200 sem `fatal error`/`parse error` no HTML.
6. Sincronizar também os arquivos de `.ai/` relevantes que mudaram (a memória foi
   deployada junto com o código, mora no mesmo diretório remoto).
7. Backup de deploy pode ser apagado depois de confirmar que está tudo funcionando
   (perguntar ao usuário se quer manter ou remover).

## Ambiente Local de Desenvolvimento
**Servidor**: Apache sem virtualhost dedicado.
- DocumentRoot: `/var/www/html`
- Projeto em: `/var/www/html/profoxtruss`
- Máquina local (não o VPS de produção) — IP local `192.168.1.31`, IP público
  `45.180.145.2` (não bate com o IP de produção, são máquinas diferentes).

## Configuração Apache (local)
1. `AllowOverride All` habilitado via `/etc/apache2/conf-available/profoxtruss.conf`
2. `.htaccess` na raiz do projeto redireciona tudo para `public/index.php`
3. `public/index.php` calcula `BASE_PATH` de `$_SERVER['SCRIPT_NAME']` com fallbacks para subdiretório
4. Quirk conhecido: acessar a raiz bare (`/profoxtruss/`) às vezes redireciona
   errado; o caminho canônico confiável é sempre `/profoxtruss/public/...`.

## Ambiente de Desenvolvimento (alternativa sem Apache)
```bash
php -S localhost:8000 -t public/
```

## Scripts Disponíveis
**Nenhum.** `scripts/deploy.sh`, `rollback.sh`, `healthcheck.sh`, `diagnostics.sh`,
`log_scan.sh` — citados em versões antigas deste arquivo — **não existem** no
disco (nem local nem remoto). Não inventar que existem.

## Observações
- `bootstrap.sh` **não existe** (apesar de referenciado em AGENTS.md e CLAUDE.md)
- Não há Docker, CI/CD, git ou containerização configurada em nenhum dos dois
  ambientes
- Backup do banco SQLite deve ser feito manualmente (ver fluxo de deploy acima)

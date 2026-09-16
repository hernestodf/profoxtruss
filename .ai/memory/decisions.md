# Decisões Arquiteturais (ADR)
## [2026-06-09] Resolução de rotas sem virtualhost
- Contexto: Apache DocumentRoot é `/var/www/html`. O projeto está em subdiretório com entry point em `public/index.php`. Sem virtualhost, o `.htaccess` de `public/` nunca era lido.
- Decisão: Criar `.htaccess` na raiz do projeto com rewrite para `public/index.php`, liberar `AllowOverride All` via `/etc/apache2/conf-available/profoxtruss.conf`, e adicionar fallback no `index.php` para extrair a URI correta quando `REQUEST_URI` não contém `/public/`.
- Consequências: URLs sem `/public/` (`/profoxtruss/logout`) e com `/public/` (`/profoxtruss/public/login`) funcionam. Assets estáticos em `public/assets/` continuam servidos diretamente.
- Commits: N/A (config de servidor, não versionado)

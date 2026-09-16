# Stack

- **Linguagens**: PHP 8.4, JavaScript (ES6)
- **Framework Backend**: Nenhum (MVC puro — Router, Layout, Controllers próprios)
- **Frontend Framework**: Alpine.js 3.x (via CDN)
- **CSS**: Tailwind CSS (via CDN), CSS custom properties (tema neon), CSS modular (`@import`)
- **Canvas 2D**: Fabric.js (via CDN) — renderização e manipulação de treliças
- **Visualização 3D**: Three.js (via CDN) — linhas de cota X/Y/Z, etiquetas por peça
- **PDF**: jsPDF (via CDN)
- **Planilhas**: SheetJS / XLSX (via CDN)
- **Banco de Dados**: SQLite via PDO
- **Web Components**: 19+ custom elements em `public/assets/js/components/*.js`
- **Servidor Dev**: PHP Built-in Server (`php -S localhost:8000`)
- **Servidor Prod**: Apache (com `AllowOverride All` + `.htaccess`)
- **Auth**: Sessão PHP com `password_verify` (bcrypt)
- **Autorização**: RBAC caseiro com cache em sessão

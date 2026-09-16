<!DOCTYPE html>
<html lang="pt-BR" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Entrar — ProFoxTruss</title>
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet"/>
    
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'bg-darkest': '#070a13',
                        'bg-dark': '#0b0f19',
                        'bg-card': '#111827',
                        'bg-border': '#1f2937',
                        'neon-cyan': '#06b6d4',
                        'neon-red': '#ef4444'
                    }
                }
            }
        }
    </script>
    
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }
    </style>
</head>
<body class="bg-bg-darkest text-slate-100 flex min-h-screen items-center justify-center p-6 antialiased">

    <!-- Login Card -->
    <div class="w-full max-w-md bg-bg-card/60 backdrop-blur-xl border border-bg-border rounded-3xl p-8 shadow-2xl shadow-neon-cyan/5 flex flex-col gap-6">
        
        <!-- Logo Header -->
        <div class="text-center">
            <div class="inline-flex w-12 h-12 rounded-2xl bg-neon-cyan/15 border border-neon-cyan/30 items-center justify-center text-neon-cyan text-xl font-black mb-3 shadow-md shadow-neon-cyan/10">
                PF
            </div>
            <h1 class="text-2xl font-extrabold tracking-tight text-white">ProFoxTruss</h1>
            <p class="text-xs text-slate-400 mt-1">Insira suas credenciais para acessar a calculadora</p>
        </div>
        
        <!-- Error Alerts -->
        <?php 
        $displayError = '';
        if (!empty($error)) {
            $displayError = $error;
        } elseif (!empty($errors) && is_array($errors)) {
            $displayError = reset($errors);
        }
        if (!empty($displayError)): 
        ?>
            <div class="bg-neon-red/10 border border-neon-red/25 text-neon-red px-4 py-3 rounded-2xl text-xs font-semibold flex items-center gap-2">
                <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                <?= htmlspecialchars($displayError) ?>
            </div>
        <?php endif; ?>
        
        <!-- Login Form -->
        <form method="POST" action="<?= url('/login') ?>" class="flex flex-col gap-4">
            <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
            
            <div>
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2">E-mail corporativo</label>
                <input type="email" name="email" required
                       class="w-full bg-[#0b0f19]/70 border border-bg-border focus:border-neon-cyan focus:outline-none rounded-xl px-4 py-3 text-sm text-white placeholder-slate-500 transition-colors font-medium"
                       placeholder="exemplo@profox.com" />
            </div>
            
            <div>
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2">Senha de acesso</label>
                <input type="password" name="password" required
                       class="w-full bg-[#0b0f19]/70 border border-bg-border focus:border-neon-cyan focus:outline-none rounded-xl px-4 py-3 text-sm text-white placeholder-slate-500 transition-colors font-medium"
                       placeholder="••••••••" />
            </div>
            
            <button type="submit" 
                    class="w-full bg-neon-cyan text-white font-bold rounded-xl py-3 text-sm hover:brightness-110 active:scale-[0.98] transition-all cursor-pointer shadow-lg shadow-neon-cyan/25 mt-2 flex items-center justify-center gap-2">
                Autenticar no Sistema
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
            </button>
        </form>
        
        <!-- Seeding Details Help -->
        <div class="border-t border-bg-border pt-4 text-center">
            <p class="text-[10px] text-slate-500 font-medium">Usuário de demonstração:<br><span class="text-neon-cyan">admin@exemplo.com</span> / <span class="text-neon-cyan">admin123</span></p>
        </div>
        
    </div>

</body>
</html>

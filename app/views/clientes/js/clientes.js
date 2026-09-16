/* app/views/clientes/js/clientes.js
   Erros sempre vão para o console com contexto (função + dado).
   Nunca use alert() para erros — use console.error() e feedback na UI.
*/

// ── Máscara CPF ───────────────────────────────────────────────────────────────

function mascaraCpf(input) {
    input.addEventListener('input', function () {
        try {
            let v = this.value.replace(/\D/g, '').substring(0, 11);

            if (v.length > 9)      v = v.replace(/^(\d{3})(\d{3})(\d{3})(\d{1,2})$/, '$1.$2.$3-$4');
            else if (v.length > 6) v = v.replace(/^(\d{3})(\d{3})(\d{1,3})$/,        '$1.$2.$3');
            else if (v.length > 3) v = v.replace(/^(\d{3})(\d{1,3})$/,               '$1.$2');

            this.value = v;
        } catch (e) {
            console.error('[clientes] mascaraCpf: erro ao formatar', { valor: this.value, erro: e.message });
        }
    });
}

// ── Validação CPF (dígitos verificadores) ─────────────────────────────────────

function cpfValido(cpf) {
    const n = cpf.replace(/\D/g, '');
    if (n.length !== 11 || /^(\d)\1{10}$/.test(n)) return false;

    for (let t = 9; t < 11; t++) {
        let soma = 0;
        for (let i = 0; i < t; i++) soma += parseInt(n[i]) * (t + 1 - i);
        const r = (soma * 10) % 11;
        if (parseInt(n[t]) !== (r > 9 ? 0 : r)) return false;
    }
    return true;
}

// ── Confirmação de exclusão ───────────────────────────────────────────────────

function confirmarDelete(form, nome) {
    if (!confirm(`Excluir "${nome}"?\n\nEsta ação não pode ser desfeita.`)) return false;
    form.submit();
    return true;
}

// ── Toast de feedback ─────────────────────────────────────────────────────────

function exibirToast() {
    try {
        const msgs = {
            criado:     '✓ Cliente criado com sucesso.',
            atualizado: '✓ Cliente atualizado com sucesso.',
            deletado:   '✓ Cliente excluído com sucesso.',
        };

        const ok  = new URLSearchParams(window.location.search).get('ok');
        const msg = msgs[ok];
        if (!msg) return;

        const toast = document.createElement('div');
        toast.className   = 'toast';
        toast.textContent = msg;
        document.body.appendChild(toast);

        setTimeout(() => toast.remove(), 3500);
        window.history.replaceState({}, '', window.location.pathname);
    } catch (e) {
        console.error('[clientes] exibirToast: falhou', e.message);
    }
}

// ── Validação do formulário no submit ─────────────────────────────────────────

function validarForm(form) {
    let valido = true;

    try {
        const cpfInput = form.querySelector('[name="cpf"]');
        const cpfErro  = form.querySelector('#erro-cpf');

        if (!cpfInput) {
            console.warn('[clientes] validarForm: campo cpf não encontrado no form');
            return true;
        }

        if (!cpfValido(cpfInput.value)) {
            if (cpfErro) cpfErro.textContent = 'CPF inválido.';
            cpfInput.classList.add('campo-erro');
            cpfInput.focus();
            console.warn('[clientes] validarForm: CPF inválido digitado', { cpf: cpfInput.value });
            valido = false;
        } else {
            if (cpfErro) cpfErro.textContent = '';
            cpfInput.classList.remove('campo-erro');
        }
    } catch (e) {
        console.error('[clientes] validarForm: erro inesperado', e.message);
    }

    return valido;
}

// ── Init ──────────────────────────────────────────────────────────────────────

document.addEventListener('DOMContentLoaded', function () {
    try {
        document.querySelectorAll('[data-mask="cpf"]').forEach(mascaraCpf);
        exibirToast();

        const form = document.querySelector('.clientes-form');
        if (form) {
            form.addEventListener('submit', function (e) {
                if (!validarForm(this)) {
                    e.preventDefault();
                    console.info('[clientes] submit bloqueado: formulário inválido');
                }
            });
        }
    } catch (e) {
        console.error('[clientes] init: falha na inicialização do módulo', e.message);
    }
});



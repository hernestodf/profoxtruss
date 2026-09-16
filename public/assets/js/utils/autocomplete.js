/**
 * public/assets/js/utils/autocomplete.js
 * Autocomplete genérico — funciona em qualquer campo do projeto.
 *
 * Uso:
 *   autocomplete({
 *     input:     document.getElementById('cliente-busca'),
 *     url:       '/api/clientes/busca',   // recebe ?q=termo
 *     minChars:  2,
 *     onSelect:  (item) => { ... },        // item = objeto retornado pela API
 *   });
 *
 * A API deve retornar: { ok: true, data: [{ id, label, ...resto }] }
 * O campo "label" é o que aparece na lista de sugestões.
 */
function autocomplete({ input, url, minChars = 2, onSelect, onClear }) {
    let debounceTimer = null;
    let activeIndex   = -1;
    let items         = [];

    // ── Cria o dropdown ───────────────────────────────────────────────────────
    const dropdown = document.createElement('ul');
    dropdown.className = 'ac-dropdown';
    dropdown.setAttribute('role', 'listbox');
    input.setAttribute('autocomplete', 'off');
    input.setAttribute('aria-autocomplete', 'list');
    input.parentElement.style.position = 'relative';
    input.parentElement.appendChild(dropdown);

    // ── Helpers ───────────────────────────────────────────────────────────────
    function show(results) {
        items       = results;
        activeIndex = -1;
        dropdown.innerHTML = '';

        if (!results.length) { hide(); return; }

        results.forEach((item, i) => {
            const li = document.createElement('li');
            li.className = 'ac-item';
            li.setAttribute('role', 'option');
            li.textContent = item.label;
            li.addEventListener('mousedown', (e) => {
                e.preventDefault(); // evita blur no input antes do click
                select(i);
            });
            dropdown.appendChild(li);
        });

        dropdown.style.display = 'block';
    }

    function hide() {
        dropdown.style.display = 'none';
        activeIndex = -1;
    }

    function select(i) {
        if (!items[i]) return;
        input.value = items[i].label;
        hide();
        onSelect?.(items[i]);
    }

    function highlight(i) {
        const lis = dropdown.querySelectorAll('.ac-item');
        lis.forEach(li => li.classList.remove('ac-item--active'));
        if (lis[i]) {
            lis[i].classList.add('ac-item--active');
            lis[i].scrollIntoView({ block: 'nearest' });
        }
    }

    // ── Eventos ───────────────────────────────────────────────────────────────
    input.addEventListener('input', () => {
        const q = input.value.trim();

        if (q.length < minChars) {
            hide();
            onClear?.();
            return;
        }

        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(async () => {
            try {
                const { data } = await api.get(`${url}?q=${encodeURIComponent(q)}`);
                show(data);
            } catch {
                hide();
            }
        }, 280); // debounce — não dispara a cada tecla
    });

    input.addEventListener('keydown', (e) => {
        const lis = dropdown.querySelectorAll('.ac-item');
        if (!lis.length) return;

        if (e.key === 'ArrowDown') {
            e.preventDefault();
            activeIndex = Math.min(activeIndex + 1, lis.length - 1);
            highlight(activeIndex);
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            activeIndex = Math.max(activeIndex - 1, 0);
            highlight(activeIndex);
        } else if (e.key === 'Enter' && activeIndex >= 0) {
            e.preventDefault();
            select(activeIndex);
        } else if (e.key === 'Escape') {
            hide();
        }
    });

    input.addEventListener('blur', () => setTimeout(hide, 150));

    // API pública — permite limpar de fora
    return { hide, clear: () => { input.value = ''; hide(); onClear?.(); } };
}



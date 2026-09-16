/**
 * public/assets/js/utils/api.js
 * Wrapper de fetch com convenção de resposta do projeto.
 *
 * Convenção do servidor:
 *   { ok: true,  data: [...] }
 *   { ok: false, error: "mensagem" }
 *
 * Uso:
 *   const { data } = await api.get('/api/produtos/busca?q=note');
 *   const { data } = await api.post('/pedidos', formData);
 */
const api = (() => {
    const BASE = window.BASE_URL ?? '';

    async function request(method, url, body = null) {
        const opts = {
            method,
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
        };

        if (body instanceof FormData) {
            opts.body = body;
        } else if (body !== null) {
            opts.headers['Content-Type'] = 'application/json';
            opts.body = JSON.stringify(body);
        }

        const res  = await fetch(BASE + url, opts);
        const json = await res.json();

        if (!json.ok) throw new Error(json.error ?? 'Erro desconhecido.');
        return json;
    }

    return {
        get:    (url)          => request('GET', url),
        post:   (url, body)    => request('POST', url, body),
        put:    (url, body)    => request('PUT', url, body),
        delete: (url)          => request('DELETE', url),
    };
})();



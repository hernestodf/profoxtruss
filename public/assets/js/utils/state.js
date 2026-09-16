/**
 * public/assets/js/utils/state.js
 * Gerenciador de estado reativo — sem framework, sem dependências.
 *
 * Uso:
 *   const store = createStore({ desconto: 0, itens: [] }, (state) => {
 *       renderTotais(state);   // chamado automaticamente em cada setState
 *   });
 *
 *   store.setState({ desconto: 10 });           // merge parcial
 *   store.setState(s => ({ desconto: s.desconto + 5 })); // updater funcional
 *   const { itens } = store.getState();
 */
function createStore(initialState, onChange) {
    // Cópia profunda para não vazar referências
    let state = structuredClone(initialState);

    return {
        getState() {
            return structuredClone(state);
        },

        setState(patch) {
            const next = typeof patch === 'function' ? patch(state) : patch;
            state = { ...state, ...next };
            onChange?.(structuredClone(state));
        },

        /**
         * Substitui um item numa lista por índice.
         * Uso: store.updateItem('itens', 2, { quantidade: 5 })
         */
        updateItem(listKey, index, patch) {
            const list = [...(state[listKey] ?? [])];
            list[index] = { ...list[index], ...patch };
            this.setState({ [listKey]: list });
        },

        /**
         * Remove um item de uma lista por índice.
         */
        removeItem(listKey, index) {
            const list = (state[listKey] ?? []).filter((_, i) => i !== index);
            this.setState({ [listKey]: list });
        },

        /**
         * Adiciona um item a uma lista.
         */
        addItem(listKey, item) {
            const list = [...(state[listKey] ?? []), item];
            this.setState({ [listKey]: list });
        },
    };
}



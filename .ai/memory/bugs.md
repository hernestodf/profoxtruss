# Histórico de Bugs Resolvidos

---
id: bug-001
type: bug
status: confirmed
confidence: 100
evidence: 1
first_seen: 2026-06-09
last_verified: 2026-06-09
superseded_by: null
tags: [alpine, watcher, memory-leak, crash]
source_commit: ""
---
# TypeError: unwatch is not a function na desinscrição de watcher do Alpine.js
No Alpine.js 3, ao declarar `const unwatch = this.$watch('prop', (val) => { ... unwatch(); })`, a variável `unwatch` é referenciada dentro do seu próprio inicializador (callback). Se o callback for invocado de forma síncrona ou em condições específicas da fila de microtasks do Alpine, a variável ainda não foi atribuída, resultando em erro de referência ou TypeError.
A correção consiste em declarar `let unwatch` previamente e verificar `typeof unwatch === 'function'` antes de invocá-lo dentro do callback.

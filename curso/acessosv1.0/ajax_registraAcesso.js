document.addEventListener('DOMContentLoaded', function () {
    // Dispara o registro assim que a página carregar
    fetch('acessosv1.0/ajax_registraAcesso.php', {
        method: 'POST',
        credentials: 'same-origin',
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
        .then(r => r.json())
        .then(data => {
            // Opcional: console para depurar
            // console.log('Registro de acesso:', data);
        })
        .catch(err => {
            // console.error('Falha ao registrar acesso', err);
        });
});

document.addEventListener('DOMContentLoaded', function () {
    fetch('acessosv1.0/ajax_RegistraUsuario.php', {
        method: 'POST',
        credentials: 'same-origin',
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
        .then(r => r.json())
        .then(data => {
            // console.log('Acesso usuário:', data);
        })
        .catch(err => {
            // console.error('Falha ao registrar acesso de usuário', err);
        });
});

//** REGISTRA URL */

(function () {
    // Evita rodar em iframes indesejados
    if (window.top !== window.self) return;

    // Monta a URL sem domínio: /caminho?query=...
    var pageUrl = window.location.pathname + window.location.search;

    // Não registra páginas vazias
    if (!pageUrl || pageUrl === "/") {
        // Se quiser registrar "/" também, basta remover esta checagem.
        // pageUrl = "/";
    }

    // Envia via fetch (AJAX)
    try {
        fetch('acessosv1.0/ajax_registraHistorico.php', {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/json; charset=utf-8',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ url: pageUrl })
        })
            .then(function (r) { return r.ok ? r.json() : null; })
            .then(function (data) {
                // console.log('Historico:', data);
            })
            .catch(function (e) { /* silencioso */ });
    } catch (e) { /* silencioso */ }
})();
$(document).ready(function () {

    // 📦 Função para obter parâmetros da URL
    function getUrlParam(paramName) {
        const urlParams = new URLSearchParams(window.location.search);
        return urlParams.get(paramName);
    }

    // 🔽 Chamada para obter o IP do visitante
    $.getJSON('https://api.ipify.org?format=json', function (data) {
        const ip = data.ip;                           // IP do usuário
        const site = window.location.href;            // URL completa
        const pagina = window.location.pathname;      // Caminho do arquivo
        const nav = getUrlParam('nav');               // Parâmetro "nav" da URL (caso exista)

        // 📤 Envio dos dados por AJAX
        $.ajax({
            url: "regixv2.0/acessospaginas.php",
            type: "POST",
            data: {
                ip: ip,
                site: site,
                pagina: pagina,
                nav: nav // novo parâmetro incluído
            },
            success: function (response) {
                console.log("Acesso registrado com sucesso.");
            },
            error: function (xhr, status, error) {
                console.error("Erro ao registrar acesso:", error);
            }
        });

    }).fail(function () {
        console.error("Não foi possível obter o IP do visitante.");
    });
});
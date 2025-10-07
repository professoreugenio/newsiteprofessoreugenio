$(document).ready(function () {
    let lmt = ""; // Inicializa a variável
    let seconds = ""; // Inicializa a variável
    // Captura todos os scripts carregados na página
    $("script").each(function () {
        const scriptSrc = $(this).attr("src");
        if (scriptSrc && scriptSrc.includes("config_aulas/JS_licoesCurso.js")) {
            // Extrai os parâmetros da URL
            const url = new URL(scriptSrc, window.location.origin);
            const params = new URLSearchParams(url.search);
            lmt = params.get("lmt"); // Obtém o valor de 'lmt'
            seconds = params.get("sec"); // Obtém o valor de 'lmt'
        }
    });
    console.log("Valor de lmt:", lmt); // Para depuração
    // Carrega o conteúdo na div com o valor de lmt
    setTimeout(function () {
        $("#showListaLicoes")
            .fadeIn(1000)
            .load("config_aulas/ListaLicoescurso.php?lmt=" + lmt);
    }, seconds);

});


function mostrarAulas(tipo) {
    let listaAtuais = document.getElementById("lista-aulas-atuais");
    let listaAnteriores = document.getElementById("lista-aulas-anteriores");
    let btnAtuais = document.getElementById("btnAtuais");
    let btnAnteriores = document.getElementById("btnAnteriores");

    if (tipo === "atuais") {
        listaAtuais.classList.remove("d-none");
        listaAnteriores.classList.add("d-none");

        btnAtuais.classList.add("btn-ativo");
        btnAtuais.classList.remove("btn-inativo");

        btnAnteriores.classList.add("btn-inativo");
        btnAnteriores.classList.remove("btn-ativo");
    } else {
        listaAtuais.classList.add("d-none");
        listaAnteriores.classList.remove("d-none");

        btnAnteriores.classList.add("btn-ativo");
        btnAnteriores.classList.remove("btn-inativo");

        btnAtuais.classList.add("btn-inativo");
        btnAtuais.classList.remove("btn-ativo");
    }
}


$(document).ready(function () {
    $.getJSON('https://api.ipify.org?format=json', function (data) {
        const ip = data.ip;
        const pagina = window.location.href;
        
        $.ajax({
            url: "v2.0/registraacessos.php",
            type: "POST",
            data: {
                ip: ip,
                pagina: pagina
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
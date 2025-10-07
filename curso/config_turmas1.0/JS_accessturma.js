$(document).on("click", "#chaveRegistraturma", function () {
    var CHAVE = $(this).attr("data-id");
    let Ts = $("#ts").val();
    let retorno = $("#retorno").val();

    // Adiciona o modal ao corpo se ainda não existir
    if (!$('#modalLoader').length) {
        $('body').append(`
            <div class="modal fade" id="modalLoader" tabindex="-1" role="dialog" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered" role="document">
                    <div class="modal-content text-white bg-dark text-center p-4">
                        <div class="d-flex flex-column align-items-center">
                            <div class="spinner-border text-light" style="width: 3rem; height: 3rem;color:#00d900" role="status"></div>
                            <h5 class="mt-3">Acessando...</h5>
                        </div>
                    </div>
                </div>
            </div>
        `);
    }

    // Exibe o modal
    $('#modalLoader').modal('show');

    $.ajax({
        url: "config_turmas1.0/accessturma.php?tokenchave=" + CHAVE,
        method: "get",
        success: function (d) {
            setTimeout(function () {
                window.open("modulos.php", "_self");
            }, 1000);
        }
    });
});



// $(document).on("click", "#chaveRegistraturma", function () {
//  var CHAVE = $(this).attr("data-id");
//  let Ts = $("#ts").val();
//  let retorno = $("#retorno").val();
//  $("#loadresult").html(
//   '<div style="background-color:#ffffff" class="alert  text-center centrodapagina" role="alert"> Acessando... <br> <div class="spinner-border" style="border-width: 0.15em;color:#008000;width: 1.2rem; height: 1.2rem;" role="status" role="status"></div> !</div>'
//  );
//  $.ajax({
//   url: "config_turmas1.0/accessturma.php?tokenchave=" + CHAVE,
//   method: "get",
//   success: function (d) {
// //    document.getElementById("viewlista").style.display = "none";

//    setTimeout(function () {
//     window.open("index.php", "_self");
//    }, 1000);
//    // window.open("pagina_login_usuario_turma.php", "_self");
//   },
//  });
// });

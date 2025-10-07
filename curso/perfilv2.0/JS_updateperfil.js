document.getElementById('btnupdate').addEventListener('click', function() {  
    let email = document.getElementById('email').value;
     $.ajax({
        url: "perfilv2.0/updateperfil.php",
        type: "post",
        data: $("#idformupdate").serialize(),
        success: function (d) {
                if (parseInt(d) == 1) {
          mensagem = "Atualizado com sucesso!";
                    cor = "#28a745"; // Verde
                    console.log(mensagem);
          
        } else if (parseInt(d) == 2) {
             mensagem = "Nenhuma alteração detectada.";
                    cor = "#ffc107"; // Amarelo
                    console.log(mensagem);
        } else if (parseInt(d) == 3) {
mensagem = "Senha atualizada com sucesso.";
                    cor = "#28a745"; // Vermelho
                    console.log(mensagem);
                    document.getElementById("senhaatual").value = "";
    document.getElementById("senhanova").value = "";

        } else if (parseInt(d) == 4) {
mensagem = "Erro ao atualizar. Senha atual não confere.";
                    cor = "#ffc107"; // Vermelho
                    console.log(mensagem);
                    document.getElementById("senhaatual").value = "";

        } else if (parseInt(d) == 5) {
mensagem = "Erro ao atualizar. Este E-mail: <br>"+email+"<br> está fora do padrão.";
                    cor = "#ffc107"; // Vermelho
                    console.log(mensagem);
                    document.getElementById("senhaatual").value = "";

        } else {

              mensagem = "Erro ao atualizar. Tente novamente mais tarde.";
                    cor = "#dc3545"; // Vermelho
                    console.log(mensagem);
         }

         mostrarModal(mensagem, cor);

        },
        error: function () {
                mostrarModal("Erro na requisição AJAX.", "#dc3545"); // Vermelho
            }
       });

        function mostrarModal(mensagem, cor) {
        // Remove o modal anterior, se existir
        $("#modalMensagem").remove();

        // Cria o modal dinamicamente
        let modalHTML = `
            

            <div class="modal fade" id="modalMensagem" tabindex="-1" role="dialog" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered" role="document">
                            <div class="modal-content text-white text-center p-4" style="background-color: ${cor};">
                                <h5 class="mt-3"> ${mensagem}</h5>
                                <button type="button" class="btn btn-light mt-3" data-bs-dismiss="modal">Fechar</button>
                            </div>
                        </div>
                    </div>
        `;

        // Adiciona o modal ao corpo da página
        $("body").append(modalHTML);

        // Exibe o modal
        $("#modalMensagem").modal("show");
    }

});


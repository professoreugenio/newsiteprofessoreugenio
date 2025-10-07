      function sair() {
            // Remove modal anterior, se existir
            $("#modalConfirmacao").remove();

            // Cria o modal dinamicamente
            let modalHtml = `
                <!-- Modal Confirmacao -->
<div class="modal fade" id="modalConfirmacao" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content" style="background-color: #0d1b2a; color: #fff; border: 2px solid #023047;">
      
      <!-- Cabeçalho -->
      <div class="modal-header" style="border-bottom: 1px solid #023047;">
        <h5 class="modal-title text-warning">⚠️ Confirmação de Saída</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fechar"></button>
      </div>
      
      <!-- Corpo -->
      <div class="modal-body text-light">
        Você realmente deseja sair?
      </div>
      
      <!-- Rodapé -->
      <div class="modal-footer" style="border-top: 1px solid #023047;">
        <button type="button" class="btn btn-outline-light" data-bs-dismiss="modal">
          Cancelar
        </button>
        <button type="button" class="btn" style="background-color: #fca311; color: #0d1b2a;" id="confirmarSaida">
          Sim, Sair
        </button>
      </div>
    </div>
  </div>
</div>

            `;

            // Adiciona o modal ao body
            $("body").append(modalHtml);

            // Exibe o modal
            let modal = new bootstrap.Modal(document.getElementById("modalConfirmacao"));
            modal.show();

            // Define a ação do botão "Sim, Sair"
            $("#confirmarSaida").on("click", function() {
                sairsessao();
                modal.hide();
            });
        }

        function sairsessao() {
            $.ajax({
                url: "config_default/logoff.php",
                method: "POST",
                success: function() {
                    window.open("../", "_self");
                    window.close();
                }
            });
        }
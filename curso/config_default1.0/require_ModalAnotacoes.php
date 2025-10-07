<!-- Modal fixo no rodapé -->
<!-- Modal Caderno de Anotações -->
<div class="modal fade" id="modalCaderno" tabindex="-1" aria-labelledby="modalCadernoLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg draggable-modal caderno-modal">
    <div class="modal-content">
      <div class="modal-header cursor-move" id="dragHandleCaderno">
        <h5 class="modal-title" id="modalCadernoLabel">
          <i class="bi bi-journal-text me-2"></i> Caderno de anotações
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
      </div>
      <div class="modal-body">
        <textarea id="anotacaoEditor"></textarea>
      </div>
      <div class="modal-footer">
        <button id="btnSalvarAnotacao" class="btn btn-primary btn-salvar">
          <i class="bi bi-save me-2"></i>Salvar
        </button>
      </div>
    </div>
  </div>
</div>


<script>
    document.addEventListener("DOMContentLoaded", function() {
        // Inicializa Summernote
        $('#anotacaoEditor').summernote({
            placeholder: 'Escreva suas anotações...',
            height: 80,
            toolbar: [
                ['style', ['bold', 'italic', 'underline', 'clear']],
                ['para', ['ul', 'ol', 'paragraph']],
                ['insert', ['link', 'picture']],
                ['view', ['undo', 'redo', 'codeview']]
            ]
        });

        // Carregar anotação existente
        fetch('caderno1.0/ajax_carregarAnotacao.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: `iduser=${encodeURIComponent(<?= $codigoUser ?? 0 ?>)}&idartigo=${encodeURIComponent(<?= $codigoaula ?? 0 ?>)}`
            })
            .then(r => r.json())
            .then(json => {
                if (json.sucesso && json.existe) {
                    $('#anotacaoEditor').summernote('code', json.html);
                }
            });

        // Botão de salvar
        document.getElementById('btnSalvarAnotacao').addEventListener('click', function() {
            const html = $('#anotacaoEditor').summernote('code');
            const data = new URLSearchParams();
            data.append('iduser', <?= $codigoUser ?? 0 ?>);
            data.append('idartigo', <?= $codigoaula ?? 0 ?>);
            data.append('anotacao', html);

            fetch('caderno1.0/ajax_salvarAnotacao.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: data.toString()
                })
                .then(r => r.json())
                .then(res => {
                    if (res.sucesso) {
                        alert('Anotação salva com sucesso!');
                    }
                });
        });

        // Fechar modal ao clicar no botão fechar
        document.getElementById('fecharCaderno').addEventListener('click', function() {
            document.getElementById('modalCaderno').style.display = 'none';
        });
    });
</script>
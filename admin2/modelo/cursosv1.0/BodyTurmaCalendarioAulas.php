<div class="card shadow-sm mb-4" data-aos="fade-up">
    <div class="card-body">
        <h5 class="card-title fw-bold mb-3">Calendário de Aulas</h5>

        <form class="row g-3 align-items-end mb-4" id="formCalendario">
            <input type="hidden" name="idturma" id="idturma" value="<?= htmlspecialchars($idTurma) ?>">

            <div class="col-md-3">
                <label for="datainicio" class="form-label">Data Início</label>
                <input type="date" name="datainicio" id="datainicio" value="<?= $datainiciost ?>" class="form-control" required>
            </div>

            <div class="col-md-3">
                <label for="datafim" class="form-label">Data Fim</label>
                <input type="date" name="datafim" id="datafim" value="<?= $datafimst ?>" class="form-control" required>
            </div>

            <div class="col-md-6 d-flex gap-3">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-calendar-event me-1"></i> Carregar Calendário
                </button>

                <button type="button" class="btn btn-danger ms-auto" id="btnExcluirTodos">
                    <i class="bi bi-trash me-1"></i> Excluir Todos os Dias
                </button>
            </div>
        </form>

        <div id="contadorDias" class="fw-semibold mb-3 text-success"></div>

        <div id="containerCalendario" class="d-flex flex-wrap gap-4"></div>
    </div>
</div>


<!-- Container para toast -->
<div id="toastContainer" class="position-fixed top-0 start-50 translate-middle-x p-3" style="z-index: 1080;"></div>

<!-- Estilo do calendário -->
<style>
   
</style>

<!-- Scripts -->
<script>
    $(document).ready(function() {
        // Carregar automaticamente se houver datas preenchidas
        if ($('#datainicio').val() && $('#datafim').val()) {
            carregarCalendario();
        }

        $('#formCalendario').on('submit', function(e) {
            e.preventDefault();
            carregarCalendario();
        });

        function carregarCalendario() {
            const dados = {
                datainicio: $('#datainicio').val(),
                datafim: $('#datafim').val(),
                idturma: $('#idturma').val()
            };

            $.post('cursosv1.0/ajax_calendarioCarregar.php', dados, function(html) {
                $('#containerCalendario').html(html);
                contarDias();
            });
        }

        // Contar dias registrados
        function contarDias() {
            setTimeout(function() {
                const total = $('#containerCalendario .calendario-dia.ativo').length;
                $('#contadorDias').text(`Total de dias registrados: ${total}`);
            }, 200);
        }

        // Clicar em um dia: inserir ou remover
        $(document).on('click', '.calendario-dia', function() {
            const dia = $(this);
            const data = dia.data('data');
            const idturma = $('#idturma').val();

            $.post('cursosv1.0/ajax_calendarioToggleDia.php', {
                data: data,
                idturma: idturma
            }, function(res) {
                dia.toggleClass('ativo');
                contarDias();
                showToast(res.mensagem, res.sucesso ? 'success' : 'danger');
            }, 'json');
        });

        // Excluir todos os dias
        $('#btnExcluirTodos').on('click', function() {
            if (!confirm('Deseja realmente apagar todos os dias registrados desta turma?')) return;

            const idturma = $('#idturma').val();

            $.post('cursosv1.0/ajax_calendarioExcluirTodos.php', {
                idturma: idturma
            }, function(res) {
                if (res.sucesso) {
                    $('#containerCalendario .calendario-dia.ativo').removeClass('ativo');
                    contarDias();
                }
                showToast(res.mensagem, res.sucesso ? 'success' : 'danger');
            }, 'json');
        });

        function showToast(mensagem, tipo) {
            const toastId = 'toast-' + Date.now();
            const html = `
                <div id="${toastId}" class="toast align-items-center text-white bg-${tipo} border-0 shadow" role="alert" aria-live="assertive" aria-atomic="true" data-bs-delay="4000">
                    <div class="d-flex">
                        <div class="toast-body">${mensagem}</div>
                        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Fechar"></button>
                    </div>
                </div>
            `;
            $('#toastContainer').append(html);
            new bootstrap.Toast(document.getElementById(toastId)).show();
        }
    });
</script>
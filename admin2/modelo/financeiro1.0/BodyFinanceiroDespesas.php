<!-- Botões de Acesso -->
<?php require 'financeiro1.0/botoesFinanceiro.php' ?>

<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');
require_once APP_ROOT . '/conexao/class.conexao.php';



// Buscar tipos de RECEITA (tipoLancamentos = 1)
$stmt = $con->prepare("SELECT codigolancamentos, nomelancamentosFL FROM a_curso_financeiroLancamentos WHERE tipoLancamentos = $tipo ORDER BY nomelancamentosFL");
$stmt->execute();
$tipos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container mt-4">

    <?php require 'financeiro1.0/CardSaldoTotal.php'; ?>




    <!-- FORM RECEITA -->
    <h4 class="mb-4 text-success"><i class="bi bi-arrow-down-circle-fill me-2"></i>Lançar <?= ($tipo == 1) ? 'Receitas' : 'Despesas'; ?></h4>

    <?php require 'financeiro1.0/formFinanceiroReceitas.php' ?>
</div>

<!-- TOAST -->
<div id="toastReceita" class="toast position-fixed top-50 start-50 translate-middle-x mt-3" role="alert" data-bs-delay="3000">
    <div class="toast-header bg-success text-white">
        <strong class="me-auto"><i class="bi bi-cash-coin me-2"></i>Financeiro</strong>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast"></button>
    </div>
    <div class="toast-body text-center" id="toastMsg">Receita lançada com sucesso!</div>
</div>

<!-- MÁSCARA E SCRIPT -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>

<script>
    function atualizarSaldo() {
        $.getJSON('financeiro1.0/ajax_FinanceiroSaldoAtual.php', function(data) {
            if (data.sucesso) {
                $('#valorSaldoCard').text(data.valor);
                $('#valorSaldoCard').removeClass('text-success text-danger').addClass(data.classe);
            }
        });
    }

    $('#formReceita').on('submit', function(e) {
        e.preventDefault();
        const formData = $(this).serialize();

        $.post('financeiro1.0/ajax_FinancieroInsertReceitas.php', formData, function(response) {
            const toastEl = document.getElementById('toastReceita');
            $('#toastMsg').text(response.mensagem || 'Erro ao lançar receita.');
            const toast = new bootstrap.Toast(toastEl);
            toast.show();

            if (response.sucesso) {
                $('#formReceita')[0].reset();
                atualizarSaldo(); // <-- Atualiza o saldo automaticamente
            }
        }, 'json');
    });
</script>
<script>
    $(document).ready(function() {
        $('#valor').mask('000.000.000,00', {
            reverse: true
        });


    });
</script>
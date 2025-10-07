<!-- Botões de Acesso -->
<?php require 'financeiro1.0/botoesFinanceiro.php' ?>

<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');
require_once APP_ROOT . '/conexao/class.conexao.php';

// Calcular saldo do mês atual
$anoMesAtual = date('Y-m');
$stmtSaldo = $con->prepare("
    SELECT 
        SUM(CASE WHEN l.tipoLancamentos = 1 THEN f.valorCF ELSE 0 END) AS total_credito,
        SUM(CASE WHEN l.tipoLancamentos = 2 THEN f.valorCF ELSE 0 END) AS total_debito
    FROM a_curso_financeiro f
    INNER JOIN a_curso_financeiroLancamentos l ON f.idLancamentoCF = l.codigolancamentos
    WHERE DATE_FORMAT(f.dataFC, '%Y-%m') = :anoMes
");
$stmtSaldo->bindValue(':anoMes', $anoMesAtual);
$stmtSaldo->execute();
$saldo = $stmtSaldo->fetch(PDO::FETCH_ASSOC);
$credito = floatval($saldo['total_credito']);
$debito = floatval($saldo['total_debito']);
$saldoAtual = $credito - $debito;

// Buscar tipos de RECEITA (tipoLancamentos = 1)
$stmt = $con->prepare("SELECT codigolancamentos, nomelancamentosFL FROM a_curso_financeiroLancamentos WHERE tipoLancamentos = $tipo ORDER BY nomelancamentosFL");
$stmt->execute();
$tipos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container mt-4">

    <!-- CARD SALDO -->
    <!-- Card de saldo fixo no topo esquerdo -->
    <!-- Card de saldo fixo no topo à direita -->
    <!-- Card de saldo fixo no topo à direita, afastado 80px -->
    <div id="cardSaldoFixo" class="position-fixed" style="top: 80px; right: 0; z-index: 1055; width: 260px;">
        <div class="bg-white shadow rounded p-3 border-start border-4">
            <div class="text-center">
                <h6 class="text-secondary mb-1">Saldo Atual (<?= date('m/Y') ?>)</h6>
                <h4 id="valorSaldoCard" class="fw-bold <?= $saldoAtual >= 0 ? 'text-success' : 'text-danger' ?>">
                    R$ <?= number_format($saldoAtual, 2, ',', '.') ?>
                </h4>

            </div>
        </div>
    </div>




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
<?php require 'financeiro1.0/botoesFinanceiro.php'; ?>

<?php
$tipoSelecionado = $_GET['tipo'] ?? 1;

$stmt = $con->prepare("SELECT codigolancamentos, nomelancamentosFL, valorFL FROM a_curso_financeiroLancamentos WHERE tipoLancamentos = :tipo ORDER BY nomelancamentosFL");
$stmt->bindValue(':tipo', $tipoSelecionado, PDO::PARAM_INT);
$stmt->execute();
$tipos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container mt-4">

    <?php require 'financeiro1.0/CardSaldoTotal.php'; ?>
    <!-- Formulário de novo tipo -->
    <form id="formNovoTipo" class="mb-4">
        <div class="row g-2 align-items-end">
            <div class="col-md-5">
                <label class="form-label fw-semibold">Novo tipo de <?= $tipoSelecionado == 1 ? 'Receita' : 'Despesa' ?></label>
                <input type="text" id="novoTipo" name="novoTipo" class="form-control" required placeholder="Ex: Mensalidade, Licença...">
                <input type="hidden" name="tipoLancamentos" value="<?= $tipoSelecionado ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold">Valor padrão (opcional)</label>
                <input type="text" id="valorNovo" name="valorFL" class="form-control text-end" placeholder="0,00">
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-success" id="btnAdicionar">
                    <i class="bi bi-plus-circle"></i> Adicionar
                </button>
            </div>
        </div>
    </form>

    <!-- Botões de alternância -->
    <div class="d-flex justify-content-end gap-2 mb-3">
        <a href="?tipo=1" class="btn <?= $tipoSelecionado == 1 ? 'btn-success' : 'btn-outline-success' ?>">Receitas</a>
        <a href="?tipo=2" class="btn <?= $tipoSelecionado == 2 ? 'btn-danger' : 'btn-outline-danger' ?>">Despesas</a>
    </div>

    <!-- Lista -->
    <ul class="list-group shadow-sm">
        <?php foreach ($tipos as $tp): ?>
            <li class="list-group-item d-flex align-items-center gap-2">
                <input type="text" class="form-control tipoNome" style="flex: 3;" value="<?= htmlspecialchars($tp['nomelancamentosFL']) ?>" data-id="<?= $tp['codigolancamentos'] ?>">
                <?php
                $valorFormatado = is_numeric($tp['valorFL']) ? number_format($tp['valorFL'], 2, ',', '.') : '';
                ?>
                <input type="text" class="form-control text-end tipoValor" style="flex: 1;" value="<?= $valorFormatado ?>" data-id="<?= $tp['codigolancamentos'] ?>">
                <div class="btn-group">
                    <button class="btn btn-outline-primary btnSalvar" data-id="<?= $tp['codigolancamentos'] ?>"><i class="bi bi-save"></i></button>
                    <button class="btn btn-outline-danger btnExcluir" data-id="<?= $tp['codigolancamentos'] ?>"><i class="bi bi-trash"></i></button>
                </div>
            </li>
        <?php endforeach; ?>
    </ul>
</div>

<!-- Toast -->
<!-- Toast genérico -->
<div class="position-fixed top-0 start-50 translate-middle-x p-3" style="z-index: 1080">
    <div id="toastFinanceiro" class="toast align-items-center border-0 shadow" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
            <div class="toast-body fw-semibold" id="toastFinanceiroTexto"></div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    </div>
</div>


<!-- Scripts -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>
<script>
    function showToast(mensagem, cor = 'bg-success') {
        const toast = document.getElementById('toastFinanceiro');
        const texto = document.getElementById('toastFinanceiroTexto');

        toast.className = `toast align-items-center text-white ${cor} border-0 shadow`;
        texto.innerText = mensagem;

        new bootstrap.Toast(toast).show();
    }

    $(document).ready(function() {
        $('.tipoValor, #valorNovo').mask('000.000.000,00', {
            reverse: true
        });

        // Inserção
        $('#formNovoTipo').on('submit', function(e) {
            e.preventDefault();
            const btn = $('#btnAdicionar');
            const original = btn.html();
            btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span>');

            $.post('financeiro1.0/ajax_insertTipoLancamento.php', $(this).serialize(), function(res) {
                if (res.sucesso) {
                    showToast('Tipo adicionado com sucesso!');
                    setTimeout(() => location.reload(), 1500);
                } else {
                    showToast(res.mensagem || 'Erro ao adicionar tipo.', 'bg-danger');
                    btn.prop('disabled', false).html(original);
                }
            }, 'json');
        });

        // Atualização
        $('.btnSalvar').on('click', function() {
            const id = $(this).data('id');
            const linha = $(this).closest('li');
            const nome = linha.find('.tipoNome').val();
            const valor = linha.find('.tipoValor').val();

            const btn = $(this);
            const original = btn.html();
            btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span>');

            $.post('financeiro1.0/ajax_updateTipoLancamento.php', {
                id,
                nome,
                valor
            }, function(res) {
                btn.prop('disabled', false).html(original);
                showToast(res.mensagem || 'Erro ao atualizar.', res.sucesso ? 'bg-success' : 'bg-danger');
            }, 'json');
        });

        // Exclusão
        $('.btnExcluir').on('click', function() {
            if (!confirm('Deseja realmente excluir este tipo?')) return;
            const id = $(this).data('id');

            $.post('financeiro1.0/ajax_deleteTipoLancamento.php', {
                id
            }, function(res) {
                if (res.sucesso) {
                    showToast('Tipo excluído com sucesso!', 'bg-warning');
                    setTimeout(() => location.reload(), 1200);
                } else {
                    showToast(res.mensagem || 'Erro ao excluir.', 'bg-danger');
                }
            }, 'json');
        });
    });
</script>
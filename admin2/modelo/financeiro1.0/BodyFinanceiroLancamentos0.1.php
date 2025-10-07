<?php require 'financeiro1.0/botoesFinanceiro.php' ?>
<?php


// Tipo selecionado: 1 = Receitas, 2 = Despesas
$tipoSelecionado = $_GET['tipo'] ?? 1;

// Buscar lançamentos do tipo selecionado
$stmt = $con->prepare("SELECT codigolancamentos, nomelancamentosFL FROM a_curso_financeiroLancamentos WHERE tipoLancamentos = :tipo ORDER BY nomelancamentosFL");
$stmt->bindValue(':tipo', $tipoSelecionado, PDO::PARAM_INT);
$stmt->execute();
$tipos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container mt-4">

    <!-- Campo para adicionar novo tipo -->
    <form id="formNovoTipo" class="mb-4">
        <div class="row g-2 align-items-end">
            <div class="col-md-6">
                <label for="novoTipo" class="form-label fw-semibold">Novo tipo de <?= $tipoSelecionado == 1 ? 'Receita' : 'Despesa' ?></label>
                <input type="text" id="novoTipo" name="novoTipo" class="form-control" required placeholder="Ex: Venda de Curso, Energia, Licença...">
                <input type="hidden" name="tipoLancamentos" value="<?= $tipoSelecionado ?>">
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-success">
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

    <!-- Lista dos lançamentos -->
    <ul class="list-group shadow-sm">
        <?php foreach ($tipos as $tp): ?>
            <li class="list-group-item d-flex justify-content-between align-items-center">
                <input type="text" class="form-control me-2 tipoNome" value="<?= htmlspecialchars($tp['nomelancamentosFL']) ?>" data-id="<?= $tp['codigolancamentos'] ?>">
                <div class="btn-group">
                    <button class="btn btn-outline-primary btnSalvar" data-id="<?= $tp['codigolancamentos'] ?>"><i class="bi bi-save"></i></button>
                    <button class="btn btn-outline-danger btnExcluir" data-id="<?= $tp['codigolancamentos'] ?>"><i class="bi bi-trash"></i></button>
                </div>
            </li>
        <?php endforeach; ?>
    </ul>
</div>

<script>
    $(document).ready(function() {
        // Adicionar novo tipo
        $('#formNovoTipo').on('submit', function(e) {
            e.preventDefault();
            const dados = $(this).serialize();
            $.post('financeiro1.0/ajax_insertTipoLancamento.php', dados, function(res) {
                if (res.sucesso) {
                    location.reload();
                } else {
                    alert(res.mensagem || 'Erro ao adicionar tipo.');
                }
            }, 'json');
        });

        // Atualizar tipo
        $('.btnSalvar').on('click', function() {
            const id = $(this).data('id');
            const nome = $(this).closest('li').find('.tipoNome').val();
            $.post('financeiro1.0/ajax_updateTipoLancamento.php', {
                id,
                nome
            }, function(res) {
                if (res.sucesso) {
                    alert('Tipo atualizado!');
                } else {
                    alert(res.mensagem || 'Erro ao atualizar.');
                }
            }, 'json');
        });

        // Excluir tipo
        $('.btnExcluir').on('click', function() {
            if (!confirm('Deseja realmente excluir este tipo?')) return;
            const id = $(this).data('id');
            $.post('financeiro1.0/ajax_deleteTipoLancamento.php', {
                id
            }, function(res) {
                if (res.sucesso) {
                    location.reload();
                } else {
                    alert(res.mensagem || 'Erro ao excluir.');
                }
            }, 'json');
        });
    });
</script>
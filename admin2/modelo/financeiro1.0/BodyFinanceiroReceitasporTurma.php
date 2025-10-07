<?php require 'financeiro1.0/botoesFinanceiro.php' ?>
<?php


$mesAtual = $_GET['mes'] ?? date('Y-m');
list($anoFiltro, $mesFiltro) = explode('-', $mesAtual);

// Consulta apenas turmas com aulas no mês
$stmt = $con->prepare("
    SELECT t.codigoturma, t.codcursost, t.nometurma, t.valorhoraaula, t.horasaulast, t.chave,
        COUNT(DISTINCT i.codigousuario) AS qtd_alunos,
        COUNT(DISTINCT d.dataaulactd) AS qtd_aulas
    FROM new_sistema_cursos_turma_data d
    INNER JOIN new_sistema_cursos_turmas t ON t.codigoturma = d.codigoturmactd
    LEFT JOIN new_sistema_inscricao_PJA i ON i.chaveturma = t.chave
    WHERE MONTH(d.dataaulactd) = :mes AND YEAR(d.dataaulactd) = :ano
    GROUP BY t.codigoturma
    ORDER BY t.nometurma
");
$stmt->bindValue(':mes', $mesFiltro, PDO::PARAM_INT);
$stmt->bindValue(':ano', $anoFiltro, PDO::PARAM_INT);
$stmt->execute();
$turmas = $stmt->fetchAll(PDO::FETCH_ASSOC);

$total_geral = 0;
?>

<?php

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

?>

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

<!-- Filtro -->
<form method="get" class="row g-2 align-items-end mb-4">
    <div class="col-auto">
        <label for="mes" class="form-label fw-semibold mb-0">Selecione o Mês:</label>
        <input type="month" id="mes" name="mes" class="form-control" value="<?= htmlspecialchars($mesAtual) ?>">
    </div>
    <div class="col-auto">
        <button type="submit" class="btn btn-primary"><i class="bi bi-search me-1"></i> Filtrar</button>
    </div>
</form>

<!-- Tabela -->
<div class="table-responsive">
    <table class="table table-bordered align-middle text-center">
        <thead class="table-light">
            <tr>
                <th>Turma</th>
                <th>Alunos</th>
                <th>Aulas no mês</th>
                <th>Valor Hora Aula</th>
                <th>Carga Horária</th>
                <th>Média por Aluno</th>
                <th>Total do Mês</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($turmas as $turma):
                $encIdCurso = encrypt($turma['codcursost'], $action = 'e');
                $encIdTurma = encrypt($turma['codigoturma'], $action = 'e');
                $valorHora = floatval($turma['valorhoraaula']);
                $cargaHoraria = floatval($turma['horasaulast']);
                $alunos = intval($turma['qtd_alunos']);
                $aulas = intval($turma['qtd_aulas']);

                $valorMes = $valorHora * $aulas * $cargaHoraria;
                $mediaAluno = ($alunos > 0) ? $valorMes / $alunos : 0;
                $totalMes = $valorMes;

                $total_geral += $totalMes;
            ?>
                <tr>
                    <td class="text-start"><a href="cursos_turmasEditarFinanceiro.php?id=<?= $encIdCurso ?>&tm=<?= $encIdTurma ?>"><?= htmlspecialchars($turma['nometurma']) ?></a></td>
                    <td><?= $alunos ?></td>
                    <td><?= $aulas ?></td>
                    <td>R$ <?= number_format($valorHora, 2, ',', '.') ?></td>
                    <td><?= $cargaHoraria ?>h</td>
                    <td>R$ <?= number_format($mediaAluno, 2, ',', '.') ?></td>
                    <td class="fw-bold text-success">R$ <?= number_format($totalMes, 2, ',', '.') ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr class="table-dark fw-bold">
                <td colspan="6" class="text-end">Total Geral:</td>
                <td>R$ <?= number_format($total_geral, 2, ',', '.') ?></td>
            </tr>
        </tfoot>
    </table>
</div>
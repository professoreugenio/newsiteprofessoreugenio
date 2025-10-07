<div class="mb-3 d-flex justify-content-end">
    <form action="cursosv1.0/exporta_frequencia_excel.php" method="post" target="_blank">
        <input type="hidden" name="chaveturma" value="<?= htmlspecialchars($ChaveTurma) ?>">
        <input type="hidden" name="idturma" value="<?= htmlspecialchars($idTurma) ?>">
        <button type="submit" class="btn btn-success">
            <i class="bi bi-file-earmark-excel-fill me-2"></i> Exportar para Excel
        </button>
    </form>
</div>

<?php
$stmt = config::connect()->prepare("SELECT codigoinscricao,codigousuario,chaveturma,codigocadastro,nome FROM new_sistema_inscricao_PJA, new_sistema_cadastro WHERE chaveturma =:chaveturma AND codigocadastro = codigousuario ORDER BY nome");
$stmt->bindParam(":chaveturma", $ChaveTurma);
$stmt->execute();
?>
<?php if ($stmt->rowCount() > 0): ?>
    <h5 class="mb-4">
        <i class="bi bi-people-fill me-2 text-primary"></i>
        <?= $stmt->rowCount() ?> Aluno(s) para <span class="fw-bold"><?= htmlspecialchars($Nometurma) ?></span>
    </h5>
<?php endif; ?>

<?php
// 1. Buscar datas das aulas
$stmtDatas = config::connect()->prepare("SELECT dataaulactd FROM new_sistema_cursos_turma_data WHERE codigoturmactd = :idturma ORDER BY dataaulactd");
$stmtDatas->bindParam(":idturma", $idTurma);
$stmtDatas->execute();

$datasAulas = [];
$meses = [];
while ($row = $stmtDatas->fetch(PDO::FETCH_ASSOC)) {
    $data = $row['dataaulactd'];
    $mes = date('m/Y', strtotime($data));
    $meses[$mes][] = $data;
}

// 2. Buscar alunos
$stmtAlunos = config::connect()->prepare("SELECT codigocadastro, nome FROM new_sistema_inscricao_PJA, new_sistema_cadastro WHERE chaveturma = :chaveturma AND codigocadastro = codigousuario ORDER BY nome");
$stmtAlunos->bindParam(":chaveturma", $ChaveTurma);
$stmtAlunos->execute();

$alunos = [];
while ($row = $stmtAlunos->fetch(PDO::FETCH_ASSOC)) {
    $alunos[] = [
        'id' => $row['codigocadastro'],
        'nome' => $row['nome']
    ];
}

// 3. Buscar presenças
$presencasPorAluno = [];
foreach ($alunos as $aluno) {
    $stmtPresenca = config::connect()->prepare("SELECT datara FROM a_site_registraacessos WHERE idusuariora = :idaluno AND idturmara = :idturma");
    $stmtPresenca->bindParam(":idaluno", $aluno['id']);
    $stmtPresenca->bindParam(":idturma", $idTurma);
    $stmtPresenca->execute();

    $presencas = [];
    while ($row = $stmtPresenca->fetch(PDO::FETCH_ASSOC)) {
        $presencas[] = date('Y-m-d', strtotime($row['datara']));
    }
    $presencasPorAluno[$aluno['id']] = $presencas;
}

$hoje = date('Y-m-d');
?>

<table class="table table-bordered table-hover align-middle">
    <thead>
        <tr>
            <th rowspan="2" style="min-width:220px;">Aluno</th>
            <?php foreach ($meses as $mes => $diasDoMes): ?>
                <th colspan="<?= count($diasDoMes) ?>" class="text-center"><?= $mes ?></th>
            <?php endforeach; ?>
            <th rowspan="2" class="text-center text-danger">Faltas</th>
        </tr>
        <tr>
            <?php foreach ($meses as $diasDoMes): ?>
                <?php foreach ($diasDoMes as $data): ?>
                    <th class="text-center"><?= date('d', strtotime($data)) ?></th>
                <?php endforeach; ?>
            <?php endforeach; ?>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($alunos as $aluno): ?>
            <tr>
                <td title="<?= htmlspecialchars($aluno['nome']) ?>" style="min-width:220px;">
                    <?php
                    $partes = preg_split('/\s+/', $aluno['nome']);
                    $preposicoes = ['de', 'da', 'do', 'das', 'dos', 'e'];
                    $filtrado = array_filter($partes, fn($p) => !in_array(mb_strtolower($p), $preposicoes));
                    echo htmlspecialchars(reset($filtrado) . ' ' . end($filtrado));
                    ?>
                </td>
                <?php
                $faltas = 0;
                foreach ($meses as $diasDoMes):
                    foreach ($diasDoMes as $dataAula):
                        $dataFormatada = date('Y-m-d', strtotime($dataAula));
                        $passada = $dataFormatada <= $hoje;
                        $presente = in_array($dataFormatada, $presencasPorAluno[$aluno['id']] ?? []);
                ?>
                        <?php if ($passada): ?>
                            <td class="text-center <?= $presente ? 'bg-success text-white' : 'bg-light' ?>">
                                <?= $presente ? date('d', strtotime($dataAula)) : 'X' ?>
                            </td>
                            <?php if (!$presente) $faltas++; ?>
                        <?php else: ?>
                            <td class="text-center"></td>
                        <?php endif; ?>
                <?php endforeach;
                endforeach;
                ?>
                <td class="text-center text-danger fw-bold"><?= $faltas ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
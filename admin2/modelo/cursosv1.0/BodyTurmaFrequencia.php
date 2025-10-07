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

$stmt = config::connect()->prepare("SELECT codigoinscricao,codigousuario,chaveturma,codigocadastro,nome,liberado_sc,data_ins,celular,senha,email FROM  new_sistema_inscricao_PJA,new_sistema_cadastro WHERE chaveturma =:chaveturma AND codigocadastro = codigousuario ORDER BY nome");
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
// 1. Buscar todas as datas das aulas da turma
$stmtDatas = config::connect()->prepare("SELECT dataaulactd FROM new_sistema_cursos_turma_data WHERE codigoturmactd = :idturma ORDER BY dataaulactd");
$stmtDatas->bindParam(":idturma", $idTurma);
$stmtDatas->execute();

$datasAulas = [];
$meses = [];
while ($row = $stmtDatas->fetch(PDO::FETCH_ASSOC)) {
    $data = $row['dataaulactd']; // yyyy-mm-dd
    $mes = date('m/Y', strtotime($data));
    $dia = date('d', strtotime($data));
    $datasAulas[] = [
        'data' => $data,
        'mes' => $mes,
        'dia' => $dia
    ];
    $meses[$mes][] = $data; // para saber a ordem dos meses e dias
}

// 2. Buscar todos os alunos da turma
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

// 3. Para cada aluno, buscar as datas de presença
$presencasPorAluno = [];
foreach ($alunos as $aluno) {
    $stmtPresenca = config::connect()->prepare("SELECT datara FROM a_site_registraacessos WHERE idusuariora = :idaluno AND idturmara = :idturma AND horara < :horara");
    $stmtPresenca->bindParam(":idaluno", $aluno['id']);
    $stmtPresenca->bindParam(":idturma", $idTurma);
    $stmtPresenca->bindParam(":horara", $hora);
    $stmtPresenca->execute();

    $presencas = [];
    while ($row = $stmtPresenca->fetch(PDO::FETCH_ASSOC)) {
        $presencas[] = $row['datara'];
    }
    $presencasPorAluno[$aluno['id']] = $presencas;
}

// 4. Montar tabela HTML: cabeçalho (meses), sub-cabeçalho (dias), linhas (alunos + presença)
?>
<table class="table table-bordered table-hover align-middle">
    <thead>
        <tr>
            <th rowspan="2" style="min-width:220px;">Aluno</th>
            <?php foreach ($meses as $mes => $diasDoMes): ?>
                <th colspan="<?= count($diasDoMes) ?>" class="text-center"><?= $mes ?></th>
            <?php endforeach; ?>
            <th rowspan="2" class="text-center text-success">Presenças</th>
            <th rowspan="2" class="text-center text-danger">Faltas</th>

        </tr>
        <tr>
            <?php $faltas = 0; ?>
            <?php foreach ($meses as $diasDoMes): ?>
                <?php foreach ($diasDoMes as $data): ?>
                    <th class="text-center"><?= date('d', strtotime($data)) ?></th>
                <?php endforeach; ?>
            <?php endforeach; ?>
        </tr>
    </thead>

    <tbody>
        <?php
        $hoje = date('Y-m-d');
        foreach ($alunos as $aluno):
            $totalPresencas = 0;
            $totalAulasPassadas = 0;
        ?>
            <tr>
                <td style="min-width:220px;">
                    <?= htmlspecialchars(implode(' ', array_slice(explode(' ', $aluno['nome']), 0, 3))) ?>
                </td>

                <?php foreach ($meses as $diasDoMes): ?>
                    <?php foreach ($diasDoMes as $dataAula): ?>
                        <?php
                        $dataAulaFormatada = date('Y-m-d', strtotime($dataAula));
                        $isPassada = ($dataAulaFormatada <= $hoje);
                        $presente = in_array($dataAulaFormatada, array_map(
                            fn($d) => date('Y-m-d', strtotime($d)),
                            $presencasPorAluno[$aluno['id']] ?? []
                        ));

                        if ($isPassada) {
                            $totalAulasPassadas++;
                            if ($presente) $totalPresencas++;
                        }
                        ?>
                        <?php if ($isPassada): ?>
                            <td style="cursor: pointer;" class="text-center toggle-presenca <?= $presente ? 'bg-success text-white' : 'bg-light' ?>"
                                data-idaluno="<?= $aluno['id'] ?>"
                                data-data="<?= $dataAulaFormatada ?>"
                                data-idturma="<?= $idTurma ?>"
                                data-presente="<?= $presente ? '1' : '0' ?>">
                                <?= $presente ? date('d', strtotime($dataAula)) : 'X' ?>
                            </td>
                        <?php else: ?>
                            <td class="text-center"></td>
                        <?php endif; ?>
                    <?php endforeach; ?>
                <?php endforeach; ?>

                <!-- Colunas de Totais -->
                <td class="text-center fw-bold text-success"><?= $totalPresencas ?></td>
                <td class="text-center fw-bold text-danger"><?= $totalAulasPassadas - $totalPresencas ?></td>
            </tr>
        <?php endforeach; ?>

    </tbody>
</table>

<!-- Toast de resposta -->
<div class="position-fixed bottom-0 end-0 p-3" style="z-index: 9999">
    <div id="toastResposta" class="toast align-items-center text-white bg-success border-0" role="alert">
        <div class="d-flex">
            <div class="toast-body" id="toastMsg">Presença atualizada</div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        $('.toggle-presenca').on('click', function() {
            const $td = $(this);
            const idAluno = $td.data('idaluno');
            const data = $td.data('data');
            const idTurma = $td.data('idturma');
            const presente = $td.data('presente');

            $.ajax({
                url: 'cursosv1.0/ajax_togglePresenca.php',
                type: 'POST',
                data: {
                    idaluno: idAluno,
                    data: data,
                    idturma: idTurma,
                    presente: presente
                },
                success: function(response) {
                    let toast = new bootstrap.Toast(document.getElementById('toastResposta'));
                    $('#toastMsg').text(response.msg || 'Atualização realizada');
                    $('#toastResposta').removeClass('bg-success-ok bg-danger')
                        .addClass(response.sucesso ? 'bg-success' : 'bg-danger');
                    toast.show();

                    if (response.sucesso) {
                        if (presente == '1') {
                            $td.text('X');
                            $td.removeClass('bg-success-ok text-dark').addClass('bg-light');
                            $td.data('presente', 0);
                        } else {
                            const dia = data.split('-')[2];
                            $td.text(dia);
                            $td.removeClass('bg-light').addClass('bg-success text-dark');
                            $td.data('presente', 1);
                        }
                    }
                },
                error: function() {
                    let toast = new bootstrap.Toast(document.getElementById('toastResposta'));
                    $('#toastMsg').text('Erro ao atualizar presença.');
                    $('#toastResposta').removeClass('bg-success-ok').addClass('bg-danger');
                    toast.show();
                }
            });
        });
    });
</script>
<?php
// Se veio parâmetro ?idAluno=... mostramos o cabeçalho desse aluno
$idAlunoSel = isset($_GET['idUsuario']) ? (string)$_GET['idUsuario'] : 0;
$idAlunoSel = encrypt($idAlunoSel, $action = 'd' );
if ($idAlunoSel > 0) {
    // Consulta dados do aluno
    $sqlAl = "SELECT codigocadastro, nome, pastasc, imagem200, datanascimento_sc, possuipc
              FROM new_sistema_cadastro WHERE codigocadastro = :id LIMIT 1";
    $stAl = $con->prepare($sqlAl);
    $stAl->bindValue(':id', $idAlunoSel, PDO::PARAM_INT);
    $stAl->execute();
    $al = $stAl->fetch(PDO::FETCH_ASSOC);

    if ($al) {
        $nomeAluno = trim($al['nome'] ?? '');
        $fotoAluno = fotoAlunoUrl($al['pastasc'] ?? '', $al['imagem200'] ?? '');
        $idade     = calcIdade($al['datanascimento_sc'] ?? null);
        $nascBr    = dtBr($al['datanascimento_sc'] ?? null);
        $pcTxt     = ((int)($al['possuipc'] ?? 0) === 1 ? 'Sim' : 'Não');

        // total de atendimentos do aluno
        $stCnt = $con->prepare("SELECT COUNT(*) FROM a_aluno_atendimento WHERE idaluno=:id");
        $stCnt->bindValue(':id', $idAlunoSel, PDO::PARAM_INT);
        $stCnt->execute();
        $qtdAt = (int)$stCnt->fetchColumn();

        $idEnc = function_exists('encrypt') ? encrypt((string)$idAlunoSel, 'e') : (string)$idAlunoSel;
?>
        <!-- Cabeçalho com aluno -->
        <div class="card border-0 shadow-sm rounded-4 mb-3">
            <div class="card-body p-3">
                <div class="d-flex align-items-center">
                    <img src="<?= h($fotoAluno) ?>" alt="Foto do aluno" width="64" height="64"
                        class="rounded-circle border shadow-sm me-3" style="object-fit:cover;">
                    <div class="flex-grow-1">
                        <div class="d-flex align-items-center flex-wrap gap-2">
                            <h5 class="mb-0"><?= h($nomeAluno) ?></h5>
                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle">
                                <i class="bi bi-journal-text me-1"></i>
                                <?= $qtdAt ?> atendimento<?= $qtdAt === 1 ? '' : 's' ?>
                            </span>
                        </div>
                        <div class="text-muted small mt-1 d-flex flex-wrap gap-2 align-items-center">
                            <span class="badge bg-light text-dark">
                                <i class="bi bi-cake me-1"></i>
                                Nascimento: <?= h($nascBr) ?><?= $idade !== null ? ' • ' . $idade . ' anos' : '' ?>
                            </span>
                            <span class="badge <?= $pcTxt === 'Sim' ? 'bg-success-subtle text-success border border-success-subtle' : 'bg-secondary-subtle text-secondary border border-secondary-subtle' ?>">
                                <i class="bi bi-pc-display me-1"></i> Possui PC: <strong class="ms-1"><?= h($pcTxt) ?></strong>
                            </span>
                        </div>
                    </div>

                    <!-- Ação: Novo Atendimento -->
                    <div class="ms-3">
                        <a href="alunoAtendimentoNovo.php?idUsuario=<?= urlencode($idEnc) ?>"
                            class="btn btn-success">
                            <i class="bi bi-plus-circle me-1"></i> Novo Atendimento
                        </a>
                    </div>
                </div>
            </div>
        </div>
<?php
    }
}
?>
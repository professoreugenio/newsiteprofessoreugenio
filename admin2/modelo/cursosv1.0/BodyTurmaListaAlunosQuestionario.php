<?php $id = "1"; ?>
<?php $ordem = "1"; ?>
<?php $status = "1"; ?>

<a href="cursos_TurmasAlunosQuestionario.php?id=<?= $_GET['id']; ?>&tm=<?= $_GET['tm']; ?>">Questinários</a>
<?php

$stmt = config::connect()->prepare("SELECT codigoinscricao,codigousuario,chaveturma,codigocadastro,nome,liberado_sc,data_ins FROM  new_sistema_inscricao_PJA,new_sistema_cadastro WHERE chaveturma =:chaveturma AND codigocadastro = codigousuario ORDER BY nome");
$stmt->bindParam(":chaveturma", $ChaveTurma);
$stmt->execute();
?>
<?php if ($stmt->rowCount() > 0): ?>
    <h5><?= $stmt->rowCount() ?> Aluno(s) para : <?= $Nometurma ?></h5>
    <ul class="list-group">
        <?php while ($row = $stmt->fetch(PDO::FETCH_ASSOC)): ?>
            <?php $id = $row['codigoinscricao'];  ?>
            <?php $idaluno = $row['codigousuario'];  ?>
            <?php $idUser = $row['codigocadastro'];  ?>
            <?php $nm = $row['nome'];  ?>
            <?php $status = $row['liberado_sc'];  ?>
            <?php
            $DtInscricao = $row['data_ins'];



            // Calcular diferença
            $dataInscricao = new DateTime($DtInscricao);
            $hoje = new DateTime();
            $diff = $hoje->diff($dataInscricao);
            $tempoTexto = '';
            $badgeClasse = '';

            if ($diff->y >= 1) {
                $tempoTexto = $diff->y . ' ano' . ($diff->y > 1 ? 's' : '');
                $badgeClasse = 'bg-purple'; // customizada
            } elseif ($diff->m >= 1) {
                $tempoTexto = $diff->m . ' mês' . ($diff->m > 1 ? 'es' : '');
                $badgeClasse = 'bg-primary';
            } elseif ($diff->d >= 7) {
                $semanas = floor($diff->d / 7);
                $tempoTexto = $semanas . ' semana' . ($semanas > 1 ? 's' : '');
                $badgeClasse = 'bg-warning text-dark';
            } elseif ($diff->d >= 1) {
                $tempoTexto = $diff->d . ' dia' . ($diff->d > 1 ? 's' : '');
                $badgeClasse = 'bg-success';
            } else {
                $tempoTexto = 'Hoje';
                $badgeClasse = 'bg-success';
            }
            ?>

            <?php
            $queryUltimoAcesso = $con->prepare("SELECT * FROM a_aluno_andamento_aula 
                WHERE idalunoaa = :idusuario AND idturmaaa = :idturma ORDER BY dataaa DESC LIMIT 1 ");
            $queryUltimoAcesso->bindParam(":idusuario", $idUser);
            $queryUltimoAcesso->bindParam(":idturma", $idTurma);
            // Executa a consulta
            $queryUltimoAcesso->execute();
            $rwUltAcesso = $queryUltimoAcesso->fetch(PDO::FETCH_ASSOC);
            $ultimadata   = isset($rwUltAcesso['dataaa'])    ? databr($rwUltAcesso['dataaa'])    : 'Sem registro';
            $ultihorai   = isset($rwUltAcesso['horaaa'])    ? horabr($rwUltAcesso['horaaa'])    : 'Sem registro';
            ?>

            <?php
                $stmtc = config::connect()->prepare("
                SELECT * FROM a_curso_questionario_resposta
                WHERE idalunoqr = :idaluno
                ");
                $stmtc->bindParam(":idaluno", $idaluno);

                $stmtc->execute();
                $contresp = $stmtc->rowCount(); ?>
            <li class="list-group-item flex-column mb-2 shadow-sm rounded">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-mortarboard me-2 text-primary fs-5"></i>
                        <a href="cursos_modulosEditar.php?id=<?= $_GET['id']; ?>&md=<?= $id; ?>" class="text-decoration-none fw-semibold text-dark me-3">

                            <?= $nm; ?>
                            <span style="width:80px" class="badge <?= $badgeClasse ?> ms-2"><?= $contresp; ?></span>
                            
                        </a>

                    </div>
                    <div class="d-flex align-items-center">
                        <span class="badge bg-secondary rounded-pill me-3">0</span>
                        <span data-bs-toggle="tooltip" title="menu">
                            <i class="bi bi-globe2 fs-5 <?= $status == 1 ? 'text-success' : 'text-danger'; ?>"></i>
                        </span>
                    </div>
                </div>

            </li>

        <?php endwhile; ?>

    </ul>
<?php else: ?>
    <h5>Nenhum aluno cadastrado para : <?= $Nometurma ?></h5>
<?php endif; ?>
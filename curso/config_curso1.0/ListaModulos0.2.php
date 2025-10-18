<?php
// 1) Buscar o último módulo acessado pelo aluno na turma (global)
$queryUltimoGeral = $con->prepare("
    SELECT idmoduloaa 
    FROM a_aluno_andamento_aula
    WHERE idalunoaa = :idusuario AND idturmaaa = :idturma
    ORDER BY dataaa DESC, horaaa DESC
    LIMIT 1
");
$queryUltimoGeral->bindParam(":idusuario", $idUser, PDO::PARAM_INT);
$queryUltimoGeral->bindParam(":idturma", $idTurma, PDO::PARAM_INT);
$queryUltimoGeral->execute();
$moduloAtualId = (int)($queryUltimoGeral->fetchColumn() ?? 0);

// 2) Buscar os módulos visíveis do curso
$queryModulos = $con->prepare("
    SELECT codigomodulos, modulo 
    FROM new_sistema_modulos_PJA 
    WHERE codcursos = :id AND visivelm = '1' 
    ORDER BY ordemm
");
$queryModulos->bindParam(":id", $idCurso, PDO::PARAM_INT);
$queryModulos->execute();
$modulos = $queryModulos->fetchAll(PDO::FETCH_ASSOC);

// 3) Renderizar APENAS botões com os nomes (links)
?>
<div class="d-flex flex-wrap gap-2" id="lista-modulos">
    <?php foreach ($modulos as $modulo):
        $idModulo = (int)$modulo['codigomodulos'];
        $nomeModulo = trim((string)$modulo['modulo']);
        $enc = encrypt("$idUser&$idCurso&$idTurma&$idModulo", 'e');

        // Destacar o último módulo acessado (opcional)
        $isAtual = ($idModulo === $moduloAtualId);
        $classeBtn = $isAtual ? 'btn-primary' : 'btn-outline-primary';
    ?>
        <a href="actionCurso.php?mdl=<?= $enc ?>"
            class="btn <?= $classeBtn ?> btn-sm"
            title="Ir para: <?= htmlspecialchars($nomeModulo) ?>">
            <i class="bi bi-journal-text me-1"></i><?= htmlspecialchars($nomeModulo) ?>
        </a>
    <?php endforeach; ?>
</div>
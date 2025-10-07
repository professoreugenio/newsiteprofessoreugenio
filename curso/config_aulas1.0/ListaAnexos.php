<?php
if (empty($codigocurso)) {
    echo "<div class='alert alert-warning'>Curso inválido.</div>";
    return;
}

// Consulta publicações do curso
$queryPub = $con->prepare("
    SELECT idpublicacaopc 
    FROM a_aluno_publicacoes_cursos 
    WHERE idcursopc = :curso
");
$queryPub->bindParam(':curso', $codigocurso);
$queryPub->execute();
$publicacoes = $queryPub->fetchAll(PDO::FETCH_COLUMN);

if (empty($publicacoes)) {
    echo "<div class='alert alert-info text-light bg-dark'>Nenhum anexo disponível para este curso.</div>";
    return;
}

// Consulta anexos das publicações
$placeholders = implode(',', array_fill(0, count($publicacoes), '?'));
$queryAnexos = $con->prepare("
    SELECT titulopa, urlpa, anexopa, extpa, pastapa
    FROM new_sistema_publicacoes_anexos_PJA 
    WHERE codpublicacao IN ($placeholders)
    ORDER BY extpa, titulopa
");
$queryAnexos->execute($publicacoes);
$anexos = $queryAnexos->fetchAll();

if (empty($anexos)) {
    echo "<div class='alert alert-info text-light bg-dark'>Nenhum anexo disponível para este curso.</div>";
    return;
}
?>

<div class="row">
    <?php foreach ($anexos as $key => $anexo):
        $ext = htmlspecialchars(strtolower($anexo['extpa']));
        $isDrive = str_contains($anexo['urlpa'], 'https://drive.google.');

        if ($isDrive) {
            $icone = 'bi bi-google text-warning'; // ou substitua por SVG do Google Drive
            $ext="Google Drive";
        } else {
            $icone = match ($ext) {
                'pdf' => 'bi bi-file-earmark-pdf-fill text-danger',
                'doc', 'docx' => 'bi bi-file-earmark-word-fill text-primary',
                'xls', 'xlsx' => 'bi bi-file-earmark-excel-fill text-success',
                'ppt', 'pptx' => 'bi bi-file-earmark-ppt-fill text-warning',
                'zip', 'rar' => 'bi bi-file-earmark-zip-fill text-secondary',
                'jpg', 'jpeg', 'png', 'gif' => 'bi bi-file-earmark-image-fill text-info',
                default => 'bi bi-file-earmark-fill text-warning'
            };
        }

        $num = $key + 1;
        $link = ($anexo['urlpa'] == "#")
            ? "../anexos/publicacoes/{$anexo['pastapa']}/{$anexo['anexopa']}"
            : $anexo['urlpa'];
    ?>
        <div class="col-md-6 mb-3">
            <div class="d-flex align-items-center justify-content-between border border-secondary rounded bg-dark text-light p-3 shadow-sm">
                <div class="d-flex align-items-center">
                    <i class="<?= $icone ?> fs-3 me-3" aria-hidden="true"></i>
                    <div class="flex-grow-1">
                        <div class="fw-semibold">
                            <span style="color: #ff9428;">Anexo <?= $num ?>:</span><br>
                            <?= htmlspecialchars($anexo['titulopa']) ?> (<?= strtoupper($ext) ?>)
                        </div>
                    </div>
                </div>
                <a href="<?= htmlspecialchars($link) ?>"
                    title="Clique para baixar <?= htmlspecialchars($anexo['titulopa']) ?>"
                    target="_blank"
                    <?= $isDrive ? '' : 'download' ?>
                    aria-label="Baixar <?= htmlspecialchars($anexo['titulopa']) ?>"
                    class="text-light text-decoration-none ms-3">
                    <span class="d-inline-flex align-items-center justify-content-center rounded-circle bg-secondary"
                        style="width: 36px; height: 36px;">
                        <i class="bi bi-download text-light fs-5"></i>
                    </span>
                </a>
            </div>
        </div>
    <?php endforeach; ?>
</div>
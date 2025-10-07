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

<?php
// ... sua consulta continua aqui (mesma lógica anterior)

// Mapeia extensões encontradas para gerar botões
$extensoesUnicas = [];
foreach ($anexos as $anexo) {
    $ext = strtolower($anexo['extpa']);
    $extensoesUnicas[$ext] = match ($ext) {
        'pdf' => ['bi bi-file-earmark-pdf-fill text-danger', 'PDF'],
        'doc', 'docx' => ['bi bi-file-earmark-word-fill text-primary', 'Word'],
        'xls', 'xlsx' => ['bi bi-file-earmark-excel-fill text-success', 'Excel'],
        'ppt', 'pptx' => ['bi bi-file-earmark-ppt-fill text-warning', 'PPT'],
        'zip', 'rar' => ['bi bi-file-earmark-zip-fill text-secondary', 'ZIP'],
        'jpg', 'jpeg', 'png', 'gif' => ['bi bi-file-earmark-image-fill text-info', 'Imagem'],
        default => ['bi bi-file-earmark-fill text-muted', strtoupper($ext)]
    };
}
?>

<!-- Botões de filtro -->
<div class="mb-4 d-flex flex-wrap gap-2">
    <button class="btn btn-outline-light btn-sm filter-btn active" data-ext="all">
        <i class="bi bi-asterisk me-1"></i> Todos
    </button>
    <?php foreach ($extensoesUnicas as $ext => [$icone, $label]): ?>
        <button class="btn btn-outline-light btn-sm filter-btn" data-ext="<?= $ext ?>">
            <i class="<?= $icone ?> me-1"></i> <?= $label ?>
        </button>
    <?php endforeach; ?>
</div>

<!-- Lista de anexos -->
<div class="row" id="anexo-container">
    <?php foreach ($anexos as $key => $anexo):
        $ext = strtolower($anexo['extpa']);
        $isDrive = str_contains($anexo['urlpa'], 'https://drive.google.');
        $icone = $isDrive
            ? 'bi bi-google text-warning'
            : match ($ext) {
                'pdf' => 'bi bi-file-earmark-pdf-fill text-danger',
                'doc', 'docx' => 'bi bi-file-earmark-word-fill text-primary',
                'xls', 'xlsx' => 'bi bi-file-earmark-excel-fill text-success',
                'ppt', 'pptx' => 'bi bi-file-earmark-ppt-fill text-warning',
                'zip', 'rar' => 'bi bi-file-earmark-zip-fill text-secondary',
                'jpg', 'jpeg', 'png', 'gif' => 'bi bi-file-earmark-image-fill text-info',
                default => 'bi bi-file-earmark-fill text-warning'
            };
        $num = $key + 1;
        $link = ($anexo['urlpa'] == "#")
            ? "../anexos/publicacoes/{$anexo['pastapa']}/{$anexo['anexopa']}"
            : $anexo['urlpa'];
    ?>
        <div class="col-md-6 mb-3 anexo-item" data-ext="<?= $ext ?>">
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
                    class="text-light text-decoration-none ms-3"
                    aria-label="Baixar <?= htmlspecialchars($anexo['titulopa']) ?>">
                    <span class="d-inline-flex align-items-center justify-content-center rounded-circle bg-secondary"
                        style="width: 36px; height: 36px;">
                        <i class="bi bi-download text-light fs-5"></i>
                    </span>
                </a>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<!-- JS de filtragem -->
<script>
    document.querySelectorAll('.filter-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
            this.classList.add('active');

            const ext = this.dataset.ext;
            document.querySelectorAll('.anexo-item').forEach(el => {
                el.style.display = (ext === 'all' || el.dataset.ext === ext) ? 'block' : 'none';
            });
        });
    });
</script>
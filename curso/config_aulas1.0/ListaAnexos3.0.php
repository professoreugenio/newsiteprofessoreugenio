<div class="d-flex flex-column align-items-center w-100" style="max-width:1024px; margin: 0 auto;">

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
    $idpub = $publicacoes['idpublicacaopc'] ?? '';



    // Consulta anexos das publicações
    $placeholders = implode(',', array_fill(0, count($publicacoes), '?'));
    $queryAnexos = $con->prepare("
    SELECT titulopa, urlpa, anexopa, extpa, pastapa
    FROM new_sistema_publicacoes_anexos_PJA 
    WHERE codpublicacao IN ($placeholders)
    ORDER BY titulopa
");
    $queryAnexos->execute($publicacoes);
    $anexos = $queryAnexos->fetchAll();
    if (empty($anexos)) {
        echo "<div class='alert alert-info text-light bg-dark'>Nenhum anexo disponível para este curso.</div>";
        return;
    }
    ?>
    <?php require_once 'config_default1.0/funcaoIcones.php'; ?>
    <?php
    $iconDrive = getCustomIcon('drive', "20");
    $iconPbix = getCustomIcon('pbix', '20');
    $iconExcel = getCustomIcon('xlsx', '20');
    ?>
    <?php
    // Organiza extensões encontradas
    $extensoesContadas = [];
    foreach ($anexos as $anexo) {
        $ext = strtolower($anexo['extpa']);
        $isDrive = str_contains($anexo['urlpa'], 'google.com');
        if ($isDrive) {
            $ext = 'google';
        }
        $extensoesContadas[$ext]['count'] = ($extensoesContadas[$ext]['count'] ?? 0) + 1;
        $extensoesContadas[$ext]['icone'] = match ($ext) {

            'pbix' => [$iconPbix, 'PwBI'],
            'xlsx', 'xls' => [$iconExcel, 'Excel'],
            'pdf' => ['<i class="bi bi-file-earmark-pdf-fill text-danger"></i>', 'PDF'],
            'json' => ['<i class="bi bi-filetype-json text-warning"></i>', 'JSON'],
            'doc', 'docx' => ['<i class="bi bi-file-earmark-word-fill text-primary"></i>', 'Word'],
            'ppt', 'pptx' => ['<i class="bi bi-file-earmark-ppt-fill text-warning"></i>', 'PPT'],
            'zip', 'rar' => ['<i class="bi bi-file-earmark-zip-fill text-secondary"></i>', 'ZIP'],
            'jpg', 'jpeg' => ['<i class="bi bi-file-earmark-image-fill text-info"></i>', 'IMG'],
            'png' => ['<i class="bi bi-file-earmark-image-fill text-info"></i>', 'IMG png'],
            'google' => [$iconDrive, 'Drive'],
            default => ['<i class="bi bi-file-earmark-fill text-warning"></i>', strtoupper($ext)]
        };
    }
    ?>
    <!-- Botões de filtro -->
    <div class="mb-4 d-flex flex-wrap gap-2">
        <button class="btn btn-outline-light btn-sm filter-btn active" data-ext="all">
            <i class="bi bi-asterisk me-1"></i> Todos (<?= count($anexos) ?>)
        </button>
        <?php foreach ($extensoesContadas as $ext => $data):
            [$icone, $label] = $data['icone'];
            $count = $data['count'];
        ?>
            <button class="btn btn-outline-light btn-sm filter-btn" data-ext="<?= $ext ?>">
                <?= $icone ?> <?= $label ?> (<?= $count ?>)
            </button>
        <?php endforeach; ?>
    </div>
    <!-- Lista de anexos -->
    <div class="row" id="anexo-container">
        <?php foreach ($anexos as $key => $anexo):
            $ext = strtolower($anexo['extpa']);
            $isDrive = str_contains($anexo['urlpa'], 'google.com');
            $isPwbiext = str_contains($anexo['extpa'], 'pbix');
            $isExcelext = str_contains($anexo['extpa'], 'xlsx');
            $isPwbiUrl = str_contains($anexo['urlpa'], 'https://app.powerbi.com');
            $num = $key + 1;
            $link = ($anexo['urlpa'] == "#")
                ? "../anexos/publicacoes/{$anexo['pastapa']}/{$anexo['anexopa']}"
                : $anexo['urlpa'];

            $extimg = strtolower(pathinfo($anexo['anexopa'], PATHINFO_EXTENSION)); // pega a extensão real do arquivo

            $extensoesImagem = ['jpg', 'jpeg', 'png', 'gif'];

            $linkimg = in_array($extimg, $extensoesImagem)
                ? "../anexos/publicacoes/{$anexo['pastapa']}/{$anexo['anexopa']}"
                : $anexo['urlpa'];

            $iconDrive = getCustomIcon('drive', "45");
            $iconPbix = getCustomIcon('pbix', '45');
            $iconExcel = getCustomIcon('xlsx', '45');
            // Ícone: Google Drive ou conforme extensão
            if ($isDrive) {
                $icone = $iconDrive;
                $ext = "google";
            } elseif ($isPwbiext) {
                $icone = $iconPbix;
            } elseif ($isPwbiUrl) {
                $icone = $iconPbix;
            } elseif (in_array($ext, ['jpg', 'jpeg', 'png', 'gif'])) {
                $icone = '<a href="' . htmlspecialchars($linkimg) . '" data-lightbox="anexos" data-title="' . htmlspecialchars($anexo['titulopa']) . '" class="anexo-lightbox">
    <img src="' . htmlspecialchars($linkimg) . '" alt="anexo" class="rounded shadow-sm" style="width: 45px; height: 45px; object-fit: cover;">
</a>';
            } elseif ($isExcelext) {
                $icone = $iconExcel;
            } else {
                $icone = '<i class="' . match ($ext) {
                    'pdf' => 'bi bi-file-earmark-pdf-fill text-danger',
                    'doc', 'docx' => 'bi bi-file-earmark-word-fill text-primary',
                    'json' => 'bi bi-filetype-json text-warning',
                    'ppt', 'pptx' => 'bi bi-file-earmark-ppt-fill text-warning',
                    'zip', 'rar' => 'bi bi-file-earmark-zip-fill text-secondary',
                    'jpg', 'jpeg', 'png', 'gif' => 'bi bi-file-earmark-image-fill text-info',
                    default => 'bi bi-file-earmark-fill text-warning'
                } . ' fs-3" aria-hidden="true"></i>';
            }
        ?>
            <div class="col-md-6 mb-3 anexo-item" data-ext="<?= $ext ?>">
                <div class="d-flex align-items-center justify-content-between border border-secondary rounded bg-dark text-light p-3 shadow-sm">
                    <div class="d-flex align-items-center">
                        <div class="me-3"><?= $icone ?></div>
                        <div class="flex-grow-1">
                            <span style="color: #ff9428;">Anexo <?= $num ?>:</span><br>
                            <?php if (!empty($codigoUser) && $codigoUser == 1): ?>
                                <div class="input-group">
                                    <input type="text" class="form-control form-control-sm bg-dark text-light border-secondary rounded-start"
                                        value="<?= htmlspecialchars($anexo['titulopa']) ?>"
                                        data-old="<?= htmlspecialchars($anexo['titulopa']) ?>"
                                        data-index="<?= $key ?>" id="inputTitulo<?= $key ?>">
                                    <button class="btn btn-outline-warning btn-sm btnUpdateTitulo"
                                        data-index="<?= $key ?>"
                                        data-old="<?= htmlspecialchars($anexo['titulopa']) ?>">
                                        <i class="bi bi-arrow-repeat me-1"></i> Update
                                    </button>
                                </div>
                            <?php else: ?>
                                <div class="fw-semibold">
                                    <span style="color: #ff9428;">Anexo <?= $num ?>:</span><br>
                                    <?= htmlspecialchars($anexo['titulopa']) ?> (<?= strtoupper($ext) ?>)
                                </div>
                            <?php endif; ?>
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
    <!-- Script de filtragem -->
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

</div>



<script>
    document.querySelectorAll('.btnUpdateTitulo').forEach(btn => {
        btn.addEventListener('click', function() {
            const index = this.dataset.index;
            const input = document.querySelector('#inputTitulo' + index);
            const titulo = input.value.trim();
            const oldTitulo = input.dataset.old;

            if (titulo === '') {
                alert('Título não pode estar vazio.');
                return;
            }

            const originalHTML = this.innerHTML;
            this.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>';
            this.disabled = true;

            fetch('config_aulas1.0/ajax_updateTituloAnexos.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: new URLSearchParams({
                        titulo,
                        oldTitulo
                    })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.status === 'ok') {
                        input.dataset.old = titulo;
                    }
                    alert(data.mensagem);
                })
                .catch(() => alert('Erro ao enviar requisição.'))
                .finally(() => {
                    this.innerHTML = originalHTML;
                    this.disabled = false;
                });
        });
    });
</script>
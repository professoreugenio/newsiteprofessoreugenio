<?php
// === Capturas ===


if ($idPublicacao <= 0) {
    echo '<div class="alert alert-warning">Publicação não identificada.</div>';
    return;
}

// Dirs de upload
$dir0 = "../../../anexos";
$dir1 = $dir0 . "/publicacoes";

// Função mini-ícone/thumbnail
function iconForExt($ext, $webPath)
{
    $ext = strtolower(ltrim($ext, '.'));
    $imgExts = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    if (in_array($ext, $imgExts) && $webPath) {
        return '<img src="' . htmlspecialchars($webPath) . '" class="border rounded" style="width:48px;height:48px;object-fit:cover" alt="anexo">';
    }
    // ícones simples por extensão
    $map = [
        'pdf' => 'bi-filetype-pdf',
        'doc' => 'bi-filetype-doc',
        'docx' => 'bi-filetype-docx',
        'xls' => 'bi-filetype-xls',
        'xlsx' => 'bi-filetype-xlsx',
        'ppt' => 'bi-filetype-ppt',
        'pptx' => 'bi-filetype-pptx',
        'pps' => 'bi-filetype-ppt',
        'ppsx' => 'bi-filetype-pptx',
        'txt' => 'bi-filetype-txt',
        'zip' => 'bi-file-zip',
        'rar' => 'bi-file-zip',
        'json' => 'bi-filetype-json',
        'js' => 'bi-filetype-js',
        'html' => 'bi-filetype-html',
        'php' => 'bi-filetype-php',
        'ai' => 'bi-file-earmark-image',
        'eps' => 'bi-file-earmark-image',
        'psd' => 'bi-file-earmark-image',
        'cdr' => 'bi-file-earmark-image',
        'otf' => 'bi-filetype-otf',
        'ttf' => 'bi-filetype-ttf',
        'pbix' => 'bi-file-earmark-bar-graph',
        'bat' => 'bi-terminal'
    ];
    $icon = $map[$ext] ?? 'bi-file-earmark';
    return '<i class="bi ' . $icon . ' fs-3 text-secondary"></i>';
}

// Carrega lista
$stmt = $con->prepare("SELECT codigomanexos, codpublicacao, idmodulo_pa, titulopa, urlpa, anexopa, extpa, sizepa, pastapa, visivel, tipo, datapa, horapa
    FROM new_sistema_publicacoes_anexos_PJA
    WHERE codpublicacao = :pub 
    ORDER BY codigomanexos DESC");
$stmt->execute([':pub' => $idPublicacao]);
$anexos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Pasta web helper
function webPath($row)
{
    if (!empty($row['anexopa']) && !empty($row['pastapa'])) {
        return "/anexos/publicacoes/" . rawurlencode($row['pastapa']) . "/" . rawurlencode($row['anexopa']);
    }
    return "";
}

// Data hoje para destaque
$hoje = date('Y-m-d');
?>

<div class="card shadow-sm border-0">
    <div class="card-header bg-white">
        <h5 class="mb-0">Anexos da Publicação: <span style="color:#0080c0"><?= $tituloPublicacao ?></span></h5>
        <?= $idPublicacao ?> -
        <?= $idModuloPublicacao ?> -
        <?= $pastapub ?>
    </div>
    <div class="card-body">

        <!-- Form URL -->
        <form id="formUrl" class="row g-2 mb-4">
            <input type="hidden" name="codpublicacao" value="<?= $idPublicacao ?>">
            <input type="hidden" name="idmodulo" value="<?= $idModuloPublicacao ?>">
            <input type="hidden" name="pastapub" value="<?= $pastapub ?>">
            <div class="col-md-5">
                <label class="form-label">URL do anexo</label>
                <input type="url" name="urlpa" class="form-control" placeholder="https://..." required>
            </div>
            <div class="col-md-5">
                <label class="form-label">Título</label>
                <input type="text" name="titulopa" class="form-control" placeholder="Título do material" required>
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100"><i class="bi bi-plus-circle me-1"></i> Inserir URL</button>
            </div>
        </form>

        <!-- Form Upload -->
        <form id="formUpload" class="row g-2 mb-4" enctype="multipart/form-data">
            <input type="hidden" name="codpublicacao" value="<?= $idPublicacao ?>">
            <input type="hidden" name="idmodulo" value="<?= $idModuloPublicacao ?>">
            <div class="col-md-5">
                <label class="form-label">Arquivo</label>
                <input type="file" name="arquivo" class="form-control" id="arquivo" required>
                <small class="text-muted">Extensões permitidas: xlsx, docx, pdf, doc, xls, pptx, ppsx, ppt, pps, txt, otf, ttf, jpg, png, jpeg, psd, cdr, eps, ai, html, php, js, rar, zip, pbix, bat, json</small>
            </div>
            <div class="col-md-5">
                <label class="form-label">Título</label>
                <input type="text" name="titulopa" class="form-control" placeholder="Título do material" required>
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-success w-100"><i class="bi bi-upload me-1"></i> Enviar</button>
            </div>
        </form>

        <!-- Ações em massa -->
        <div class="d-flex gap-2 mb-2">
            <button id="btnMarcarTodos" class="btn btn-outline-secondary btn-sm"><i class="bi bi-check2-square me-1"></i> Marcar todos</button>
            <button id="btnDesmarcarTodos" class="btn btn-outline-secondary btn-sm"><i class="bi bi-square me-1"></i> Desmarcar todos</button>
            <button id="btnAplicarVis" class="btn btn-outline-primary btn-sm"><i class="bi bi-eye me-1"></i> Aplicar visibilidade</button>
        </div>

        <!-- Lista -->
        <ul class="list-group" id="listaAnexos">
            <?php if (empty($anexos)): ?>
                <li class="list-group-item text-center text-muted">Nenhum anexo cadastrado.</li>
                <?php else: foreach ($anexos as $ax):
                    $web = webPath($ax);
                    $thumb = iconForExt($ax['extpa'] ?: pathinfo($ax['anexopa'] ?? '', PATHINFO_EXTENSION), $web);
                    $destacar = (substr($ax['datapa'] ?? '', 0, 10) === $hoje) ? 'bg-info bg-opacity-25 border-info' : '';
                ?>
                    <li class="list-group-item d-flex align-items-center justify-content-between <?= $destacar ?>" id="ax_<?= $ax['codigomanexos'] ?>">
                        <div class="d-flex align-items-center gap-3">
                            <div><?= $thumb ?></div>
                            <div>
                                <input type="text" class="form-control form-control-sm titulo-ax" data-id="<?= $ax['codigomanexos'] ?>" value="<?= htmlspecialchars($ax['titulopa']) ?>">
                                <?php if (!empty($ax['urlpa'])): ?>
                                    <a href="<?= htmlspecialchars($ax['urlpa']) ?>" target="_blank" class="small text-muted d-inline-block mt-1"><i class="bi bi-link-45deg"></i> Abrir URL</a>
                                <?php elseif ($web): ?>
                                    <a href="<?= htmlspecialchars($web) ?>" target="_blank" class="small text-muted d-inline-block mt-1"><i class="bi bi-box-arrow-up-right"></i> Abrir arquivo</a>
                                <?php endif; ?>
                                <div class="small text-muted mt-1">
                                    <span class="me-2">Tipo: <?= htmlspecialchars($ax['tipo'] ?: ($ax['urlpa'] ? 'url' : 'arquivo')) ?></span>
                                    <?php if ($ax['sizepa']): ?><span class="me-2"><?= number_format($ax['sizepa'] / 1024, 1, ',', '.') ?> KB</span><?php endif; ?>
                                    <?php if ($ax['extpa']): ?><span class="me-2"><?= strtoupper($ax['extpa']) ?></span><?php endif; ?>
                                    <?php if ($ax['datapa']): ?><span><?= date('d/m/Y', strtotime($ax['datapa'])) ?> <?= htmlspecialchars($ax['horapa']) ?></span><?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <div class="form-check me-2" title="Visível?">
                                <input class="form-check-input chk-vis" type="checkbox" data-id="<?= $ax['codigomanexos'] ?>" <?= intval($ax['visivel']) ? 'checked' : '' ?>>
                            </div>
                            <button class="btn btn-sm btn-outline-primary" onclick="atualizarTitulo(<?= $ax['codigomanexos'] ?>)"><i class="bi bi-save"></i></button>
                            <button class="btn btn-sm btn-outline-danger" onclick="excluirAnexo(<?= $ax['codigomanexos'] ?>)"><i class="bi bi-trash"></i></button>
                        </div>
                    </li>
            <?php endforeach;
            endif; ?>
        </ul>

    </div>
</div>

<script>
    // === Config ===
    const allowExt = ['xlsx', 'docx', 'pdf', 'doc', 'xls', 'pptx', 'ppsx', 'ppt', 'pps', 'txt', 'otf', 'ttf', 'jpg', 'png', 'jpeg', 'psd', 'cdr', 'eps', 'ai', 'html', 'php', 'js', 'rar', 'zip', 'pbix', 'bat', 'json'];
</script>


<script>
    // === Helpers ===
    function reloadLista() {
        const url = 'publicacoesv1.0/ajax_anexo_list.php?pub=<?= urlencode($idPublicacao) ?>&md=<?= urlencode($idModuloPublicacao) ?>';
        showLoading('Recarregando anexos...');
        fetch(url)
            .then(r => r.text())
            .then(html => {
                document.getElementById('listaAnexos').innerHTML = html;
                bindDyn();
            })
            .catch(() => showToast('Falha ao recarregar a lista.', 'danger'))
            .finally(hideLoading);
    }

    function bindDyn() {
        // Toggle visível (checkbox)
        document.querySelectorAll('.chk-vis').forEach(chk => {
            chk.addEventListener('change', () => {
                const id = chk.dataset.id;
                const val = chk.checked ? 1 : 0;
                showLoading('Atualizando visibilidade...');
                fetch('publicacoesv1.0/ajax_anexo_toggle_visivel.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded'
                        },
                        body: 'id=' + encodeURIComponent(id) + '&visivel=' + val
                    })
                    .then(r => r.json())
                    .then(j => {
                        if (!j.sucesso) {
                            showToast('Falha ao alterar visibilidade.', 'danger');
                            chk.checked = !chk.checked; // desfaz
                        } else {
                            showToast('Visibilidade atualizada!', 'success');
                        }
                    })
                    .catch(() => showToast('Erro de comunicação.', 'danger'))
                    .finally(hideLoading);
            });
        });
    }

    // Bind inicial
    document.addEventListener('DOMContentLoaded', bindDyn);
</script>


<script>
    // === URL submit ===
    (function() {
        const form = document.getElementById('formUrl');
        if (!form) return;

        form.addEventListener('submit', function(e) {
            e.preventDefault();
            const fd = new FormData(this);

            showLoading('Inserindo URL...');
            fetch('publicacoesv1.0/ajax_anexo_insert_url.php', {
                    method: 'POST',
                    body: fd
                })
                .then(r => r.json())
                .then(j => {
                    if (j.sucesso) {
                        this.reset();
                        showToast('URL inserida com sucesso!', 'success');
                        reloadLista();
                    } else {
                        showToast(j.msg || 'Erro ao inserir URL.', 'danger');
                    }
                })
                .catch(() => showToast('Erro de comunicação.', 'danger'))
                .finally(hideLoading);
        });
    })();
</script>

<script>
    // === Upload submit ===
    (function() {
        const form = document.getElementById('formUpload');
        if (!form) return;

        form.addEventListener('submit', function(e) {
            e.preventDefault();

            const f = document.getElementById('arquivo');
            if (!f || !f.files.length) {
                showToast('Selecione um arquivo.', 'warning');
                return;
            }

            const ext = f.files[0].name.split('.').pop().toLowerCase();
            if (!allowExt.includes(ext)) {
                showToast('Extensão não permitida: ' + ext, 'warning');
                return;
            }

            // Cria o FormData ANTES de usar set/append
            const fd = new FormData(this);

            // Garante o envio de pastapub (mesmo se o input estiver fora do form)
            const pastapubEl = document.querySelector('[name="pastapub"]');
            if (pastapubEl) fd.set('pastapub', pastapubEl.value);

            // (Opcional) reforça envio de outros hiddens, caso estejam fora do form
            const codpubEl = document.querySelector('[name="codpublicacao"]');
            if (codpubEl) fd.set('codpublicacao', codpubEl.value);
            const idmodEl = document.querySelector('[name="idmodulo"]');
            if (idmodEl) fd.set('idmodulo', idmodEl.value);

            showLoading('Enviando arquivo...');
            fetch('publicacoesv1.0/ajax_anexo_upload.php', {
                    method: 'POST',
                    body: fd
                })
                .then(r => r.json())
                .then(j => {
                    if (j.sucesso) {
                        this.reset();
                        showToast('Arquivo enviado com sucesso!', 'success');
                        reloadLista();
                    } else {
                        showToast(j.msg || 'Erro no upload.', 'danger');
                    }
                })
                .catch(() => showToast('Erro de comunicação.', 'danger'))
                .finally(hideLoading);
        });
    })();
</script>



<script>
    // === Ações linha ===
    function atualizarTitulo(id) {
        const input = document.querySelector('#ax_' + id + ' .titulo-ax');
        const titulo = input ? input.value.trim() : '';
        if (!titulo) {
            showToast('Informe um título.', 'warning');
            return;
        }

        showLoading('Atualizando título...');
        fetch('publicacoesv1.0/ajax_anexo_update_titulo.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: 'id=' + encodeURIComponent(id) + '&titulopa=' + encodeURIComponent(titulo)
            })
            .then(r => r.json())
            .then(j => {
                if (!j.sucesso) showToast('Falha ao atualizar título.', 'danger');
                else showToast('Título atualizado!', 'success');
            })
            .catch(() => showToast('Erro de comunicação.', 'danger'))
            .finally(hideLoading);
    }

    function excluirAnexo(id) {
        if (!confirm('Excluir este anexo?')) return;

        showLoading('Excluindo anexo...');
        fetch('publicacoesv1.0/ajax_anexo_delete.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: 'id=' + encodeURIComponent(id)
            })
            .then(r => r.json())
            .then(j => {
                if (j.sucesso) {
                    document.getElementById('ax_' + id)?.remove();
                    showToast('Anexo excluído!', 'success');
                } else {
                    showToast('Falha ao excluir.', 'danger');
                }
            })
            .catch(() => showToast('Erro de comunicação.', 'danger'))
            .finally(hideLoading);
    }
</script>

<script>
    // === Marcar/Aplicar em massa ===
    (function() {
        const btnMarcar = document.getElementById('btnMarcarTodos');
        const btnDesmarcar = document.getElementById('btnDesmarcarTodos');
        const btnAplicar = document.getElementById('btnAplicarVis');

        btnMarcar?.addEventListener('click', () => {
            document.querySelectorAll('.chk-vis').forEach(chk => chk.checked = true);
            showToast('Todos marcados.', 'secondary');
        });

        btnDesmarcar?.addEventListener('click', () => {
            document.querySelectorAll('.chk-vis').forEach(chk => chk.checked = false);
            showToast('Todos desmarcados.', 'secondary');
        });

        btnAplicar?.addEventListener('click', () => {
            const checks = Array.from(document.querySelectorAll('.chk-vis'));
            if (!checks.length) {
                showToast('Nada para aplicar.', 'warning');
                return;
            }

            showLoading('Aplicando visibilidade...');
            Promise.all(checks.map(chk => {
                    const id = chk.dataset.id;
                    const val = chk.checked ? 1 : 0;
                    return fetch('publicacoesv1.0/ajax_anexo_toggle_visivel.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded'
                        },
                        body: 'id=' + encodeURIComponent(id) + '&visivel=' + val
                    });
                }))
                .then(() => showToast('Visibilidade aplicada!', 'success'))
                .catch(() => showToast('Falha ao aplicar visibilidade.', 'danger'))
                .finally(hideLoading);
        });
    })();
</script>



<!-- <script>
    // === Config ===
    const allowExt = ['xlsx', 'docx', 'pdf', 'doc', 'xls', 'pptx', 'ppsx', 'ppt', 'pps', 'txt', 'otf', 'ttf', 'jpg', 'png', 'jpeg', 'psd', 'cdr', 'eps', 'ai', 'html', 'php', 'js', 'rar', 'zip', 'pbix', 'bat', 'json'];

    // === Helpers ===
    function reloadLista() {
        const url = 'publicacoesv1.0/ajax_anexo_list.php?pub=<?= urlencode($idPublicacao) ?>&md=<?= urlencode($idModuloPublicacao) ?>';
        fetch(url)
            .then(r => r.text())
            .then(html => {
                document.getElementById('listaAnexos').innerHTML = html;
                bindDyn();
            });
    }

    function bindDyn() {
        document.querySelectorAll('.chk-vis').forEach(chk => {
            chk.addEventListener('change', (e) => {
                const id = chk.dataset.id;
                const val = chk.checked ? 1 : 0;
                fetch('publicacoesv1.0/ajax_anexo_toggle_visivel.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: 'id=' + encodeURIComponent(id) + '&visivel=' + val
                }).then(r => r.json()).then(j => {
                    if (!j.sucesso) alert('Falha ao alterar visibilidade.');
                });
            });
        });
    }
    bindDyn();

    // === URL submit ===
    document.getElementById('formUrl').addEventListener('submit', function(e) {
        e.preventDefault();
        const fd = new FormData(this);
        fetch('publicacoesv1.0/ajax_anexo_insert_url.php', {
                method: 'POST',
                body: fd
            })
            .then(r => r.json()).then(j => {
                if (j.sucesso) {
                    this.reset();
                    reloadLista();
                } else alert(j.msg || 'Erro ao inserir URL');
            });
    });

    // === Upload submit ===
    document.getElementById('formUpload').addEventListener('submit', function(e) {
        e.preventDefault();
        const f = document.getElementById('arquivo');
        if (!f.files.length) {
            alert('Selecione um arquivo.');
            return;
        }
        const ext = f.files[0].name.split('.').pop().toLowerCase();
        if (!allowExt.includes(ext)) {
            alert('Extensão não permitida: ' + ext);
            return;
        }
        const fd = new FormData(this);
        fetch('publicacoesv1.0/ajax_anexo_upload.php', {
                method: 'POST',
                body: fd
            })
            .then(r => r.json()).then(j => {
                if (j.sucesso) {
                    this.reset();
                    reloadLista();
                } else alert(j.msg || 'Erro no upload');
            });
    });

    // === Ações linha ===
    function atualizarTitulo(id) {
        const input = document.querySelector('#ax_' + id + ' .titulo-ax');
        const titulo = input ? input.value.trim() : '';
        if (!titulo) {
            alert('Informe um título.');
            return;
        }
        fetch('publicacoesv1.0/ajax_anexo_update_titulo.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: 'id=' + encodeURIComponent(id) + '&titulopa=' + encodeURIComponent(titulo)
        }).then(r => r.json()).then(j => {
            if (!j.sucesso) alert('Falha ao atualizar título.');
        });
    }

    function excluirAnexo(id) {
        if (!confirm('Excluir este anexo?')) return;
        fetch('publicacoesv1.0/ajax_anexo_delete.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: 'id=' + encodeURIComponent(id)
        }).then(r => r.json()).then(j => {
            if (j.sucesso) {
                document.getElementById('ax_' + id)?.remove();
            } else alert('Falha ao excluir.');
        });
    }

    // === Marcar/Aplicar em massa ===
    document.getElementById('btnMarcarTodos').addEventListener('click', () => {
        document.querySelectorAll('.chk-vis').forEach(chk => chk.checked = true);
    });
    document.getElementById('btnDesmarcarTodos').addEventListener('click', () => {
        document.querySelectorAll('.chk-vis').forEach(chk => chk.checked = false);
    });
    document.getElementById('btnAplicarVis').addEventListener('click', () => {
        const reqs = [];
        document.querySelectorAll('.chk-vis').forEach(chk => {
            const id = chk.dataset.id;
            const val = chk.checked ? 1 : 0;
            reqs.push(fetch('publicacoesv1.0/ajax_anexo_toggle_visivel.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: 'id=' + encodeURIComponent(id) + '&visivel=' + val
            }));
        });
        Promise.all(reqs).then(() => {
            /* opcional: toast */
        });
    });
</script> -->
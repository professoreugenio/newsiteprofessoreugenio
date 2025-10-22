<?php
// BodyAfiliadoformProdutoEditar.php
// Requisitos: $con (PDO), jQuery, jquery.mask, Bootstrap 5+, Bootstrap Icons, AOS

if (!isset($con) || !$con instanceof PDO) {
    echo '<div class="alert alert-danger">Conexão indisponível.</div>';
    return;
}

// Recupera ID (pode vir encryptado)
$raw = $_GET['id'] ?? '';
if ($raw === '') {
    echo '<div class="alert alert-warning">ID não informado.</div>';
    return;
}
$decId = $raw;
if (function_exists('encrypt')) {
    try {
        $decId = encrypt($raw, 'd');
    } catch (Throwable $e) { /* fallback */
    }
}
$decParts = explode('&', (string)$decId);
$id = (int)($decParts[0] ?? $decId);

try {
    $q = $con->prepare("
      SELECT codigoprodutoafiliado, idturmaap, nomeap, valorap, comissaoap, urlprodutoap,visivelap, pastaap, dataap, horaap,
             img1080x1920, img1024x1024
        FROM a_site_afiliados_produto
       WHERE codigoprodutoafiliado = :id
       LIMIT 1
    ");
    $q->bindValue(':id', $id, PDO::PARAM_INT);
    $q->execute();
    $p = $q->fetch(PDO::FETCH_ASSOC);

    if (!$p) {
        echo '<div class="alert alert-info">Produto não encontrado.</div>';
        return;
    }

    $fmt   = fn($v) => number_format((float)$v, 2, ',', '.');
    $nome  = (string)($p['nomeap'] ?? '');
    $valor = $fmt($p['valorap'] ?? 0);
    $comis = $fmt($p['comissaoap'] ?? 0);
    $vis   = (int)($p['visivelap'] ?? 0) === 1;
    $pasta = (string)($p['pastaap'] ?? '');
    $idturma = (string)($p['idturmaap'] ?? '');
    $pasta = (string)($p['pastaap'] ?? '');
    $img1  = (string)($p['img1080x1920'] ?? '');
    $img2  = (string)($p['img1024x1024'] ?? '');
    $data  = $p['dataap'] ? date('d/m/Y', strtotime($p['dataap'])) : '';
    $hora  = $p['horaap'] ? substr($p['horaap'], 0, 5) : '';

    $idEnc = function_exists('encrypt') ? encrypt((string)$id) : (string)$id;

    // Helper para normalizar URL para root-relative
    $toWeb = function ($path) {
        $path = (string)$path;
        if ($path === '') return '';
        return ($path[0] === '/') ? $path : '/' . $path;
    };
} catch (Throwable $e) {
    echo '<div class="alert alert-danger">Erro ao carregar dados: ' . htmlspecialchars($e->getMessage()) . '</div>';
    return;
}
?>

<div class="container-fluid" data-aos="fade-up">
    <div class="row g-4">
        <!-- Esquerda: Form edição -->
        <div class="col-12 col-lg-7">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <h5 class="m-0"><i class="bi bi-pencil-square me-2"></i>Editar produto</h5>
                        <span class="badge bg-secondary"><?php echo htmlspecialchars($data . ($hora ? " • $hora" : '')); ?></span>
                    </div>

                    <form id="formProdutoEditar" autocomplete="off">
                        <input type="hidden" name="id" value="<?php echo htmlspecialchars($idEnc); ?>">

                        <div class="mb-3">
                            <label class="form-label">Nome do produto</label>
                            <input type="text" class="form-control" name="nomeap" required maxlength="200"
                                value="<?php echo htmlspecialchars($nome); ?>">
                        </div>

                        <!-- Novo campo: URL do produto -->
                        <div class="mb-3">
                            <label for="urlproduto" class="form-label">URL do produto</label>
                            <input type="url" class="form-control" name="urlproduto" id="urlproduto"
                                placeholder="https://exemplo.com/seu-produto"
                                value="<?php echo htmlspecialchars($p['urlprodutoap'] ?? ''); ?>">
                            <div class="form-text">Informe a URL completa (incluindo https://).</div>
                        </div>

                        <div class="row g-3">
                            <div class="col-12 col-md-4">
                                <label class="form-label">Valor (R$)</label>
                                <input type="text" class="form-control money" name="valorap" required
                                    value="<?php echo htmlspecialchars($valor); ?>">
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label">Comissão (%)</label>
                                <input type="text" class="form-control money" name="comissaoap" required
                                    value="<?php echo htmlspecialchars($comis); ?>">
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label d-block">Visibilidade</label>
                                <div class="form-check form-switch mt-2">
                                    <input class="form-check-input" type="checkbox" role="switch"
                                        id="visivelap" name="visivelap" <?php echo $vis ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="visivelap">Visível</label>
                                </div>
                            </div>
                        </div>

                        <!-- SELECT DE TURMAS COMERCIAIS (curso comercial + visível na home) -->
                        <div class="mt-3">
                            <label for="idturmaafiliado" class="form-label">Turma Comercial Vinculada</label>
                            <select class="form-select" id="idturmaafiliado" name="idturmaafiliado">
                                <option value="">Selecione uma turma comercial...</option>
                                <?php
                                try {
                                    $sql = "
                SELECT 
                    t.codigoturma,
                    t.nometurma,
                    t.valorvenda,
                    t.codcursost
                FROM new_sistema_cursos_turmas AS t
                INNER JOIN new_sistema_cursos AS c
                        ON c.codigocursos = t.codcursost
                WHERE 
                    c.comercialsc = 1
                   
                ORDER BY t.nometurma ASC
            ";
                                    $stmt = $con->prepare($sql);
                                    $stmt->execute();

                                    while ($t = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                        $selected = ($t['codigoturma'] == ($p['idturmaap'] ?? '')) ? 'selected' : '';
                                        $valorFmt = number_format((float)($t['valorbrutoct'] ?? 0), 2, ',', '.');
                                        echo '<option value="' . htmlspecialchars($t['codigoturma']) . '" ' . $selected . '>'
                                            . htmlspecialchars($t['nometurma']) . ' — R$ ' . $valorFmt
                                            . ' (Curso #' . (int)$t['codcursost'] . ')</option>';
                                    }
                                } catch (Exception $e) {
                                    echo '<option value="">Erro ao carregar turmas</option>';
                                }
                                ?>
                            </select>
                            <div class="form-text">Listei somente turmas de cursos comerciais e visíveis na home.</div>
                        </div>


                        <div class="mt-3">
                            <label class="form-label">Pasta do produto</label>
                            <input type="text" class="form-control" value="<?php echo htmlspecialchars($pasta); ?>" readonly>
                            <div class="small text-muted">
                                Diretório: <code>fotos/produtosafiliados/<?php echo htmlspecialchars($pasta ?: '{pasta}'); ?></code>
                            </div>
                        </div>

                        <div class="d-flex gap-2 mt-3">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save me-1"></i> Salvar alterações
                            </button>
                            <a class="btn btn-outline-secondary" href="sistema_afiliadosProdutosEditar.php?id=<?php echo urlencode($idEnc); ?>">
                                <i class="bi bi-arrow-repeat me-1"></i> Recarregar
                            </a>
                            <button type="button" class="btn btn-danger ms-auto" id="btnDeleteProduto">
                                <i class="bi bi-trash3 me-1"></i> Excluir produto
                            </button>
                        </div>
                    </form>



                </div>
            </div>
        </div>

        <!-- Direita: Upload imagens lado-a-lado -->
        <div class="col-12 col-lg-5">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-4">
                    <h6 class="mb-1"><i class="bi bi-images me-2"></i>Imagens da Camapnha</h6>
                    <div class="small text-muted mb-3">Novos uploads <strong>substituem</strong> automaticamente a imagem anterior.</div>

                    <div class="row g-3">
                        <!-- 1080x1920 -->
                        <div class="col-6">
                            <div class="border rounded-3 p-3 h-100 d-flex flex-column">
                                <div class="fw-semibold">1080×1920</div>
                                <div class="small text-secondary mb-2">Retrato (Stories)</div>

                                <div class="ratio ratio-9x16 mb-2 bg-light rounded overflow-hidden position-relative" id="box1080">
                                    <?php if ($img1): ?>
                                        <img id="preview1080" src="<?php echo htmlspecialchars($toWeb($img1)); ?>" alt="" class="w-100 h-100 object-fit-cover">
                                    <?php else: ?>
                                        <div class="d-flex align-items-center justify-content-center text-secondary ratio-fill" id="preview1080Holder">
                                            <i class="bi bi-image me-1"></i> Sem imagem
                                        </div>
                                    <?php endif; ?>
                                    <button style="width: 30px; height: 30px;" type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0 m-1 img-delete-btn <?php echo $img1 ? '' : 'd-none'; ?>" id="btnDel1080" title="Excluir imagem">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>

                                <input class="form-control form-control-sm mb-2" type="file" accept="image/*" id="file1080">
                                <div class="d-grid gap-2 mt-auto">
                                    <button class="btn btn-outline-primary btn-sm" id="btnUpload1080">
                                        <i class="bi bi-upload me-1"></i> Enviar (substitui)
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- 1024x1024 -->
                        <div class="col-6">
                            <div class="border rounded-3 p-3 h-50 d-flex flex-column">
                                <div class="fw-semibold">1024×1024</div>
                                <div class="small text-secondary mb-2">Quadrado (Feed)</div>

                                <div class="ratio ratio-1x1 mb-2 bg-light rounded overflow-hidden position-relative" id="box1024">
                                    <?php if ($img2): ?>
                                        <img id="preview1024" src="<?php echo htmlspecialchars($toWeb($img2)); ?>" alt="" class="w-100 h-100 object-fit-contain">
                                    <?php else: ?>
                                        <div class="d-flex align-items-center justify-content-center text-secondary ratio-fill" id="preview1024Holder">
                                            <i class="bi bi-image me-1"></i> Sem imagem
                                        </div>
                                    <?php endif; ?>
                                    <button style="width: 30px; height: 30px;" type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0 m-1 img-delete-btn <?php echo $img2 ? '' : 'd-none'; ?>" id="btnDel1024" title="Excluir imagem">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>

                                <input class="form-control form-control-sm mb-2" type="file" accept="image/*" id="file1024">
                                <div class="d-grid gap-2 mt-auto">
                                    <button class="btn btn-outline-primary btn-sm" id="btnUpload1024">
                                        <i class="bi bi-upload me-1"></i> Enviar (substitui)
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="small text-muted mt-3">
                        Caminho: <code>fotos/produtosafiliados/<?php echo htmlspecialchars($pasta ?: '{pasta}'); ?></code>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    const PROD_NOME = <?php echo json_encode((string)$nome, JSON_UNESCAPED_UNICODE); ?>;

    // máscara de moeda
    $(function() {
        if ($.fn.mask) {
            $('.money').mask('000.000.000,00', {
                reverse: true
            });
        }
    });

    // Helper para normalizar url
    function toWeb(u) {
        return u && u[0] !== '/' ? '/' + u : u;
    }

    // Salvar edição
    $('#formProdutoEditar').on('submit', function(e) {
        e.preventDefault();
        const fd = new FormData(this);
        fd.set('visivelap', $('#visivelap').is(':checked') ? '1' : '0');

        $.ajax({
            url: 'afiliados1.0/ajax_afiliadosProdutoUpdate.php',
            method: 'POST',
            data: fd,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function(r) {
                if (r && r.ok) {
                    alert('Alterações salvas.');
                } else {
                    alert(r && r.msg ? r.msg : 'Não foi possível salvar.');
                }
            },
            error: function(xhr) {
                alert('Erro ao enviar. ' + (xhr.responseText || ''));
            }
        });
    });

    // ===== Upload 1080x1920 (substitui automaticamente) =====
    $('#btnUpload1080').on('click', function() {
        const f = $('#file1080')[0].files[0];
        if (!f) {
            alert('Selecione um arquivo 1080×1920.');
            return;
        }

        const fd = new FormData();
        fd.append('id', '<?php echo htmlspecialchars($idEnc); ?>');
        fd.append('tipo', '1080x1920');
        fd.append('arquivo', f);

        $.ajax({
            url: 'afiliados1.0/ajax_afiliadosProdutoUpload.php',
            method: 'POST',
            data: fd,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function(r) {
                if (r && r.ok && r.url) {
                    const webUrl = toWeb(r.url) + '?v=' + Date.now(); // bust cache
                    $('#preview1080Holder').remove();
                    if ($('#preview1080').length === 0) {
                        $('<img id="preview1080" class="w-100 h-100 object-fit-cover" alt="">').appendTo($('#box1080'));
                    }
                    $('#preview1080').attr('src', webUrl);
                    $('#btnDel1080').removeClass('d-none').prop('disabled', false).attr('aria-hidden', 'false');
                    $('#file1080').val('');
                } else {
                    alert(r && r.msg ? r.msg : 'Falha no upload 1080×1920.');
                }
            },
            error: function(xhr) {
                alert('Erro no upload 1080×1920. ' + (xhr.responseText || ''));
            }
        });
    });

    // ===== Upload 1024x1024 (substitui automaticamente) =====
    $('#btnUpload1024').on('click', function() {
        const f = $('#file1024')[0].files[0];
        if (!f) {
            alert('Selecione um arquivo 1024×1024.');
            return;
        }

        const fd = new FormData();
        fd.append('id', '<?php echo htmlspecialchars($idEnc); ?>');
        fd.append('tipo', '1024x1024');
        fd.append('arquivo', f);

        $.ajax({
            url: 'afiliados1.0/ajax_afiliadosProdutoUpload.php',
            method: 'POST',
            data: fd,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function(r) {
                if (r && r.ok && r.url) {
                    const webUrl = toWeb(r.url) + '?v=' + Date.now(); // bust cache
                    $('#preview1024Holder').remove();
                    if ($('#preview1024').length === 0) {
                        $('<img id="preview1024" class="w-100 h-100 object-fit-contain" alt="">').appendTo($('#box1024'));
                    }
                    $('#preview1024').attr('src', webUrl);
                    $('#btnDel1024').removeClass('d-none').prop('disabled', false).attr('aria-hidden', 'false');
                    $('#file1024').val('');
                } else {
                    alert(r && r.msg ? r.msg : 'Falha no upload 1024×1024.');
                }
            },
            error: function(xhr) {
                alert('Erro no upload 1024×1024. ' + (xhr.responseText || ''));
            }
        });
    });

    // ===== Excluir 1080x1920 =====
    $('#btnDel1080').on('click', function() {
        if (!confirm('Excluir a imagem 1080×1920?')) return;

        const fd = new FormData();
        fd.append('id', '<?php echo htmlspecialchars($idEnc); ?>');
        fd.append('tipo', '1080x1920');

        $.ajax({
            url: 'afiliados1.0/ajax_afiliadosProdutoDeleteImage.php',
            method: 'POST',
            data: fd,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function(r) {
                if (r && r.ok) {
                    $('#preview1080').remove();
                    if ($('#preview1080Holder').length === 0) {
                        $('<div id="preview1080Holder" class="d-flex align-items-center justify-content-center text-secondary ratio-fill"><i class="bi bi-image me-1"></i> Sem imagem</div>').appendTo($('#box1080'));
                    }
                    $('#btnDel1080').addClass('d-none');
                } else {
                    alert(r && r.msg ? r.msg : 'Não foi possível excluir a imagem 1080×1920.');
                }
            },
            error: function(xhr) {
                alert('Erro ao excluir 1080×1920. ' + (xhr.responseText || ''));
            }
        });
    });

    // ===== Excluir 1024x1024 =====
    $('#btnDel1024').on('click', function() {
        if (!confirm('Excluir a imagem 1024×1024?')) return;

        const fd = new FormData();
        fd.append('id', '<?php echo htmlspecialchars($idEnc); ?>');
        fd.append('tipo', '1024x1024');

        $.ajax({
            url: 'afiliados1.0/ajax_afiliadosProdutoDeleteImage.php',
            method: 'POST',
            data: fd,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function(r) {
                if (r && r.ok) {
                    $('#preview1024').remove();
                    if ($('#preview1024Holder').length === 0) {
                        $('<div id="preview1024Holder" class="d-flex align-items-center justify-content-center text-secondary ratio-fill"><i class="bi bi-image me-1"></i> Sem imagem</div>').appendTo($('#box1024'));
                    }
                    $('#btnDel1024').addClass('d-none');
                } else {
                    alert(r && r.msg ? r.msg : 'Não foi possível excluir a imagem 1024×1024.');
                }
            },
            error: function(xhr) {
                alert('Erro ao excluir 1024×1024. ' + (xhr.responseText || ''));
            }
        });
    });

    // ===== Excluir PRODUTO (registro + todas imagens) =====
    $('#btnDeleteProduto').on('click', function() {
        if (!confirm('Excluir DEFINITIVAMENTE o produto "' + PROD_NOME + '" e TODAS as imagens? Esta ação não pode ser desfeita.')) return;

        const fd = new FormData();
        fd.append('id', '<?php echo htmlspecialchars($idEnc); ?>');

        $.ajax({
            url: 'afiliados1.0/ajax_afiliadosProdutoDelete.php',
            method: 'POST',
            data: fd,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function(r) {
                if (r && r.ok) {
                    alert('Produto excluído com sucesso.');
                    window.location.href = r.redirect || 'sistema_afiliadosProdutos.php?ii';
                } else {
                    alert(r && r.msg ? r.msg : 'Não foi possível excluir o produto.');
                }
            },
            error: function(xhr) {
                alert('Erro ao excluir o produto. ' + (xhr.responseText || ''));
            }
        });
    });
</script>



<style>
    /* Proporções */
    .ratio-9x16 {
        position: relative;
        width: 100%;
        padding-top: calc(16/9*100%);
    }

    .ratio-1x1 {
        position: relative;
        width: 100%;
        padding-top: 100%;
    }

    /* SOMENTE as imagens/holders ocupam toda a área da razão (não o botão!) */
    .ratio-9x16>img,
    .ratio-1x1>img,
    .ratio-9x16>.ratio-fill,
    .ratio-1x1>.ratio-fill {
        position: absolute;
        inset: 0;
    }

    .object-fit-cover {
        object-fit: cover;
    }

    .object-fit-contain {
        object-fit: contain;
        background: #f8f9fa;
    }

    /* Botão excluir sempre acima da imagem e do holder */
    .img-delete-btn {
        z-index: 3;
    }
</style>
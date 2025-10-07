<?php
function e($v)
{
    return htmlspecialchars($v ?? '', ENT_QUOTES, 'UTF-8');
}

$midiaId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($midiaId <= 0) {
    echo "<div class='alert alert-warning'>ID da mídia inválido.</div>";
    return;
}

// Carrega mídia
try {
    $sql = "SELECT 
                m.codigomidiasanuncio,
                m.idclienteAM,
                m.idcategroiaAM,
                m.idcampanhaAM,
                m.linkAM,
                m.imagemAM,
                m.youtubeAM,
                m.chaveyoutubeAM,
                m.dataAM,
                m.horaAM,
                c.tituloACAM,
                cli.nomeclienteAC
            FROM a_site_anuncios_midias m
            LEFT JOIN a_site_anuncios_campanhas c ON c.codigocampanhaanuncio = m.idcampanhaAM
            LEFT JOIN a_site_anuncios_clientes  cli ON cli.codigoclienteanuncios = m.idclienteAM
            WHERE m.codigomidiasanuncio = :id
            LIMIT 1";
    $st = $con->prepare($sql);
    $st->bindValue(':id', $midiaId, PDO::PARAM_INT);
    $st->execute();
    if ($st->rowCount() === 0) {
        echo "<div class='alert alert-danger'>Mídia não encontrada.</div>";
        return;
    }
    $mid = $st->fetch(PDO::FETCH_ASSOC);
} catch (Throwable $e) {
    echo "<div class='alert alert-danger'>Erro ao carregar mídia: " . e($e->getMessage()) . "</div>";
    return;
}

// Carrega categorias (mesma taxonomia)
$cats = [];
try {
    $sc = $con->prepare("SELECT codigocategoriaanuncio, nomecategoriaACT FROM a_site_anuncios_categorias ORDER BY nomecategoriaACT ASC");
    $sc->execute();
    $cats = $sc->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $e) {
    echo "<div class='alert alert-danger'>Erro ao carregar categorias: " . e($e->getMessage()) . "</div>";
    return;
}
?>

<div class="container-fluid px-0">
    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-3">
        <div>
            <h5 class="mb-0 text-white">Editar Mídia</h5>
            <small class="text-muted">
                Campanha: <?= e($mid['tituloACAM']) ?> • Cliente: <?= e($mid['nomeclienteAC']) ?>
                • ID Mídia #<?= (int)$mid['codigomidiasanuncio'] ?>
            </small>
        </div>
        <div class="d-flex gap-2">
            <a href="anuncios_midias.php?campanha=<?= (int)$mid['idcampanhaAM'] ?>" class="btn btn-outline-light btn-sm">← Voltar</a>
        </div>
    </div>

    <div class="card bg-dark text-light border-0 shadow-sm">
        <div class="card-body">
            <form id="formMidiaEditar" enctype="multipart/form-data" novalidate>
                <input type="hidden" name="id" value="<?= (int)$mid['codigomidiasanuncio'] ?>">
                <input type="hidden" name="idcampanhaAM" value="<?= (int)$mid['idcampanhaAM'] ?>">
                <input type="hidden" name="idclienteAM" value="<?= (int)$mid['idclienteAM'] ?>">
                <input type="hidden" name="imagemAtual" value="<?= e($mid['imagemAM']) ?>">

                <div class="row g-4">
                    <!-- COLUNA ESQUERDA: TODOS OS INPUTS -->
                    <div class="col-12 col-lg-9">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label">Categoria da mídia</label>
                                <select name="idcategroiaAM" class="form-select">
                                    <option value="">Selecione...</option>
                                    <?php foreach ($cats as $c):
                                        $sel = ((int)$mid['idcategroiaAM'] === (int)$c['codigocategoriaanuncio']) ? 'selected' : '';
                                    ?>
                                        <option value="<?= (int)$c['codigocategoriaanuncio'] ?>" <?= $sel ?>>
                                            <?= e($c['nomecategoriaACT']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="form-text">Opcional.</div>
                            </div>

                            <div class="col-12">
                                <label class="form-label">Link (URL externo)</label>
                                <input type="url" name="linkAM" class="form-control"
                                    value="<?= e($mid['linkAM']) ?>" placeholder="https://exemplo.com/landing">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">YouTube (URL)</label>
                                <input type="url" name="youtubeAM" class="form-control"
                                    value="<?= e($mid['youtubeAM']) ?>" placeholder="https://www.youtube.com/watch?v=XXXXXXXXXXX">
                                <div class="form-text">Se preencher, a chave pode ser extraída automaticamente.</div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Chave do YouTube</label>
                                <div class="input-group">
                                    <input type="text" name="chaveyoutubeAM" class="form-control"
                                        value="<?= e($mid['chaveyoutubeAM']) ?>" placeholder="Ex.: dQw4w9WgXcQ">
                                    <button class="btn btn-outline-secondary" type="button" id="btnExtrairYt">Extrair do link</button>
                                </div>
                            </div>

                            <div class="col-12">
                                <label class="form-label">Imagem (1:1 recomendado)</label>
                                <input type="file" name="imagemAM" class="form-control" accept="image/*" id="inputNovaImagem">
                                <div class="form-text">JPG/PNG/WebP. Se enviar, substituirá a imagem atual.</div>
                            </div>

                            <div class="col-12 d-flex gap-2 mt-2">
                                <button type="submit" class="btn btn-success">Salvar alterações</button>
                                <a href="anuncios_midias.php?campanha=<?= (int)$mid['idcampanhaAM'] ?>" class="btn btn-outline-secondary">Cancelar</a>
                            </div>
                        </div>
                    </div>

                    <!-- COLUNA DIREITA: SOMENTE A IMAGEM -->
                    <div class="col-12 col-lg-3">
                        <label class="form-label">Imagem atual</label>
                        <div class="ratio ratio-1x1 border rounded-4 overflow-hidden bg-secondary">
                            <?php if (!empty($mid['imagemAM'])): ?>
                                <img id="previewAtual" src="<?= e($mid['imagemAM']) ?>" alt="atual"
                                    class="w-100 h-100" style="object-fit:cover;">
                            <?php else: ?>
                                <div id="placeholderImg"
                                    class="d-flex w-100 h-100 align-items-center justify-content-center text-white-50">
                                    Sem imagem
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="form-check mt-2">
                            <input class="form-check-input" type="checkbox" id="chkRemoveImg" name="removerImagem" value="1">
                            <label class="form-check-label" for="chkRemoveImg">Remover imagem atual</label>
                        </div>
                    </div>
                </div>
            </form>


            <!-- Toast -->
            <div class="position-fixed bottom-0 end-0 p-3" style="z-index:1080">
                <div id="toastRetorno" class="toast text-bg-dark border-0" role="alert" aria-live="assertive" aria-atomic="true">
                    <div class="toast-body" id="toastMsg">...</div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Preview da NOVA imagem (mostra na coluna direita)
    (function() {
        const input = document.getElementById('inputNovaImagem');
        const imgPreview = document.getElementById('previewAtual');
        const placeholder = document.getElementById('placeholderImg');
        if (!input) return;

        input.addEventListener('change', () => {
            const f = input.files?.[0];
            if (!f) return;
            const url = URL.createObjectURL(f);
            if (imgPreview) {
                imgPreview.src = url;
            } else if (placeholder) {
                // cria <img> se antes não havia imagem
                const img = document.createElement('img');
                img.id = 'previewAtual';
                img.className = 'w-100 h-100';
                img.style.objectFit = 'cover';
                img.src = url;
                placeholder.replaceWith(img);
            }
        });
    })();

    // Extrair chave do YouTube
    function extrairChaveYouTube(url) {
        try {
            const u = new URL(url);
            if (u.hostname.includes('youtu.be')) return u.pathname.replace('/', '').trim();
            if (u.searchParams.has('v')) return u.searchParams.get('v');
            const m = u.pathname.match(/\/embed\/([A-Za-z0-9_\-]{5,})/);
            if (m) return m[1];
            return '';
        } catch {
            return '';
        }
    }

    (function() {
        const form = document.getElementById('formMidiaEditar');
        const ytInput = form.querySelector('[name="youtubeAM"]');
        const keyInput = form.querySelector('[name="chaveyoutubeAM"]');
        const btnYt = document.getElementById('btnExtrairYt');

        btnYt?.addEventListener('click', () => {
            const url = ytInput.value.trim();
            if (!url) return;
            const key = extrairChaveYouTube(url);
            if (key) keyInput.value = key;
        });

        // (mantenha seu AJAX existente para salvar)
    })();
</script>
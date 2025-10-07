<?php
function e($v)
{
    return htmlspecialchars($v ?? '', ENT_QUOTES, 'UTF-8');
}

$campanhaId = isset($_GET['campanha']) ? (int)$_GET['campanha'] : 0;
if ($campanhaId <= 0) {
    echo "<div class='alert alert-warning'>Campanha inválida.</div>";
    return;
}

// Carrega campanha e cliente para header e hidden fields
$cliId = null;
$cliNome = '';
$campTitulo = '';
try {
    $st = $con->prepare("
        SELECT c.tituloACAM, c.idclienteACAM, cli.nomeclienteAC
        FROM a_site_anuncios_campanhas c
        LEFT JOIN a_site_anuncios_clientes cli ON cli.codigoclienteanuncios = c.idclienteACAM
        WHERE c.codigocampanhaanuncio = :id
        LIMIT 1
    ");
    $st->bindValue(':id', $campanhaId, PDO::PARAM_INT);
    $st->execute();
    if ($row = $st->fetch(PDO::FETCH_ASSOC)) {
        $campTitulo = $row['tituloACAM'] ?? '';
        $cliId      = (int)($row['idclienteACAM'] ?? 0);
        $cliNome    = $row['nomeclienteAC'] ?? '';
    } else {
        echo "<div class='alert alert-danger'>Campanha não encontrada.</div>";
        return;
    }
} catch (Throwable $e) {
    echo "<div class='alert alert-danger'>Erro ao carregar campanha: " . e($e->getMessage()) . "</div>";
    return;
}

// Carrega categorias (usando a_site_anuncios_categorias)
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
            <h5 class="mb-0 text-white">Adicionar Mídia</h5>
            <small class="text-muted">
                Campanha: <?= e($campTitulo) ?> • Cliente: <?= e($cliNome) ?> (ID <?= (int)$cliId ?>)
            </small>
        </div>
        <div class="d-flex gap-2">
            <a href="anuncios_midias.php?campanha=<?= (int)$campanhaId ?>" class="btn btn-outline-light btn-sm">← Voltar</a>
        </div>
    </div>

    <div class="card bg-dark text-light border-0 shadow-sm">
        <div class="card-body">
            <form id="formMidiaNovo" enctype="multipart/form-data" novalidate>
                <input type="hidden" name="idcampanhaAM" value="<?= (int)$campanhaId ?>">
                <input type="hidden" name="idclienteAM" value="<?= (int)$cliId ?>">

                <div class="row g-3">

                    <div class="col-md-4">
                        <label class="form-label">Categoria da mídia</label>
                        <select name="idcategroiaAM" class="form-select">
                            <option value="">Selecione...</option>
                            <?php foreach ($cats as $c): ?>
                                <option value="<?= (int)$c['codigocategoriaanuncio'] ?>"><?= e($c['nomecategoriaACT']) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <div class="form-text">Opcional. Usa a mesma taxonomia das categorias de anúncios.</div>
                    </div>

                    <div class="col-md-8">
                        <label class="form-label">Link (URL externo)</label>
                        <input type="url" name="linkAM" class="form-control" placeholder="https://exemplo.com/landing">
                        <div class="form-text">Se esta mídia aponta para uma página específica.</div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Imagem (1:1 recomendado)</label>
                        <input type="file" name="imagemAM" class="form-control" accept="image/*">
                        <div class="form-text">Formatos: JPG/PNG/WebP. Tamanho quadrado (ex.: 1080x1080).</div>
                    </div>

                    <div class="col-md-6 d-none" id="previewWrap">
                        <label class="form-label">Pré-visualização</label>
                        <div class="ratio ratio-1x1 border rounded-4 overflow-hidden">
                            <img id="previewImg" alt="preview" class="w-100 h-100" style="object-fit:cover;">
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">YouTube (URL)</label>
                        <input type="url" name="youtubeAM" class="form-control" placeholder="https://www.youtube.com/watch?v=XXXXXXXXXXX">
                        <div class="form-text">Cole o link completo; a chave será extraída automaticamente.</div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Chave do YouTube</label>
                        <input type="text" name="chaveyoutubeAM" class="form-control" placeholder="Ex.: dQw4w9WgXcQ">
                        <div class="form-text">Se o link estiver preenchido, tentamos extrair automaticamente.</div>
                    </div>

                    <div class="col-12 d-flex gap-2 mt-2">
                        <button type="submit" class="btn btn-success">Salvar mídia</button>
                        <button type="reset" class="btn btn-outline-secondary">Limpar</button>
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
    // Preview da imagem 1:1
    (function() {
        const input = document.querySelector('input[name="imagemAM"]');
        const wrap = document.getElementById('previewWrap');
        const img = document.getElementById('previewImg');
        if (!input || !wrap || !img) return;

        input.addEventListener('change', () => {
            const f = input.files?.[0];
            if (!f) {
                wrap.classList.add('d-none');
                return;
            }
            const url = URL.createObjectURL(f);
            img.src = url;
            wrap.classList.remove('d-none');
        });
    })();

    // Extrai a chave do YouTube do URL
    function extrairChaveYouTube(url) {
        try {
            const u = new URL(url);
            if (u.hostname.includes('youtu.be')) {
                return u.pathname.replace('/', '').trim();
            }
            if (u.searchParams.has('v')) {
                return u.searchParams.get('v');
            }
            // /embed/{id}
            const m = u.pathname.match(/\/embed\/([A-Za-z0-9_\-]{5,})/);
            if (m) return m[1];
            return '';
        } catch {
            return '';
        }
    }

    (function() {
        const form = document.getElementById('formMidiaNovo');
        const toastEl = document.getElementById('toastRetorno');
        const toastMsg = document.getElementById('toastMsg');
        const ytInput = form.querySelector('[name="youtubeAM"]');
        const keyInput = form.querySelector('[name="chaveyoutubeAM"]');

        function showToast(msg) {
            toastMsg.textContent = msg;
            new bootstrap.Toast(toastEl, {
                delay: 2500
            }).show();
        }

        ytInput.addEventListener('change', () => {
            const url = ytInput.value.trim();
            if (!url) return;
            const key = extrairChaveYouTube(url);
            if (key && !keyInput.value) keyInput.value = key;
        });

        form.addEventListener('submit', async function(ev) {
            ev.preventDefault();
            ev.stopPropagation();

            const fd = new FormData(form);
            // Se veio URL do YouTube e não veio chave, tenta extrair
            const yt = (fd.get('youtubeAM') || '').toString().trim();
            const ky = (fd.get('chaveyoutubeAM') || '').toString().trim();
            if (yt && !ky) fd.set('chaveyoutubeAM', extrairChaveYouTube(yt));

            try {
                const resp = await fetch('anuncios1.0/ajax_midiasInsert.php', {
                    method: 'POST',
                    body: fd
                });
                const json = await resp.json();

                if (json.status === 'ok') {
                    showToast(json.mensagem || 'Mídia salva!');
                    setTimeout(() => {
                        window.location.href = 'anuncios_midias.php?campanha=<?= (int)$campanhaId ?>';
                    }, 900);
                } else {
                    showToast(json.mensagem || 'Não foi possível salvar a mídia.');
                }
            } catch (e) {
                showToast('Erro inesperado no envio.');
            }
        }, false);
    })();
</script>
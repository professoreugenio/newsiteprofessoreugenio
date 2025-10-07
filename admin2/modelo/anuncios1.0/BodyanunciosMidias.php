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

// (Opcional) Carrega título da campanha e cliente p/ cabeçalho
$campanha = ['titulo' => '', 'cliente' => '', 'cliente_id' => null];
try {
    $st = $con->prepare("
        SELECT c.tituloACAM AS titulo, c.idclienteACAM AS cliente_id, cli.nomeclienteAC AS cliente
        FROM a_site_anuncios_campanhas c
        LEFT JOIN a_site_anuncios_clientes cli ON cli.codigoclienteanuncios = c.idclienteACAM
        WHERE c.codigocampanhaanuncio = :id
        LIMIT 1
    ");
    $st->bindValue(':id', $campanhaId, PDO::PARAM_INT);
    $st->execute();
    if ($row = $st->fetch(PDO::FETCH_ASSOC)) {
        $campanha['titulo']     = $row['titulo'] ?? '';
        $campanha['cliente']    = $row['cliente'] ?? '';
        $campanha['cliente_id'] = $row['cliente_id'] ?? null;
    }
} catch (Throwable $e) { /* silencioso no header */
}

// Carrega mídias da campanha
try {
    $sql = "
        SELECT 
            codigomidiasanuncio,
            idclienteAM,
            idcategroiaAM,
            idcampanhaAM,
            linkAM,
            imagemAM,
            youtubeAM,
            chaveyoutubeAM,
            dataAM,
            horaAM
        FROM a_site_anuncios_midias
        WHERE idcampanhaAM = :camp
        ORDER BY codigomidiasanuncio ASC, dataAM ASC, horaAM ASC
    ";
    $stmt = $con->prepare($sql);
    $stmt->bindValue(':camp', $campanhaId, PDO::PARAM_INT);
    $stmt->execute();
    $midias = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $e) {
    echo "<div class='alert alert-danger'>Erro ao carregar mídias: " . e($e->getMessage()) . "</div>";
    return;
}
?>

<style>
    /* Card quadrado com imagem de fundo */
    .card-midia {
        position: relative;
        overflow: hidden;
        border-radius: 1rem;
        background: #6c757d;
        /* cinza padrão quando não tem imagem */
    }

    .card-midia .bg-img {
        position: absolute;
        inset: 0;
        background-size: cover;
        background-position: center;
    }

    .card-midia .overlay {
        position: absolute;
        inset: 0;
        background: rgba(0, 0, 0, .15);
        opacity: 0;
        transition: opacity .2s ease-in-out;
    }

    .card-midia:hover .overlay {
        opacity: 1;
    }

    .card-midia .badges {
        position: absolute;
        top: .5rem;
        left: .5rem;
        right: .5rem;
        display: flex;
        gap: .25rem;
        flex-wrap: wrap;
        justify-content: space-between;
    }

    /* Placeholder (sem imagem): número centralizado */
    .card-midia .placeholder-num {
        position: absolute;
        inset: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2rem;
        font-weight: 700;
        color: #ffffff;
        text-shadow: 0 2px 6px rgba(0, 0, 0, .35);
    }

    /* Ações no canto inferior direito */
    .card-midia .actions {
        position: absolute;
        right: .5rem;
        bottom: .5rem;
        display: flex;
        gap: .4rem;
    }
</style>

<div class="container-fluid px-0">
    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-3">
        <div>
            <h5 class="mb-0 text-white">Mídias da Campanha</h5>
            <?php if ($campanha['titulo']): ?>
                <small class="text-muted">
                    <?= e($campanha['titulo']) ?>
                    <?php if ($campanha['cliente']): ?> • Cliente: <?= e($campanha['cliente']) ?><?php endif; ?>
                </small>
            <?php endif; ?>
        </div>
        <div class="d-flex gap-2">
            <?php if (!empty($campanha['cliente_id'])): ?>
                <a href="anuncios_campanhas.php?cliente=<?= (int)$campanha['cliente_id'] ?>" class="btn btn-outline-light btn-sm">← Voltar</a>
            <?php endif; ?>
            <a href="anuncios_midiasNovo.php?campanha=<?= (int)$campanhaId ?>" class="btn btn-success btn-sm">
                + Adicionar mídia
            </a>
        </div>
    </div>

    <?php if (!$midias): ?>
        <div class="alert alert-secondary">Nenhuma mídia cadastrada para esta campanha.</div>
    <?php else: ?>
        <div class="row g-3 justify-content-center">
            <?php
            $ordem = 0;
            foreach ($midias as $m):
                $ordem++;
                $idMidia = (int)$m['codigomidiasanuncio'];
                $img     = trim($m['imagemAM'] ?? '');
                $yt      = trim($m['youtubeAM'] ?? '');
                $key     = trim($m['chaveyoutubeAM'] ?? '');
                $link    = trim($m['linkAM'] ?? '');

                $temImagem = ($img !== '');
                $bgStyle = $temImagem ? "style=\"background-image:url('" . e($img) . "');\"" : "";
            ?>
                <div class="col-6 col-sm-4 col-md-3 col-lg-2 d-flex justify-content-center">
                    <div class="w-100">
                        <div class="ratio ratio-1x1 card-midia">
                            <?php if (!empty($m['imagemAM'])): ?>
                                <div class="bg-img" style="background-image:url('<?= e($m['imagemAM']) ?>');"></div>
                            <?php else: ?>
                                <div class="placeholder-num"><?= $ordem ?></div>
                            <?php endif; ?>

                            <div class="overlay"></div>

                            <div class="badges">
                                <?php if (!empty($m['youtubeAM']) || !empty($m['chaveyoutubeAM'])): ?>
                                    <span class="badge bg-danger">YouTube</span>
                                <?php endif; ?>
                                <?php if (!empty($m['linkAM'])): ?>
                                    <span class="badge bg-info text-dark">Link</span>
                                <?php endif; ?>
                            </div>

                            <!-- Botões arredondados com ícones -->
                            <div class="actions">
                                <a href="anuncios_midiasEditar.php?id=<?= (int)$m['codigomidiasanuncio'] ?>"
                                    class="btn btn-sm btn-primary rounded-circle d-flex align-items-center justify-content-center"
                                    title="Editar">
                                    <i class="bi bi-pencil-fill"></i>
                                </a>
                                <button type="button"
                                    class="btn btn-sm btn-danger rounded-circle d-flex align-items-center justify-content-center btnExcluirMidia"
                                    data-id="<?= (int)$m['codigomidiasanuncio'] ?>"
                                    title="Excluir">
                                    <i class="bi bi-trash-fill"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>


            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>


<script>
    document.addEventListener('click', async (ev) => {
        const btn = ev.target.closest('.btnExcluirMidia');
        if (!btn) return;
        const id = btn.getAttribute('data-id');
        const titulo = btn.getAttribute('data-titulo');
        if (!confirm(`Excluir ${titulo}?`)) return;

        try {
            const resp = await fetch('anuncios1.0/ajax_midiasDelete.php', {
                method: 'POST',
                body: new URLSearchParams({
                    id
                })
            });
            const json = await resp.json();
            if (json.status === 'ok') {
                btn.closest('.col-6').remove();
                alert('Mídia excluída com sucesso!');
            } else {
                alert(json.mensagem || 'Não foi possível excluir.');
            }
        } catch (e) {
            alert('Erro ao comunicar com o servidor.');
        }
    });
</script>
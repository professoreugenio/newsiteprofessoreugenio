<?php
function e($str)
{
    return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
}

// Consulta: clientes + última data de campanha
$sql = "
    SELECT 
        c.codigoclienteanuncios,
        c.nomeclienteAC,
        c.celularAC,
        c.whatsappAC,
        c.linksiteAC,
        MAX(cam.dataACAM) AS ultima_dataACAM
    FROM a_site_anuncios_clientes c
    LEFT JOIN a_site_anuncios_campanhas cam 
           ON cam.idclienteACAM = c.codigoclienteanuncios
    GROUP BY 
        c.codigoclienteanuncios,
        c.nomeclienteAC,
        c.celularAC,
        c.whatsappAC,
        c.linksiteAC
    ORDER BY c.nomeclienteAC ASC
";

$stmt = $con->prepare($sql); // $con já disponível na página principal
$stmt->execute();
$clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Helpers
function dataBR2($date)
{
    if (!$date) return '—';
    try {
        return (new DateTime($date))->format('d/m/Y');
    } catch (Throwable) {
        return e($date);
    }
}
function soDigitos($v)
{
    return preg_replace('/\D+/', '', (string)$v);
}
?>

<div class="container-fluid px-0">
    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-3">
        <h5 class="mb-0 text-white">Clientes de Anúncios</h5>
        <div class="d-flex gap-2">
            <input id="filtroClientes" type="text" class="form-control form-control-sm" placeholder="Filtrar por nome ou celular...">
            <a href="anuncios_clientesNovo.php" class="btn btn-success btn-sm">+ Novo Cliente</a>
        </div>
    </div>

    <div class="card bg-dark text-light border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-dark table-hover table-striped align-middle mb-0" id="tabelaClientes">
                    <thead class="table-secondary text-dark">
                        <tr>
                            <th style="width: 45%;">Cliente</th>
                            <th style="width: 20%;">Celular</th>
                            <th style="width: 20%;">Última Campanha</th>
                            <th style="width: 15%;" class="text-end">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!$clientes): ?>
                            <tr>
                                <td colspan="4" class="text-center py-4">Nenhum cliente cadastrado.</td>
                            </tr>
                            <?php else: foreach ($clientes as $c):
                                $id   = (int)$c['codigoclienteanuncios'];
                                $nome = $c['nomeclienteAC'];
                                $cel  = $c['celularAC'];
                                $wpp  = $c['whatsappAC'];
                                $site = $c['linksiteAC'];
                                $ultima = $c['ultima_dataACAM'] ? dataBR2($c['ultima_dataACAM']) : '—';

                                $urlEditar    = "anuncios_clientesEditar.php?id={$id}";
                                $urlCampanhas = "anuncios_campanhas.php?cliente={$id}";
                                $wppLink      = $wpp ? "https://wa.me/55" . soDigitos($wpp) : null;
                            ?>
                                <tr>
                                    <td>
                                        <a href="<?= e($urlEditar) ?>" class="text-decoration-none fw-semibold link-light">
                                            <?= e($nome) ?>
                                        </a>
                                        <?php if (!empty($site)): ?>
                                            <div class="small">
                                                <a href="<?= e($site) ?>" target="_blank" rel="noopener" class="link-warning text-decoration-none">
                                                    <?= e($site) ?>
                                                </a>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?= e($wpp ?: '—') ?>
                                        <?php if ($wppLink): ?>
                                            <a class="btn btn-sm btn-outline-success ms-2" href="<?= e($wppLink) ?>" target="_blank" rel="noopener" title="Abrir WhatsApp">
                                                WhatsApp
                                            </a>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($ultima !== '—'): ?>
                                            <span class="badge bg-info text-dark"><?= $ultima ?></span>
                                        <?php else: ?>
                                            <span class="text-muted">Sem campanhas</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end">
                                        <a href="<?= e($urlEditar) ?>" class="btn btn-sm btn-outline-light">Editar</a>
                                        <a href="<?= e($urlCampanhas) ?>" class="btn btn-sm btn-primary">Campanhas</a>
                                    </td>
                                </tr>
                        <?php endforeach;
                        endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
    // Filtro local por nome/celular
    (function() {
        const input = document.getElementById('filtroClientes');
        const tabela = document.getElementById('tabelaClientes');
        if (!input || !tabela) return;

        input.addEventListener('input', function() {
            const termo = this.value.toLowerCase().trim();
            const linhas = tabela.querySelectorAll('tbody tr');

            linhas.forEach(tr => {
                const tds = tr.querySelectorAll('td');
                if (tds.length < 3) return;
                const txt = (tds[0].innerText + ' ' + tds[1].innerText).toLowerCase();
                tr.style.display = txt.includes(termo) ? '' : 'none';
            });
        });
    })();
</script>
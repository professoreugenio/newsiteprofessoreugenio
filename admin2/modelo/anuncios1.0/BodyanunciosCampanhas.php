<?php
function e($v)
{
    return htmlspecialchars($v ?? '', ENT_QUOTES, 'UTF-8');
}
function dataBR2($d)
{
    if (!$d) return '—';
    try {
        return (new DateTime($d))->format('d/m/Y');
    } catch (Throwable) {
        return e($d);
    }
}
function moneyBR($n)
{
    if ($n === null || $n === '') return '—';
    if (is_numeric($n)) return 'R$ ' . number_format((float)$n, 2, ',', '.');
    $n = str_replace(['.', ','], ['', '.'], preg_replace('/[^\d,\.]/', '', $n));
    return 'R$ ' . number_format((float)$n, 2, ',', '.');
}

$clienteId = isset($_GET['cliente']) ? (int)$_GET['cliente'] : 0;
if ($clienteId <= 0) {
    echo "<div class='alert alert-warning'>Cliente inválido.</div>";
    return;
}

// Cliente
try {
    $stCli = $con->prepare("SELECT codigoclienteanuncios, nomeclienteAC FROM a_site_anuncios_clientes WHERE codigoclienteanuncios = :id LIMIT 1");
    $stCli->bindValue(':id', $clienteId, PDO::PARAM_INT);
    $stCli->execute();
    $cli = $stCli->fetch(PDO::FETCH_ASSOC);
    if (!$cli) {
        echo "<div class='alert alert-danger'>Cliente não encontrado.</div>";
        return;
    }
} catch (Throwable $e) {
    echo "<div class='alert alert-danger'>Erro ao carregar cliente: " . e($e->getMessage()) . "</div>";
    return;
}

// Campanhas (agora trazendo visivelACAM)
try {
    $sql = "SELECT 
                codigocampanhaanuncio,
                chaveACAM,
                idclienteACAM,
                tituloACAM,
                valorACAM,
                datainicioACAM,
                datafimACAM,
                visivelACAM
            FROM a_site_anuncios_campanhas
            WHERE idclienteACAM = :cli
            ORDER BY datainicioACAM DESC, codigocampanhaanuncio DESC";
    $stmt = $con->prepare($sql);
    $stmt->bindValue(':cli', $clienteId, PDO::PARAM_INT);
    $stmt->execute();
    $campanhas = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $e) {
    echo "<div class='alert alert-danger'>Erro ao carregar campanhas: " . e($e->getMessage()) . "</div>";
    return;
}
?>

<div class="container-fluid px-0">
    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-3">
        <div>
            <h5 class="mb-0 text-white">Campanhas de: <?= e($cli['nomeclienteAC']) ?></h5>
            <small class="text-muted">Cliente #<?= (int)$cli['codigoclienteanuncios'] ?></small>
        </div>
        <div class="d-flex gap-2">
            <a href="anuncios.php" class="btn btn-outline-light btn-sm">← Voltar</a>
            <a href="anuncios_campanhasNovo.php?cliente=<?= (int)$clienteId ?>" class="btn btn-success btn-sm">+ Adicionar Campanha</a>
        </div>
    </div>

    <div class="card bg-dark text-light border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-dark table-hover table-striped align-middle mb-0">
                    <thead class="table-secondary text-dark">
                        <tr>
                            <th style="width: 40%;">Campanha</th>
                            <th style="width: 12%;">Valor</th>
                            <th style="width: 16%;">Início</th>
                            <th style="width: 16%;">Fim</th>
                            <th style="width: 8%;">Visível</th>
                            <th style="width: 8%;" class="text-end">Ações</th>
                            <th style="width: 8%;" class="text-end">Excluir</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!$campanhas): ?>
                            <tr>
                                <td colspan="6" class="text-center py-4">Nenhuma campanha cadastrada.</td>
                            </tr>
                            <?php else: foreach ($campanhas as $cam):
                                $idc = (int)$cam['codigocampanhaanuncio'];
                                $on  = (int)$cam['visivelACAM'] === 1;
                            ?>
                                <tr data-id="<?= $idc ?>">
                                    <td class="fw-semibold">
                                        <a href="anuncios_campanhasEditar.php?id=<?= $idc ?>" class="text-decoration-none link-light">
                                            <?= e($cam['tituloACAM']) ?>
                                        </a>
                                        <?php if (!empty($cam['chaveACAM'])): ?>
                                            <div class="small text-muted">
                                                <code><?= e($cam['chaveACAM']) ?></code>
                                                <button type="button" class="btn btn-link btn-sm p-0 ms-2 text-decoration-none copiarChave" data-chave="<?= e($cam['chaveACAM']) ?>">copiar</button>
                                            </div>
                                        <?php endif; ?>
                                    </td>

                                    <td><?= moneyBR($cam['valorACAM']) ?></td>
                                    <td><?= dataBR2($cam['datainicioACAM']) ?></td>
                                    <td><?= dataBR2($cam['datafimACAM']) ?></td>
                                    <td>
                                        <button type="button" class="btn btn-sm <?= $on ? 'btn-success' : 'btn-secondary' ?> btnToggleVisivel"
                                            data-id="<?= $idc ?>" data-visivel="<?= $on ? 1 : 0 ?>" title="Alternar visibilidade">
                                            <?= $on ? 'ON' : 'OFF' ?>
                                        </button>
                                    </td>

                                    <td class="text-end">
                                        <a href="anuncios_midias.php?campanha=<?= $idc ?>" class="btn btn-sm btn-primary">Ver mídias</a>

                                    </td>
                                    <td class="text-end">

                                        <button type="button" class="btn btn-sm btn-danger btnExcluirCampanha"
                                            data-id="<?= $idc ?>" data-titulo="<?= e($cam['tituloACAM']) ?>">
                                            Excluir
                                        </button>
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
    // Alternar visibilidade via AJAX
    (function() {
        const tabela = document.querySelector('table');
        if (!tabela) return;

        tabela.addEventListener('click', async (ev) => {
            const btn = ev.target.closest('.btnToggleVisivel');
            if (!btn) return;

            const id = btn.getAttribute('data-id');
            const atual = btn.getAttribute('data-visivel') === '1' ? 1 : 0;

            try {
                const resp = await fetch('anuncios1.0/ajax_campanhaToggleVisivel.php', {
                    method: 'POST',
                    body: new URLSearchParams({
                        id
                    })
                });
                const json = await resp.json();
                if (json.status === 'ok') {
                    const novo = json.visivel === 1 ? 1 : 0;
                    btn.setAttribute('data-visivel', novo);
                    btn.classList.toggle('btn-success', novo === 1);
                    btn.classList.toggle('btn-secondary', novo === 0);
                    btn.textContent = novo === 1 ? 'ON' : 'OFF';
                } else {
                    alert(json.mensagem || 'Não foi possível alterar a visibilidade.');
                }
            } catch (e) {
                alert('Erro ao comunicar com o servidor.');
            }
        });
    })();
</script>



<script>
    document.addEventListener('click', (ev) => {
        const btn = ev.target.closest('.copiarChave');
        if (!btn) return;
        const chave = btn.getAttribute('data-chave') || '';
        navigator.clipboard.writeText(chave).then(() => {
            btn.textContent = 'copiado!';
            setTimeout(() => btn.textContent = 'copiar', 1200);
        });
    });
</script>


<script>
    (function() {
        const tabela = document.querySelector('table');
        if (!tabela) return;

        // Toggle visibilidade já existe – aqui tratamos exclusão também
        tabela.addEventListener('click', async (ev) => {
            const btnDel = ev.target.closest('.btnExcluirCampanha');
            if (btnDel) {
                const id = btnDel.getAttribute('data-id');
                const titulo = btnDel.getAttribute('data-titulo');
                if (!confirm(`Tem certeza que deseja excluir a campanha:\n"${titulo}" ?`)) return;

                try {
                    const resp = await fetch('anuncios1.0/ajax_campanhaDelete.php', {
                        method: 'POST',
                        body: new URLSearchParams({
                            id
                        })
                    });
                    const json = await resp.json();
                    if (json.status === 'ok') {
                        // remove linha da tabela
                        btnDel.closest('tr').remove();
                        alert('Campanha excluída com sucesso!');
                    } else {
                        alert(json.mensagem || 'Não foi possível excluir.');
                    }
                } catch (e) {
                    alert('Erro ao comunicar com o servidor.');
                }
            }
        });
    })();
</script>
<?php

/**
 * BodyAfiliadosListaUsuarios.php
 * Requisitos: $con (PDO) disponível, Bootstrap 5+, Bootstrap Icons, AOS.
 */

if (!isset($con) || !$con instanceof PDO) {
    echo '<div class="alert alert-danger">Conexão indisponível.</div>';
    return;
}

/* --------- Utilidades --------- */
$secureEncrypt = function ($val) {
    if (function_exists('encrypt')) {
        try {
            return encrypt((string)$val);
        } catch (\Throwable $e) {
        }
    }
    return (string)$val;
};
$fmtData = function (?string $data, ?string $hora) {
    if (!$data) return '-';
    try {
        $d = new DateTime($data . ($hora ? (' ' . $hora) : ''));
        return $d->format('d/m/Y') . ($hora ? (' · ' . $d->format('H:i')) : '');
    } catch (\Throwable $e) {
        return $data;
    }
};
$avatarPath = function ($pastasc, $img) {
    $pasta = $pastasc ?: '10000000000';
    $nome  = $img ?: 'usuario.jpg';
    return "/fotos/usuarios/{$pasta}/{$nome}";
};

/* --------- Filtros / paginação --------- */
$q       = trim((string)($_GET['q'] ?? ''));        // termo livre (nome/chave)
$nome    = trim((string)($_GET['nome'] ?? ''));     // busca específica por nome
$page    = max(1, (int)($_GET['p'] ?? 1));
$perPage = 20;
$offset  = ($page - 1) * $perPage;

/* --------- WHERE dinâmico --------- */
$where  = [];
$params = [];

if ($q !== '') {
    $where[]      = '(u.nome LIKE :q OR c.chaveafiliadoSA LIKE :q)';
    $params[':q'] = "%{$q}%";
}
if ($nome !== '') {
    $where[]         = 'u.nome LIKE :nome';
    $params[':nome'] = "%{$nome}%";
}
$whereSql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

/* --------- Contagem --------- */
$sqlCount = "
    SELECT COUNT(*) AS total
    FROM a_site_afiliados_chave c
    LEFT JOIN new_sistema_cadastro u ON u.codigocadastro = c.idusuarioSA
    {$whereSql}
";
$stmtCount = $con->prepare($sqlCount);
foreach ($params as $k => $v) $stmtCount->bindValue($k, $v);
$stmtCount->execute();
$totalRows  = (int)($stmtCount->fetchColumn() ?: 0);
$totalPages = (int)ceil($totalRows / $perPage);

/* --------- Consulta principal --------- */
$sql = "
    SELECT 
        c.codigochaveafiliados,
        c.idusuarioSA,
        c.chaveafiliadoSA,
        c.dataSA,
        c.horaSA,
        u.nome,
        u.pastasc,
        u.imagem50
    FROM a_site_afiliados_chave c
    LEFT JOIN new_sistema_cadastro u ON u.codigocadastro = c.idusuarioSA
    {$whereSql}
    ORDER BY c.dataSA DESC, c.horaSA DESC, c.codigochaveafiliados DESC
    LIMIT :limit OFFSET :offset
";
$stmt = $con->prepare($sql);
foreach ($params as $k => $v) $stmt->bindValue($k, $v);
$stmt->bindValue(':limit',  $perPage, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset,  PDO::PARAM_INT);
$stmt->execute();
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* --------- Helper de URL da paginação preservando filtros --------- */
$makeUrl = function ($p) use ($q, $nome) {
    $params = ['p' => $p];
    if ($q    !== '') $params['q']    = $q;
    if ($nome !== '') $params['nome'] = $nome;
    return '?' . http_build_query($params);
};
?>
<div class="container-fluid" data-aos="fade-up">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
        <h5 class="m-0">
            <i class="bi bi-people-fill me-2"></i>Afiliados cadastrados
            <!-- ID no badge p/ atualizar contagem -->
            <span id="afCount" class="badge bg-secondary ms-2"><?= number_format($totalRows, 0, ',', '.'); ?></span>
        </h5>

        <!-- Busca com 2 campos -->
        <form method="get" class="d-flex flex-wrap gap-2" role="search" autocomplete="off">
            <input type="text" class="form-control form-control-sm" name="nome"
                value="<?= htmlspecialchars($nome); ?>" placeholder="Pesquisar por nome...">
            <input type="text" class="form-control form-control-sm" name="q"
                value="<?= htmlspecialchars($q); ?>" placeholder="Buscar por nome ou chave...">
            <?php if ($page > 1): ?>
                <input type="hidden" name="p" value="<?= (int)$page; ?>">
            <?php endif; ?>
            <button class="btn btn-sm btn-primary">
                <i class="bi bi-search"></i>
            </button>
            <?php if ($q !== '' || $nome !== ''): ?>
                <a href="?p=1" class="btn btn-sm btn-outline-secondary" title="Limpar filtros">
                    <i class="bi bi-x-lg"></i>
                </a>
            <?php endif; ?>
        </form>
    </div>

    <div id="toastAreaAf" class="mb-2"></div>

    <?php if (!$rows): ?>
        <div id="afEmpty" class="alert alert-info" data-aos="fade-up">
            Nenhum afiliado encontrado<?= ($q || $nome) ? ' para a busca informada.' : '.' ?>
        </div>
    <?php else: ?>
        <!-- IDs no container da lista e paginação -->
        <div id="afList" class="list-group shadow-sm rounded-3 overflow-hidden" data-aos="fade-up" data-aos-delay="100">
            <?php foreach ($rows as $r):
                $idChave   = (int)$r['codigochaveafiliados'];
                $idUser    = (int)($r['idusuarioSA'] ?? 0);
                $nomeU     = $r['nome'] ?: 'Usuário sem nome';
                $chave     = $r['chaveafiliadoSA'] ?: '-';
                $dataLabel = $fmtData($r['dataSA'] ?? null, $r['horaSA'] ?? null);
                $imgUrl    = $avatarPath($r['pastasc'] ?? null, $r['imagem50'] ?? null);
                $idEnc     = $secureEncrypt($idChave);
                $href      = "sistema_afiliados_campanha.php?chave=" . urlencode($chave) . "&id=" . urlencode($idEnc);
            ?>
                <div class="list-group-item list-group-item-action py-3" data-idaf="<?= (int)$idChave; ?>">
                    <div class="d-flex align-items-center gap-3">
                        <img src="<?= htmlspecialchars($imgUrl); ?>" alt="" width="48" height="48"
                            class="rounded-circle flex-shrink-0" style="object-fit:cover">

                        <div class="flex-grow-1">
                            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                                <div class="me-2">
                                    <div class="fw-semibold"><?= htmlspecialchars($nomeU); ?></div>
                                    <div class="text-muted small">
                                        Afiliação: <span class="fw-medium"><?= htmlspecialchars($dataLabel); ?></span>
                                    </div>
                                    <div class="text-muted small">
                                        Chave: <code class="text-wrap"><?= htmlspecialchars($chave); ?></code>
                                    </div>
                                </div>

                                <div class="text-end d-flex gap-2">
                                    <a href="<?= htmlspecialchars($href); ?>" class="btn btn-sm btn-success">
                                        <i class="bi bi-bullseye me-1"></i> Ver Campanhas
                                    </a>
                                    <button type="button" class="btn btn-sm btn-outline-danger btn-af-excluir"
                                        data-idaf="<?= (int)$idChave; ?>">
                                        <i class="bi bi-trash3 me-1"></i> Excluir
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <?php if ($totalPages > 1): ?>
            <nav id="afPagination" class="mt-3" aria-label="Paginação afiliados" data-aos="fade-up" data-aos-delay="150">
                <ul class="pagination pagination-sm mb-0">
                    <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                        <a class="page-link" href="<?= $makeUrl(max(1, $page - 1)); ?>" tabindex="-1" aria-disabled="<?= $page <= 1 ? 'true' : 'false' ?>">
                            «
                        </a>
                    </li>
                    <?php
                    $start = max(1, $page - 2);
                    $end   = min($totalPages, $page + 2);
                    for ($i = $start; $i <= $end; $i++): ?>
                        <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                            <a class="page-link" href="<?= $makeUrl($i); ?>"><?= $i; ?></a>
                        </li>
                    <?php endfor; ?>
                    <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
                        <a class="page-link" href="<?= $makeUrl(min($totalPages, $page + 1)); ?>">
                            »
                        </a>
                    </li>
                </ul>
            </nav>
        <?php endif; ?>
    <?php endif; ?>
</div>

<script>
    (function() {
        const toastArea = document.getElementById('toastAreaAf');
        const listWrap = document.getElementById('afList'); // container da lista
        const countBad = document.getElementById('afCount'); // badge de contagem
        const pagWrap = document.getElementById('afPagination'); // paginação (pode não existir)
        const emptyBox = document.getElementById('afEmpty');

        function showToast(msg, ok = true) {
            const id = 't' + Date.now();
            const cls = ok ? 'success' : 'danger';
            const el = document.createElement('div');
            el.className = 'alert alert-' + cls + ' alert-dismissible fade show';
            el.id = id;
            el.innerHTML = msg + '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
            toastArea.appendChild(el);
            setTimeout(() => {
                bootstrap.Alert.getOrCreateInstance(el).close();
            }, 3000);
        }

        function parseCount() {
            if (!countBad) return null;
            const raw = (countBad.textContent || '').trim().replace(/\./g, '').replace(',', '.');
            const n = parseInt(raw, 10);
            return isNaN(n) ? null : n;
        }

        function setCount(n) {
            if (!countBad) return;
            // formatação simples: separador de milhar com ponto
            countBad.textContent = n.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
        }

        function currentPage() {
            const url = new URL(window.location.href);
            const p = parseInt(url.searchParams.get('p') || '1', 10);
            return isNaN(p) ? 1 : p;
        }

        function goToPrevPage() {
            const url = new URL(window.location.href);
            const p = currentPage();
            if (p > 1) {
                url.searchParams.set('p', String(p - 1));
                window.location.href = url.toString();
                return true;
            }
            return false;
        }

        function afterRemoveHandling() {
            // Se ainda há itens, nada a fazer:
            if (listWrap && listWrap.querySelector('.list-group-item')) return;

            // Se não há itens:
            // 1) Se página > 1, volta automaticamente para a página anterior
            if (goToPrevPage()) return;

            // 2) Se é página 1, mostra alerta de vazio, remove lista e paginação
            if (listWrap) listWrap.remove();
            if (pagWrap) pagWrap.remove();

            if (!document.getElementById('afEmpty')) {
                const alert = document.createElement('div');
                alert.id = 'afEmpty';
                alert.className = 'alert alert-info';
                alert.textContent = 'Nenhum afiliado encontrado.';
                toastArea.insertAdjacentElement('afterend', alert);
            }
        }

        document.addEventListener('click', async (e) => {
            const btn = e.target.closest('.btn-af-excluir');
            if (!btn) return;

            const item = btn.closest('[data-idaf]');
            const idaf = item ? item.getAttribute('data-idaf') : null;
            if (!idaf) return;

            const ok = confirm('Confirma a exclusão desta chave de afiliado? Esta ação não pode ser desfeita.');
            if (!ok) return;

            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Excluindo...';

            try {
                const form = new FormData();
                form.append('idaf', idaf);

                const resp = await fetch('afiliados1.0/ajax_ExcluirAfiliado.php', {
                    method: 'POST',
                    body: form,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                const data = await resp.json();
                if (!resp.ok || !data || !data.ok) throw new Error(data?.msg || 'Falha ao excluir.');

                // Remove item da DOM
                item.style.opacity = '.2';
                setTimeout(() => item.remove(), 160);

                // Atualiza contador
                const c = parseCount();
                if (c !== null && c > 0) setCount(c - 1);

                // Se ficou vazio, age conforme regras (volta página anterior ou mostra alerta)
                setTimeout(afterRemoveHandling, 200);

                showToast('Afiliado excluído com sucesso.', true);
            } catch (err) {
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-trash3 me-1"></i> Excluir';
                showToast(err.message || 'Erro inesperado.', false);
                console.error(err);
            }
        });
    })();
</script>
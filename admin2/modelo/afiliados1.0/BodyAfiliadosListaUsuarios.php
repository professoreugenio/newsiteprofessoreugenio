<?php

/**
 * BodyAfiliadosListaUsuarios.php
 * Requisitos: $con (PDO) disponível, Bootstrap 5+, Bootstrap Icons, AOS.
 * Lista afiliados da tabela a_site_afiliados_chave com dados do usuário em new_sistema_cadastro.
 * Busca por nome ou chave, paginação básica, avatar e link para sistema_afiliados_campanha.php
 */

// --- Guardas e utilidades ---
if (!isset($con) || !$con instanceof PDO) {
    echo '<div class="alert alert-danger">Conexão indisponível.</div>';
    return;
}

// Função segura para usar encrypt() apenas se existir
$secureEncrypt = function ($val) {
    if (function_exists('encrypt')) {
        try {
            return encrypt((string)$val);
        } catch (\Throwable $e) { /* noop */
        }
    }
    return (string)$val;
};

// Sanitização/controles
$q       = trim((string)($_GET['q'] ?? ''));
$page    = max(1, (int)($_GET['p'] ?? 1));
$perPage = 20;
$offset  = ($page - 1) * $perPage;

// --- Monta WHERE dinâmico (busca) ---
$where  = [];
$params = [];

if ($q !== '') {
    $where[]            = '(u.nome LIKE :q OR c.chaveafiliadoSA LIKE :q)';
    $params[':q']       = "%{$q}%";
}

$whereSql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

// --- Contagem total para paginação ---
$sqlCount = "
    SELECT COUNT(*) AS total
    FROM a_site_afiliados_chave c
    LEFT JOIN new_sistema_cadastro u ON u.codigocadastro = c.idusuarioSA
    {$whereSql}
";
$stmtCount = $con->prepare($sqlCount);
foreach ($params as $k => $v) $stmtCount->bindValue($k, $v);
$stmtCount->execute();
$totalRows = (int)($stmtCount->fetchColumn() ?: 0);
$totalPages = (int)ceil($totalRows / $perPage);

// --- Consulta principal ---
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

// Helpers de formatação
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

// Contagem visível
?>
<div class="container-fluid" data-aos="fade-up">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
        <h5 class="m-0">
            <i class="bi bi-people-fill me-2"></i>Afiliados cadastrados
            <span class="badge bg-secondary ms-2"><?= number_format($totalRows, 0, ',', '.'); ?></span>
        </h5>

        <form method="get" class="d-flex gap-2" role="search" autocomplete="off">
            <input type="text" class="form-control form-control-sm" name="q" value="<?= htmlspecialchars($q); ?>"
                placeholder="Buscar por nome ou chave...">
            <?php if ($page > 1): ?>
                <input type="hidden" name="p" value="<?= (int)$page; ?>">
            <?php endif; ?>
            <button class="btn btn-sm btn-primary">
                <i class="bi bi-search"></i>
            </button>
            <?php if ($q !== ''): ?>
                <a href="?p=1" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-x-lg"></i>
                </a>
            <?php endif; ?>
        </form>
    </div>

    <?php if (!$rows): ?>
        <div class="alert alert-info" data-aos="fade-up">
            Nenhum afiliado encontrado<?= $q ? ' para a busca informada.' : '.' ?>
        </div>
    <?php else: ?>
        <div class="list-group shadow-sm rounded-3 overflow-hidden" data-aos="fade-up" data-aos-delay="100">
            <?php foreach ($rows as $r):
                $idChave   = (int)$r['codigochaveafiliados'];
                $idUser    = (int)($r['idusuarioSA'] ?? 0);
                $nome      = $r['nome'] ?: 'Usuário sem nome';
                $chave     = $r['chaveafiliadoSA'] ?: '-';
                $dataLabel = $fmtData($r['dataSA'] ?? null, $r['horaSA'] ?? null);
                $imgUrl    = $avatarPath($r['pastasc'] ?? null, $r['imagem50'] ?? null);
                $idEnc     = $secureEncrypt($idChave);
                // Link alvo:
                // - Passa a CHAVE (obrigatória para rastreio)
                // - Passa também um ID criptografado como auxílio/contexto
                $href = "sistema_afiliados_campanha.php?chave=" . urlencode($chave) . "&id=" . urlencode($idEnc);
            ?>
                <div class="list-group-item list-group-item-action py-3">
                    <div class="d-flex align-items-center gap-3">
                        <img src="<?= htmlspecialchars($imgUrl); ?>" alt="" width="48" height="48"
                            class="rounded-circle flex-shrink-0" style="object-fit:cover">

                        <div class="flex-grow-1">
                            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                                <div class="me-2">
                                    <div class="fw-semibold"><?= htmlspecialchars($nome); ?></div>
                                    <div class="text-muted small">
                                        Afiliação: <span class="fw-medium"><?= htmlspecialchars($dataLabel); ?></span>
                                    </div>
                                    <div class="text-muted small">
                                        Chave: <code class="text-wrap"><?= htmlspecialchars($chave); ?></code>
                                    </div>
                                </div>

                                <div class="text-end">
                                    <a href="<?= htmlspecialchars($href); ?>" class="btn btn-sm btn-success">
                                        <i class="bi bi-bullseye me-1"></i> Ver Campanhas
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Paginação -->
        <?php if ($totalPages > 1): ?>
            <nav class="mt-3" aria-label="Paginação afiliados" data-aos="fade-up" data-aos-delay="150">
                <ul class="pagination pagination-sm mb-0">
                    <?php
                    // Helper para montar URLs preservando 'q'
                    $makeUrl = function ($p) use ($q) {
                        $params = ['p' => $p];
                        if ($q !== '') $params['q'] = $q;
                        return '?' . http_build_query($params);
                    };
                    ?>
                    <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                        <a class="page-link" href="<?= $makeUrl(max(1, $page - 1)); ?>" tabindex="-1" aria-disabled="<?= $page <= 1 ? 'true' : 'false' ?>">
                            «
                        </a>
                    </li>
                    <?php
                    // janela curta de páginas
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
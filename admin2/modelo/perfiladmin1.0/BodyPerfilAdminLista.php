<?php
if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

if (!function_exists('temPermissao')) {
    function temPermissao($nivelUsuario, $permitidos = [])
    {
        return in_array($nivelUsuario, $permitidos);
    }
}

// $niveladm já deve estar definido no escopo da página (ex.: $niveladm = $rw['nivel'];)
$soAdminPodeExcluir = temPermissao((int)($niveladm ?? 0), [1]);

// Conexão
try {
    if (!isset($con) || !($con instanceof PDO)) {
        $con = config::connect();
    }
    $con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (Throwable $e) {
    echo "<div class='alert alert-danger'>Erro de conexão.</div>";
    return;
}

// Filtros e paginação
$q          = trim($_GET['q'] ?? '');
$fNivel     = isset($_GET['nivel']) && $_GET['nivel'] !== '' ? (int)$_GET['nivel'] : null;
$fLiberado  = isset($_GET['liberado']) && $_GET['liberado'] !== '' ? (int)$_GET['liberado'] : null;
$page       = max(1, (int)($_GET['page'] ?? 1));
$perPage    = max(5, min(50, (int)($_GET['perPage'] ?? 20)));
$offset     = ($page - 1) * $perPage;

$conds = ['1=1'];
$params = [];

if ($q !== '') {
    $conds[] = '(nome LIKE :q OR email LIKE :q OR celular LIKE :q)';
    $params[':q'] = '%' . $q . '%';
}
if ($fNivel !== null && in_array($fNivel, [1, 2, 3, 4], true)) {
    $conds[] = 'nivel = :nivel';
    $params[':nivel'] = $fNivel;
}
if ($fLiberado !== null && in_array($fLiberado, [0, 1], true)) {
    $conds[] = 'liberado = :lib';
    $params[':lib'] = $fLiberado;
}

$where = implode(' AND ', $conds);

// Total
$stmtCount = $con->prepare("SELECT COUNT(*) FROM new_sistema_usuario WHERE $where");
foreach ($params as $k => $v) {
    $stmtCount->bindValue($k, $v);
}
$stmtCount->execute();
$totalReg = (int)$stmtCount->fetchColumn();
$totalPages = max(1, (int)ceil($totalReg / $perPage));

// Lista
$sql = "
    SELECT 
        codigousuario, nome, email, celular, dataaniversario, chave, pastasu,
        imagem, imagem200, nivel, liberado, onlinesu, timestampsu
    FROM new_sistema_usuario
    WHERE $where
    ORDER BY timestampsu DESC, nome ASC
    LIMIT :limit OFFSET :offset
";
$stmt = $con->prepare($sql);
foreach ($params as $k => $v) {
    $stmt->bindValue($k, $v);
}
$stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Mapa de níveis
$niveis = [1 => 'Admin', 2 => 'Suporte', 3 => 'Professor', 4 => 'Vendas'];

function fotoUsuarioLista(array $rw): string
{
    $imgExibe = $rw['imagem200'] ?: ($rw['imagem'] ?: 'usuario.jpg');
    if ($imgExibe === 'usuario.jpg') {
        return '../../fotos/usuarios/usuario.png';
    }
    $pasta = $rw['pastasu'] ?? '';
    return "../../fotos/usuarios/" . htmlspecialchars($pasta) . "/" . htmlspecialchars($imgExibe);
}
?>
<div class="container-fluid" data-aos="fade-up">
    <div class="d-flex flex-wrap align-items-center justify-content-between mb-3">
        <h5 class="text-white mb-2 mb-md-0">
            <i class="bi bi-people-gear me-2"></i> Usuários Cadastrados
        </h5>
        <div class="d-flex gap-2">
            <?php if (temPermissao((int)($niveladm ?? 0), [1])): ?>
                <a href="perfiladmin_novo.php" class="btn btn-success btn-sm">
                    <i class="bi bi-person-plus-fill me-1"></i> Novo Usuário
                </a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Filtros -->
    <form class="card shadow-sm border-0 mb-3">
        <div class="card-body">
            <div class="row g-2">
                <div class="col-md-4">
                    <input type="text" name="q" class="form-control" placeholder="Buscar por nome, e-mail ou celular"
                        value="<?= htmlspecialchars($q) ?>">
                </div>
                <div class="col-md-3">
                    <select name="nivel" class="form-select">
                        <option value="">Todos os níveis</option>
                        <?php foreach ($niveis as $valor => $rotulo): ?>
                            <option value="<?= $valor ?>" <?= ($fNivel === $valor ? 'selected' : '') ?>>
                                <?= $rotulo ?> = <?= $valor ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="liberado" class="form-select">
                        <option value="">Todos os status</option>
                        <option value="1" <?= ($fLiberado === 1 ? 'selected' : '') ?>>Liberado</option>
                        <option value="0" <?= ($fLiberado === 0 ? 'selected' : '') ?>>Bloqueado</option>
                    </select>
                </div>
                <div class="col-md-2 d-grid">
                    <button class="btn btn-primary">
                        <i class="bi bi-search me-1"></i> Filtrar
                    </button>
                </div>
            </div>
        </div>
    </form>

    <!-- Tabela -->
    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width:60px;">Foto</th>
                            <th>Nome</th>
                            <th>E-mail</th>
                            <th>Celular</th>
                            <th>Nível</th>
                            <th>Status</th>
                            <th>Atualizado</th>
                            <th class="text-end" style="width:150px;">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!$rows): ?>
                            <tr>
                                <td colspan="8" class="text-center py-4 text-muted">Nenhum usuário encontrado.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($rows as $rwU):
                                $foto = fotoUsuarioLista($rwU);
                                $idEnc = encrypt((string)$rwU['codigousuario'], $action = 'e');
                                $nivelNome = $niveis[(int)$rwU['nivel']] ?? ('Nível ' . (int)$rwU['nivel']);
                                $badgeNivel = match ((int)$rwU['nivel']) {
                                    1 => 'bg-primary',
                                    2 => 'bg-info',
                                    3 => 'bg-warning text-dark',
                                    4 => 'bg-secondary',
                                    default => 'bg-dark'
                                };
                                $badgeLib = ((int)$rwU['liberado'] === 1) ? 'badge bg-success' : 'badge bg-danger';
                                $txtLib   = ((int)$rwU['liberado'] === 1) ? 'Liberado' : 'Bloqueado';
                            ?>
                                <tr id="rowU<?= (int)$rwU['codigousuario'] ?>">
                                    <td>
                                        <img src="<?= htmlspecialchars($foto) ?>" alt="" class="rounded-circle"
                                            style="width:40px;height:40px;object-fit:cover;">
                                    </td>
                                    <td class="fw-semibold"><?= htmlspecialchars($rwU['nome'] ?? '') ?></td>
                                    <td><?= htmlspecialchars($rwU['email'] ?? '') ?></td>
                                    <td><?= htmlspecialchars($rwU['celular'] ?? '') ?></td>
                                    <td>
                                        <span class="badge <?= $badgeNivel ?>"><?= htmlspecialchars($nivelNome) ?></span>
                                    </td>
                                    <td><span class="<?= $badgeLib ?>"><?= $txtLib ?></span></td>
                                    <td><small class="text-muted"><?= htmlspecialchars($rwU['timestampsu'] ?? '') ?></small></td>
                                    <td class="text-end">
                                        <a href="perfiladmin.php?id=<?= $idEnc ?>" class="btn btn-sm btn-outline-primary" title="Editar">
                                            <i class="bi bi-pencil-square"></i>
                                        </a>
                                        <?php if ($soAdminPodeExcluir): ?>
                                            <button type="button"
                                                class="btn btn-sm btn-outline-danger ms-1"
                                                onclick="excluirUsuario(<?= (int)$rwU['codigousuario'] ?>, this)"
                                                title="Excluir">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <?php if ($totalPages > 1): ?>
            <div class="card-footer bg-white">
                <nav>
                    <ul class="pagination justify-content-end mb-0">
                        <?php
                        // Helper para montar URL mantendo filtros
                        $baseParams = $_GET;
                        $baseParams['perPage'] = $perPage;

                        $mkUrl = function ($p) use ($baseParams) {
                            $baseParams['page'] = $p;
                            return '?' . http_build_query($baseParams);
                        };
                        ?>
                        <li class="page-item <?= ($page <= 1 ? 'disabled' : '') ?>">
                            <a class="page-link" href="<?= $mkUrl(max(1, $page - 1)) ?>" tabindex="-1" aria-disabled="<?= ($page <= 1 ? 'true' : 'false') ?>">
                                «
                            </a>
                        </li>
                        <?php
                        $start = max(1, $page - 2);
                        $end   = min($totalPages, $page + 2);
                        for ($p = $start; $p <= $end; $p++): ?>
                            <li class="page-item <?= ($p === $page ? 'active' : '') ?>">
                                <a class="page-link" href="<?= $mkUrl($p) ?>"><?= $p ?></a>
                            </li>
                        <?php endfor; ?>
                        <li class="page-item <?= ($page >= $totalPages ? 'disabled' : '') ?>">
                            <a class="page-link" href="<?= $mkUrl(min($totalPages, $page + 1)) ?>">»</a>
                        </li>
                    </ul>
                </nav>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Toast -->
<div class="position-fixed top-50 start-50 translate-middle p-3" style="z-index:1080;">
    <div id="toastPerfilAdminLista" class="toast align-items-center text-bg-dark border-0"
        role="alert" aria-live="assertive" aria-atomic="true" data-bs-delay="3000">
        <div class="d-flex">
            <div class="toast-body" id="toastPerfilAdminListaMsg">Pronto.</div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto"
                data-bs-dismiss="toast" aria-label="Fechar"></button>
        </div>
    </div>
</div>

<script>
    function excluirUsuario(id, btn) {
        if (!confirm('Tem certeza que deseja excluir este usuário? Esta ação não pode ser desfeita.')) {
            return;
        }
        const toastEl = document.getElementById('toastPerfilAdminLista');
        const toastMsg = document.getElementById('toastPerfilAdminListaMsg');
        const showToast = (m, err = false) => {
            toastMsg.textContent = m;
            toastEl.classList.remove('text-bg-dark', 'text-bg-danger', 'text-bg-success');
            toastEl.classList.add(err ? 'text-bg-danger' : 'text-bg-success');
            new bootstrap.Toast(toastEl).show();
        };

        btn.disabled = true;

        const fd = new FormData();
        fd.append('id', String(id));

        fetch('perfiladmin1.0/ajax_PerfilAdminDelete.php', {
                method: 'POST',
                body: fd
            })
            .then(r => r.json())
            .then(j => {
                if (j.status === 'ok') {
                    const row = document.getElementById('rowU' + id);
                    if (row) row.remove();
                    showToast(j.mensagem || 'Usuário excluído com sucesso.');
                } else {
                    showToast(j.mensagem || 'Falha ao excluir.', true);
                }
            })
            .catch(() => showToast('Erro de comunicação com o servidor.', true))
            .finally(() => {
                btn.disabled = false;
            });
    }
</script>
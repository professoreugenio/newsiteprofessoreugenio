<?php

$filtro = $_GET['filtro'] ?? 'visivel';

$where = "codpagesadminsc = :codpagesadminsc";
if ($filtro === 'visivel') $where .= " AND visivelsc = 1 AND matriz = 1 AND (lixeirasc IS NULL OR lixeirasc = 0)";
if ($filtro === 'oculto') $where .= " AND visivelsc = 0 AND matriz = 1 AND (lixeirasc IS NULL OR lixeirasc = 0)";
if ($filtro === 'lixeira') $where .= " AND lixeirasc = 1 AND matriz = 1 ";
if ($filtro === 'copias') $where .= " AND comercialsc = 1 ";

$sql = "SELECT codigocategorias, nome, comercialsc, onlinesc, visivelsc, lixeirasc
FROM new_sistema_categorias_PJA
WHERE $where
ORDER BY nome";
$stmt = $con->prepare($sql);
$stmt->execute([':codpagesadminsc' => 327]);
$categorias = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<div class="mb-3 d-flex gap-2">
    <a href="?filtro=visivel" class="btn btn-outline-success btn-sm <?= $filtro == 'visivel' ? 'active' : '' ?>"><i class="bi bi-eye"></i> Visíveis</a>
    <a href="?filtro=oculto" class="btn btn-outline-secondary btn-sm <?= $filtro == 'oculto' ? 'active' : '' ?>"><i class="bi bi-eye-slash"></i> Ocultas</a>
    <a href="?filtro=lixeira" class="btn btn-outline-danger  btn-sm <?= $filtro == 'lixeira' ? 'active' : '' ?>"><i class="bi bi-trash"></i> Lixeira</a>
    <a href="?filtro=copias" class="btn btn-outline-default  btn-sm <?= $filtro == 'copias' ? 'active' : '' ?>"><i class="bi bi-files"></i> Copias</a>
</div>


<div class="card shadow-sm border-0">
    <div class="card-header bg-white">
        <h5 class="mb-0">Categorias de Publicações</h5>
    </div>
    <ul class="list-group list-group-flush" id="listaCategorias">
        <?php foreach ($categorias as $cat): ?>
            <?php $encId = encrypt($cat['codigocategorias'], $action = 'e'); ?>
            <?php
            $quantPublicacoes = contaPublicacoes($con, $cat['codigocategorias']);
            ?>
            <li class="list-group-item d-flex justify-content-between align-items-center" id="cat_<?= $cat['codigocategorias'] ?>">
                <div>
                    <a href="cursos_modulos.php?id=<?= urlencode($encId) ?>" class="fw-semibold link-primary text-decoration-none">
                        <?= htmlspecialchars($cat['nome']) ?>
                    </a>
                    <span class="badge bg-secondary ms-2"><?= $quantPublicacoes ?> publicação<?= $quantPublicacoes != 1 ? 's' : '' ?></span>
                </div>
                <div class="d-flex gap-3 align-items-center">
                    <!-- VISIBILIDADE -->
                    <button class="btn btn-sm p-0 border-0 bg-transparent" title="<?= $cat['visivelsc'] ? 'Ocultar' : 'Tornar visível' ?>" onclick="toggleVisivel(<?= $cat['codigocategorias'] ?>, <?= $cat['visivelsc'] ? 0 : 1 ?>)">
                        <i class="bi <?= $cat['visivelsc'] ? 'bi-eye text-success' : 'bi-eye-slash text-secondary' ?>"></i>
                    </button>
                    <!-- ONLINE -->
                    <?php if ($cat['onlinesc']): ?>
                        <i class="bi bi-wifi text-info" title="Online"></i>
                    <?php endif; ?>
                    <!-- COMERCIAL -->
                    <?php if ($cat['comercialsc']): ?>
                        <i class="bi bi-currency-dollar text-warning" title="Comercial"></i>
                    <?php endif; ?>
                    <!-- LIXEIRA -->
                    <?php if (empty($cat['lixeirasc'])): ?>


                        <button class="btn btn-sm p-0 border-0 bg-transparent" title="Enviar para Lixeira" onclick="enviarLixeira(<?= $cat['codigocategorias'] ?>)">
                            <i class="bi bi-trash text-danger"></i>
                        </button>
                    <?php else: ?>
                        <?php if ($filtro == 'lixeira'): ?>
                            <button class="btn btn-sm p-0 border-0 bg-transparent" title="Restaurar Categoria" onclick="restaurarCategoria(<?= $cat['codigocategorias'] ?>)">
                                <i class="bi bi-arrow-counterclockwise text-success"></i>
                            </button>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </li>
        <?php endforeach; ?>
        <?php if (empty($categorias)): ?>
            <li class="list-group-item text-center text-muted">Nenhuma categoria encontrada.</li>
        <?php endif; ?>
    </ul>

</div>
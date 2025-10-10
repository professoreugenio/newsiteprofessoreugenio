<?php
// -----------------------------------------
// Sanitização e normalização dos filtros
// -----------------------------------------
$institucional = filter_input(INPUT_GET, 'institucional', FILTER_VALIDATE_INT, [
    'options' => ['default' => null, 'min_range' => 0, 'max_range' => 1]
]);

$statusComercial = filter_input(INPUT_GET, 'status', FILTER_VALIDATE_INT, [
    'options' => ['default' => null, 'min_range' => 0, 'max_range' => 1]
]);

// Se "matriz" for flag 0/1, mantenha VALIDATE_INT; se for string, altere para sanitize:
$matriz = filter_input(INPUT_GET, 'matriz', FILTER_VALIDATE_INT, [
    'options' => ['default' => null, 'min_range' => 0, 'max_range' => 1]
]);

$con = config::connect(); // garante conexão

// -----------------------------------------
// Montagem dinâmica do WHERE
// -----------------------------------------
$where = ["c.lixeirasc != 1"];
$params = [];

if ($statusComercial !== null) {
    $where[] = "c.comercialsc = :status";
    $params[':status'] = $statusComercial;
}
if ($institucional !== null) {
    $where[] = "c.institucionalsc = :institucional";
    $params[':institucional'] = $institucional;
}
if ($matriz !== null) {
    $where[] = "c.matriz = :matriz";
    $params[':matriz'] = $matriz;
}

$whereSql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

// -----------------------------------------
// Consulta única (evita N+1): conta turmas por curso
// -----------------------------------------
$sql = "
    SELECT
        c.codigocategorias,
        c.nome,
        c.visivelsc,
        c.ordemsc,
        c.datasc,
        c.horasc,
        c.comercialsc,
        c.onlinesc,
        c.matriz,
        (
            SELECT COUNT(1)
            FROM new_sistema_cursos_turmas t
            WHERE t.codcursost = c.codigocategorias
        ) AS qtd_turmas
    FROM new_sistema_categorias_PJA c
    $whereSql
    ORDER BY c.ordemsc ASC, c.datasc DESC, c.horasc DESC
";

$stmt = $con->prepare($sql);
foreach ($params as $k => $v) {
    $stmt->bindValue($k, $v, PDO::PARAM_INT);
}
$stmt->execute();
?>

<?php if ($stmt->rowCount() > 0): ?>
    <ul class="list-group">
        <?php while ($row = $stmt->fetch(PDO::FETCH_ASSOC)): ?>
            <?php
            $id        = (int)($row['codigocategorias'] ?? 0);
            $encId     = encrypt($id, $action = 'e');
            $nome      = htmlspecialchars($row['nome'] ?? 'Sem título');
            $ordem     = (int)($row['ordemsc'] ?? 0);
            $visivel   = (int)($row['visivelsc'] ?? 0);
            $comercial = (int)($row['comercialsc'] ?? 0);
            $onlinFlg  = (int)($row['onlinesc'] ?? 0);
            $matrizFlg = (int)($row['matriz'] ?? 0);
            $qtdTurmas = (int)($row['qtd_turmas'] ?? 0);

            // Badges/status
            $badgeOnlineTitle = $onlinFlg === 1 ? 'Curso online' : 'Curso offline';
            $badgeOnlineClass = $onlinFlg === 1 ? 'text-success' : 'text-danger';

            $badgeComTitle = $comercial === 1 ? 'Comercial' : 'Livre';
            $badgeComClass = $comercial === 1 ? 'text-success' : 'text-danger';

            $badgeVisTitle = $visivel === 1 ? 'Visível' : 'Oculto';
            $badgeVisClass = $visivel === 1 ? 'bg-success' : 'bg-secondary';

            $matrizLabel    = $matrizFlg === 1 ? '<span class="badge bg-info text-dark ms-2">Matriz</span>' : '';
            ?>
            <li class="list-group-item flex-column mb-2 shadow-sm rounded">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-mortarboard me-2 text-primary fs-5"></i>

                        <a href="cursos_editar.php?id=<?= $encId; ?>"
                            class="text-decoration-none fw-semibold text-dark me-3">
                            <?= $nome; ?> <?= $matrizLabel; ?> <?= $id; ?>
                            <small class="text-muted ms-1">(<?= $qtdTurmas; ?> turma<?= $qtdTurmas !== 1 ? 's' : '' ?>)</small>
                        </a>

                        <button class="btn btn-sm btn-outline-secondary"
                            onclick="verTurmas(<?= $id; ?>, this)"
                            aria-expanded="false"
                            aria-controls="turmas-<?= $id; ?>">
                            <i class="bi bi-chevron-down"></i>
                        </button>
                    </div>

                    <div class="d-flex align-items-center gap-3">
                        <span class="badge rounded-pill <?= $badgeVisClass; ?>" data-bs-toggle="tooltip" title="<?= $badgeVisTitle; ?>">
                            <?= $ordem; ?>
                        </span>
                        <span data-bs-toggle="tooltip" title="<?= $badgeOnlineTitle; ?>">
                            <i class="bi bi-globe2 fs-5 <?= $badgeOnlineClass; ?>"></i>
                        </span>
                        <span data-bs-toggle="tooltip" title="<?= $badgeComTitle; ?>">
                            <i class="fa fa-dollar-sign fs-5 <?= $badgeComClass; ?>"></i>
                        </span>
                    </div>
                </div>

                <!-- Turmas (lazy via AJAX) -->
                <div class="mt-2 ps-4 collapse" id="turmas-<?= $id; ?>" data-loaded="0" data-url="cursosv1.0/ajax_carregar_turmas.php?id=<?= $id; ?>">
                    <!-- conteúdo AJAX -->
                </div>
            </li>
        <?php endwhile; ?>
    </ul>
<?php else: ?>
    <div class="alert alert-warning d-flex align-items-center" role="alert">
        <i class="bi bi-exclamation-triangle-fill me-2"></i>
        Nenhum curso encontrado com os filtros atuais.
    </div>
<?php endif; ?>

<!-- Loader reutilizável -->
<div id="loaderInlineTemplate" class="d-none">
    <small class="text-muted d-inline-flex align-items-center">
        <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
        Carregando turmas...
    </small>
</div>

<script>
    // Bootstrap tooltips
    document.addEventListener('DOMContentLoaded', () => {
        const ttList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        ttList.map(el => new bootstrap.Tooltip(el));
    });

    // Cache simples e animação com Collapse
    async function verTurmas(idCurso, btn) {
        const box = document.getElementById('turmas-' + idCurso);
        if (!box) return;

        const collapse = bootstrap.Collapse.getOrCreateInstance(box, {
            toggle: false
        });
        const expanded = btn.getAttribute('aria-expanded') === 'true';

        if (expanded) {
            collapse.hide();
            btn.setAttribute('aria-expanded', 'false');
            btn.innerHTML = '<i class="bi bi-chevron-down"></i>';
            return;
        }

        // Carrega via AJAX apenas uma vez (cache)
        const isLoaded = box.getAttribute('data-loaded') === '1';
        if (!isLoaded) {
            const loaderTpl = document.getElementById('loaderInlineTemplate');
            box.innerHTML = loaderTpl ? loaderTpl.innerHTML : '<small class="text-muted">Carregando turmas...</small>';

            const url = box.getAttribute('data-url') || ('cursosv1.0/ajax_carregar_turmas.php?id=' + encodeURIComponent(idCurso));
            try {
                const res = await fetch(url, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                if (!res.ok) throw new Error('HTTP ' + res.status);
                const html = await res.text();
                box.innerHTML = html;
                box.setAttribute('data-loaded', '1');
            } catch (e) {
                box.innerHTML = '<small class="text-danger">Erro ao carregar turmas.</small>';
                console.error(e);
            }
        }

        collapse.show();
        btn.setAttribute('aria-expanded', 'true');
        btn.innerHTML = '<i class="bi bi-chevron-up"></i>';
    }
</script>
<?php
// --- Controle do filtro de status ---
// --- Controle do filtro de status ---
$filtroGet = strtolower(trim($_GET['filtro'] ?? ''));

switch ($filtroGet) {
    case 'oculto':
        $filtro = '0';
        break;
    case 'lixeira':
        $filtro = '9';
        break;
    case 'visivel':
    case '':
    default:
        $filtro = '1';
        break;
}

// Label e cor do status atual
$labelFiltro = [
    '1' => ['txt' => 'Visível',  'class' => 'badge bg-success'],
    '0' => ['txt' => 'Oculto',   'class' => 'badge bg-secondary'],
    '9' => ['txt' => 'Lixeira',  'class' => 'badge bg-danger']
][$filtro];


// --- Consulta ---
$con = config::connect();
$stmt = $con->prepare("
    SELECT codcursos, modulo, ordemm, visivelm, codigomodulos
    FROM new_sistema_modulos_PJA
    WHERE codcursos = :idcurso AND visivelm = :filtro
    ORDER BY ordemm
");
$stmt->bindParam(':idcurso', $idCurso, PDO::PARAM_INT);
$stmt->bindParam(':filtro', $filtro, PDO::PARAM_STR);
$stmt->execute();
?>

<div class="d-flex align-items-center gap-2 mb-2">
    <i class="bi bi-view-stacked text-primary"></i>
    <span class="<?= $labelFiltro['class'] ?>"><?= $labelFiltro['txt'] ?></span>
    <small class="text-muted">Curso: <?= (int)$idCurso ?></small>
</div>

<?php if ($stmt->rowCount() > 0): ?>
    <ul class="list-group">
        <?php while ($row = $stmt->fetch(PDO::FETCH_ASSOC)): ?>
            <?php
            $id     = (int)$row['codigomodulos'];
            $encId  = encrypt($id, $action = 'e');
            $nm     = $row['modulo'];
            $ordem  = (int)$row['ordemm'];
            $status = (string)$row['visivelm'];
            // Ícone e cor do status
            $icoClasse = ($status === '1') ? 'text-success' : (($status === '0') ? 'text-muted' : 'text-danger');
            $icoTitle  = ($status === '1') ? 'Visível' : (($status === '0') ? 'Oculto' : 'Lixeira');
            ?>
            <li class="list-group-item flex-column mb-2 shadow-sm rounded">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-mortarboard me-2 text-primary fs-5"></i>
                        <a href="cursos_publicacoes.php?id=<?= $_GET['id']; ?>&md=<?= $encId; ?>"
                            class="text-decoration-none fw-semibold text-dark me-3">
                            <?= htmlspecialchars($nm) ?> <small class="text-muted">· Md: <?= $id ?></small>
                        </a>
                    </div>

                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-secondary rounded-pill" title="Ordem"><?= $ordem ?></span>

                        <i class="bi bi-globe2 fs-5 <?= $icoClasse ?>" title="<?= $icoTitle ?>"></i>

                        <?php if ($status !== '9'): // só mostra lixeira se ainda não estiver na lixeira 
                        ?>
                            <button type="button"
                                class="btn btn-sm btn-outline-danger enviar-lixeira"
                                data-id="<?= $encId ?>"
                                title="Enviar para a lixeira">
                                <i class="bi bi-trash3"></i>
                            </button>
                        <?php else: ?>
                            <span class="badge bg-danger-subtle text-danger border">Na lixeira</span>
                        <?php endif; ?>
                    </div>
                </div>
            </li>
        <?php endwhile; ?>
    </ul>
<?php else: ?>
    <p class="text-muted">Nenhum módulo encontrado.</p>
<?php endif; ?>

<script>
    (function() {
        // Ativa tooltips se Bootstrap estiver carregado
        if (window.bootstrap && bootstrap.Tooltip) {
            document.querySelectorAll('[title]').forEach(function(el) {
                new bootstrap.Tooltip(el);
            });
        }

        // Ação: enviar para lixeira
        document.querySelectorAll('.enviar-lixeira').forEach(function(btn) {
            btn.addEventListener('click', async function() {
                const encId = this.getAttribute('data-id');
                if (!encId) return;

                if (!confirm('Enviar este módulo para a lixeira?')) return;

                const fd = new FormData();
                fd.set('id', encId);
                fd.set('status', '9'); // lixeira

                try {
                    const resp = await fetch('modulosv1.0/ajax_moduloAlterarStatus.php', {
                        method: 'POST',
                        body: fd,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });
                    const data = await resp.json();
                    if (data && data.success) {
                        // Recarrega a página mantendo o filtro atual
                        const params = new URLSearchParams(window.location.search);
                        // mantém id e filtro
                        window.location.search = params.toString();
                    } else {
                        alert(data && data.message ? data.message : 'Falha ao mover para lixeira.');
                    }
                } catch (err) {
                    alert('Erro de comunicação: ' + err);
                }
            });
        });
    })();
</script>
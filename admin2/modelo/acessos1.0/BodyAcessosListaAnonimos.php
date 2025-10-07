<?php
// BodyAcessosLista.php (MÓDULO) — Filtra por dia (default: hoje)

if (!defined('BASEPATH')) define('BASEPATH', true);
if (!defined('APP_ROOT')) define('APP_ROOT', dirname(__DIR__));
require_once APP_ROOT . '/conexao/class.conexao.php';
require_once APP_ROOT . '/autenticacao.php';

function h($s)
{
    return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
}

/** Monta a foto circular do usuário (usando imagem50, fallback usuário.png) */
function fotoUsuarioCircular(?string $pasta, ?string $img50): string
{
    $p = trim((string)$pasta);
    $img = trim((string)$img50);
    if ($img === '' || $img === 'usuario.jpg') {
        return '../../fotos/usuarios/usuario.png';
    }
    return '../../fotos/usuarios/' . $p . '/' . $img;
}

// --------- Filtro por DIA (default hoje) ----------
$hojeISO     = date('Y-m-d');
$dataFiltro  = isset($_GET['dia']) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $_GET['dia']) ? $_GET['dia'] : $hojeISO;
$dataFiltroBR = date('d/m/Y', strtotime($dataFiltro));
// --------------------------------------------------

// ROTA para ver dados do usuário (ajuste se necessário)
$ROTA_VER_USUARIO = 'alunos_editar.php?id=';

try {
    $pdo = config::connect();
    // Lista acessos do DIA e onde há usuário (idusuariora > 0)
    $sql = "
        SELECT 
            r.chavera, r.idusuariora, r.idturmara, r.ipra, r.dispositivora, r.datara, r.horara,
            c.codigocadastro, c.nome, c.pastasc, c.imagem50,
            t.nometurma
        FROM a_site_registraacessosvendas r
        LEFT JOIN new_sistema_cadastro c  ON c.codigocadastro = r.idusuariora
        LEFT JOIN new_sistema_cursos_turmas t ON t.codigoturma = r.idturmara
        WHERE r.idusuariora = 0
          AND r.datara = :dataFiltro
        ORDER BY r.datara DESC, r.horara DESC
        LIMIT 500
    ";
    $st = $pdo->prepare($sql);
    $st->bindParam(':dataFiltro', $dataFiltro);
    $st->execute();
    $registros = $st->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    echo "<div class='alert alert-danger'>Erro ao carregar acessos: " . h($e->getMessage()) . "</div>";
    return;
}
?>

<!-- Filtro por dia -->
<form class="row g-2 align-items-end mb-3" method="get">
    <div class="col-auto">
        <label for="dia" class="form-label mb-0 small">Filtrar por dia</label>
        <input type="date" class="form-control form-control-sm" id="dia" name="dia" value="<?= h($dataFiltro) ?>">
    </div>
    <div class="col-auto">
        <button class="btn btn-sm btn-primary" type="submit">
            <i class="bi bi-search me-1"></i> Aplicar
        </button>
        <a class="btn btn-sm btn-outline-secondary" href="?dia=<?= h($hojeISO) ?>">
            <i class="bi bi-calendar-day me-1"></i> Hoje
        </a>
    </div>
    <div class="col-auto ms-auto">
        <span class="badge text-bg-light border">
            <i class="bi bi-calendar3 me-1"></i><?= h($dataFiltroBR) ?>
        </span>
        <span class="badge text-bg-secondary ms-1">
            Total: <?= count($registros) ?>
        </span>
    </div>
</form>

<div class="row g-3">
    <?php if (!empty($registros)): ?>
        <?php foreach ($registros as $rw):
            $foto = fotoUsuarioCircular($rw['pastasc'] ?? '', $rw['imagem50'] ?? 'usuario.jpg');

            $idusuario = (int)($rw['idusuariora'] ?? 0);
            // ✅ Se idusuario = 1, força "Professor"
            $nome = ($idusuario === 1) ? 'Professor' : ($rw['nome'] ?? '—');

            $nometurma  = $rw['nometurma'] ?? '—';
            $data       = $rw['datara'] ? date('d/m/Y', strtotime($rw['datara'])) : '—';
            $hora       = $rw['horara'] ?? '—';
            $chavera    = $rw['chavera'] ?? '';
            $ip         = $rw['ipra'] ?? '';
            $dispositivo = $rw['dispositivora'] ?? '';
        ?>
            <div class="col-12" data-aos="fade-up">
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-body d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center gap-3">
                            <img src="<?= h($foto) ?>" alt="Foto de <?= h($nome) ?>"
                                class="rounded-circle" width="56" height="56" style="object-fit:cover;">
                            <div class="d-flex flex-column">
                                <a class="fw-semibold text-decoration-none"
                                    href="<?= h($ROTA_VER_USUARIO . $idusuario) ?>"
                                    title="Ver dados do aluno">
                                    <?= h($nome) ?>
                                </a>
                                <div class="small text-muted">
                                    <i class="bi bi-people me-1"></i><?= h($nometurma) ?>
                                </div>
                                <div class="small">
                                    <span class="badge text-bg-light border">
                                        <i class="bi bi-calendar-date me-1"></i><?= h($data) ?>
                                        <i class="bi bi-dot"></i>
                                        <i class="bi bi-clock me-1"></i><?= h($hora) ?>
                                    </span>
                                    <?php if ($dispositivo !== ''): ?>
                                        <span class="badge text-bg-info-subtle border ms-1">
                                            <i class="bi bi-phone me-1"></i><?= h($dispositivo) ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex align-items-center gap-2">
                            <?php if ($ip !== ''): ?>
                                <span class="badge text-bg-secondary" title="IP de origem">
                                    <i class="bi bi-wifi"></i> <?= h($ip) ?>
                                </span>
                            <?php endif; ?>
                            <button
                                class="btn btn-sm btn-outline-primary btnHistorico"
                                data-chavera="<?= h($chavera) ?>"
                                data-idusuario="<?= h($idusuario) ?>"
                                data-ip="<?= h($ip) ?>"
                                title="Ver páginas acessadas">
                                <i class="bi bi-journal-text me-1"></i> Histórico
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="col-12">
            <div class="alert alert-warning">Nenhum acesso encontrado para <?= h($dataFiltroBR) ?>.</div>
        </div>
    <?php endif; ?>
</div>

<!-- Modal Histórico -->
<div class="modal fade" id="modalHistoricoAcessos" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content rounded-4">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-journal-text me-2"></i> Histórico de Páginas Acessadas
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body">
                <div id="infoHeaderHistorico" class="small text-muted mb-2"></div>
                <div id="conteudoHistorico">
                    <div class="d-flex align-items-center gap-2 text-muted">
                        <div class="spinner-border spinner-border-sm" role="status"></div>
                        Carregando histórico...
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>

<script>
    // Abrir modal e buscar histórico
    $(document).on('click', '.btnHistorico', function() {
        const chavera = $(this).data('chavera');
        const idusuario = $(this).data('idusuario');
        const ip = $(this).data('ip') || '';

        $('#conteudoHistorico').html(
            '<div class="d-flex align-items-center gap-2 text-muted">' +
            '<div class="spinner-border spinner-border-sm" role="status"></div>' +
            ' Carregando histórico...</div>'
        );

        $('#infoHeaderHistorico').html(
            '<span class="badge text-bg-light border me-1"><i class="bi bi-fingerprint me-1"></i>Chave: ' + (chavera || '—') + '</span>' +
            (ip ? '<span class="badge text-bg-secondary"><i class="bi bi-wifi me-1"></i>IP: ' + ip + '</span>' : '')
        );

        const modalEl = document.getElementById('modalHistoricoAcessos');
        const modal = new bootstrap.Modal(modalEl);
        modal.show();

        $.ajax({
                url: 'acessos1.0/ajax_acessosHistorico.php',
                method: 'POST',
                data: {
                    chavera: chavera,
                    idusuario: idusuario
                },
                dataType: 'html'
            })
            .done(function(html) {
                $('#conteudoHistorico').html(html);
            })
            .fail(function(xhr) {
                $('#conteudoHistorico').html(
                    '<div class="alert alert-danger mb-0">Falha ao carregar histórico: ' + (xhr.responseText || xhr.statusText) + '</div>'
                );
            });
    });
</script>
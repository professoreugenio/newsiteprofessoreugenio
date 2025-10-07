<?php
// BodyAcessosLista.php — Lista acessos diários por usuário (base nas tabelas definidas)

if (!defined('BASEPATH')) define('BASEPATH', true);
if (!defined('APP_ROOT')) define('APP_ROOT', dirname(__DIR__));
require_once APP_ROOT . '/conexao/class.conexao.php';
require_once APP_ROOT . '/autenticacao.php';

date_default_timezone_set('America/Recife');

function h($s)
{
    return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
}

/** Foto circular do usuário (imagem50; fallback usuario.png) */
function fotoUsuarioCircular(?string $pasta, ?string $img50): string
{
    $p = trim((string)$pasta);
    $img = trim((string)$img50);
    if ($img === '' || $img === 'usuario.jpg' || $img === 'usuario.png') {
        return '../../fotos/usuarios/usuario.png';
    }
    return '../../fotos/usuarios/' . $p . '/' . $img;
}

/** Converte dispositivo: prioridade historico(int 1/2/3), fallback anonímico(varchar) */
function labelDispositivo(?int $histInt, ?string $anonStr): string
{
    if (!is_null($histInt)) {
        switch ((int)$histInt) {
            case 1:
                return 'desktop';
            case 2:
                return 'mobile';
            case 3:
                return 'tablet';
            default:
                return '—';
        }
    }
    $d = strtolower(trim((string)$anonStr));
    if ($d !== '') return $d;
    return '—';
}

// --------- Filtro por DIA (default hoje) ----------
$hojeISO      = date('Y-m-d');
$dataFiltro   = (isset($_GET['dia']) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $_GET['dia'])) ? $_GET['dia'] : $hojeISO;
$dataFiltroBR = date('d/m/Y', strtotime($dataFiltro));
// --------------------------------------------------

// ROTA para ver dados do usuário (ajuste se necessário)
$ROTA_VER_USUARIO = 'alunoTurmas.php?idUsuario=';

try {
    $pdo = config::connect();

    // Base: a_site_registraacessoUsuario (u)
    // LEFT JOIN a_site_registraacessos (a) para IP/dispositivo do registro anônimo no MESMO dia
    // Subconsultas em a_site_registraacessoshistorico (h) para:
    //  - último dispositivorah do dia (prioritário)
    //  - total de pageviews do dia
    $sql = "
        SELECT
            u.chaveacessorau       AS chavera,
            u.idusuariorau         AS idusuario,
            u.idturmarau           AS idturma,
            u.datarau              AS data_reg,
            u.horarau              AS hora_reg,

            c.codigocadastro,
            c.nome,
            c.pastasc,
            c.imagem50,

            t.nometurma,

            a.ipra                 AS ip_anon,
            a.dispositivora        AS disp_anon,

            /* último dispositivo do dia no histórico (1=desktop,2=mobile,3=tablet) */
            (
                SELECT h1.dispositivorah
                FROM a_site_registraacessoshistorico h1
                WHERE h1.chaverah = u.chaveacessorau
                  AND h1.datarah  = u.datarau
                ORDER BY h1.datarah DESC, h1.horarah DESC
                LIMIT 1
            ) AS disp_hist,

            /* total de pageviews do dia no histórico */
            (
                SELECT COUNT(*)
                FROM a_site_registraacessoshistorico h2
                WHERE h2.chaverah = u.chaveacessorau
                  AND h2.datarah  = u.datarau
            ) AS views_dia

        FROM a_site_registraacessoUsuario u
        LEFT JOIN new_sistema_cadastro c
               ON c.codigocadastro = u.idusuariorau
        LEFT JOIN new_sistema_cursos_turmas t
               ON t.codigoturma = u.idturmarau
        LEFT JOIN a_site_registraacessos a
               ON a.chavera = u.chaveacessorau
              AND a.datara = u.datarau

        WHERE u.datarau = :dataFiltro
        ORDER BY u.datarau DESC, u.horarau DESC
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

            $idusuario = (int)($rw['idusuario'] ?? 0);
            $nome      = ($idusuario === 1) ? 'Professor' : ($rw['nome'] ?? '—');
            $nometurma = $rw['nometurma'] ?? '—';

            $data = $rw['data_reg'] ? date('d/m/Y', strtotime($rw['data_reg'])) : '—';
            $hora = $rw['hora_reg'] ?? '—';
            $chavera = $rw['chavera'] ?? '';

            $ip = $rw['ip_anon'] ?? '';
            $dispLabel = labelDispositivo(
                isset($rw['disp_hist']) ? (int)$rw['disp_hist'] : null,
                $rw['disp_anon'] ?? null
            );

            $views_dia = (int)($rw['views_dia'] ?? 0);
        ?>
            <div class="col-12" data-aos="fade-up">
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-body d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center gap-3">
                            <img src="<?= h($foto) ?>" alt="Foto de <?= h($nome) ?>"
                                class="rounded-circle" width="56" height="56" style="object-fit:cover;">
                            <div class="d-flex flex-column">

                                <?php $encId = encrypt($idusuario, $action = 'e'); ?>
                                <a class="fw-semibold text-decoration-none"
                                    href="<?= h($ROTA_VER_USUARIO . $encId) ?>"
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

                                    <?php if ($dispLabel !== '—'): ?>
                                        <span class="badge text-bg-info-subtle border ms-1" title="Dispositivo">
                                            <i class="bi bi-phone me-1"></i><?= h($dispLabel) ?>
                                        </span>
                                    <?php endif; ?>

                                    <span class="badge text-bg-secondary ms-1" title="Páginas do dia">
                                        <i class="bi bi-journal-text me-1"></i><?= h($views_dia) ?> pág/dia
                                    </span>
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
                                data-data="<?= h($rw['data_reg'] ?? '') ?>"
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
    $(document).on('click', '.btnHistorico', function() {
        const chavera = $(this).data('chavera') || '';
        const idusuario = $(this).data('idusuario') || '';
        const dataDia = $(this).data('data') || ''; // YYYY-mm-dd

        $('#conteudoHistorico').html(
            '<div class="d-flex align-items-center gap-2 text-muted">' +
            '<div class="spinner-border spinner-border-sm" role="status"></div>' +
            ' Carregando histórico...</div>'
        );

        $('#infoHeaderHistorico').html(
            '<span class="badge text-bg-light border me-1"><i class="bi bi-fingerprint me-1"></i>Chave: ' + (chavera || '—') + '</span>' +
            (dataDia ? '<span class="badge text-bg-secondary"><i class="bi bi-calendar me-1"></i>Dia: ' + dataDia + '</span>' : '')
        );

        const modalEl = document.getElementById('modalHistoricoAcessos');
        const modal = new bootstrap.Modal(modalEl);
        modal.show();

        $.ajax({
                url: 'acessos1.0/ajax_acessosHistorico.php', // endpoint que lista as páginas do dia
                method: 'POST',
                data: {
                    chavera: chavera,
                    idusuario: idusuario,
                    dia: dataDia
                },
                dataType: 'html'
            })
            .done(function(html) {
                $('#conteudoHistorico').html(html);
            })
            .fail(function(xhr) {
                $('#conteudoHistorico').html(
                    '<div class="alert alert-danger mb-0">Falha ao carregar histórico: ' +
                    (xhr.responseText || xhr.statusText) + '</div>'
                );
            });
    });
</script>
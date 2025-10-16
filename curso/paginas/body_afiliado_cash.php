<?php include 'v2.0/nav.php'; ?>

<?php
// Contexto fornecido pelo arquivo pai: $idUser, encrypt(), conexão já aberta, etc.

$idProduto = 0;
if (isset($_GET['prod'])) {
    $dec = encrypt($_GET['prod'], 'd');
    $idProduto = (int)$dec;
}

function h($s)
{
    return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
}
function money_br(float $v): string
{
    return 'R$ ' . number_format($v, 2, ',', '.');
}
function with_aff_param(string $urlBase, string $afKey): string
{
    if ($afKey === '' || $urlBase === '') return $urlBase;
    return $urlBase . (str_contains($urlBase, '?') ? '&' : '?') . 'af=' . rawurlencode($afKey);
}
function calcularComissao(float $valorProduto, ?float $comissaoap): array
{
    if ($comissaoap === null || $comissaoap <= 0) return ['valor' => 0.0, 'label' => '0%'];
    if ($comissaoap > 100) return ['valor' => $comissaoap, 'label' => money_br($comissaoap) . ' (fixo)'];
    $pct = ($comissaoap <= 1) ? $comissaoap * 100 : $comissaoap;
    $val = $valorProduto * ($pct / 100);
    $pctFmt = rtrim(rtrim(number_format($pct, 2, ',', '.'), '0'), ',');
    return ['valor' => $val, 'label' => $pctFmt . '%'];
}

$con = config::connect();

/* chave do afiliado */
$afKey = '';
$stmtKey = $con->prepare("
    SELECT chaveafiliadoSA 
      FROM a_site_afiliados_chave
     WHERE idusuarioSA = :idu
  ORDER BY dataSA DESC, horaSA DESC
     LIMIT 1
");
$stmtKey->bindValue(':idu', (int)$idUser, PDO::PARAM_INT);
$stmtKey->execute();
$afKey = (string)($stmtKey->fetchColumn() ?: '');

/* produto (inclui pastaap p/ imagem e urlprodutoap p/ link) */
$params = [];
if ($idProduto > 0) {
    $sqlProd = "
        SELECT codigoprodutoafiliado, nomeap, valorap, comissaoap, img1080x1920, img1024x1024, pastaap, dataap, horaap, urlprodutoap
          FROM a_site_afiliados_produto
         WHERE codigoprodutoafiliado = :id
         LIMIT 1
    ";
    $params[':id'] = $idProduto;
} else {
    $sqlProd = "
        SELECT codigoprodutoafiliado, nomeap, valorap, comissaoap, img1080x1920, img1024x1024, pastaap, dataap, horaap, urlprodutoap
          FROM a_site_afiliados_produto
      ORDER BY dataap DESC, horaap DESC
         LIMIT 1
    ";
}
$stmtProd = $con->prepare($sqlProd);
foreach ($params as $k => $v) $stmtProd->bindValue($k, $v, PDO::PARAM_INT);
$stmtProd->execute();
$produto = $stmtProd->fetch(PDO::FETCH_ASSOC);

$temProduto = is_array($produto) && !empty($produto);
$nome       = $temProduto ? (string)$produto['nomeap'] : '';
$valorap    = $temProduto ? (float)$produto['valorap'] : 0.00;
$comissaoap = $temProduto ? (float)$produto['comissaoap'] : 0.00;
$pastaap    = $temProduto ? (string)($produto['pastaap'] ?? '') : '';
$imgQ       = $temProduto ? (string)($produto['img1024x1024'] ?? '') : '';
$imgS       = $temProduto ? (string)($produto['img1080x1920'] ?? '') : '';
$urlBase    = $temProduto ? (string)($produto['urlprodutoap'] ?? '') : '';

/* IMAGEM — caminho (mantendo seu padrão atual de base relativa) */
$IMG_BASE_URL = '../../';
$imgFile = $imgQ ?: $imgS;
$imgSrc  = $IMG_BASE_URL . $imgFile;
$placeholder = '../../img/noimg.jpg';

$calc       = calcularComissao($valorap, $comissaoap);
$valorCom   = $calc['valor'];
$labelCom   = $calc['label'];
$afUrl      = $temProduto ? with_aff_param($urlBase, $afKey) : '';
?>

<style>
    /* ---------- tema visual moderno e compacto ---------- */
    :root {
        --af-card-bg: #fff;
        --af-text: #111827;
        --af-subtext: #6b7280;
        --af-border: rgba(17, 24, 39, .08);
        --af-soft: rgba(2, 6, 23, .04);
    }

    .af-card {
        background: var(--af-card-bg);
        color: var(--af-text);
        border: 1px solid var(--af-border);
        box-shadow: 0 6px 20px rgba(2, 6, 23, .06);
        backdrop-filter: saturate(120%) blur(2px);
    }

    .title {
        font-weight: 700;
        line-height: 1.2;
        letter-spacing: .2px;
    }

    .hint {
        color: var(--af-subtext);
        font-size: .875rem;
        line-height: 1.1;
    }

    .value {
        color: var(--af-text);
        font-weight: 700;
        line-height: 1.1;
    }

    .value-strong {
        color: var(--af-text);
        font-weight: 800;
    }

    .row-tight {
        row-gap: .5rem;
    }

    .p-compact {
        padding: 1rem !important;
    }

    /* chips de métricas */
    .metric {
        display: flex;
        align-items: center;
        gap: .5rem;
        background: var(--af-soft);
        border: 1px dashed var(--af-border);
        padding: .5rem .75rem;
        border-radius: .75rem;
    }

    .metric i {
        opacity: .9;
    }

    /* botão copiar destacado */
    .btn-af {
        border-radius: .75rem;
        font-weight: 600;
    }

    /* toolbar da lista */
    .af-toolbar {
        gap: .5rem;
    }

    .af-toolbar .meta {
        display: flex;
        align-items: center;
        gap: .5rem;
    }

    .af-toolbar .label {
        color: var(--af-subtext);
        font-size: .875rem;
    }

    /* tabela moderna */
    .af-table-wrap {
        max-height: 420px;
        overflow: auto;
        border-radius: .75rem;
        border: 1px solid var(--af-border);
    }

    .af-table {
        --bs-table-bg: #fff;
    }

    .af-table thead th {
        position: sticky;
        top: 0;
        z-index: 2;
        background: #f8fafc;
        border-bottom: 1px solid var(--af-border);
        text-transform: uppercase;
        font-weight: 700;
        font-size: .75rem;
        letter-spacing: .3px;
    }

    .af-table td,
    .af-table th {
        padding-top: .6rem;
        padding-bottom: .6rem;
        vertical-align: middle;
    }

    .af-table tbody tr:hover td {
        background: rgba(2, 6, 23, .03);
    }

    /* números monoespaçados à direita */
    .td-num {
        text-align: right;
        font-variant-numeric: tabular-nums;
        white-space: nowrap;
    }

    /* badges suaves */
    .badge-soft {
        border: 1px solid transparent;
        font-weight: 600;
    }

    .badge-soft-warning {
        background: var(--bs-warning-bg-subtle);
        color: #9a6700;
        border-color: #f7e0a3;
    }

    .badge-soft-success {
        background: var(--bs-success-bg-subtle);
        color: #0a6d2a;
        border-color: #bde5c8;
    }

    .badge-soft-info {
        background: var(--bs-info-bg-subtle);
        color: #055160;
        border-color: #b6e3f0;
    }

    /* realce por status */
    .tr-pendente td {
        background: rgba(255, 193, 7, .05);
    }

    .tr-aprovado td {
        background: rgba(25, 135, 84, .04);
    }

    .cell-title {
        font-weight: 600;
    }

    .cell-sub {
        color: var(--af-subtext);
        font-size: .8rem;
    }

    @media (max-width: 576px) {
        .af-toolbar {
            flex-direction: column;
            align-items: flex-start !important;
            gap: .25rem;
        }
    }
</style>

<div class="container py-3" data-aos="fade-up">
    <?php if (!$temProduto): ?>
        <div class="alert alert-warning border-0 shadow-sm rounded-3">
            Nenhum produto encontrado.
        </div>
    <?php else: ?>
        <!-- ======= CARD DO PRODUTO ======= -->
        <div class="card af-card border-0 rounded-4">
            <div class="row g-0 align-items-center row-tight p-compact">
                <!-- Imagem (máx 150px) -->
                <div class="col-12 col-md-3 d-flex justify-content-center">
                    <img
                        src="<?= h($imgSrc ?: $placeholder) ?>"
                        alt="<?= h($nome) ?>"
                        class="img-fluid rounded-3 shadow-sm"
                        style="max-height:150px; object-fit:cover;"
                        onerror="this.onerror=null;this.src='<?= h($placeholder) ?>';">
                </div>

                <!-- Dados -->
                <div class="col-12 col-md-9">
                    <div class="p-compact">
                        <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
                            <h1 class="h5 title mb-0"><?= h($nome) ?></h1>
                        </div>

                        <div class="d-flex flex-wrap gap-3 align-items-stretch mt-2">
                            <div class="metric">
                                <i class="bi bi-cash-coin"></i>
                                <div>
                                    <div class="hint">Valor do produto</div>
                                    <div class="value fs-5"><?= money_br($valorap) ?></div>
                                </div>
                            </div>
                            <div class="metric">
                                <i class="bi bi-percent"></i>
                                <div>
                                    <div class="hint">Comissão</div>
                                    <div class="value">
                                        <?= h($labelCom) ?>
                                        <?php if ($valorCom > 0): ?>
                                            <span class="hint">→</span>
                                            <span class="value-strong"><?= money_br($valorCom) ?></span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex align-items-center gap-3 mt-3">
                            <button
                                class="btn btn-primary btn-sm px-3 btn-af"
                                id="btnCopyLink"
                                type="button"
                                data-link="<?= h($afUrl) ?>"
                                <?php if (!$afUrl): ?>disabled<?php endif; ?>>
                                <i class="bi bi-clipboard-check me-1"></i> Copiar link afiliado
                            </button>

                            <?php if ($afKey === ''): ?>
                                <span class="text-danger small d-inline-flex align-items-center gap-1">
                                    <i class="bi bi-exclamation-triangle"></i>
                                    Cadastre sua chave de afiliado para gerar o link.
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ======= LISTA DE PAGAMENTOS PENDENTES ======= -->
        <div class="card border-0 shadow-sm rounded-4 mt-3 af-card" data-aos="fade-up">
            <?php
            // --- Query das pendências + turma + curso (+ comissão do produto quando houver) ---
            $sqlPend = "
        SELECT 
            ac.codigofiliadoscache,
            ac.idafiliadochaveac,
            ac.idprodutoac,
            ac.idclienteac,
            ac.valorac,
            ac.statusac,
            ac.pagamentoac,
            ac.dataac,
            ac.horaac,
            t.nometurma,
            t.codcursost,
            cat.nome AS nomecurso,
            prod.comissaoap
        FROM a_site_afiliados_cache ac
        LEFT JOIN new_sistema_cursos_turmas t
              ON t.chave = CAST(ac.idprodutoac AS CHAR)
        LEFT JOIN new_sistema_cursos cat
              ON cat.codigocursos = t.codcursost
        LEFT JOIN a_site_afiliados_produto prod
              ON prod.codigoprodutoafiliado = ac.idprodutoac
        WHERE (ac.pagamentoac IS NULL OR ac.pagamentoac = '' OR ac.pagamentoac = 0)
        ORDER BY ac.dataac DESC, ac.horaac DESC
      ";
            $stmtPend = $con->prepare($sqlPend);
            $stmtPend->execute();
            $itens = $stmtPend->fetchAll(PDO::FETCH_ASSOC) ?: [];

            // helper p/ comissão
            $calcComItem = function (float $valor, $comissaoNum): float {
                $c = ($comissaoNum === null) ? 0.0 : (float)$comissaoNum;
                if ($c <= 0)   return 0.0;
                if ($c > 100)  return $c;                  // fixo em R$
                if ($c <= 1)   return $valor * $c;         // fração (0.4 = 40%)
                return $valor * ($c / 100.0);              // percentual (40 = 40%)
            };

            // total com fallback ao $comissaoap do topo
            $totalComissao = 0.0;
            foreach ($itens as $r) {
                $valorItem     = (float)($r['valorac'] ?? 0);
                $comissaoLinha = isset($r['comissaoap']) && $r['comissaoap'] !== null
                    ? (float)$r['comissaoap']
                    : (float)$comissaoap;
                $totalComissao += $calcComItem($valorItem, $comissaoLinha);
            }
            ?>

            <div class="d-flex justify-content-between align-items-center af-toolbar p-3 pb-2">
                <div class="d-flex align-items-center gap-2">
                    <h2 class="h6 m-0 fw-semibold d-flex align-items-center gap-2">
                        <i class="bi bi-receipt-cutoff"></i> Pagamentos pendentes
                    </h2>
                    <span class="badge badge-soft badge-soft-info rounded-pill">
                        <?php echo count($itens); ?> itens
                    </span>
                </div>
                <div class="meta">
                    <span class="label">Comissão total</span>
                    <span class="badge badge-soft badge-soft-success rounded-pill">
                        <?= money_br($totalComissao) ?>
                    </span>
                </div>
            </div>

            <div class="card-body pt-2">
                <?php if (empty($itens)): ?>
                    <div class="alert alert-info border-0 shadow-sm rounded-3 m-0 d-flex align-items-center gap-2">
                        <i class="bi bi-info-circle"></i> Nenhum pagamento pendente encontrado.
                    </div>
                <?php else: ?>
                    <div class="af-table-wrap">
                        <table class="table table-sm af-table table-hover align-middle m-0">
                            <thead>
                                <tr>
                                    <th style="width: 140px;">Data</th>
                                    <th style="width: 110px;">Status</th>
                                    <th>Turma</th>
                                    <th>Curso</th>
                                    <th class="td-num" style="width: 140px;">Valor</th>
                                    <th class="td-num" style="width: 160px;">Comissão</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($itens as $r):
                                    $valor = (float)($r['valorac'] ?? 0);

                                    // percentual com fallback
                                    $comissaoLinha = isset($r['comissaoap']) && $r['comissaoap'] !== null
                                        ? (float)$r['comissaoap']
                                        : (float)$comissaoap;

                                    // valor comissão
                                    $comVl = $calcComItem($valor, $comissaoLinha);

                                    // label percentual
                                    if ($comissaoLinha > 100) {
                                        $pctLabel = 'fixo';
                                    } elseif ($comissaoLinha > 1) {
                                        $pctLabel = rtrim(rtrim(number_format($comissaoLinha, 2, ',', '.'), '0'), ',') . '%';
                                    } elseif ($comissaoLinha > 0) {
                                        $pctLabel = rtrim(rtrim(number_format($comissaoLinha * 100, 2, ',', '.'), '0'), ',') . '%';
                                    } else {
                                        $pctLabel = '0%';
                                    }

                                    // status
                                    $status = (int)($r['statusac'] ?? 0);
                                    $statusBadge = ($status === 0)
                                        ? '<span class="badge badge-soft badge-soft-warning rounded-pill">Pendente</span>'
                                        : '<span class="badge badge-soft badge-soft-success rounded-pill">Aprovado</span>';
                                    $trClass = ($status === 0) ? 'tr-pendente' : 'tr-aprovado';

                                    // data/hora
                                    $dataFmt = '-';
                                    $d = trim((string)($r['dataac'] ?? ''));
                                    $t = trim((string)($r['horaac'] ?? ''));
                                    if ($d !== '') {
                                        $p = explode('-', $d);
                                        if (count($p) === 3) $dataFmt = $p[2] . '/' . $p[1] . '/' . $p[0];
                                        if ($t !== '') $dataFmt .= ' ' . substr($t, 0, 5);
                                    }

                                    // turma/curso
                                    $turma = trim((string)($r['nometurma'] ?? ''));
                                    if ($turma === '') $turma = 'Turma não localizada';
                                    $curso = trim((string)($r['nomecurso'] ?? ''));
                                    if ($curso === '') $curso = 'Curso #' . (string)($r['codcursost'] ?? ($r['idprodutoac'] ?? ''));
                                ?>
                                    <tr class="<?= $trClass ?>">
                                        <td class="text-nowrap"><span class="cell-title"><?= h($dataFmt) ?></span></td>
                                        <td><?= $statusBadge ?></td>
                                        <td class="text-truncate" style="max-width: 280px;">
                                            <div class="cell-title"><?= h($turma) ?></div>
                                        </td>
                                        <td class="text-truncate" style="max-width: 340px;">
                                            <div class="cell-title"><?= h($curso) ?></div>
                                            <div class="cell-sub"><i class="bi bi-percent"></i> Comissão usada: <?= h($pctLabel) ?></div>
                                        </td>
                                        <td class="td-num"><?= money_br($valor) ?></td>
                                        <td class="td-num fw-semibold"><?= money_br($comVl) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="5" class="td-num">Total de comissões</th>
                                    <th class="td-num fw-bold"><?= money_br($totalComissao) ?></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>

    <?php endif; ?>
</div>

<!-- Toast de cópia -->
<div class="position-fixed bottom-0 end-0 p-3" style="z-index:1080">
    <div id="copyToast" class="toast align-items-center text-bg-dark border-0" role="status" aria-live="polite" aria-atomic="true">
        <div class="d-flex">
            <div class="toast-body">Link copiado para a área de transferência!</div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Fechar"></button>
        </div>
    </div>
</div>

<!-- Scripts base (mantidos conforme sua página base) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.js"></script>
<script>
    AOS.init({
        duration: 500,
        once: true
    });

    document.addEventListener('DOMContentLoaded', function() {
        const btn = document.getElementById('btnCopyLink');
        if (!btn) return;
        btn.addEventListener('click', async () => {
            const link = btn.getAttribute('data-link') || '';
            if (!link) return;
            try {
                await navigator.clipboard.writeText(link);
            } catch (e) {
                const temp = document.createElement('input');
                temp.value = link;
                document.body.appendChild(temp);
                temp.select();
                document.execCommand('copy');
                temp.remove();
            }
            const toastEl = document.getElementById('copyToast');
            if (toastEl) new bootstrap.Toast(toastEl, {
                delay: 1500
            }).show();
        });
    });
</script>
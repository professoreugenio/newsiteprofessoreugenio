<?php

/**
 * Body_AfiliadosExtrato.php (com comissão do afiliado)
 * - Lista valores LIBERADOS para pagamento ao afiliado (comissão)
 * - Separa por PIX e Cartão
 * - Mostra Saldo Total (somatório das comissões)
 *
 * Pré: $chaveAfiliado (string)
 */

if (!function_exists('formatBRL')) {
    function formatBRL($v)
    {
        return 'R$ ' . number_format((float)($v ?? 0), 2, ',', '.');
    }
}
if (!function_exists('e')) {
    function e($s)
    {
        return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
    }
}

$chaveAfiliado = $chaveAfiliado ?? '';
if ($chaveAfiliado === '') {
    echo '<div class="alert alert-warning">Chave de afiliado não encontrada.</div>';
    return;
}

$con = config::connect();
if (!$con) {
    echo '<div class="alert alert-danger">Falha na conexão.</div>';
    return;
}

/* =========================================================================
 *  Regra de liberação:
 *   - statussv = 1 (pago/confirmado)
 *   - afiliadopagamentosv = 0 ou NULL (ainda não repassado)
 *   - chaveafiliadosv = $chaveAfiliado
 *
 *  JOIN com a_site_afiliados_produto para pegar comissaoap/valorap
 * ========================================================================= */
$sql = "
  SELECT
    v.valorvendasv,
    v.tipopagamentosv,
    p.comissaoap,
    p.valorap
  FROM a_site_vendas v
  LEFT JOIN a_site_afiliados_produto p
         ON p.codigoprodutoafiliado = v.idcursosv
  WHERE COALESCE(v.afiliadopagamentosv,0) = 0
    AND v.statussv = 1
    AND v.chaveafiliadosv = :chave
  ORDER BY v.datacomprasv DESC, v.horacomprasv DESC, v.codigovendas DESC
";

$rows = [];
if ($con instanceof PDO) {
    $st = $con->prepare($sql);
    $st->execute([':chave' => $chaveAfiliado]);
    $rows = $st->fetchAll(PDO::FETCH_ASSOC);
} elseif ($con instanceof mysqli) {
    if (method_exists($con, 'set_charset')) $con->set_charset('utf8mb4');
    $sqlM = str_replace(':chave', '?', $sql);
    $st = $con->prepare($sqlM);
    if (!$st) {
        echo '<div class="alert alert-danger">Erro na consulta.</div>';
        return;
    }
    $st->bind_param('s', $chaveAfiliado);
    $st->execute();
    $res = $st->get_result();
    $rows = $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
    $st->close();
} else {
    echo '<div class="alert alert-danger">Tipo de conexão não suportado.</div>';
    return;
}

/* =========================================================================
 *  Cálculo da comissão por linha
 *  Modo: 'auto' (padrão) | 'percent' | 'amount'
 * ========================================================================= */
$_COMMISSION_MODE = $_COMMISSION_MODE ?? 'auto';

function calcComissaoLinha(array $r, string $mode = 'auto'): float
{
    $valorVenda = (float)($r['valorvendasv'] ?? 0);
    $comissaoAP = isset($r['comissaoap']) ? (float)$r['comissaoap'] : 0;
    $valorAP    = isset($r['valorap'])    ? (float)$r['valorap']    : 0;

    if ($valorVenda <= 0) return 0.0;

    if ($mode === 'percent') {
        return $valorVenda * ($comissaoAP / 100.0);
    }
    if ($mode === 'amount') {
        if ($valorAP > 0) return $comissaoAP * ($valorVenda / $valorAP);
        return $comissaoAP;
    }

    // AUTO: decide baseado em comissaoap parecer % (<= 100)
    if ($comissaoAP > 0 && $comissaoAP <= 100) {
        return $valorVenda * ($comissaoAP / 100.0);
    }
    // Caso seja valor fixo em R$: escalar se houver desconto
    if ($comissaoAP > 0) {
        if ($valorAP > 0) return $comissaoAP * ($valorVenda / $valorAP);
        return $comissaoAP;
    }
    return 0.0;
}

/* Separação por tipo de pagamento */
$pixVals = [];
$carVals = [];

foreach ($rows as $r) {
    $comissao = calcComissaoLinha($r, $_COMMISSION_MODE);
    if ($comissao <= 0) continue;

    $tipo = mb_strtolower(trim((string)($r['tipopagamentosv'] ?? '')));
    if (strpos($tipo, 'pix') !== false) {
        $pixVals[] = $comissao;
    } else {
        $carVals[] = $comissao; // cartão/credito/debito/outros
    }
}

$totPix = array_sum($pixVals);
$totCar = array_sum($carVals);
$totAll = $totPix + $totCar;
?>

<style>
    /* ====== Cards do Extrato (comissão) ====== */
    .ex-cards {
        display: grid;
        grid-template-columns: 1fr 1fr 1fr;
        gap: 12px;
    }

    .ex-card {
        border-radius: 16px;
        border: 1px solid rgba(255, 255, 255, .08);
        color: #fff;
        background: linear-gradient(145deg, rgba(17, 34, 64, .98), rgba(17, 34, 64, .9));
        padding: 14px;
        box-shadow: 0 .6rem 1.2rem rgba(0, 0, 0, .22);
    }

    .ex-card .hd {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 8px;
    }

    .ex-card .hd .ttl {
        font-weight: 800;
        letter-spacing: .2px;
        font-size: .95rem;
    }

    .ex-card .hd .ico {
        width: 34px;
        height: 34px;
        border-radius: 10px;
        display: grid;
        place-items: center;
        background: rgba(255, 255, 255, .08);
        font-size: 1rem;
    }

    .ex-list {
        max-height: 190px;
        overflow: auto;
        border-radius: 10px;
        background: rgba(255, 255, 255, .03);
        border: 1px solid rgba(255, 255, 255, .05);
        padding: 8px;
    }

    .ex-list .ln {
        display: flex;
        align-items: center;
        justify-content: space-between;
        font-size: .9rem;
        padding: 3px 4px;
        border-radius: 6px;
    }

    .ex-list .ln:nth-child(odd) {
        background: rgba(255, 255, 255, .02);
    }

    .ex-total {
        border-top: 1px dashed rgba(255, 255, 255, .15);
        margin-top: 8px;
        padding-top: 8px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        font-weight: 800;
    }

    .ex-pix {
        background: linear-gradient(145deg, rgba(0, 187, 156, .14), rgba(0, 187, 156, .05));
    }

    .ex-pix .ex-total .vl {
        color: #00BB9C;
    }

    .ex-car {
        background: linear-gradient(145deg, rgba(99, 102, 241, .14), rgba(99, 102, 241, .05));
    }

    .ex-car .ex-total .vl {
        color: #7c83ff;
    }

    .ex-sum {
        background: linear-gradient(145deg, rgba(255, 156, 0, .14), rgba(255, 156, 0, .05));
    }

    .ex-sum .big {
        font-size: 1.3rem;
        font-weight: 900;
        color: #FF9C00;
    }

    .ex-badge {
        font-size: .75rem;
        padding: .15rem .45rem;
        border-radius: 999px;
        background: rgba(255, 255, 255, .08);
        border: 1px solid rgba(255, 255, 255, .12);
    }

    @media (max-width: 992px) {
        .ex-cards {
            grid-template-columns: 1fr;
        }

        .ex-list {
            max-height: 220px;
        }
    }
</style>

<section id="AfiliadosExtrato" class="py-3">
    <div class="container">

        <div class="d-flex align-items-center justify-content-between mb-2">
            <h5 class="m-0" style="color:#00BB9C; font-weight:800; letter-spacing:.3px;">
                <i class="bi bi-cash-coin me-2"></i>Comissões liberadas para pagamento
            </h5>
            <span class="badge rounded-pill" style="background:rgba(0,187,156,.15); color:#00BB9C; border:1px solid rgba(0,187,156,.35);">
                Chave: <strong><?= e($chaveAfiliado) ?></strong>
            </span>
        </div>

        <div class="ex-cards" data-aos="fade-up">
            <!-- PIX -->
            <div class="ex-card ex-pix">
                <div class="hd">
                    <div class="ttl">Comissões via PIX</div>
                    <span class="ico"><i class="bi bi-qr-code"></i></span>
                </div>
                <div class="ex-list">
                    <?php if (empty($pixVals)): ?>
                        <div class="text-white-50 small">Nenhuma comissão liberada por PIX.</div>
                        <?php else: foreach ($pixVals as $i => $v): ?>
                            <div class="ln">
                                <span class="text-white-50">#<?= $i + 1 ?></span>
                                <span class="fw-bold"><?= formatBRL($v) ?></span>
                            </div>
                    <?php endforeach;
                    endif; ?>
                </div>
                <div class="ex-total">
                    <span>Total PIX</span>
                    <span class="vl"><?= formatBRL($totPix) ?></span>
                </div>
            </div>

            <!-- Cartão -->
            <div class="ex-card ex-car">
                <div class="hd">
                    <div class="ttl">Comissões via Cartão</div>
                    <span class="ico"><i class="bi bi-credit-card-2-front"></i></span>
                </div>
                <div class="ex-list">
                    <?php if (empty($carVals)): ?>
                        <div class="text-white-50 small">Nenhuma comissão liberada por Cartão.</div>
                        <?php else: foreach ($carVals as $i => $v): ?>
                            <div class="ln">
                                <span class="text-white-50">#<?= $i + 1 ?></span>
                                <span class="fw-bold"><?= formatBRL($v) ?></span>
                            </div>
                    <?php endforeach;
                    endif; ?>
                </div>
                <div class="ex-total">
                    <span>Total Cartão</span>
                    <span class="vl"><?= formatBRL($totCar) ?></span>
                </div>
            </div>

            <!-- Saldo Total -->
            <div class="ex-card ex-sum">
                <div class="hd">
                    <div class="ttl">Saldo Total para Recebimento</div>
                    <span class="ico"><i class="bi bi-wallet2"></i></span>
                </div>
                <div class="d-flex align-items-baseline justify-content-between">
                    <div class="text-white-50">Somatório de comissões (PIX + Cartão)</div>
                    <div class="big"><?= formatBRL($totAll) ?></div>
                </div>
                <div class="mt-2 d-flex align-items-center gap-2">
                    <span class="ex-badge">PIX: <?= count($pixVals) ?></span>
                    <span class="ex-badge">Cartão: <?= count($carVals) ?></span>
                    <span class="ex-badge">Total: <?= count($pixVals) + count($carVals) ?></span>
                </div>
            </div>
        </div>

        <div class="mt-3 text-white-50 small">
            <i class="bi bi-info-circle me-1"></i>
            Cálculo em modo <code><?= e($_COMMISSION_MODE) ?></code>:
            <code>percent</code> usa % de <code>comissaoap</code>;
            <code>amount</code> usa valor fixo (R$) com ajuste proporcional por desconto;
            <code>auto</code> decide conforme <code>comissaoap</code>.
        </div>

    </div>
</section>
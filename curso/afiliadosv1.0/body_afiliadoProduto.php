<?php
/* ==============================
 * body_afiliadoProduto.php
 * ============================== */

/* Helpers */
if (!function_exists('e')) {
    function e($s)
    {
        return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
    }
}
if (!function_exists('formatBRL')) {
    function formatBRL($v)
    {
        return 'R$ ' . number_format((float)($v ?? 0), 2, ',', '.');
    }
}

/* Ler parâmetro criptografado e obter ID */
$enc = $_GET['prod'] ?? '';
$idProduto = 0;
if ($enc !== '' && function_exists('encrypt')) {
    $dec = encrypt($enc, 'd');
    if (ctype_digit((string)$dec)) {
        $idProduto = (int)$dec;
    }
}
if ($idProduto <= 0) {
    echo '<div class="alert alert-danger">Produto inválido ou não informado.</div>';
    return;
}

/* Conexão e consulta */
$con = config::connect();
if (!$con) {
    echo '<div class="alert alert-danger">Falha na conexão.</div>';
    return;
}

$sql = "SELECT
          codigoprodutoafiliado, nomeap, urlprodutoap, valorap, comissaoap,
          pastaap, img1080x1920, img1024x1024, dataap, horaap, visivelap
        FROM a_site_afiliados_produto
        WHERE codigoprodutoafiliado = :id
        LIMIT 1";

$row = null;
if ($con instanceof PDO) {
    $st = $con->prepare($sql);
    $st->execute([':id' => $idProduto]);
    $row = $st->fetch(PDO::FETCH_ASSOC);
} elseif ($con instanceof mysqli) {
    if (method_exists($con, 'set_charset')) $con->set_charset('utf8mb4');
    $st = $con->prepare(str_replace(':id', '?', $sql));
    $st->bind_param('i', $idProduto);
    $st->execute();
    $res = $st->get_result();
    $row = $res ? $res->fetch_assoc() : null;
    $st->close();
} else {
    echo '<div class="alert alert-danger">Tipo de conexão não suportado.</div>';
    return;
}

if (!$row) {
    echo '<div class="alert alert-warning">Produto não encontrado.</div>';
    return;
}

/* Variáveis do produto */
$nome        = (string)($row['nomeap'] ?? 'Produto');
$urlBase     = (string)($row['urlprodutoap'] ?? '#');
$valor       = (float)($row['valorap'] ?? 0);
$perc        = (float)($row['comissaoap'] ?? 0); // Percentual
$imgV        = $row['img1080x1920'] ?? '';
$imgQ        = $row['img1024x1024'] ?? '';

/* Chave do afiliado (espera-se que venha do contexto) */
$chaveAfiliado = $chaveAfiliado ?? '';
$temAf = ($chaveAfiliado !== '');

/* Montar URL final com &af= */
if ($urlBase !== '') {
    $sep = (strpos($urlBase, '?') !== false) ? '&' : '?';
    $urlAf = $urlBase . $sep . 'af=' . rawurlencode($chaveAfiliado);
} else {
    $urlAf = '#';
}

/* Cálculo do valor a receber (comissão) — comissaoap como PERCENTUAL.
   Se comissaoap no seu banco for em R$, troque para: $valorReceber = (float)$row['comissaoap']; */
$valorReceber = $valor * ($perc / 100);

/* (Opcional) URL de vídeo para Reels — defina antes do require se quiser */
$videoReelsUrl = $videoReelsUrl ?? ''; // ex: 'https://seuservidor.com/midia/reels_prod123.mp4'

/* Gerador de QR Code por URL (serviço público) */
$qrcodeUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=320x320&data=' . rawurlencode($urlAf);
?>

<style>
    /* Header */
    .af-head h4 {
        color: #00BB9C;
        font-weight: 800;
        letter-spacing: .4px;
    }

    /* Cards métricas */
    .af-metrics {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 12px;
    }

    .af-card {
        border-radius: 16px;
        padding: 14px 16px;
        color: #fff;
        border: 1px solid rgba(255, 255, 255, .07);
    }

    .af-card .lbl {
        font-size: .9rem;
        opacity: .9;
        margin-bottom: 6px;
    }

    .af-card .val {
        font-size: 1.35rem;
        font-weight: 900;
        line-height: 1.1;
    }

    .af-card.vlr {
        background: #0f2747;
    }

    .af-card.perc {
        background: #0c3a34;
    }

    .af-card.rec {
        background: #4a2a08;
    }

    .af-chip {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        border-radius: 999px;
        padding: .25rem .6rem;
        font-size: .85rem;
        background: rgba(0, 187, 156, .14);
        color: #00BB9C;
        border: 1px solid rgba(0, 187, 156, .35);
    }

    /* Abas */
    .af-tabs .nav-link {
        text-transform: uppercase;
        font-weight: 800;
        letter-spacing: .3px;
        border: 0 !important;
        color: #9fb3c8;
    }

    .af-tabs .nav-link.active {
        color: #112240;
        background: #FF9C00;
    }

    /* Conteúdo das abas */
    .af-pane {
        background: #0f2747;
        border: 1px solid rgba(255, 255, 255, .07);
        border-radius: 14px;
        padding: 16px;
        color: #fff;
    }

    .af-cta {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
    }

    .btn-copy {
        background: #FF9C00;
        color: #112240;
        font-weight: 800;
        border: 0;
    }

    .btn-copy:hover {
        filter: brightness(1.06);
        color: #112240;
    }

    .af-grid-3 {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 12px;
    }

    .af-card-img {
        background: #0b1a33;
        border: 1px solid rgba(255, 255, 255, .05);
        border-radius: 12px;
        padding: 12px;
        text-align: center;
    }

    .af-card-img img {
        max-width: 100%;
        border-radius: 10px;
    }

    .af-card-img .ttl {
        font-weight: 800;
        margin-bottom: 8px;
        color: #9fb3c8;
    }

    .af-card-img .btn {
        font-weight: 700;
    }

    @media (max-width: 992px) {
        .af-metrics {
            grid-template-columns: 1fr;
        }

        .af-grid-3 {
            grid-template-columns: 1fr;
        }
    }
</style>

<section id="AfiliadoProduto" class="py-3">
    <div class="container">

        <!-- Cabeçalho / Chave -->
        <div class="af-head mb-3 d-flex align-items-center justify-content-between">
            <h4 class="m-0"><i class="bi bi-bag-check me-2"></i><?= e($nome) ?></h4>

        </div>

        <!-- Métricas -->
        <!-- CARD FIXO DE MÉTRICAS (topo/direita) -->
        <div class="af-metrics-card glass" data-aos="fade-left">
            <div class="af-metrics">
                <div class="af-card vlr">
                    <span class="ico"><i class="bi bi-currency-dollar"></i></span>
                    <div class="txt">
                        <div class="lbl">Valor do Produto</div>
                        <div class="val"><?= formatBRL($valor) ?></div>
                    </div>
                </div>

                <div class="af-card perc">
                    <span class="ico"><i class="bi bi-percent"></i></span>
                    <div class="txt">
                        <div class="lbl">% da Comissão</div>
                        <div class="val"><?= number_format($perc, 2, ',', '.') ?>%</div>
                    </div>
                </div>

                <div class="af-card rec">
                    <span class="ico"><i class="bi bi-wallet2"></i></span>
                    <div class="txt">
                        <div class="lbl">Você Recebe</div>
                        <div class="val"><?= formatBRL($valorReceber) ?></div>
                    </div>
                </div>
            </div>

            <span class="af-chip" title="Sua chave exclusiva de afiliado">
                <i class="bi bi-key-fill me-1"></i> Chave:
                <strong><?= $temAf ? e($chaveAfiliado) : '—' ?></strong>
            </span>
        </div>


        <!-- (Opcional) espaçador para evitar sobreposição em telas grandes -->
        <div class="af-metrics-spacer"></div>


        <!-- Abas -->
        <div class="af-tabs mb-2">
            <ul class="nav nav-pills gap-2" id="afTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="tab-whats" data-bs-toggle="pill" data-bs-target="#pane-whats" type="button" role="tab">
                        <i class="bi bi-whatsapp me-1"></i> WhatsApp
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="tab-reels" data-bs-toggle="pill" data-bs-target="#pane-reels" type="button" role="tab">
                        <i class="bi bi-camera-reels me-1"></i> Reels Instagram Vídeo
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="tab-post" data-bs-toggle="pill" data-bs-target="#pane-post" type="button" role="tab">
                        <i class="bi bi-image me-1"></i> Post Imagem
                    </button>
                </li>
            </ul>
        </div>

        <div class="tab-content">

            <!-- WHATSAPP -->
            <div class="tab-pane fade show active" id="pane-whats" role="tabpanel" aria-labelledby="tab-whats">
                <div class="af-pane">
                    <p class="mb-2">
                        <strong>Dica:</strong> compartilhe agora mesmo este link nos seus grupos e contatos do WhatsApp — apresente os benefícios do curso e convide as pessoas a garantirem a vaga. Quanto antes você divulgar, mais chances de conversão!
                    </p>
                    <div class="af-cta">
                        <button type="button" class="btn btn-copy" data-copy="<?= e($urlAf) ?>">
                            <i class="bi bi-clipboard-check"></i> Copiar link de afiliação
                        </button>
                        <a class="btn btn-success"
                            href="https://api.whatsapp.com/send?text=<?= rawurlencode('Conheça este curso: ' . $nome . ' ' . $urlAf) ?>"
                            target="_blank" rel="noopener">
                            <i class="bi bi-whatsapp"></i> Abrir WhatsApp com mensagem
                        </a>
                    </div>
                    <small class="text-white-50 d-block mt-2">Seu link exclusivo: <code class="text-break"><?= e($urlAf) ?></code></small>
                </div>
            </div>

            <!-- REELS INSTAGRAM VÍDEO -->
            <div class="tab-pane fade" id="pane-reels" role="tabpanel" aria-labelledby="tab-reels">
                <div class="af-pane">
                    <?php if (!empty($videoReelsUrl)): ?>
                        <div class="mb-3">
                            <div class="af-card-img">
                                <div class="ttl">Vídeo para Reels</div>
                                <video controls preload="metadata" style="max-width:100%; border-radius:10px;">
                                    <source src="<?= e($videoReelsUrl) ?>" type="video/mp4">
                                </video>
                                <div class="mt-2">
                                    <a class="btn btn-primary btn-sm" href="<?= e($videoReelsUrl) ?>" download>
                                        <i class="bi bi-download"></i> Baixar vídeo
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info">
                            Adicione a variável <code>$videoReelsUrl</code> antes do <em>require</em> para exibir o vídeo de download do Reels (formato .mp4).
                        </div>
                    <?php endif; ?>

                    <div class="af-cta mt-2">
                        <button type="button" class="btn btn-copy" data-copy="<?= e($urlAf) ?>">
                            <i class="bi bi-clipboard-check"></i> Copiar link de afiliação
                        </button>
                        <a class="btn btn-outline-light btn-sm"
                            href="https://www.youtube.com/results?search_query=como+postar+reels+no+instagram+pelo+celular"
                            target="_blank" rel="noopener">
                            <i class="bi bi-youtube"></i> Ver instruções no YouTube
                        </a>
                    </div>
                </div>
            </div>

            <!-- POST IMAGEM -->
            <div class="tab-pane fade" id="pane-post" role="tabpanel" aria-labelledby="tab-post">
                <div class="af-pane">
                    <div class="af-grid-3">
                        <div class="af-card-img">
                            <div class="ttl">Imagem 1080×1920 (Stories/Reels)</div>
                            <?php if ($imgV): ?>
                                <img src="<?= e($imgV) ?>" alt="1080x1920">
                                <div class="mt-2">
                                    <a class="btn btn-primary btn-sm" href="<?= e($imgV) ?>" download>
                                        <i class="bi bi-download"></i> Baixar imagem
                                    </a>
                                </div>
                            <?php else: ?>
                                <div class="text-white-50">Sem imagem cadastrada.</div>
                            <?php endif; ?>
                        </div>

                        <div class="af-card-img">
                            <div class="ttl">Imagem 1024×1024 (Feed)</div>
                            <?php if ($imgQ): ?>
                                <img src="<?= e($imgQ) ?>" alt="1024x1024">
                                <div class="mt-2">
                                    <a class="btn btn-primary btn-sm" href="<?= e($imgQ) ?>" download>
                                        <i class="bi bi-download"></i> Baixar imagem
                                    </a>
                                </div>
                            <?php else: ?>
                                <div class="text-white-50">Sem imagem cadastrada.</div>
                            <?php endif; ?>
                        </div>

                        <div class="af-card-img">
                            <div class="ttl">QR Code do seu link</div>
                            <img src="<?= e($qrcodeUrl) ?>" alt="QR Code" width="320" height="320">
                            <div class="mt-2">
                                <button type="button" class="btn btn-copy btn-sm" data-copy="<?= e($urlAf) ?>">
                                    <i class="bi bi-clipboard-check"></i> Copiar URL do produto
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="mt-3">
                        <button type="button" class="btn btn-copy" data-copy="<?= e($urlAf) ?>">
                            <i class="bi bi-clipboard-check"></i> Copiar URL do produto
                        </button>
                    </div>
                </div>
            </div>

        </div>

        <!-- Toast -->
        <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 1080">
            <div id="copyToast" class="toast align-items-center text-bg-success border-0" role="alert">
                <div class="d-flex">
                    <div class="toast-body">
                        Link copiado com sucesso! Agora é só colar onde quiser.
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                </div>
            </div>
        </div>

    </div>
</section>

<script>
    (function() {
        // Botões de copiar
        document.querySelectorAll('.btn-copy').forEach(function(btn) {
            btn.addEventListener('click', async function() {
                const val = this.getAttribute('data-copy') || '';
                try {
                    await navigator.clipboard.writeText(val);
                    const el = document.getElementById('copyToast');
                    if (window.bootstrap && el) {
                        new bootstrap.Toast(el).show();
                    } else {
                        alert('Copiado!');
                    }
                } catch (e) {
                    alert('Não foi possível copiar o link.');
                }
            });
        });
    })();
</script>
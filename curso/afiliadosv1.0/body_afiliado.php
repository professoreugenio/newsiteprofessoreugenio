<?php
/* Conexão e consulta (mantive daqui para facilitar uso isolado) */
$con = config::connect();
if (!$con) {
    throw new RuntimeException('Falha na conexão.');
}

$sql = "
  SELECT codigoprodutoafiliado, nomeap, urlprodutoap, valorap, comissaoap,
         pastaap, img1080x1920, img1024x1024, dataap, horaap, visivelap
  FROM a_site_afiliados_produto
  WHERE COALESCE(visivelap, 1) = 1
  ORDER BY dataap DESC, horaap DESC, codigoprodutoafiliado DESC
";
$produtos = [];
if ($con instanceof PDO) {
    $st = $con->prepare($sql);
    $st->execute();
    $produtos = $st->fetchAll(PDO::FETCH_ASSOC);
} elseif ($con instanceof mysqli) {
    if (method_exists($con, 'set_charset')) $con->set_charset('utf8mb4');
    $st = $con->prepare($sql);
    $st->execute();
    $res = $st->get_result();
    $produtos = $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
    $st->close();
}

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
?>



<section id="Corpo" class="py-4">
    <div class="container">

        <div class="d-flex align-items-center justify-content-between mb-2">
            <h5 class="m-0" style="color:#00BB9C; font-weight:800; letter-spacing:.4px;">
                <i class="bi bi-bag-check me-2"></i>Produtos para Afiliados
            </h5>
            <span class="badge rounded-pill" style="background:rgba(0,187,156,.15); color:#00BB9C; border:1px solid rgba(0,187,156,.35);">
                <?= isset($produtos) && is_array($produtos) ? count($produtos) : 0 ?> itens
            </span>
        </div>

        <?php if (empty($produtos)): ?>
            <div class="alert alert-info">Nenhum produto disponível para afiliação no momento.</div>
        <?php else: ?>
            <div class="af-list">
                <?php $i = 1;
                foreach ($produtos as $p):
                    $pid   = (int)($p['codigoprodutoafiliado'] ?? 0);
                    $nome  = (string)($p['nomeap']            ?? 'Produto');
                    $valor = $p['valorap']   ?? 0;
                    $comis = $p['comissaoap'] ?? 0;
                    $img   = $p['img1024x1024'] ?: ($p['img1080x1920'] ?? 'https://via.placeholder.com/160/0b172f/ffffff?text=IMG');

                    /* % comissão baseada no valor (se fizer sentido) */
                    $perc = (is_numeric($valor) && (float)$valor > 0 && is_numeric($comis))
                        ? round(((float)$comis / (float)$valor) * 100) : 0;

                    /* encrypt do ID -> afiliados_produtos.php?prod= */
                    $hash = function_exists('encrypt') ? encrypt((string)$pid, 'e') : (string)$pid;
                    $link = 'afiliados_produtos.php?prod=' . rawurlencode($hash);
                ?>
                    <div class="af-item" data-aos="fade-up">
                        <div class="af-index"><?= str_pad((string)$i, 2, '0', STR_PAD_LEFT) ?></div>
                        <div class="af-sep"></div>
                        <img class="af-thumb" src="<?= e($img) ?>" alt="<?= e($nome) ?>" width="80" height="80" loading="lazy">
                        <div class="af-info">
                            <div class="af-title"><?= e($nome) ?></div>
                            <div class="af-meta">
                                <span>Valor: <span class="val"><?= formatBRL($valor) ?></span></span>
                                <span>Comissão: <span class="com"><?= (int)$perc ?>%</span></span>
                            </div>
                        </div>
                        <div class="af-action">
                            <a href="<?= e($link) ?>" class="af-btn" title="Abrir produto">
                                <i class="bi bi-box-arrow-up-right"></i> Abrir
                            </a>
                        </div>
                    </div>
                <?php $i++;
                endforeach; ?>
            </div>
        <?php endif; ?>

    </div>
</section>
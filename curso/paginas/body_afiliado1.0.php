<?php

/**
 * AUTENTICAÇÃO POR COOKIE (sem SESSION), conforme seu padrão
 */
try {
    if (!empty($_COOKIE['adminstart'])) {
        $decUser = encrypt($_COOKIE['adminstart'], $action = 'd');
    } else if (!empty($_COOKIE['startusuario'])) {
        $decUser = encrypt($_COOKIE['startusuario'], $action = 'd');
    } else {
        throw new Exception('Usuário não autenticado (cookies ausentes).');
    }

    if (!$decUser || strpos($decUser, '&') === false) {
        throw new Exception('Token de usuário inválido.');
    }

    $expUser = explode("&", $decUser);
    $idUser = (int) ($expUser[0] ?? 0);
    if ($idUser <= 0) {
        throw new Exception('Usuário inválido.');
    }
} catch (Throwable $e) {
    echo '<div class="alert alert-danger">' . $e->getMessage() . '</div>';
    return;
}

// Helpers
function formatBRL($valor)
{
    // caso esteja vindo string/decimal ou inteiro
    if ($valor === null || $valor === '') return 'R$ 0,00';
    return 'R$ ' . number_format((float)$valor, 2, ',', '.');
}

// Verifica se já tem dados do afiliado
$stmtDados = $con->prepare("
    SELECT codigodados, idusuarioad, cpfad, pixad, bancoad, dataad, horaad
    FROM a_site_afiliados_dados
    WHERE idusuarioad = :id
    LIMIT 1
    ");
$stmtDados->bindValue(':id', $idUser, PDO::PARAM_INT);
$stmtDados->execute();
$rwDados = $stmtDados->fetch(PDO::FETCH_ASSOC);
$temDadosAfiliado = (bool)$rwDados;

// Carrega produtos para afiliados
$stmtProd = $con->query("
    SELECT codigoprodutoafiliado, nomeap, valorap, comissaoap, img1080x1920, img1024x1024, dataap, horaap
    FROM a_site_afiliados_produto
    ORDER BY codigoprodutoafiliado DESC
    ");
$produtos = $stmtProd->fetchAll(PDO::FETCH_ASSOC);

// (Opcional) se você tiver uma função encrypt(), gere um token seguro para o link
function mkLinkAfiliado($idProduto, $idUser)
{
    if (!function_exists('encrypt')) return 'afiliados_cash.php?prod=' . (int)$idProduto;
    // padrão usado no seu projeto: juntar dados no token
    $payload = $idProduto . '&' . $idUser;
    return 'afiliados_cash.php?prod=' . urlencode(encrypt($payload, $action = 'e'));
}
?>
<!-- Navbar -->
<?php include 'v2.0/nav.php'; ?>

<style>
    :root {
        --bg: #112240;
        /* fundo padrão do seu sistema */
        --text: #ffffff;
        /* textos */
        --muted: #b8c2d3;
        /* texto suave */
        --chip: #1b2b50;
        /* chips/badges */
        --brand: #00BB9C;
        /* h1 padrão que você definiu */
        --accent: #FF9C00;
        /* h2 padrão que você definiu */
        --card: #0e1a33cc;
        /* vidro (glass) */
        --stroke: rgba(255, 255, 255, .12);
        --shadow: rgba(11, 16, 36, .5);
        --hover: rgba(255, 255, 255, .08);
    }

    body {
        background: var(--bg);
        color: var(--text);
    }

    /* Cabeçalho do módulo */
    #Corpo .section-head h4 {
        color: var(--brand);
        letter-spacing: .2px;
        font-weight: 800;
    }

    #Corpo .section-head small {
        color: var(--muted);
    }

    /* GRID RESPONSIVO (cards quadrados 1:1) */
    .pf-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
        gap: 1rem;
    }

    /* CARD */
    .pf-card {
        position: relative;
        border-radius: 1rem;
        overflow: hidden;
        box-shadow: 0 12px 26px var(--shadow);
        border: 1px solid var(--stroke);
        background: var(--card);
        transition: transform .2s ease, box-shadow .2s ease, border-color .2s ease;
        isolation: isolate;
    }

    .pf-media {
        aspect-ratio: 1/1;
        /* mantém 1:1 */
        background-size: cover;
        background-position: center;
        filter: saturate(1.05);
    }

    /* Gradient para leitura do texto */
    .pf-card::after {
        content: "";
        position: absolute;
        inset: 0;
        background: linear-gradient(180deg, rgba(0, 0, 0, 0) 45%, rgba(0, 0, 0, .65) 100%);
        pointer-events: none;
        z-index: 0;
    }

    .pf-card:hover {
        transform: translateY(-4px);
        border-color: rgba(255, 255, 255, .18);
        box-shadow: 0 18px 34px rgba(11, 16, 36, .65);
    }

    /* LEGEND */
    .pf-legend {
        position: absolute;
        left: .85rem;
        right: .85rem;
        bottom: .75rem;
        z-index: 2;
        display: flex;
        flex-direction: column;
        gap: .35rem;
    }

    .pf-title {
        font-weight: 700;
        font-size: .95rem;
        line-height: 1.25;
        text-shadow: 0 1px 0 rgba(0, 0, 0, .3);
    }

    .pf-tags {
        display: flex;
        flex-wrap: wrap;
        gap: .35rem;
    }

    .tag {
        font-size: .75rem;
        padding: .2rem .5rem;
        border-radius: .5rem;
        background: var(--chip);
        border: 1px solid var(--stroke);
        color: var(--text);
        white-space: nowrap;
    }

    .tag--valor {
        background: rgba(0, 187, 156, .15);
        border-color: rgba(0, 187, 156, .35);
    }

    .tag--comis {
        background: rgba(255, 156, 0, .18);
        border-color: rgba(255, 156, 0, .4);
    }

    /* BADGE NO TOPO DIREITO (comissão em destaque) */
    .pf-badge {
        position: absolute;
        top: .6rem;
        right: .6rem;
        z-index: 2;
        padding: .25rem .55rem;
        font-size: .75rem;
        font-weight: 700;
        letter-spacing: .2px;
        background: rgba(0, 0, 0, .55);
        border: 1px solid var(--stroke);
        border-radius: .5rem;
        backdrop-filter: blur(6px);
    }

    /* TOOLBAR DE AÇÕES (aparece no hover) */
    .pf-actions {
        position: absolute;
        inset: auto .6rem .6rem auto;
        display: flex;
        gap: .4rem;
        z-index: 2;
        opacity: 0;
        transform: translateY(6px);
        transition: opacity .2s ease, transform .2s ease;
    }

    .pf-card:hover .pf-actions {
        opacity: 1;
        transform: translateY(0);
    }

    .pf-btn {
        display: inline-flex;
        align-items: center;
        gap: .35rem;
        padding: .35rem .55rem;
        font-size: .8rem;
        line-height: 1;
        border-radius: .5rem;
        border: 1px solid var(--stroke);
        background: var(--hover);
        color: #fff;
        text-decoration: none;
        backdrop-filter: blur(6px);
    }

    .pf-btn:hover {
        background: rgba(255, 255, 255, .12);
    }

    /* Barra de título + contador */
    .pf-bar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: .75rem;
    }

    .pf-count.badge {
        background: rgba(255, 255, 255, .08);
        border: 1px solid var(--stroke);
        color: #fff;
    }

    /* Modal (mantendo seu esquema) */
    .modal-aff .modal-content {
        background: #2d095c;
        color: #fff;
        border: 1px solid rgba(255, 255, 255, .15);
        box-shadow: 0 12px 30px rgba(0, 0, 0, .45);
    }

    .modal-aff .form-control,
    .modal-aff .form-select {
        background: rgba(255, 255, 255, .08);
        color: #fff;
        border-color: rgba(255, 255, 255, .25);
    }

    .modal-aff .form-control::placeholder {
        color: rgba(255, 255, 255, .65);
    }

    .modal-aff .btn-save {
        background: var(--accent);
        border: none;
        color: #fff;
        font-weight: 700;
    }

    .modal-aff .btn-save:hover {
        filter: brightness(.95);
    }

    /* Botões topo de ação (require_Menunav) – realce sutil */
    .btn-aff {
        background: var(--accent);
        border: none;
        color: #fff;
    }

    .btn-aff:hover {
        filter: brightness(.95);
        color: #fff;
    }
</style>


<!-- Conteúdo -->
<section id="Corpo" class="py-4">
    <div class="container">
        <div class="text-center mb-4" data-aos="fade-down">
            <h4 class="mt-2 mb-1 text-white">
                <i class="bi bi-layers"></i> Afiliado
            </h4>
            <div class="small text-light-50">
                Chave: <strong id="affKeyLabel">—</strong>
            </div>
        </div>

        <!-- BOTÕES SUPERIORES -->
        <?php require 'afiliadosv1.0/require_Menunav.php'; ?>

        <!-- LISTA DE PRODUTOS -->
        <div class="mb-3">
            <div class="pf-bar" data-aos="fade-right">
                <h5 class="m-0 section-head">
                    <span class="text-uppercase" style="font-weight:800; letter-spacing:.4px;">
                        <i class="bi bi-bag-check me-2"></i> Produtos para Afiliados
                    </span>
                </h5>
                <span class="pf-count badge rounded-pill"><?= count($produtos) ?> itens</span>
            </div>

            <?php if (empty($produtos)): ?>
                <div class="alert alert-info">Nenhum produto disponível para afiliação no momento.</div>
            <?php else: ?>
                <div class="pf-grid">
                    <?php foreach ($produtos as $p):
                        $pid   = (int)$p['codigoprodutoafiliado'];
                        $nome  = (string)$p['nomeap'];
                        $valor = $p['valorap'];
                        $comis = $p['comissaoap'];
                        $img   = $p['img1024x1024'] ?: $p['img1080x1920'];
                        $img   = $img ?: 'https://via.placeholder.com/1024x1024/1e1e1e/ffffff?text=Produto';
                        $link  = mkLinkAfiliado($pid, $idUser);

                        // Comissão em % (se fizer sentido no seu modelo)
                        $perc = (is_numeric($valor) && (float)$valor > 0 && is_numeric($comis))
                            ? round(((float)$comis / (float)$valor) * 100)
                            : null;
                    ?>
                        <div class="pf-card" data-aos="zoom-in">
                            <div class="pf-media" style="background-image:url('<?= htmlspecialchars($img) ?>');"></div>

                            <?php if ($perc !== null): ?>
                                <div class="pf-badge" title="Comissão percentual estimada">
                                    +<?= (int)$perc ?>%
                                </div>
                            <?php endif; ?>

                            <!-- Toolbar de ações -->
                            <div class="pf-actions">
                                <a href="<?= $link ?>" class="pf-btn" title="Abrir material">
                                    <i class="bi bi-box-arrow-up-right"></i> Abrir
                                </a>
                                <button class="pf-btn js-copy"
                                    type="button"
                                    data-copy="<?= htmlspecialchars($link) ?>"
                                    title="Copiar link de afiliação">
                                    <i class="bi bi-clipboard-check"></i> Copiar
                                </button>
                            </div>

                            <!-- Legenda -->
                            <div class="pf-legend">
                                <div class="pf-title"><?= htmlspecialchars($nome) ?></div>
                                <div class="pf-tags">
                                    <span class="tag tag--valor">Valor: <?= formatBRL($valor) ?></span>
                                    <span class="tag tag--comis">Comissão: <?= formatBRL($comis) ?></span>
                                </div>
                            </div>

                            <!-- Link cobrindo o card (fallback de navegação) -->
                            <a href="<?= $link ?>" class="stretched-link" aria-label="Abrir produto: <?= htmlspecialchars($nome) ?>"></a>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="text-end mt-3" data-aos="fade-up">
                    <a href="afiliados_cash.php" class="btn btn-outline-light btn-sm">
                        Ver todos em lista
                    </a>
                </div>
            <?php endif; ?>
        </div>

    </div>
</section>

<!-- Rodapé -->
<?php require 'v2.0/footer.php'; ?>

<!-- MODAL: DADOS DO AFILIADO (CPF/PIX/BANCO) -->
<div class="modal fade modal-aff" id="modalDadosAfiliado" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="modal-title"><i class="bi bi-person-vcard me-2"></i> Dados do Afiliado</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body pt-0">
                <form id="formDadosAfiliado" novalidate>
                    <input type="hidden" name="idusuarioad" value="<?= $idUser ?>">
                    <div class="mb-3">
                        <label class="form-label">CPF</label>
                        <input type="text" class="form-control" name="cpfad" placeholder="Somente números"
                            value="<?= htmlspecialchars((string)($rwDados['cpfad'] ?? '')) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Chave PIX</label>
                        <input type="text" class="form-control" name="pixad" placeholder="CPF, e-mail, telefone ou aleatória"
                            value="<?= htmlspecialchars((string)($rwDados['pixad'] ?? '')) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Banco / Agência / Conta</label>
                        <input type="text" class="form-control" name="bancoad" placeholder="Ex.: Banco X - ag 0000 - cc 00000-0"
                            value="<?= htmlspecialchars((string)($rwDados['bancoad'] ?? '')) ?>">
                    </div>
                </form>
                <div id="affMsg" class="small mt-2"></div>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-save" id="btnSalvarAff">
                    <i class="bi bi-check2-circle me-1"></i> Salvar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap/AOS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.js"></script>
<script>
    AOS.init({
        duration: 700,
        once: true
    });
</script>

<script>
    (function() {
        // Se não tem dados, abre modal automaticamente
        const temDados = <?= $temDadosAfiliado ? 'true' : 'false' ?>;
        if (!temDados) {
            const m = new bootstrap.Modal(document.getElementById('modalDadosAfiliado'));
            m.show();
        }

        // Salvar dados do afiliado (AJAX)
        const btn = document.getElementById('btnSalvarAff');
        const form = document.getElementById('formDadosAfiliado');
        const msg = document.getElementById('affMsg');

        btn?.addEventListener('click', async () => {
            msg.textContent = 'Salvando...';
            const fd = new FormData(form);
            try {
                const r = await fetch('afiliadosv1.0/ajax_salvarDadosAfiliado.php', {
                    method: 'POST',
                    body: fd,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                const js = await r.json();
                if (js?.ok) {
                    msg.innerHTML = '<span class="text-success"><i class="bi bi-check2-circle me-1"></i>Dados salvos com sucesso.</span>';
                    setTimeout(() => location.reload(), 800);
                } else {
                    msg.innerHTML = '<span class="text-warning"><i class="bi bi-exclamation-triangle me-1"></i>' + (js?.msg || 'Falha ao salvar') + '</span>';
                }
            } catch (e) {
                msg.innerHTML = '<span class="text-danger"><i class="bi bi-x-circle me-1"></i>Erro na requisição.</span>';
            }
        });

        // (Opcional) preencher a label da chave se você já tiver a lógica da chave do afiliado
        // document.getElementById('affKeyLabel').textContent = '<?= isset($rwChave['chave']) ? $rwChave['chave'] : '—' ?>';
    })();
</script>

<script src="regixv2.0/acessopaginas.js?<?= time(); ?>"></script>
<script src="config_default1.0/JS_logoff.js?<?= time(); ?>"></script>
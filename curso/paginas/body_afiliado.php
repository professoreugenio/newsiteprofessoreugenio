<?php
// ===== Config navegação (auto-detecta pela página atual, ou defina $activeTab) =====
if (empty($activeTab)) {
    $map = [
        'afiliados.php'            => 'home',
        'afiliados_extrato.php'    => 'extrato',
        'afiliados_hisatorico.php' => 'historico', // conforme você informou
        'afiliados_perfil.php'     => 'perfil',
    ];
    $activeTab = $map[basename($_SERVER['SCRIPT_NAME'] ?? '')] ?? 'home';
}

$tabs = [
    'home'      => ['label' => 'home',      'href' => 'afiliados.php',            'icon' => 'bi-house-fill'],
    'extrato'   => ['label' => 'extrato',   'href' => 'afiliados_extrato.php',    'icon' => 'bi-cash-coin'],
    'historico' => ['label' => 'histórico', 'href' => 'afiliados_hisatorico.php', 'icon' => 'bi-clock-history'],
    'perfil'    => ['label' => 'perfil',    'href' => 'afiliados_perfil.php',     'icon' => 'bi-person-circle'],
];

// ===== Helpers visuais =====
if (!function_exists('formatBRL')) {
    function formatBRL($v)
    {
        if ($v === null || $v === '') return 'R$ 0,00';
        return 'R$ ' . number_format((float)$v, 2, ',', '.');
    }
}
if (!function_exists('e')) {
    function e($s)
    {
        return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
    }
}

// ===== Hash padrão para o link solicitado =====
$hashPadrao = 'Q20wK01DZ0Y4Zis1KzJ4VzcyRWw5dz09';
?>

<style>
    /* Barra de abas */
    .afi-tabs .nav-link {
        text-transform: uppercase;
        letter-spacing: .4px;
        font-weight: 700;
        border: 0 !important;
        color: #6c757d;
    }

    .afi-tabs .nav-link.active {
        color: #fff;
        background: linear-gradient(90deg, #00BB9C, #0aa38a);
        box-shadow: 0 .25rem .75rem rgba(0, 0, 0, .12);
    }

    /* Cabeçalho da seção */
    .pf-bar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        border-bottom: 1px solid rgba(255, 255, 255, .08);
        padding-bottom: 8px;
        margin-bottom: 12px;
    }

    .pf-count {
        background: rgba(0, 187, 156, .15);
        color: #00BB9C;
        border: 1px solid rgba(0, 187, 156, .35);
    }

    /* LISTA HORIZONTAL */
    .pf-hscroll {
        position: relative;
        overflow: hidden;
    }

    .pf-track {
        display: grid;
        grid-auto-flow: column;
        grid-auto-columns: 260px;
        gap: 16px;
        overflow-x: auto;
        scroll-snap-type: x mandatory;
        padding-bottom: .5rem;
    }

    .pf-track::-webkit-scrollbar {
        height: 8px;
    }

    .pf-track::-webkit-scrollbar-thumb {
        background: #d1d5db;
        border-radius: 999px;
    }

    /* Card (aproveitando suas classes) */
    .pf-card {
        scroll-snap-align: start;
        background: #112240;
        color: #fff;
        border: 1px solid rgba(255, 255, 255, .08);
        border-radius: 16px;
        overflow: hidden;
        position: relative;
        transition: transform .2s ease, box-shadow .2s ease;
    }

    .pf-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 .75rem 1.5rem rgba(0, 0, 0, .18);
    }

    /* Miniatura (miniatura explícita) */
    .pf-media {
        height: 120px;
        background-size: cover;
        background-position: center;
        background-repeat: no-repeat;
    }

    .pf-badge {
        position: absolute;
        top: 10px;
        left: 10px;
        padding: .25rem .5rem;
        font-size: .8rem;
        border-radius: 999px;
        background: #FF9C00;
        color: #112240;
        font-weight: 800;
    }

    .pf-actions {
        display: flex;
        gap: 8px;
        padding: 10px 12px 0 12px;
    }

    .pf-btn {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: #FF9C00;
        color: #112240;
        border: 0;
        border-radius: 10px;
        padding: .4rem .6rem;
        font-weight: 700;
        text-decoration: none;
    }

    .pf-btn:hover {
        filter: brightness(1.05);
        color: #112240;
    }

    .pf-legend {
        padding: 8px 12px 12px 12px;
    }

    .pf-title {
        font-weight: 800;
        line-height: 1.25;
        margin-bottom: 4px;
    }

    .pf-tags {
        display: flex;
        flex-direction: column;
        gap: 2px;
        font-size: .9rem;
        opacity: .95;
    }

    .tag--valor .v {
        color: #FF9C00;
        font-weight: 800;
    }

    .tag--comis .c {
        color: #00BB9C;
        font-weight: 800;
    }

    /* Botões de scroll */
    .pf-nav {
        position: absolute;
        top: 50%;
        transform: translateY(-50%);
        width: 42px;
        height: 42px;
        border-radius: 50%;
        display: grid;
        place-items: center;
        background: #fff;
        border: 0;
        box-shadow: 0 .5rem 1rem rgba(0, 0, 0, .15);
        opacity: .92;
    }

    .pf-nav:hover {
        opacity: 1;
    }

    .pf-prev {
        left: -8px;
    }

    .pf-next {
        right: -8px;
    }

    @media (max-width: 768px) {
        .pf-track {
            grid-auto-columns: 80%;
        }

        .pf-nav {
            display: none;
        }
    }
</style>

<section id="Corpo" class="py-4">
    <div class="container">

        <!-- NAV SUPERIOR: home | extrato | histórico | perfil -->
        <div class="afi-tabs mb-3">
            <ul class="nav nav-pills gap-2">
                <?php foreach ($tabs as $k => $tab): ?>
                    <li class="nav-item">
                        <a class="nav-link <?= $activeTab === $k ? 'active' : '' ?>" href="<?= e($tab['href']) ?>">
                            <i class="bi <?= e($tab['icon']) ?> me-1"></i><?= e($tab['label']) ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>

        <!-- LISTA DE PRODUTOS -->
        <div class="mb-3">
            <div class="pf-bar" data-aos="fade-right">
                <h5 class="m-0 section-head">
                    <span class="text-uppercase" style="font-weight:800; letter-spacing:.4px;">
                        <i class="bi bi-bag-check me-2"></i> Produtos para Afiliados
                    </span>
                </h5>
                <span class="pf-count badge rounded-pill"><?= isset($produtos) && is_array($produtos) ? count($produtos) : 0 ?> itens</span>
            </div>

            <?php if (empty($produtos)): ?>
                <div class="alert alert-info">Nenhum produto disponível para afiliação no momento.</div>
            <?php else: ?>
                <div class="pf-hscroll" data-aos="zoom-in">
                    <button class="pf-nav pf-prev" type="button" data-dir="-1" aria-label="Anterior">
                        <i class="bi bi-chevron-left"></i>
                    </button>

                    <div class="pf-track" id="pfTrack">
                        <?php foreach ($produtos as $p):
                            $pid   = (int)($p['codigoprodutoafiliado'] ?? 0);
                            $nome  = (string)($p['nomeap']            ?? 'Produto');
                            $valor = $p['valorap']   ?? 0;
                            $comis = $p['comissaoap'] ?? 0;
                            $img   = $p['img1024x1024'] ?: ($p['img1080x1920'] ?? null);
                            $img   = $img ?: 'https://via.placeholder.com/600x400/1e1e1e/ffffff?text=Produto';

                            // % comissão (se fizer sentido no seu modelo)
                            $perc = (is_numeric($valor) && (float)$valor > 0 && is_numeric($comis))
                                ? round(((float)$comis / (float)$valor) * 100)
                                : null;

                            // Link solicitado (prioriza hash do item se existir)
                            $hash = isset($p['hashap']) && $p['hashap'] !== '' ? (string)$p['hashap'] : $hashPadrao;
                            $link = 'afiliados_produtos.php?prod=' . rawurlencode($hash);
                        ?>
                            <div class="pf-card" data-aos="fade-up">
                                <div class="pf-media" style="background-image:url('<?= e($img) ?>');"></div>

                                <?php if ($perc !== null): ?>
                                    <div class="pf-badge" title="Comissão percentual estimada">+<?= (int)$perc ?>%</div>
                                <?php endif; ?>

                                <div class="pf-actions">
                                    <a href="<?= e($link) ?>" class="pf-btn" title="Abrir material">
                                        <i class="bi bi-box-arrow-up-right"></i> Abrir
                                    </a>
                                    <button class="pf-btn js-copy" type="button" data-copy="<?= e($link) ?>" title="Copiar link de afiliação">
                                        <i class="bi bi-clipboard-check"></i> Copiar
                                    </button>
                                </div>

                                <div class="pf-legend">
                                    <div class="pf-title"><?= e($nome) ?></div>
                                    <div class="pf-tags">
                                        <span class="tag tag--valor">Valor: <span class="v"><?= formatBRL($valor) ?></span></span>
                                        <span class="tag tag--comis">Comissão: <span class="c"><?= formatBRL($comis) ?></span></span>
                                    </div>
                                </div>

                                <a href="<?= e($link) ?>" class="stretched-link" aria-label="Abrir produto: <?= e($nome) ?>"></a>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <button class="pf-nav pf-next" type="button" data-dir="1" aria-label="Próximo">
                        <i class="bi bi-chevron-right"></i>
                    </button>
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

<script>
    // Scroll horizontal por cartão
    (function() {
        const track = document.getElementById('pfTrack');
        if (!track) return;

        function stepWidth() {
            const card = track.querySelector('.pf-card');
            return card ? card.getBoundingClientRect().width + 16 : 260;
        }
        document.querySelectorAll('.pf-nav').forEach(btn => {
            btn.addEventListener('click', () => {
                const dir = Number(btn.getAttribute('data-dir') || 1);
                track.scrollBy({
                    left: dir * stepWidth(),
                    behavior: 'smooth'
                });
            });
        });

        // Copiar link
        document.querySelectorAll('.js-copy').forEach(b => {
            b.addEventListener('click', async () => {
                try {
                    await navigator.clipboard.writeText(b.dataset.copy || '');
                    b.innerHTML = '<i class="bi bi-clipboard-check"></i> Copiado!';
                    setTimeout(() => {
                        b.innerHTML = '<i class="bi bi-clipboard-check"></i> Copiar';
                    }, 1200);
                } catch (e) {
                    alert('Não foi possível copiar o link.');
                }
            });
        });
    })();
</script>
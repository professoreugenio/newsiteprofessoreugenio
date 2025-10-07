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
    if (!function_exists('encrypt')) return 'afiliados_produtos.php?prod=' . (int)$idProduto;
    // padrão usado no seu projeto: juntar dados no token
    $payload = $idProduto . '&' . $idUser;
    return 'afiliados_produtos.php?prod=' . urlencode(encrypt($payload, $action = 'e'));
}
?>
<!-- Navbar -->
<?php include 'v2.0/nav.php'; ?>

<style>
    /* Grade com cards (200x200) */
    .pf-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, 200px);
        gap: .75rem;
        justify-content: start;
    }

    .pf-card {
        width: 200px;
        height: 200px;
        border-radius: .75rem;
        overflow: hidden;
        position: relative;
        background-size: cover;
        background-position: center center;
        background-repeat: no-repeat;
        box-shadow: 0 6px 14px rgba(0, 0, 0, .10);
        border: 1px solid rgba(255, 255, 255, .12);
        cursor: pointer;
        transition: transform .15s ease, box-shadow .15s ease;
    }

    .pf-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 18px rgba(0, 0, 0, .16);
    }

    .pf-legend {
        position: absolute;
        left: 8px;
        bottom: 6px;
        background: rgba(0, 0, 0, .55);
        color: #fff;
        font-size: .75rem;
        padding: .15rem .5rem;
        border-radius: .35rem;
    }

    .pf-module-head h6 {
        margin: 0;
        color: #fff;
    }

    /* Botões topo */
    .btn-aff {
        background: #ff7f2aff;
        border: none;
        color: #fff;
    }

    .btn-aff:hover {
        filter: brightness(0.95);
        color: #fff;
    }

    /* Modal cores pedidas */
    .modal-aff .modal-content {
        background: #2d095cff;
        color: #fff;
        border: 1px solid rgba(255, 255, 255, .15);
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
        background: #ff7f2aff;
        color: #fff;
        border: none;
    }

    .modal-aff .btn-save:hover {
        filter: brightness(0.95);
        color: #fff;
    }

    .tag {
        font-size: .75rem;
        padding: .15rem .4rem;
        border-radius: .35rem;
        background: rgba(0, 0, 0, .35);
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
            <div class="d-flex align-items-center justify-content-between mb-2">
                <h5 class="text-white m-0" data-aos="fade-right">
                    <i class="bi bi-bag-check me-2"></i> Produtos para Afiliados
                </h5>
                <span class="badge bg-secondary" data-aos="fade-left"><?= count($produtos) ?> itens</span>
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
                    ?>
                        <div class="pf-card" data-aos="zoom-in"
                            style="background-image:url('<?= htmlspecialchars($img) ?>');">
                            <div class="pf-legend">
                                <div class="fw-semibold"><?= htmlspecialchars($nome) ?></div>
                                <div class="small">
                                    <span class="tag me-1">Valor: <?= formatBRL($valor) ?></span>
                                    <span class="tag">Comissão: <?= formatBRL($comis) ?></span>
                                </div>
                            </div>
                            <a href="<?= $link ?>" class="stretched-link" title="Material do produto"></a>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="text-end mt-2">
                    <a href="afiliados_produtos.php" class="btn btn-outline-light btn-sm" data-aos="fade-up">
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
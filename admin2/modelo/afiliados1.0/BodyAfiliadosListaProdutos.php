<?php
// BodyAfiliadosListaProdutos.php
// Requisitos: $con (PDO) já disponível, Bootstrap 5+ e Bootstrap Icons carregados

if (!isset($con) || !$con instanceof PDO) {
    echo '<div class="alert alert-danger">Conexão não disponível.</div>';
    return;
}

// Helpers
$fmt = fn($v) => number_format((float)$v, 2, ',', '.');
$mkLink = function ($id) {
    // Se existir função encrypt(), usa; senão envia o id puro
    $param = function_exists('encrypt') ? encrypt($id) : $id;
    return 'sistema_afiliadosProdutosEditar.php?id=' . urlencode($param);
};

try {
    $sql = "
        SELECT 
            codigoprodutoafiliado,
            nomeap,
            valorap,
            comissaoap,
            visivelap,
            dataap,
            horaap
        FROM a_site_afiliados_produto
        ORDER BY 
            dataap DESC,
            horaap DESC,
            codigoprodutoafiliado DESC
    ";
    $stmt = $con->query($sql);
    $produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $e) {
    echo '<div class="alert alert-danger">Erro ao carregar produtos: ' . htmlspecialchars($e->getMessage()) . '</div>';
    return;
}
?>

<div class="d-flex align-items-center justify-content-between mb-3" data-aos="fade-up">
    <h5 class="m-0">
        <i class="bi bi-box-seam me-2"></i>Produtos afiliados
        <span class="badge bg-secondary ms-2"><?= count($produtos) ?></span>
    </h5>
</div>

<?php if (empty($produtos)): ?>
    <div class="alert alert-info" data-aos="fade-up">
        Nenhum produto cadastrado ainda.
    </div>
<?php else: ?>
    <div class="list-group shadow-sm" data-aos="fade-up" data-aos-delay="50">
        <?php foreach ($produtos as $p):
            $id   = (int)$p['codigoprodutoafiliado'];
            // nomeap veio como int na tabela, mas tratamos como string para exibição
            $nome = (string)$p['nomeap'];
            $val  = $p['valorap'];
            $com  = $p['comissaoap'];
            $vis  = (int)$p['visivelap'] === 1;
            $link = $mkLink($id);
            $data = $p['dataap'] ?: '';
            $hora = $p['horaap'] ?: '';
        ?>
            <div class="list-group-item list-group-item-action py-3">
                <div class="d-flex align-items-center gap-3">
                    <!-- Ícone de visibilidade -->
                    <div class="flex-shrink-0">
                        <?php if ($vis): ?>
                            <span class="text-success" title="Visível">
                                <i class="bi bi-eye-fill fs-5"></i>
                            </span>
                        <?php else: ?>
                            <span class="text-muted" title="Oculto">
                                <i class="bi bi-eye-slash fs-5"></i>
                            </span>
                        <?php endif; ?>
                    </div>

                    <!-- Conteúdo principal -->
                    <div class="flex-grow-1">
                        <a href="<?= $link ?>" class="text-decoration-none">
                            <div class="fw-semibold"><?= htmlspecialchars($nome) ?></div>
                        </a>
                        <div class="small text-secondary">
                            <?= $data ? date('d/m/Y', strtotime($data)) : '' ?>
                            <?= $hora ? ' • ' . substr($hora, 0, 5) : '' ?>
                        </div>
                    </div>

                    <!-- Valor e comissão -->
                    <div class="text-end me-2">
                        <div class="badge bg-light text-dark border">
                            Valor: R$ <?= $fmt($val) ?>
                        </div>
                        <div class="badge bg-primary ms-1">
                            Comissão: R$ <?= $fmt($com) ?>
                        </div>
                    </div>

                    <!-- Botão seta -->
                    <div class="flex-shrink-0">
                        <a class="btn btn-outline-secondary btn-sm" href="<?= $link ?>" title="Editar">
                            <i class="bi bi-arrow-right-short fs-5"></i>
                        </a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<!-- Estilo opcional para afinar a lista no tema do afiliados -->
<style>
    /* Tons suaves e foco em acessibilidade */
    .list-group-item:hover {
        background: rgba(0, 0, 0, .02);
    }

    .badge.bg-primary {
        background-color: #0d6efd !important;
    }
</style>
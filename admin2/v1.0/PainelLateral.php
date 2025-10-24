<div id="painelLateral" class="position-fixed top-0 start-0 bg-white shadow rounded-end p-4"
    style="width: 260px; height: 100vh; z-index: 1050; transform: translateX(-100%); transition: transform 0.3s ease;">
    <button class="btn-close position-absolute top-0 end-0 m-3" onclick="fecharPainel()" aria-label="Fechar"></button>
    <h5 class="mb-4 mt-4 text-primary"><i class="bi bi-grid-fill me-2"></i>Menu Rápido *</h5>
    <?php
    function ConsultaArray($con, $inicio, $limite)
    {
        $queryArray = $con->prepare("SELECT * FROM a_site_sessao WHERE visivelss = '1'   ORDER BY ordemss ASC LIMIT $inicio,$limite");
        $queryArray->execute();
        return $queryArray->fetchALL();
    }
    ?>
    <?php
    $resultarray = ConsultaArray($con, '0', '100');
    $quant = count($resultarray);
    ?>

    <?php foreach ($resultarray as $key => $rw_Sessao) { ?>

        <?php $encSessao = encrypt($rw_Sessao['codigosessao'], $action = 'e'); ?>

        <?php
        // valida $idSes: usa o id da sessão atual se presente, senão usa $idSes existente, senão fallback para 1
        if (isset($rw_Sessao['codigosessao']) && is_numeric($rw_Sessao['codigosessao'])) {
            $idSes = (int) $rw_Sessao['codigosessao'];
        } else {
            $idSes = (isset($idSes) && is_numeric($idSes)) ? (int) $idSes : 1;
        }
        ?>
        <div>
            <!-- bi-journal-text -->
            <a href="#" class="d-block mb-2 text-dark fw-bold" onclick="abrirMenu('<?php echo $rw_Sessao['iconess'] ?? ''; ?>')">
                <i class="bi <?php echo $rw_Sessao['iconess'] ?? ''; ?>  me-2"></i>
                <?php echo $rw_Sessao['nomesessao']; ?>
                <span id="toggle-<?php echo $rw_Sessao['iconess']; ?>" class="toggle-seta">▶</span>
            </a>
            <div id="menu-<?php echo $rw_Sessao['iconess']; ?>" class="submenu ps-3">
                <?php
                $queryCategoria = $con->prepare("SELECT * FROM a_site_paginas WHERE idsessaosp = '$idSes' AND visivelsp = '1' ORDER BY ordemsp ASC LIMIT 0,20");
                $queryCategoria->execute();
                $resultarrayCategoria = $queryCategoria->fetchALL();
                foreach ($resultarrayCategoria as $rwPagina) {
                ?>

                    <?php


                    $nomePagina = trim((string)$rwPagina['nomepaginasp']); // Exemplo: 'clientes'
                    $nomePasta = trim((string)$rwPagina['pastasp']); // Exemplo: 'clientes'

                    if (!empty($nomePagina)) {



                        /* ==========================================================
       🗂️ 1. Definição de caminhosnomePastaLimpo
    ========================================================== */
                        $baseDir       = dirname(__DIR__, 3) . '/admin2/modelo/';
                        $paginaDir     = $baseDir . $nomePasta . '/';
                        $nomePastaLimpo = str_replace(array("pg", "_"), "", $nomePasta);

                        $versaoDir     = $paginaDir . $nomePastaLimpo . '1.0/';

                        /* ==========================================================
       🏗️ 2. Criação das pastas
    ========================================================== */
                        foreach ([$baseDir, $paginaDir, $versaoDir] as $dir) {
                            if (!is_dir($dir)) {
                                mkdir($dir, 0775, true);
                            }
                        }

                        /* ==========================================================
       📝 3. Gera o arquivo index.php
    ========================================================== */
                        $indexPath = $paginaDir . 'index.php';
                        $conteudoIndex = <<<PHP
<?php
define('BASEPATH', true);
define('APP_ROOT', dirname(__DIR__, 3));
require_once APP_ROOT . '/conexao/class.conexao.php';
require_once APP_ROOT . '/autenticacao.php';
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <?php require_once APP_ROOT . '/admin2/v1.0/head.php'; ?>
    <link rel="stylesheet" href="/admin2/v1.0/CSS_config.css?<?= time(); ?>">
    <?php require_once APP_ROOT . '/admin2/v1.0/dadosuser.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.js"></script>
</head>

<body id="adminLayout">
    <?php require_once APP_ROOT . '/admin2/v1.0/nav.php'; ?>
    <?php require_once APP_ROOT . '/admin2/v1.0/PainelLateral.php'; ?>
    <div class="container-fluid px-4 mt-5">
        <!-- Cabeçalho -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div class="mt-4">
                <h3><i class="bi bi-journal-text me-2"></i> {$nomePagina}</h3>
            </div>
            <?php require_once APP_ROOT . '/admin2/modelo/{$nomePasta}/{$nomePastaLimpo}1.0/Subnav.php'; ?>
        </div>
        <?php require_once APP_ROOT . '/admin2/modelo/{$nomePasta}/{$nomePastaLimpo}1.0/Body{$nomePastaLimpo}1.0.php'; ?>
    </div>
    <!-- Scripts -->
    <script src="<?php require_once APP_ROOT . "/admin2/v1.0/PainelLateral.js"; ?>"></script>
    <?php require_once APP_ROOT . '/admin2/v1.0/footer.php'; ?>
</body>

</html>
PHP;

                        file_put_contents($indexPath, $conteudoIndex);

                        /* ==========================================================
       🧭 4. Gera o Subnav.php
    ========================================================== */
                        $subnavPath = $versaoDir . 'Subnav.php';
                        $conteudoSubnav = <<<PHP
<?php
/* SUBNAV */
\$pagina = basename(\$_SERVER['PHP_SELF']);
\$status = \$_GET['status'] ?? null;
\$institucional = \$_GET['institucional'] ?? null;
\$matriz = \$_GET['matriz'] ?? null;
\$todos = \$_GET['todos'] ?? null;
?>
<div class="d-flex gap-2 flex-wrap mb-3">
    <?php if (temPermissao(\$niveladm, [1])): ?>
        <a href="{$nomePagina}.php?status=1"
           class="btn btn-flat btn-sm <?= \$pagina == '{$nomePagina}.php' ? 'btn-laranja' : '' ?>">
            <i class="bi bi-shop"></i> CLIENTES
        </a>

        <a href="{$nomePagina}.php?institucional=1"
           class="btn btn-flat btn-sm <?= \$pagina == '{$nomePagina}.php' ? 'btn-laranja' : '' ?>">
            <i class="bi bi-globe"></i> NOVO CLIENTE
        </a>
    <?php endif; ?>
</div>
PHP;

                        file_put_contents($subnavPath, $conteudoSubnav);

                        /* ==========================================================
       📄 5. Gera o arquivo Body{nomePagina}1.0.php
    ========================================================== */
                        $bodyPath = $versaoDir . 'Body' . $nomePastaLimpo . '1.0.php';
                        $conteudoBody = <<<PHP
<?php
/**
 * Body{$nomePagina}1.0.php
 * Estrutura base para o conteúdo principal da página {$nomePagina}
 */
?>
<div class="card shadow-sm" data-aos="fade-up">
    <div class="card-body">
        <h5 class="card-title text-primary mb-3">Bem-vindo à página {$nomePagina}</h5>
        <p class="text-muted">
            Este é o corpo inicial gerado automaticamente.
            Substitua este conteúdo conforme a necessidade da página.
        </p>
        <hr>
        <div class="alert alert-info">
            <i class="bi bi-lightbulb"></i> Use este espaço para carregar listas, formulários ou relatórios.
        </div>
    </div>
</div>
PHP;

                        file_put_contents($bodyPath, $conteudoBody);

                        /* ==========================================================
       ✅ 6. Confirmação visual
    ========================================================== */
                        "<div class='alert alert-success mt-3'>
            <strong>Módulo criado com sucesso!</strong><br>
            Estrutura: /{$nomePasta}/{$nomePastaLimpo}1.0/<br>
            <ul class='mb-0'>
                <li>index.php</li>
                <li>Subnav.php</li>
                <li>Body{$nomePastaLimpo}1.0.php</li>
            </ul>
          </div>";
                    }
                    ?>


                    <?php $encPage = encrypt($rwPagina['codigopaginas'], $action = 'e'); ?>
                    <a href="../modelo/<?= $rwPagina['pastasp']; ?>/?ses=<?php echo $encSessao; ?>&page=<?php echo $encPage; ?>&ts=<?= time() ?>" class="d-block text-muted mb-1">:: <?php echo $rwPagina['nomepaginasp']; ?></a>
                <?php } ?>
            </div>
        </div>
    <?php } ?>
</div>
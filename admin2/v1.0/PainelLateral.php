<div id="painelLateral" class="position-fixed top-0 start-0 bg-white shadow rounded-end p-4"
    style="width: 260px; height: 100vh; z-index: 1050; transform: translateX(-100%); transition: transform 0.3s ease;">
    <button class="btn-close position-absolute top-0 end-0 m-3" onclick="fecharPainel()" aria-label="Fechar"></button>
    <h5 class="mb-4 mt-4 text-primary"><i class="bi bi-grid-fill me-2"></i>Menu Rápido *</h5>
    <?php
    function ConsultaArray($con, $inicio, $limite)
    {
        $queryArray = $con->prepare("SELECT * FROM sistema_sessao WHERE visivel = '1'   ORDER BY ordem ASC LIMIT $inicio,$limite");
        $queryArray->execute();
        return $queryArray->fetchALL();
    }
    ?>
    <?php
    $resultarray = ConsultaArray($con, '0', '100');
    $quant = count($resultarray);
    ?>

    <?php foreach ($resultarray as $key => $rw_Sessao) { ?>
        <?php $idSes = $rw_Sessao['codigosessao']; ?>
        <?php $encSessao = encrypt($rw_Sessao['codigosessao'], $action = 'e'); ?>
        <div>
            <a href="#" class="d-block mb-2 text-dark fw-bold" onclick="abrirMenu('<?php echo $rw_Sessao['tag']; ?>')">
                <i class="bi bi-journal-text me-2"></i>
                <?php echo $rw_Sessao['nome']; ?>
                <span id="toggle-<?php echo $rw_Sessao['tag']; ?>" class="toggle-seta">▶</span>
            </a>
            <div id="menu-<?php echo $rw_Sessao['tag']; ?>" class="submenu ps-3">
                <?php
                $queryCategoria = $con->prepare("SELECT * FROM new_sistema_paginasadmin WHERE codsessao = '$idSes' AND visivelpa = '1' ORDER BY ordemsp ASC LIMIT 0,20");
                $queryCategoria->execute();
                $resultarrayCategoria = $queryCategoria->fetchALL();
                foreach ($resultarrayCategoria as $rwPagina) {
                ?>

                    <?php
                    /**
                     * GERADOR DE ESTRUTURA COMPLETA DE PÁGINAS EM /modelo/
                     * Cria automaticamente:
                     * - /modelo/{nomePagina}/
                     * - /modelo/{nomePagina}/{nomePagina}1.0/
                     * - index.php
                     * - Subnav.php
                     * - Body{nomePagina}1.0.php
                     */

                    $nomePagina = "pg_" . trim((string)$rwPagina['diretorio']); // Exemplo: 'clientes'

                    if (!empty($nomePagina)) {



                        /* ==========================================================
       🗂️ 1. Definição de caminhos
    ========================================================== */
                        $baseDir       = dirname(__DIR__, 1) . '/';
                        $paginaDir     = $baseDir . $nomePagina . '/';
                        $nomePaginaLimpo = str_replace(array("pg", "_"), "", $nomePagina);
                        
                        $versaoDir     = $paginaDir . $nomePaginaLimpo . '1.0/';

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
            <?php require_once APP_ROOT . '/admin2/modelo/{$nomePagina}/{$nomePagina}1.0/Subnav.php'; ?>
        </div>
        <?php require_once APP_ROOT . '/admin2/modelo/{$nomePagina}/{$nomePagina}1.0/Body{$nomePagina}1.0.php'; ?>
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
                        $bodyPath = $versaoDir . 'Body' . $nomePagina . '1.0.php';
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
            Estrutura: /modelo/{$nomePagina}/{$nomePagina}1.0/<br>
            <ul class='mb-0'>
                <li>index.php</li>
                <li>Subnav.php</li>
                <li>Body{$nomePagina}1.0.php</li>
            </ul>
          </div>";
                    }
                    ?>


                    <?php $encPage = encrypt($rwPagina['codigopaginasadmin'], $action = 'e'); ?>
                    <a href="../actions.php?ses=<?php echo $encSessao; ?>&page=<?php echo $encPage; ?>&ts=<?= time() ?>" class="d-block text-muted mb-1">:: <?php echo $rwPagina['nomepaginapa']; ?></a>
                <?php } ?>
            </div>
        </div>
    <?php } ?>
</div>
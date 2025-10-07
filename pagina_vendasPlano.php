<?php
// Segurança básica para impedir acesso direto:
define('BASEPATH', true);
require_once __DIR__ . '/conexao/class.conexao.php';
require_once __DIR__ . '/autenticacao.php';
require_once __DIR__ . '/vendasv1.0/v2.0query_vendas.php';

if (!isset($_SESSION['idUsuario']) || !isset($_SESSION['emailUsuario']) || !isset($_SESSION['nomeUsuario'])) {
    header("Location: pagina_vendas.php");
    exit;
}

$tituloPagina   = sprintf('%s | Professor Eugênio – Invista em sua qualificação profissional', $nomeCurso);
$descricaoPagina = $descricao;
$keywordsPagina  = $descricaoPagina . ', ' . extractWords($descricaoPagina) . ', professor eugenio, professoreugenio, cursos online, aulas online';
$versaoAssets    = time();
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <?php require_once __DIR__ . '/vendasv1.0/VendasHead.php'; ?>
</head>

<body>
    <!-- NAVBAR -->
    <?php include_once __DIR__ . '/config_default/body_navall.php'; ?>
    <main class="container py-2">
        <div class="row justify-content-center">
            <div class="col-lg-6">
                <?php
                if (isset($_COOKIE['nav'])):  $dec = encrypt($_COOKIE['nav'], $action = 'd');
                endif; ?>
                <?php // if ($vendaliberada == '1'): 
                ?>
                <?php if ($vendaliberada == "1"): ?>
<?php echo $dec = encrypt($_COOKIE['nav'], $action = 'd'); ?>
                    <?php include_once __DIR__ . '/vendasv1.0/bodyVendasPlano.php'; ?>
                <?php else: ?>

                    <?php include_once __DIR__ . '/vendasv1.0/bodyManutencao.php'; ?>
                <?php endif; ?>
            </div>
        </div>
    </main>
    <?php include_once __DIR__ . '/vendasv1.0/VendasFooter.php'; ?>
    <script src="acessosv1.0/ajax_registraAcesso.js"></script>



</body>

</html>
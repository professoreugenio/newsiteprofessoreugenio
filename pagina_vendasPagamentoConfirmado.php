<?php
// Segurança básica para impedir acesso direto:
define('BASEPATH', true);
// pagina_vendasPagamentoConfirmado.php
require_once __DIR__ . '/conexao/class.conexao.php';
require_once __DIR__ . '/autenticacao.php';
require_once __DIR__ . '/vendasv1.0/v2.0query_vendas.php';
$tituloPagina   = sprintf('%s | Professor Eugênio – Invista em sua qualificação profissional', $nomeCurso);
$descricaoPagina = $descricao;
$keywordsPagina  = $descricaoPagina . ', ' . extractWords($descricaoPagina) . ', professor eugenio, professoreugenio, cursos online, aulas online';
$versaoAssets    = time();
?>
<?php
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
                <?php // if ($vendaliberada == '1'): 
                ?>
                <?php if ($vendaliberada == "1"): ?>

                    <?php include_once __DIR__ . '/vendasv1.0/BodyConfirmaPagamento.php'; ?>
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
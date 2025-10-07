<?php
// Segurança básica para impedir acesso direto:
define('BASEPATH', true);
require_once __DIR__ . '/conexao/class.conexao.php';
require_once __DIR__ . '/autenticacao.php';
require_once __DIR__ . '/vendasv1.0/v2.0query_vendas.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
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
    <style>
        .plano-select {
            cursor: pointer;
            transition: all 0.3s ease;
            border: 2px solid transparent;
        }

        .btn-check:checked+.plano-select {
            border-color: solid 6px #00BB9C !important;

            transform: scale(1.02);
            box-shadow: 0 0 30px rgba(0, 187, 156, 0.7) !important;
        }

        .bt-mercadopago {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background-color: #009ee3;
            color: #fff;
            font-weight: 600;
            font-size: 1rem;
            padding: 10px 18px;
            border: none;
            border-radius: 8px;
            text-decoration: none;
            transition: all 0.3s ease;
            box-shadow: 0 4px 10px rgba(0, 158, 227, 0.3);
        }

        .bt-mercadopago:hover {
            background-color: #007bbd;
            color: #fff;
            text-decoration: none;
            box-shadow: 0 4px 14px rgba(0, 123, 189, 0.4);
        }

        .bt-mercadopago i {
            font-size: 1.2rem;
        }
    </style>
</head>

<body>
    <!-- NAVBAR -->
    <?php include_once __DIR__ . '/config_default/body_navall.php'; ?>
    <main class="container py-2">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <?php // if ($vendaliberada == '1'): 
                ?>
                <?php if ($vendaliberada == "1"): ?>
                    <?php include_once __DIR__ . '/vendasv1.0/BodyVendasPagamento.php'; ?>
                <?php else: ?>
                    <?php include_once __DIR__ . '/vendasv1.0/manutencaoBody.php'; ?>
                <?php endif; ?>
            </div>
        </div>
    </main>
    <?php include_once __DIR__ . '/vendasv1.0/VendasFooter.php'; ?>



</body>

</html>
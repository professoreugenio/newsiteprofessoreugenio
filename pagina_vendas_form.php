<?php
// Segurança básica para impedir acesso direto:
define('BASEPATH', true);
require_once __DIR__ . '/conexao/class.conexao.php';
require_once __DIR__ . '/autenticacao.php';
require_once __DIR__ . '/vendasv1.0/v2.0query_vendas.php';
$tituloPagina   = sprintf('%s | Professor Eugênio – Invista em sua qualificação profissional', $nomeCurso);
$descricaoPagina = $descricao;
$keywordsPagina  = $descricaoPagina . ', ' . extractWords($descricaoPagina) . ', professor eugenio, professoreugenio, cursos online, aulas online';
$versaoAssets    = time();
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($tituloPagina) ?></title>
    <meta name="description" content="<?= htmlspecialchars($descricaoPagina) ?>">
    <meta name="keywords" content="<?= htmlspecialchars($keywordsPagina) ?>">
    <?php require_once __DIR__ . '/head/head_midiassociais.php'; ?>
    <link rel="preconnect" href="https://cdn.jsdelivr.net" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="mycss/default.css?v=<?= $versaoAssets ?>" rel="stylesheet">
    <link href="mycss/config.css?v=<?= $versaoAssets ?>" rel="stylesheet">
    <link href="vendasv1.0/CSS_vendas.css?v=<?= $versaoAssets ?>" rel="stylesheet">
    <link href="mycss/nav.css?v=<?= $versaoAssets ?>" rel="stylesheet">
    <link href="mycss/animate.min.css?v=<?= $versaoAssets ?>" rel="stylesheet">
    <link href="config_default/config.css?v=<?= $versaoAssets ?>" rel="stylesheet">
    <link href="config_chat/CSS_atendimentoonline.css?v=<?= $versaoAssets ?>" rel="stylesheet">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(to right, #0A192F, #112240);
            color: #FFFFFF;
        }

        .card-oferta {
            background-color: #112240;
            border-radius: 1rem;
            overflow: hidden;
            color: #FFFFFF;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.5);
            height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .card-oferta img {
            width: 100%;
            height: 60vh;
            object-fit: cover;
            object-position: center;
            display: block;
        }

        .card-oferta .precos {
            background-color: #0A192F;
            padding: 1rem;
            text-align: center;
        }

        .card-oferta .precos p {
            margin: 0.3rem 0;
            font-size: 1.1rem;
        }

        .bg-dark {
            background-color: #112240 !important;
        }

        .btn-info {
            background: linear-gradient(to right, #00BFA6, #00BB9C);
            border: none;
            color: #fff;
        }

        .btn-info:hover {
            background: linear-gradient(to right, #00a890, #009e87);
        }

        h4,
        h5,
        .text-success {
            color: #00BB9C !important;
        }

        @media (max-width: 768px) {
            .card-oferta {
                height: auto;
            }

            .card-oferta img {
                height: auto;
            }
        }
    </style>
    <style>
        .btn-gradient {
            background: linear-gradient(135deg, #00BFA6, #00BB9C);
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 30px;
            font-size: 1.1rem;
            transition: all 0.3s ease-in-out;
            box-shadow: 0 4px 20px rgba(0, 191, 166, 0.4);
        }

        .btn-gradient:hover {
            transform: scale(1.05);
            box-shadow: 0 6px 25px rgba(0, 187, 156, 0.6);
        }
    </style>

    <style>
        .vitrine-venda {
            min-height: 100vh;
            background: linear-gradient(to bottom, rgba(10, 25, 47, 0.85), rgba(17, 34, 64, 0.85)),
                url('https://professoreugenio.com/img/vendas/bgvendas2.jpg') cover no-repeat;

            background-repeat: no-repeat;
            background-position: center center;
        }
    </style>
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-W1W8QZFR43"></script>
    <script>
        window.dataLayer = window.dataLayer || [];

        function gtag() {
            dataLayer.push(arguments);
        }
        gtag('js', new Date());
        gtag('config', 'G-W1W8QZFR43');
    </script>

    <!-- CSS do Plyr -->
    <link rel="stylesheet" href="https://cdn.plyr.io/3.7.8/plyr.css" />
    <!-- JS do Plyr -->
    <script src="https://cdn.plyr.io/3.7.8/plyr.polyfilled.js"></script>
</head>

<body>
    <?php include_once __DIR__ . '/config_default/body_navall.php'; ?>
    <main class="container py-4">
        <div class="row">
        </div>
        <?php
        require_once __DIR__ . '/vendasv1.0/form_vendas.php';

        ?>
    </main>


    <script src="vendasv1.0/JS_formVendas.js?<?= time(); ?>"></script>

    <div style="color:#000000" class="modal fade" id="statusModal" tabindex="-1" aria-labelledby="statusModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" style="color:rgb(2, 88, 209);" id="modalTitle"></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="modalBody"></div>
                    <div id="modalComplemento"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                </div>
            </div>
        </div>
    </div>
    <footer class="bg-dark text-white text-center py-4 mt-5">
        <small>&copy; <?= date('Y') ?> Curso Power BI – Todos os direitos reservados</small>
    </footer>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        AOS.init();
    </script>
    <script src="config_chat/js/atendimentoonline.js?v=<?= $versaoAssets ?>"></script>
    <script src="regixv2.0/acessopaginas.js?v=<?= $versaoAssets ?>"></script>
    <script src="acessosv1.0/ajax_registraAcesso.js"></script>
</body>

</html>
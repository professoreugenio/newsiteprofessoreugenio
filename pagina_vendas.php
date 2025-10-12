<?php
define('BASEPATH', true);
require_once __DIR__ . '/conexao/class.conexao.php';
require_once __DIR__ . '/autenticacao.php';

$nav = $_GET['nav'] ?? ($_COOKIE['nav'] ?? '');

$_SESSION['af'] = $_GET['af'] ?? ($_COOKIE['af'] ?? ''); // Novo parâmetro afiliado




function isCrawler(): bool
{
    $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
    return (bool)preg_match('/facebookexternalhit|Facebot|WhatsApp|Twitterbot|Pinterest|LinkedInBot|Slackbot|TelegramBot|Discord|discordbot|google.*snippet/i', $ua);
}

// Só bloqueie humanos; deixe crawlers verem a página
if (empty($_COOKIE['nav']) && !isCrawler()) {
    $nav = $_GET['nav'] ?? '';
    header('Location: action.php?curso=' . urlencode($nav));
    exit;
}


require_once __DIR__ . '/vendasv1.0/v2.0query_vendas.php';
$tituloPagina   = "" . $nomeCurso . " | Professor Eugênio – Invista em sua qualificação profissional";
$descricaoPagina = "Curso online " . $descricao;
$keywordsPagina  = $descricaoPagina . ', ' . extractWords($descricaoPagina) . ', professor eugenio, professoreugenio, cursos online, aulas online';
$imgMidia = '' . $imgMidiaCurso;
$versaoAssets    = time();
?>
<!DOCTYPE html>
<html lang="pt-br" prefix="og: http://ogp.me/ns#">

<head>


    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1" />

    <title><?php echo $tituloPagina;  ?></title>
    <meta name="description"
        content="<?php echo $descricaoPagina;  ?>">

    <!-- CANONICAL (precisa bater com o link compartilhado) -->
    <link rel="canonical" href="<?php echo $paginaatual;  ?>" />

    <!-- OPEN GRAPH (Facebook / WhatsApp) -->
    <meta property="og:type" content="website" />
    <meta property="og:locale" content="pt_BR" />
    <meta property="og:site_name" content="Professor Eugênio" />
    <meta property="og:title" content="<?php echo $tituloPagina;  ?>" />
    <meta property="og:description"
        content="<?php echo $descricaoPagina;  ?>" />
    <meta property="og:url" content="<?php echo $paginaatual;  ?>" />

    <!-- Imagem OG (absoluta, https, 1200x630 recomendado) -->
    <meta property="og:image"
        content="<?php echo $imgMidia;  ?>" />
    <meta property="og:image:secure_url"
        content="<?php echo $imgMidia;  ?>" />
    <meta property="og:image:type" content="image/jpeg" />
    <meta property="og:image:width" content="1200" />
    <meta property="og:image:height" content="630" />
    <meta property="og:image:alt" content="Professor Eugênio - Cursos Online" />
    <meta property="og:updated_time" content="<?= $data ?>T<?= $hora ?>-03:00" />

    <!-- TWITTER CARD -->
    <meta name="twitter:card" content="summary_large_image" />
    <meta name="twitter:title" content="<?php echo $tituloPagina;  ?>" />
    <meta name="twitter:description" content="<?php echo $descricaoPagina;  ?>" />
    <meta name="twitter:image"
        content="<?php echo $imgMidia;  ?>" />
    <!-- Se tiver @, preencha -->
    <!-- <meta name="twitter:site" content="@professoreugenio" /> -->

    <!-- FAVICON -->
    <link rel="icon" href="https://professoreugenio.com/img/favicon.png" type="image/png" />


    <!-- CSS / Ícones -->
    <link rel="preconnect" href="https://cdn.jsdelivr.net">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="vendasv1.0/css/CSS_config.css?v=1757617503" rel="stylesheet">

    <!-- AOS -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">

    <!-- Google Analytics -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-W1W8QZFR43"></script>
    <script>
        window.dataLayer = window.dataLayer || [];

        function gtag() {
            dataLayer.push(arguments);
        }
        gtag('js', new Date());
        gtag('config', 'G-W1W8QZFR43');
    </script>

    <!-- Botões sociais -->
    <style>
        .btn-youtube {
            background: #FF0000;
            color: #fff
        }

        .btn-youtube:hover {
            background: #e00000;
            color: #fff
        }

        .btn-instagram {
            background: #C13584;
            color: #fff
        }

        .btn-instagram:hover {
            background: #a62d72;
            color: #fff
        }
    </style>
</head>


<body>
    <!-- NAVBAR -->
    <?php include_once __DIR__ . '/config_default/body_navall.php'; ?>
    <?php // if ($vendaliberada == '1'): 
    ?>


    <?php if ($vendaliberada == "1"): ?>
       
        <?php include_once __DIR__ . '/vendasv1.0/VendasBody2.php'; ?>
    <?php else: ?>
        <?php include_once __DIR__ . '/vendasv1.0/bodyManutencao.php'; ?>
    <?php endif; ?>

    <?php include_once __DIR__ . '/vendasv1.0/VendasFooter.php'; ?>

    <script src="acessosv1.0/ajax_registraAcesso.js"></script>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    <script>
        $(document).ready(function() {
            // Captura os parâmetros da URL
            const urlParams = new URLSearchParams(window.location.search);
            const nav = urlParams.get('nav');
            const af = urlParams.get('af'); // novo parâmetro afiliado
            console.log("aqui: " + af);

            if (nav) {
                $.post('vendasv1.0/ajax_registraNav.php', {
                    nav: nav,
                    af: af || '' // envia vazio se não existir
                }, function(resp) {
                    if (resp.status === 'ok') {
                        console.log("Cookie NAV registrado:", resp.cookie);
                        if (resp.af) {
                            console.log("Afiliado registrado:", resp.af);
                        }
                    } else {
                        console.warn("Erro ao registrar nav:", resp.msg);
                    }
                }, 'json');
            }
        });
    </script>


    <!-- <script>
        $(document).ready(function() {
            // Captura o parâmetro nav da URL
            const urlParams = new URLSearchParams(window.location.search);
            const nav = urlParams.get('nav');

            if (nav) {
                $.post('vendasv1.0/ajax_registraNav.php', {
                    nav: nav
                }, function(resp) {
                    if (resp.status === 'ok') {
                        console.log("Cookie NAV registrado:", resp.cookie);
                    } else {
                        console.warn("Erro ao registrar nav:", resp.msg);
                    }
                }, 'json');
            }
        });
    </script> -->

</body>

</html>
<?php require 'view1.0/query_user.php'; ?>
<?php require 'view1.0/page_view_query.php'; ?>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Aula: <?php echo $tituloPublicacao;  ?></title>
<!-- FACEBOOK -->
<meta property="og:title" content="<?php echo $tituloPublicacao;  ?>">
<meta property="og:description" content="<?php echo $olho;  ?>">
<meta property="og:image" content="<?php echo $imgMidia;  ?>">
<meta property="og:image:alt" content="Imagem lição <?php echo $ordempub;  ?>">
<meta property="og:url" content="<?php echo $paginaatual;  ?>">
<meta property="og:type" content="website">
<meta property="og:site_name" content="@professoreugenio">
<meta property="og:locale" content="pt_BR">
<!-- TWITTER -->
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="<?php echo $tituloPublicacao;  ?>">
<meta name="twitter:description" content="<?php echo $olho;  ?>">
<meta name="twitter:image" content="<?php echo $imgMidia;  ?>">
<meta name="twitter:image:alt" content="Imagem lição <?php echo $ordempub;  ?>">
<meta name="twitter:site" content="@seuTwitter">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<link rel="stylesheet" href="config_index2/body_index.css">
<!-- <link rel="stylesheet" href="defaultv1.0/css/default.css"> -->
<link rel="stylesheet" href="defaultv1.0/css/youtube_thumbs.css?<?= time() ?>">
<!-- <link rel="stylesheet" href="mycss/config.css?<?= time() ?>"> -->
<link rel="stylesheet" href="defaultv1.0/css/CSS_mascote.css?ts=<?= time() ?>">
<link rel="stylesheet" href="indexv1.0/css/nav.css">
<link rel="stylesheet" href="view1.0/css/floatingAB.css?ts=<?= time() ?>">
<link rel="stylesheet" href="view1.0/css/CSS_view.css?ts=<?= time() ?>">
<link rel="stylesheet" href="view1.0/css/CSS_page_view.css?ts=<?= time() ?>">
<link rel="stylesheet" href="view1.0/css/CSS_canvasLicoes.css?= time() ?>">
<link rel="stylesheet" href="defaultv1.0/css/blink.css?ts=<?= time() ?>">
<link rel="stylesheet" href="view1.0/css/config_modal.css?ts=<?= time() ?>">
<!-- <link rel="stylesheet" href="config_view/floatingTopicos.css?ts=<?= time() ?>"> -->
<link rel="stylesheet" href="config_chat/CSS_chat.css?= time() ?>">
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<style>
    .btn-whatsapp {
        background-color: #25D366;
        color: white;
    }

    .btn-whatsapp:hover {
        background-color: #1ebe5b;
        color: white;
    }

    container {
        color: #ffffff;
    }

    .fixed-div-bottom {
        position: fixed;
        bottom: 0;
        left: 0;
        width: 100%;
        background-color: white;
        border-radius: 10px 10px 0 0;
        padding: 10px;
        box-shadow: 0px -2px 5px rgba(0, 0, 0, 0.1);
        text-align: center;
        z-index: 9000;
    }
</style>

<style>
    .card {
        max-width: 500px;
        margin: 20px auto;
        position: fixed;
        bottom: 20px;
    }

    .status {
        font-size: 0.9rem;
        color: #888;
    }
</style>
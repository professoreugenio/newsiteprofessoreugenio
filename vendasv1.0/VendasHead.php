<?php define('APP_ROOT', dirname(__DIR__, 1)); // sobe 2 níveis 
?>
<meta charset="utf-8">
<?php $titulodados = $nomeCurso; ?>
<?php $descricaodados = $descricao; ?>
<?php $imgMidia = $imgMidiaCurso; ?>
<?php require APP_ROOT . '/head/head_midiassociais.php'; ?>
<?php $versaoAssets = time(); ?>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<link href="vendasv1.0/css/CSS_config.css?v=<?= $versaoAssets; ?>" rel="stylesheet">

<script async src="https://www.googletagmanager.com/gtag/js?id=G-W1W8QZFR43"></script>
<script>
    window.dataLayer = window.dataLayer || [];

    function gtag() {
        dataLayer.push(arguments);
    }
    gtag('js', new Date());
    gtag('config', 'G-W1W8QZFR43');
</script>

<style>
    .btn-youtube {
        background: #FF0000;
        color: #fff;
    }

    .btn-youtube:hover {
        background: #e00000;
        color: #fff;
    }

    .btn-instagram {
        background: #C13584;
        color: #fff;
    }

    .btn-instagram:hover {
        background: #a62d72;
        color: #fff;
    }
</style>
<!-- AOS CSS -->
<link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet" />
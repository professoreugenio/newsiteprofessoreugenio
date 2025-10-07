<?php
define('BASEPATH', true);
define('APP_ROOT', dirname(__DIR__, 2));
require_once APP_ROOT . '/conexao/class.conexao.php';
require_once APP_ROOT . '/autenticacao.php';
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <?php require_once APP_ROOT . '/admin2/v1.0/head.php'; ?>
    <link rel="stylesheet" href="/admin2/v1.0/CSS_config.css?<?= time(); ?>">
    <?php require_once APP_ROOT . '/admin2/v1.0/dadosuser.php'; ?>
    <?php require_once APP_ROOT . '/admin2/modelo/bancodeImagens1.0/QueryBancodeImagens.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-lite.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-lite.min.js"></script>
    <style>
        .note-editor.note-frame {
            border-radius: 1rem;
        }

        .note-editable {
            min-height: 220px;
        }
    </style>

    <style>
        .thumb-100 {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: .5rem;
            border: 1px solid rgba(255, 255, 255, .08);
            transition: transform .15s ease;
            cursor: zoom-in;
            background: #0b0f16;
        }

        .thumb-100:hover {
            transform: scale(1.04);
        }

        .item-midia {
            position: relative;
        }

        .item-actions {
            position: absolute;
            right: 4px;
            top: 4px;
            display: flex;
            gap: .25rem;
            background: rgba(0, 0, 0, .35);
            padding: .15rem;
            border-radius: .35rem;
        }

        .item-meta {
            font-size: .75rem;
            color: #aab2c0;
        }

        #gridMidias {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
            gap: .75rem;
        }

        .progress-sm {
            height: .4rem;
        }

        /* Modal img grande */
        #lightboxImg {
            max-width: 100%;
            max-height: 70vh;
            border-radius: .5rem;
        }
    </style>

    <style>
        .thumb-250 {
            width: 250px;
            height: 250px;
            object-fit: cover;
            border-top-left-radius: .5rem;
            border-top-right-radius: .5rem;
            background: #0b0f16;
            border: 1px solid rgba(255, 255, 255, .08);
            transition: transform .15s ease;
            cursor: zoom-in;
            display: block;
            margin: 0 auto;
        }

        .thumb-250:hover {
            transform: scale(1.02);
        }

        #gridMidias {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(270px, 1fr));
            /* cabe 250px + paddings */
            gap: 1rem;
        }

        .item-card {
            background: #0f141c;
            border: 1px solid rgba(255, 255, 255, .08);
            border-radius: .5rem;
            overflow: hidden;
        }

        .item-actions {
            position: absolute;
            right: 6px;
            top: 6px;
            display: flex;
            gap: .3rem;
            background: rgba(0, 0, 0, .35);
            padding: .2rem;
            border-radius: .4rem;
        }

        .item-head {
            position: relative;
        }

        .item-footer {
            padding: .5rem .6rem;
            background: #0b0f16;
            border-top: 1px solid rgba(255, 255, 255, .06);
        }

        .item-meta {
            font-size: .8rem;
            color: #aab2c0;
            display: grid;
            gap: .25rem;
        }

        .item-meta .line {
            display: flex;
            align-items: center;
            gap: .5rem;
            flex-wrap: wrap;
        }

        .item-meta .sep {
            opacity: .4;
        }

        .progress-sm {
            height: .4rem;
        }

        #lightboxImg {
            max-width: 100%;
            max-height: 70vh;
            border-radius: .5rem;
        }
    </style>

</head>

<body id="adminLayout">
    <?php require_once APP_ROOT . '/admin2/v1.0/nav.php'; ?>
    <?php require_once APP_ROOT . '/admin2/v1.0/PainelLateral.php'; ?>
    <div class="container-fluid px-4 mt-5">
        <!-- Cabeçalho -->
        <div class="d-flex justify-content-between align-items-center mb-2">
            <div class="mt-4">
                <h3>
                    <i class="bi bi-camera" aria-hidden="true"></i> Banco de Imagens /
                    <span style="color:#ff0080; font-size: 20px"><i class="bi bi-images me-2"></i> <?= $nmGaleria; ?></span>
                </h3>

            </div>
            <div class="mt-4">
                <?php require_once APP_ROOT . '/admin2/modelo/bancodeImagens1.0/Subnav.php'; ?>

            </div>
        </div>
        <?php $Pasta = date('M') . "_" . date('Ymd') . time();;  ?>
        <?php require_once APP_ROOT . '/admin2/modelo/bancodeImagens1.0/BodyBancoImagensMidias.php'; ?>
    </div>
    <!-- Scripts -->
    <script src="../v1.0/PainelLateral.js"></script>
    <?php require_once APP_ROOT . '/admin2/v1.0/footer.php'; ?>
</body>

</html>
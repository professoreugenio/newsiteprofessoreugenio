<!-- Navbar -->
<?php include 'v2.0/nav.php'; ?>

<!-- Sidebars -->
<?php require_once 'config_default1.0/sidebarLateral.php'; ?>
<?php require_once 'config_default1.0/sidebarLateralLicoes.php'; ?>

<!-- Conteúdo principal -->
<main class="container mt-4">
    <div class="row w-100">
        <div class="col-md-12">
            <?php if (!empty($codigoUser) && $codigoUser == 1): ?>
                <h4 class="text-center">
                    Comercial <?= $comercialDados; ?> { Curso:<?= $nmCurso; ?> }{ IdTurma:<?= $idTurma; ?> }{ IdCurso: <?= $codigocurso; ?> }{ IdModulo: <?= $codigomodulo; ?> } <a href="modulo_curso_videos.php">VÍDEOS</a>
                </h4>
                <h4>
                    <?php $dec = encrypt($_COOKIE['adminstart'], $action = 'd');;  ?>
                </h4>
            <?php endif; ?>

            <!-- Módulo: Status do Curso -->
            <?php require_once 'config_curso1.0/listaVideos.php'; ?>
        </div>
    </div>
</main>

<!-- Rodapé -->
<?php require_once 'v2.0/footer.php'; ?>

<!-- JS Bootstrap + AOS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.js"></script>
<script>
    AOS.init();
</script>

<!-- Scripts adicionais -->
<script>
    function abrirPagina(url) {
        window.open(url, '_self');
    }
</script>
<script src="v2.0/registraacessos.js?<?= time(); ?>"></script>
<script src="config_default1.0/JS_logoff.js?<?= time(); ?>"></script>
<script src="config_aulas1.0/JS_sidebarLateral.js?<?= time(); ?>"></script>
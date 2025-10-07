<!-- Navbar -->
<?php include 'v2.0/nav.php'; ?>

<!-- Sidebars -->
<?php require_once 'config_default1.0/sidebarLateral.php'; ?>
<?php require_once 'config_default1.0/sidebarLateralLicoes.php'; ?>

<!-- Conteúdo principal -->
<main class="container mt-4">
    <div class="row w-100">
        <div class="col-md-12">


            <!-- Módulo: Status do Curso -->
            <?php if ($codigoUser == '1'): ?>

                <a href="modelopaginaModulosLicoes2.php">Novo</a>
            <?php require_once 'config_curso1.0/v5.0curso_modulo_status.php';
            else:
                require_once 'config_curso1.0/v4.0curso_modulo_status.php';
            endif; ?>
        </div>
        <?php if (!empty($codigoUser) && $codigoUser == 1): ?>
            <h6 class="text-center">*<?= $nomeTurma; ?>*</h6>
            <h6 class="text-center">
                Comercial <?= $comercialDados; ?> { Curso:<?= $nmCurso; ?> }{ IdTurma:<?= $idTurma; ?> }{ IdCurso: <?= $codigocurso; ?> }
            </h6>
            <h4>
                <?php $dec = encrypt($_COOKIE['adminstart'], $action = 'd');;  ?>
            </h4>
        <?php endif; ?>
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
<!-- Navbar -->
<?php include 'v2.0/nav.php'; ?>
<!-- Conteúdo -->
<main class="container ">
    <?php if ($codigoUser == 1): ?>
        <?php require 'config_curso1.0/ListaModulos-real.php'; ?>
    <?php else: ?>
        <?php require 'config_curso1.0/ListaModulos.php'; ?>
    <?php endif; ?>
</main>
<!-- Rodapé -->
<?php require 'v2.0/footer.php'; ?>
<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<!-- AOS JS -->
<script src="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.js"></script>
<script>
    AOS.init();
</script>
<script>
    function abrirPagina(url) {
        window.open(url, '_self');
    }
</script>
<script src="regixv2.0/acessopaginas.js?<?= time(); ?>"></script>
<script src="config_default1.0/JS_logoff.js?<?= time(); ?>"></script>
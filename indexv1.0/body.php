<?php
if (!isset($_COOKIE['startusuario']) && empty($_COOKIE['adminstart'])) {
    echo ('<a id="btloginaluno" href="login_aluno.php?ts=' . time() . '" class="btn btn-aluno">
        <i class="bi bi-person-circle me-2"></i> Aluno
    </a>');
}
?>
<?php if (!empty($_COOKIE['adminstart'])) {
    $decUser = encrypt($_COOKIE['adminstart'], $action = 'd');
    $exp = explode("&", $decUser);
    $codigoUser = $exp[0];
} ?>
<!-- Navbar -->
<?php include 'indexv1.0/body_nav.php'; ?>
<?php include 'indexv1.0/body_headerVideo.php'; ?>
<!-- Header -->
<?php if (empty($codigoUser)): include 'indexv1.0/modal_busca.php';
endif; ?>
<!-- Botão de Pesquisa Fixo -->
<button class="fixed-search-btn" id="searchBtn">
    <i class="bi bi-search"></i>
</button>


<?php include 'indexv1.0/body_section_cursos_livres.php'; ?>
<?php include 'indexv1.0/body_section_cursos_vendas.php'; ?>
<?php include 'indexv1.0/body_section_sobre.php'; ?>

<section class="container py-5">
    <h2 style="color: #ffffff;" class="text-center mt-4 mb-4">Parcerias</h2>
    <div class="row">
        <div class="d-flex justify-content-center flex-wrap">
            <?php require 'anunciov1.0/carousel_um.php' ?>
            <?php require 'anunciov1.0/carousel_dois.php' ?>
            <?php require 'anunciov1.0/carousel_tres.php' ?>
            <?php require 'anunciov1.0/carousel_quatro.php' ?>
        </div>

    </div>
</section>

<script src="regixv2.0/acessopaginas.js?t=<? time(); ?>"></script>
<?php if (!empty($_COOKIE['registracessos'])): echo "Registro OK";
else: echo "Não Registrado";
endif; ?>
<!-- Contato Section -->
<button id="scrollToTopBtn" class="scrollToTopBtn"><i class="bi bi-arrow-up"></i></button>
<?php require 'defaultv1.0/link_adm.php'; ?>
<!-- Footer -->
<footer class="bg-dark text-white text-center py-3">
    <p class="mb-0">&copy;Professor Eugênio 2025 Cursos Online. Todos os direitos reservados.</p>
</footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
</script>
<button id="scrollButton" class="scroll-btn"> <i class="bi bi-chevron-down"></i> </button>
<script>
    document.getElementById("scrollButton").addEventListener("click", function() {
        window.scrollBy({
            top: window.innerHeight,
            behavior: 'smooth'
        });
        this.style.display = "none";
    });
</script>
<script src="defaultv1.0/scrollToTop.js"></script>
<script src="defaultv1.0/JS_logoff.js"></script>
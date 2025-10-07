<!-- CTA FINAL -->
<section class="text-center py-5" id="inscricao">
    <div class="container" data-aos="zoom-in">
        <h2 class="mb-3">Inscreva-se Agora</h2>

        <?= $cta ?? '' ?>


        <a href="<?= $linkInscricao ?? 'pagina_vendasInscricao.php?t=' . time() . '&nav=' . $_GET['nav']; ?>" class="btn btn-gradient btn-lg">
            Fazer Minha Inscrição
        </a>


    </div>
</section>
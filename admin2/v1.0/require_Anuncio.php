    <?php if (temPermissao($niveladm, [1])): ?>
        <!-- Anúncios -->
        <div class="col-md-6 col-xl-3" data-aos="fade-up" data-aos-delay="150">
            <div class="card stat-card card-amber h-100 border-start">
                <div class="card-body d-flex flex-column gap-2">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="d-flex align-items-center gap-2">
                            <span class="icon-badge"><i class="bi bi-badge-ad-fill"></i></span>
                            <h6 class="title mb-0">Anúncios</h6>
                        </div>
                        <span class="value"><?= $totalAnuncios ?? 0; ?></span>
                    </div>
                    <span class="label text-muted">Anúncios no site</span>
                    <a href="anuncios.php" class="stretched-link" aria-label="Abrir Anúncios"></a>
                    <span class="corner"></span>
                </div>
            </div>
        </div>
    <?php endif; ?>
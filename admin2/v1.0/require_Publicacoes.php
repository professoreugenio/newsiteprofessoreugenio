    <?php if (temPermissao($niveladm, [1])): ?>
        <!-- Conteúdos -->
        <div class="col-md-6 col-xl-3" data-aos="fade-up" data-aos-delay="400">
            <div class="card stat-card card-orange h-100 border-start">
                <div class="card-body d-flex flex-column gap-2">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="d-flex align-items-center gap-2">
                            <span class="icon-badge"><i class="bi bi-collection-play-fill"></i></span>
                            <h6 class="title mb-0">Publicações</h6>
                        </div>
                        <span class="value">245</span>
                    </div>
                    <span class="label text-muted">Materiais disponíveis</span>
                    <a href="conteudoCategorias.php" class="stretched-link" aria-label="Abrir Publicações"></a>
                    <span class="corner"></span>
                </div>
            </div>
        </div>
    <?php endif; ?>
<?php if (temPermissao($niveladm, [1, 3])): ?>
    <!-- Cursos -->
    <div class="col-md-6 col-xl-3" data-aos="fade-up" data-aos-delay="300">
        <div class="card stat-card card-lime h-100 border-start">
            <div class="card-body d-flex flex-column gap-2">
                <div class="d-flex justify-content-between align-items-start">
                    <div class="d-flex align-items-center gap-2">
                        <span class="icon-badge"><i class="bi bi-journal-text"></i></span>
                        <h6 class="title mb-0">Cursos</h6>
                    </div>
                    <span class="value"><?= $conttl; ?> <small class="text-muted">(<?= $conton; ?>)</small></span>
                </div>
                <span class="label text-muted">Cursos publicados</span>
                <a href="cursos.php?status=1" class="stretched-link" aria-label="Abrir Cursos"></a>
                <span class="corner"></span>
            </div>
        </div>
    </div>
<?php endif; ?>
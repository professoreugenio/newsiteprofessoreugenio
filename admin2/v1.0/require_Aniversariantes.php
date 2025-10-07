<?php if (temPermissao($niveladm, [1, 2])): ?>
    <!-- Aniversariantes -->
    <div class="col-md-6 col-xl-3" data-aos="fade-up" data-aos-delay="250">
        <div class="card stat-card card-sky h-100 border-start">
            <div class="card-body d-flex flex-column gap-2">
                <div class="d-flex justify-content-between align-items-start">
                    <div class="d-flex align-items-center gap-2">
                        <span class="icon-badge"><i class="bi bi-gift-fill"></i></span>
                        <h6 class="title mb-0">Aniversariantes</h6>
                    </div>
                    <span class="value"><?= $totalAlunosAni; ?></span>
                </div>
                <span class="label text-muted">Neste mês</span>
                <a href="alunosAniversariantes.php" class="stretched-link" aria-label="Abrir Aniversariantes"></a>
                <span class="corner"></span>
            </div>
        </div>
    </div>
<?php endif; ?>
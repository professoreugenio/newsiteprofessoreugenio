<?php if (temPermissao($niveladm, [1])): ?>
    <!-- CPanel -->
    <div class="col-md-6 col-xl-3" data-aos="fade-up" data-aos-delay="550">
        <div class="card stat-card card-slate h-100 border-start">
            <div class="card-body d-flex flex-column gap-2">
                <div class="d-flex justify-content-between align-items-start">
                    <div class="d-flex align-items-center gap-2">
                        <span class="icon-badge"><i class="bi bi-server"></i></span>
                        <h6 class="title mb-0">CPanel</h6>
                    </div>
                    <span class="value">Admin</span>
                </div>
                <span class="label text-muted">Acesso direto ao servidor</span>
                <a target="_blank" href="https://professoreugenio.com:2083" class="stretched-link" aria-label="Abrir CPanel"></a>
                <span class="corner"></span>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php if (temPermissao($niveladm, [1])): ?>
    <!-- Vendas -->
    <div class="col-md-6 col-xl-3" data-aos="fade-up" data-aos-delay="200">
        <div class="card stat-card card-violet h-100 border-start">
            <div class="card-body d-flex flex-column gap-2">
                <div class="d-flex justify-content-between align-items-start">
                    <div class="d-flex align-items-center gap-2">
                        <span class="icon-badge"><i class="bi bi-bag-check-fill"></i></span>
                        <h6 class="title mb-0">Páginas Admin</h6>
                    </div>
                    
                </div>
                <span class="label text-muted">Construtor de Páginas</span>
                <a href="paginasAdmin.php?view=atuais" class="stretched-link" aria-label="Abrir Vendas"></a>
                <span class="corner"></span>
            </div>
        </div>
    </div>
<?php endif; ?>
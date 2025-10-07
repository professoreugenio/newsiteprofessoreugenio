    <?php if (temPermissao($niveladm, [1, 2])): ?>
        <!-- Banco de Imagens -->
        <div class="col-md-6 col-xl-3" data-aos="fade-up" data-aos-delay="0">
            <div class="card stat-card card-indigo h-100 border-start">
                <div class="card-body d-flex flex-column gap-2">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="d-flex align-items-center gap-2">
                            <span class="icon-badge"><i class="bi bi-images"></i></span>
                            <h6 class="title mb-0">Afiliados</h6>
                        </div>
                    </div>
                    <span class="label text-muted">Gerenciar produtos Vendas</span>
                    <a href="sistema_afiliadosProdutos.php" class="stretched-link" aria-label="Abrir Banco de Imagens"></a>
                    <span class="corner"></span>
                </div>
            </div>
        </div>
    <?php endif; ?>
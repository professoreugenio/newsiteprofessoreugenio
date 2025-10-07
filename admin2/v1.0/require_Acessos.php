  <?php if (temPermissao($niveladm, [1])): ?>
      <!-- Acessos -->
      <div class="col-md-6 col-xl-3" data-aos="fade-up" data-aos-delay="350">
          <div class="card stat-card card-slate h-100 border-start">
              <div class="card-body d-flex flex-column gap-2">
                  <div class="d-flex justify-content-between align-items-start">
                      <div class="d-flex align-items-center gap-2">
                          <span class="icon-badge"><i class="bi bi-bar-chart-fill"></i></span>
                          <h6 class="title mb-0">Acessos</h6>
                      </div>
                      <span class="value"><?= $totalAcessos; ?></span>
                  </div>
                  <span class="label text-muted">Acessos hoje</span>
                  <a href="alunosAcessos.php" class="stretched-link" aria-label="Abrir Acessos"></a>
                  <span class="corner"></span>
              </div>
          </div>
      </div>
  <?php endif; ?>
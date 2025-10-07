  <?php if (temPermissao($niveladm, [1, 2])): ?>
      <?php
        $stmt = config::connect()->prepare("
        SELECT codigoForum FROM a_curso_forum
        WHERE acessadoCF = 0
        ORDER BY dataCF DESC, horaCF DESC
      ");
        $stmt->execute();
        $itens = $stmt->fetchAll(PDO::FETCH_ASSOC);
        ?>
      <!-- Avaliações -->
      <div class="col-md-6 col-xl-3" data-aos="fade-up" data-aos-delay="50">
          <div class="card stat-card card-rose h-100 border-start">
              <div class="card-body d-flex flex-column gap-2">
                  <div class="d-flex justify-content-between align-items-start">
                      <div class="d-flex align-items-center gap-2">
                          <span class="icon-badge"><i class="bi bi-emoji-smile"></i></span>
                          <h6 class="title mb-0">Avaliações</h6>
                      </div>
                      <span class="value"><?= count($itens) ?></span>
                  </div>
                  <span class="label text-muted">Últimas avaliações dos usuário</span>
                  <a href="avaliacoes.php" class="stretched-link" aria-label="Abrir Avaliações"></a>
                  <span class="corner"></span>
              </div>
          </div>
      </div>
  <?php endif; ?>
<?php if (temPermissao($niveladm, [1, 2])): ?>
    <?php
    $mesAtual = date('Y-m');
    $filtroMes = $_GET['mes'] ?? $mesAtual;
    $stmt = config::connect()->prepare("
        SELECT codigocontato FROM new_sistema_contato
        WHERE DATE_FORMAT(datasc, '%Y-%m') = :mes
        ORDER BY datasc DESC, horasc DESC
      ");
    $stmt->execute([':mes' => $filtroMes]);
    $ttlMsg = $stmt->rowCount();
    ?>
    <!-- Mensagens -->
    <div class="col-md-6 col-xl-3" data-aos="fade-up" data-aos-delay="450">
        <div class="card stat-card card-rose h-100 border-start">
            <div class="card-body d-flex flex-column gap-2">
                <div class="d-flex justify-content-between align-items-start">
                    <div class="d-flex align-items-center gap-2">
                        <span class="icon-badge"><i class="bi bi-chat-left-text-fill"></i></span>
                        <h6 class="title mb-0">Mensagens</h6>
                    </div>
                    <span class="value"><?= $ttlMsg; ?></span>
                </div>
                <span class="label text-muted">Novas mensagens</span>
                <a href="MensagensRecebidas.php" class="stretched-link" aria-label="Abrir Mensagens"></a>
                <span class="corner"></span>
            </div>
        </div>
    </div>
<?php endif; ?>
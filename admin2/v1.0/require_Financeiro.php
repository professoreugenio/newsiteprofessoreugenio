 <?php if (temPermissao($niveladm, [1])): ?>
     <?php
        $anoMesAtual = date('Y-m');
        $stmtSaldo = $con->prepare("
        SELECT
          SUM(CASE WHEN l.tipoLancamentos = 1 THEN f.valorCF ELSE 0 END) AS total_credito,
          SUM(CASE WHEN l.tipoLancamentos = 2 THEN f.valorCF ELSE 0 END) AS total_debito
        FROM a_curso_financeiro f
        INNER JOIN a_curso_financeiroLancamentos l
          ON f.idLancamentoCF = l.codigolancamentos
        WHERE DATE_FORMAT(f.dataFC, '%Y-%m') = :anoMes
      ");
        $stmtSaldo->bindValue(':anoMes', $anoMesAtual);
        $stmtSaldo->execute();
        $saldo = $stmtSaldo->fetch(PDO::FETCH_ASSOC);
        $credito = floatval($saldo['total_credito']);
        $debito = floatval($saldo['total_debito']);
        $saldoAtual = $credito - $debito;
        ?>
     <!-- Financeiro -->
     <div class="col-md-6 col-xl-3" data-aos="fade-up" data-aos-delay="500">
         <div class="card stat-card card-cyan h-100 border-start">
             <div class="card-body d-flex flex-column gap-2">
                 <div class="d-flex justify-content-between align-items-start">
                     <div class="d-flex align-items-center gap-2">
                         <span class="icon-badge"><i class="bi bi-currency-dollar"></i></span>
                         <h6 class="title mb-0">Financeiro</h6>
                     </div>
                     <span class="value valor-card">R$ <?= number_format($saldoAtual, 2, ',', '.'); ?></span>
                 </div>
                 <span class="label text-muted">Faturamento do mês</span>
                 <a href="curso_Financeiro.php" class="stretched-link" aria-label="Abrir Financeiro"></a>
                 <span class="corner"></span>
             </div>
         </div>
     </div>
 <?php endif; ?>
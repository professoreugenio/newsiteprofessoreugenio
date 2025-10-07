 <?php if (temPermissao($niveladm, [1, 2])): ?>
     <!-- Usuários -->
     <div class="col-md-6 col-xl-3" data-aos="fade-up" data-aos-delay="100">
         <div class="card stat-card card-emerald h-100 border-start">
             <div class="card-body d-flex flex-column gap-2">
                 <div class="d-flex justify-content-between align-items-start">
                     <div class="d-flex align-items-center gap-2">
                         <span class="icon-badge"><i class="bi bi-people-fill"></i></span>
                         <h6 class="title mb-0">Usuários</h6>
                     </div>
                     <span class="value"><?= $totalAlunos; ?></span>
                 </div>
                 <span class="label text-muted">Usuários no sistema</span>
                 <a href="alunos.php" class="stretched-link" aria-label="Abrir Usuários"></a>
                 <span class="corner"></span>
             </div>
         </div>
     </div>
 <?php endif; ?>
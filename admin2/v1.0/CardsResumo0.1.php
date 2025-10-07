<!-- CARDS DE RESUMO -->
<div class="row g-4">
    <?php if (temPermissao($niveladm, [1, 2])): ?>
        <div class="row g-4">
            <div class="col-md-6 col-xl-3" data-aos="fade-up">
                

                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <h5 class="card-title text-primary"><i class="bi bi-people-fill me-2"></i> Usuários</h5>
                        <p class="card-text fs-4 fw-bold"><?= $totalAlunos; ?></p>
                        <small class="text-muted">Usuários no sistema <a href="alunos.php">Acesse</a></small>
                    </div>
                </div>



            </div>
            <div class="col-md-6 col-xl-3" data-aos="fade-up">

                <?php require '../v1.0/queryAniversariantes.php'; ?>
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <h5 class="card-title text-primary">
                            <i class="bi bi-people-fill me-2"></i> Aniversariantes
                        </h5>
                        <p class="card-text fs-4 fw-bold"><?= $totalAlunosAni; ?></p>
                        <small class="text-muted">Visualizar <a href="alunosAniversariantes.php">Acesse</a></small>
                    </div>
                </div>
            </div>
            <?php require '../v1.0/queryContCursos.php'; ?>
            <div class="col-md-6 col-xl-3" data-aos="fade-up">

                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <h5 class="card-title text-success"><i class="bi bi-journal-text me-2"></i> Cursos</h5>
                        <p class="card-text fs-4 fw-bold"><?= $conttl; ?> (<?= $conton; ?> online )</p>
                        <small class="text-muted">Cursos publicados</small>
                        <a href="cursos.php">Acesse</a>
                    </div>
                </div>
            </div>
            <?php require '../v1.0/queryContAcessos.php'; ?>
            <div class="col-md-6 col-xl-3" data-aos="fade-up">
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <h5 class="card-title text-success"><i class="bi bi-folder-fill me-2"></i> Acessos</h5>
                        <p class="card-text fs-4 fw-bold"><?= $totalAcessos ?></p>
                        <small class="text-muted">Usuários no site <a href="alunosAcessos.php">Acesse</a></small>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3" data-aos="fade-up">
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <h5 class="card-title text-warning"><i class="bi bi-folder-fill me-2"></i> Conteúdos</h5>
                        <p class="card-text fs-4 fw-bold">245</p>
                        <small class="text-muted">Materiais disponíveis</small>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-xl-3" data-aos="fade-up">
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <h5 class="card-title text-danger"><i class="bi bi-chat-left-text-fill me-2"></i> Mensagens</h5>
                        <p class="card-text fs-4 fw-bold">7</p>
                        <small class="text-muted">Novas mensagens</small>
                    </div>
                </div>
            </div>
            <?php if (temPermissao($niveladm, [1])): ?>
                <div class="col-md-6 col-xl-3" data-aos="fade-up">
                    <div class="card shadow-sm border-0">
                        <div class="card-body">
                            <h5 class="card-title text-danger"><i class="bi bi-chat-left-text-fill me-2"></i> PHP MyAdmin Cpanel</h5>
                            <p class="card-text fs-4 fw-bold">7</p>
                            <small class="text-muted">Cpanel <a target="_blank" href="https://professoreugenio.com:2083">Acessar</a></small>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>
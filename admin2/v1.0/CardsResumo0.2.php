<div class="d-flex justify-content-end mb-3">
    <button id="toggleValores" class="btn btn-outline-secondary btn-sm">
        <i id="iconToggle" class="bi bi-eye"></i> Ocultar valores
    </button>
</div>

<div class="row g-4">

    <?php require '../v1.0/queryContTotalAlunos.php'; ?>
    <?php require '../v1.0/queryAniversariantes.php'; ?>
    <?php require '../v1.0/queryContCursos.php'; ?>
    <?php require '../v1.0/queryContAcessos.php'; ?>

    <?php if (temPermissao($niveladm, [1, 2])): ?>
        <!-- Card Usuários -->
        <div class="col-md-6 col-xl-3" data-aos="fade-up">
            <div class="card shadow-sm border-start border-4 border-primary h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <h5 class="card-title text-primary mb-1">
                            <i class="bi bi-camera" aria-hidden="true"></i> Banco de Imagens
                        </h5>
                        <span class="fs-5 fw-bold text-primary"></span>
                    </div>
                    <p class="text-muted small mb-1">Banco de Imagens</p>
                    <a href="bancodeImagens.php" class="small text-decoration-none">Acesse <i class="bi bi-arrow-right"></i></a>
                </div>
            </div>
        </div>

    <?php endif; ?>


    <?php if (temPermissao($niveladm, [1, 2])): ?>
        <?php
        // LISTAGEM: somente itens ainda não acessados
        $stmt = config::connect()->prepare("
    SELECT 
        codigoForum, idusuarioCF, idartigoCF, idcodforumCF,
        textoCF, visivelCF, acessadoCF, dataCF, destaqueCF, horaCF
    FROM a_curso_forum
    WHERE acessadoCF = 0
    ORDER BY dataCF DESC, horaCF DESC
");
        $stmt->execute();
        $itens = $stmt->fetchAll(PDO::FETCH_ASSOC);
        ?>
        <div class="col-md-6 col-xl-3" data-aos="fade-up">
            <div class="card shadow-sm border-start border-4 border-primary h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <h5 class="card-title text-primary mb-1">
                            <i class="bi bi-people-fill me-2"></i> Avaliações
                        </h5>
                        <span class="fs-5 fw-bold text-primary"><?= count($itens) ?></span>
                    </div>
                    <p class="text-muted small mb-1">Últimas avaliações</p>
                    <a href="avaliacoes.php" class="small text-decoration-none">Acesse
                        <i class="bi bi-arrow-right"></i>
                    </a>
                </div>
            </div>
        </div>

    <?php endif; ?>

    <?php if (temPermissao($niveladm, [1, 2])): ?>
        <!-- Card Usuários -->
        <div class="col-md-6 col-xl-3" data-aos="fade-up">
            <div class="card shadow-sm border-start border-4 border-primary h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <h5 class="card-title text-primary mb-1">
                            <i class="bi bi-people-fill me-2"></i> Usuários
                        </h5>
                        <span class="fs-5 fw-bold text-primary"><?= $totalAlunos; ?></span>
                    </div>
                    <p class="text-muted small mb-1">Usuários no sistema</p>
                    <a href="alunos.php" class="small text-decoration-none">Acesse <i class="bi bi-arrow-right"></i></a>
                </div>
            </div>
        </div>

    <?php endif; ?>

    <?php if (temPermissao($niveladm, [1])): ?>
        <!-- Card Usuários -->
        <div class="col-md-6 col-xl-3" data-aos="fade-up">
            <div class="card shadow-sm border-start border-4 border-primary h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <h5 class="card-title text-primary mb-1">
                            <i class="bi bi-people-fill me-2"></i> Anúncios
                        </h5>
                        <span class="fs-5 fw-bold text-primary"><?= $totalAnuncios ?? 0; ?></span>
                    </div>
                    <p class="text-muted small mb-1">Anúncios no Site</p>
                    <a href="anuncios.php" class="small text-decoration-none">Acesse <i class="bi bi-arrow-right"></i></a>
                </div>
            </div>
        </div>

    <?php endif; ?>



    <!-- Card Vendas -->
    <?php if (temPermissao($niveladm, [1])): ?>
        <div class="col-md-6 col-xl-3" data-aos="fade-up">
            <div class="card shadow-sm border-start border-4 border-primary h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <h5 class="card-title text-primary mb-1">
                            <i class="bi bi-people-fill me-2"></i> Vendas
                        </h5>
                        <span class="fs-5 fw-bold text-primary">$</span>
                    </div>
                    <p class="text-muted small mb-1">Páginas de Venda</p>
                    <a href="vendas.php?status=1" class="small text-decoration-none">Acesse <i class="bi bi-arrow-right"></i></a>
                </div>
            </div>
        </div>

    <?php endif; ?>
    <!-- Card Avaliações -->



    <?php if (temPermissao($niveladm, [1, 2])): ?>
        <!-- Card Aniversariantes -->
        <div class="col-md-6 col-xl-3" data-aos="fade-up">
            <div class="card shadow-sm border-start border-4 border-info h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <h5 class="card-title text-info mb-1">
                            <i class="bi bi-gift-fill me-2"></i> Aniversariantes
                        </h5>
                        <span class="fs-5 fw-bold text-info">
                            <span class="fs-5 fw-bold text-primary"><?= $totalAlunosAni; ?></span>
                        </span>
                    </div>
                    <p class="text-muted small mb-1">Neste mês</p>
                    <a href="alunosAniversariantes.php" class="small text-decoration-none">Acesse <i class="bi bi-arrow-right"></i></a>
                </div>
            </div>
        </div>

    <?php endif; ?>

    <?php if (temPermissao($niveladm, [1, 3])): ?>
        <!-- Card Cursos -->
        <div class="col-md-6 col-xl-3" data-aos="fade-up">
            <div class="card shadow-sm border-start border-4 border-success h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <h5 class="card-title text-success mb-1">
                            <i class="bi bi-journal-text me-2"></i> Cursos
                        </h5>
                        <span class="fs-5 fw-bold text-success"><?= $conttl; ?> <small class="text-muted">(<?= $conton; ?>)</small></span>
                    </div>
                    <p class="text-muted small mb-1">Cursos publicados</p>
                    <a href="vendas.php?status=1" class="small text-decoration-none">Acesse <i class="bi bi-arrow-right"></i></a>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <?php if (temPermissao($niveladm, [1])): ?>
        <!-- Card Acessos -->
        <div class="col-md-6 col-xl-3" data-aos="fade-up">
            <div class="card shadow-sm border-start border-4 border-secondary h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <h5 class="card-title text-secondary mb-1">
                            <i class="bi bi-bar-chart-fill me-2"></i> Acessos
                        </h5>
                        <span class="fs-5 fw-bold text-secondary"><?= $totalAcessos; ?>
                        </span>
                    </div>
                    <p class="text-muted small mb-1">Acessos hoje</p>
                    <a href="alunosAcessos.php" class="small text-decoration-none">Acesse <i class="bi bi-arrow-right"></i></a>
                </div>
            </div>
        </div>

    <?php endif; ?>
    <?php if (temPermissao($niveladm, [1])): ?>
        <!-- Card Conteúdos -->
        <div class="col-md-6 col-xl-3" data-aos="fade-up">
            <div class="card shadow-sm border-start border-4 border-warning h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <h5 class="card-title text-warning mb-1">
                            <i class="bi bi-collection-play-fill me-2"></i> Conteúdos
                        </h5>
                        <span class="fs-5 fw-bold text-warning">245</span>
                    </div>
                    <p class="text-muted small mb-1">Materiais disponíveis</p>
                    <a href="conteudoCategorias.php" class="small text-decoration-none">Acesse <i class="bi bi-arrow-right"></i></a>
                </div>
            </div>
        </div>

        <!-- Card Mensagens -->

        <?php

        // Filtro por mês
        $mesAtual = date('Y-m');
        $filtroMes = $_GET['mes'] ?? $mesAtual;

        $stmt = config::connect()->prepare("
    SELECT codigocontato, nomesc, assuntosc, datasc, horasc
    FROM new_sistema_contato
    WHERE DATE_FORMAT(datasc, '%Y-%m') = :mes
    ORDER BY datasc DESC, horasc DESC
");
        $stmt->execute([':mes' => $filtroMes]);
        $mensagens = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $ttlMsg = count($mensagens);
        ?>
        <div class="col-md-6 col-xl-3" data-aos="fade-up">
            <div class="card shadow-sm border-start border-4 border-danger h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <h5 class="card-title text-danger mb-1">
                            <i class="bi bi-chat-left-text-fill me-2"></i> Mensagens
                        </h5>
                        <span class="fs-5 fw-bold text-danger"><?= $ttlMsg; ?></span>
                    </div>
                    <p class="text-muted small mb-1">Novas mensagens</p>
                    <a href="MensagensRecebidas.php" class="small text-decoration-none">Acesse <i class="bi bi-arrow-right"></i></a>
                </div>
            </div>
        </div>
    <?php endif; ?>
    <?php if (temPermissao($niveladm, [1])): ?>
        <!-- Card Financeiro -->
        <?php
        $anoMesAtual = date('Y-m');
        $stmtSaldo = $con->prepare("SELECT SUM(CASE WHEN l.tipoLancamentos = 1 THEN f.valorCF ELSE 0 END) AS total_credito, SUM(CASE WHEN l.tipoLancamentos = 2 THEN f.valorCF ELSE 0 END) AS total_debito FROM a_curso_financeiro f INNER JOIN a_curso_financeiroLancamentos l ON f.idLancamentoCF = l.codigolancamentos WHERE DATE_FORMAT(f.dataFC, '%Y-%m') = :anoMes");
        $stmtSaldo->bindValue(':anoMes', $anoMesAtual);
        $stmtSaldo->execute();
        $saldo = $stmtSaldo->fetch(PDO::FETCH_ASSOC);
        $credito = floatval($saldo['total_credito']);
        $debito = floatval($saldo['total_debito']);
        $saldoAtual = $credito - $debito;
        ?>
        <div class="col-md-6 col-xl-3" data-aos="fade-up">
            <div class="card shadow-sm border-start border-4 border-dark h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <h5 class="card-title text-dark mb-1">
                            <i class="bi bi-currency-dollar me-2"></i> Financeiro
                        </h5>
                        <span class="fs-5 fw-bold text-dark">
                            <span class="fs-5 fw-bold text-primary valor-card">
                                R$ <?= number_format($saldoAtual, 2, ',', '.'); ?>
                            </span>


                        </span>
                    </div>
                    <p class="text-muted small mb-1">Faturamento do mês</p>
                    <a href="curso_Financeiro.php" class="small text-decoration-none">Detalhes <i class="bi bi-arrow-right"></i></a>
                </div>
            </div>
        </div>

    <?php endif; ?>
    <?php if (temPermissao($niveladm, [1])): ?>
        <!-- Card CPanel -->
        <?php if (temPermissao($niveladm, [1])): ?>
            <div class="col-md-6 col-xl-3" data-aos="fade-up">
                <div class="card shadow-sm border-start border-4 border-danger h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <h5 class="card-title text-danger mb-1">
                                <i class="bi bi-server me-2"></i> CPanel
                            </h5>
                            <span class="fs-5 fw-bold text-danger">Admin</span>
                        </div>
                        <p class="text-muted small mb-1">Acesso direto ao servidor</p>
                        <a target="_blank" href="https://professoreugenio.com:2083" class="small text-decoration-none">Acessar <i class="bi bi-box-arrow-up-right"></i></a>
                    </div>
                </div>
            </div>
        <?php endif; ?>

    <?php endif; ?>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        const toggleBtn = document.getElementById("toggleValores");
        const icon = document.getElementById("iconToggle");
        let visivel = false; // Começa oculto

        // Oculta todos os valores inicialmente
        document.querySelectorAll('.valor-card').forEach(el => {
            el.style.visibility = 'hidden';
        });

        toggleBtn.addEventListener("click", function() {
            visivel = !visivel;
            document.querySelectorAll('.valor-card').forEach(el => {
                el.style.visibility = visivel ? 'visible' : 'hidden';
            });
            icon.className = visivel ? 'bi bi-eye' : 'bi bi-eye-slash';
            toggleBtn.innerHTML = `<i id="iconToggle" class="${icon.className}"></i> ${visivel ? 'Ocultar' : 'Exibir'} valores`;
        });

        // Ajusta ícone e texto inicial
        icon.className = 'bi bi-eye-slash';
        toggleBtn.innerHTML = `<i id="iconToggle" class="bi bi-eye-slash"></i> Exibir valores`;
    });
</script>
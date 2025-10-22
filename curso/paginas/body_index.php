<!-- Navbar -->
<?php include 'v2.0/nav.php'; ?>
<!-- Conteúdo -->
<section id="Corpo" class="">
    <div class="container text-center">
        <!-- Listagem de Turmas -->
        <div class="container">

            <!-- BUSCA DE CONTEÚDO -->
            <!-- BUSCA DE CONTEÚDO (compacta, arredondada, centralizada) -->
            <?php require 'config_curso1.0/require_buscar.php'; ?>

            <?php if ($codigoUser == 1): ?>

                <a id="modulos"></a>
                <?php require 'config_curso1.0/ListaModulos2.0.php'; ?>

            <?php else: ?>
                <a id="modulos"></a>

                <?php require 'config_curso1.0/ListaModulos2.0.php'; ?>

            <?php endif; ?>

            <?php if ($comercialTurma == '1') : ?>
                <?php require 'config_curso1.0/require_Ultimaspublicacoes.php'; ?>
            <?php endif; ?>

            <!-- aqui banner -->
            <!-- aqui banner -->
            <!-- aqui banner -->
            <?php require 'config_curso1.0/require_BannerAmazon.php' ?>

            <!-- Responsividade personalizada -->
            <style>
                @media (min-width: 768px) {
                    .w-md-50 {
                        width: 50% !important;
                    }
                }
            </style>


        </div>
    </div>
</section>
<!-- Modal: Aulas Atuais / Assistidas -->
<!-- Modal: Aulas Atuais / Assistidas -->
<?php
// Garante que $moduloAtualId já foi definido (como no passo anterior)
if (empty($moduloAtualId)) {
    $stmt = $con->prepare("
                SELECT idmoduloaa 
                FROM a_aluno_andamento_aula
                WHERE idalunoaa = :idusuario AND idturmaaa = :idturma
                ORDER BY dataaa DESC, horaaa DESC
                LIMIT 1
            ");
    $stmt->bindParam(':idusuario', $idUser, PDO::PARAM_INT);
    $stmt->bindParam(':idturma', $idTurma, PDO::PARAM_INT);
    $stmt->execute();
    $moduloAtualId = (int)($stmt->fetchColumn() ?? 0);
}
if ($moduloAtualId > 0): ?>
    <div class="modal fade aulas-modal aulas-modal--light" id="modalAulasAtuais" tabindex="-1" aria-labelledby="modalAulasAtuaisLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content aulas-modal__content shadow-lg border-0 rounded-4 overflow-hidden">
                <!-- Header -->
                <div class="modal-header aulas-modal__header border-0 p-4 position-relative">
                    <h5 class="modal-title m-0 d-flex align-items-center gap-2" id="modalAulasAtuaisLabel" data-aos="fade-down">
                        <span class="aulas-modal__dot"></span> Último Módulo/ assistido
                    </h5>
                    <button type="button" class="btn-close btn-close-white ms-auto" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                <!-- Body -->
                <div class="modal-body aulas-modal__body p-4">
                    <p class="text-center">Clique no nome do módulo para acessar suas aulas.</p>
                    <?php
                    // Busca o nome do módulo atual
                    $stmtMod = $con->prepare("
                SELECT codigomodulos, modulo 
                FROM new_sistema_modulos_PJA
                WHERE codigomodulos = :idmodulo AND codcursos = :idcurso AND visivelm = '1'
                LIMIT 1
            ");
                    $stmtMod->bindParam(':idmodulo', $moduloAtualId, PDO::PARAM_INT);
                    $stmtMod->bindParam(':idcurso', $idCurso, PDO::PARAM_INT);
                    $stmtMod->execute();
                    if ($rowMod = $stmtMod->fetch(PDO::FETCH_ASSOC)):
                        $nomeModuloAtual = $rowMod['modulo'];
                        $encUltimo = encrypt("$idUser&$idCurso&$idTurma&$moduloAtualId", 'e');
                    ?>
                        <!-- Destaque do último módulo acessado -->
                        <div class="aulas-modal__highlight mt-2 mb-1 text-center" data-aos="fade-up">
                            <span class="badge aulas-modal__badge fw-bold">ATUAL</span>
                            <a href="actionCurso.php?mdl=<?= $encUltimo; ?>" class="aulas-modal__link fw-bold text-decoration-none">
                                <?= htmlspecialchars($nomeModuloAtual); ?>
                                <i class="bi bi-chevron-right ms-1"></i>
                            </a>
                        </div>
                    <?php
                    else:
                    ?>
                        <div class="aulas-modal__alert" data-aos="fade-up">
                            Nenhum acesso registrado ainda.
                        </div>
                    <?php endif; ?>
                </div>
                <!-- Footer -->
                <div class="modal-footer aulas-modal__footer d-flex justify-content-center align-items-center p-3 border-0">
                    <button type="button" class="btn btn-warning" data-bs-dismiss="modal">
                        FECHAR
                    </button>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        if (window.AOS) AOS.init({
            duration: 700,
            once: true
        });
        const el = document.getElementById('modalAulasAtuais');
        if (el && window.bootstrap) {
            const hoje = new Date().toISOString().slice(0, 10);
            const ultimaData = localStorage.getItem('aulasAtuaisData');
            if (ultimaData !== hoje) {
                // ainda não mostrou hoje → abre o modal
                const modal = new bootstrap.Modal(el);
                modal.show();
                // salva que já mostrou hoje
                localStorage.setItem('aulasAtuaisData', hoje);
            }
        }
    });
</script>
<!-- Rodapé -->
<?php require 'v2.0/footer.php'; ?>
<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<!-- AOS JS -->
<script src="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.js"></script>
<script>
    AOS.init();
</script>
<script>
    function abrirPagina(url) {
        window.open(url, '_self');
    }
</script>
<!-- <script src="regixv3.0/acessopaginas.js?<?= time(); ?>" type="module"></script> -->
<script src="config_default1.0/JS_logoff.js?<?= time(); ?>"></script>
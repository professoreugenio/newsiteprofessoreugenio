<!-- Navbar -->
<?php include 'v2.0/nav.php'; ?>
<!-- Conteúdo -->
<section id="Corpo" class="">
    <div class="container text-center">
        <!-- Listagem de Turmas -->
        <div class="container">
            <div class="text-center mb-5">
                <h4 class="mt-4 mb-2 text-white">
                    <i class="bi bi-layers"></i> Módulos do Curso*
                </h4>
                <a href="modulos.php" class="btn btn-primary">Acessar Módulos</a>
            </div>
        </div>
    </div>
</section>
<?php
/**
 * MÓDULO: Busca simples em new_sistema_publicacoes_PJA
 * - Retorna apenas dados da própria tabela
 * - Usa conexão $con já existente no escopo
 */


 $idcurso = $idCurso ?? null;
 $idturma = $idTurma ?? null;

if (!isset($con) || !$con instanceof PDO) {
    echo '<div class="alert alert-danger">Conexão indisponível.</div>';
    return;
}

$q = trim((string)($_GET['q'] ?? ''));
if ($q === '') {
    echo '<div class="alert alert-info">Digite um termo para buscar.</div>';
    return;
}

// Normalização do termo (case-insensitive)
$qParam = '%' . mb_strtolower(preg_replace('/\s+/', ' ', $q)) . '%';


?>
<div class="container py-4">
    <header class="mb-4 d-flex align-items-center justify-content-between">
        <h1 class="h4 m-0" style="color:var(--brand-h1)">Resultados da busca</h1>
        <a href="<?= htmlspecialchars($_SERVER['HTTP_REFERER'] ?? 'index.php') ?>" class="btn btn-outline-light btn-sm">
            <i class="bi bi-arrow-left"></i> Voltar
        </a>
    </header>

    

    <?php require 'config_curso1.0/require_Modulosbusca.php'; ?>

</div>




<!-- Modal: Aulas Atuais / Assistidas -->
<!-- Modal: Aulas Atuais / Assistidas -->
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
<script src="regixv2.0/acessopaginas.js?<?= time(); ?>"></script>
<script src="config_default1.0/JS_logoff.js?<?= time(); ?>"></script>
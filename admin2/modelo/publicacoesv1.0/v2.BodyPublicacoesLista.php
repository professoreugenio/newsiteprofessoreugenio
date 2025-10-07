<?php $id = "1"; ?>
<?php $ordem = "1"; ?>
<?php $status = "1"; ?>

<div class="d-flex flex-wrap gap-2 mb-3">
    <?php
    $aulaAtual = isset($_GET['aula']) ? intval($_GET['aula']) : 1;
    for ($i = 1; $i <= 9; $i++):
        $classe = $aulaAtual === $i ? 'btn-primary' : 'btn-outline-primary';
        $link = "cursos_publicacoes.php?id={$_GET['id']}&md={$_GET['md']}&aula=$i";


    ?>
        <a href="<?= $link ?>" class="btn <?= $classe ?> btn-sm"><?= $i ?></a>
    <?php endfor; ?>
</div>
<?php require_once APP_ROOT . '/admin2/modelo/adm/idcursomodulo.php'; ?>
<?php
// Recebe o número da aula via GET ou define como 1 por padrão
$aulaSelecionada = isset($_GET['aula']) ? intval($_GET['aula']) : 1;

$stmt = config::connect()->prepare("
    SELECT codigopublicacoes, titulo, ordempc, visivelpc, codigopublicacoescursos,idmodulopc
    FROM a_aluno_publicacoes_cursos, new_sistema_publicacoes_PJA
    WHERE codigopublicacoes = idpublicacaopc AND idcursopc = :idcurso AND idmodulopc = :idmodulo AND aula = :aula 

    ORDER BY ordempc, visivelpc DESC
");
$stmt->bindParam(":idcurso", $idCurso);
$stmt->bindParam(":idmodulo", $idModulo);
$stmt->bindParam(":aula", $aulaSelecionada, PDO::PARAM_INT);
$stmt->execute();
?>
<?php if ($stmt->rowCount() > 0): ?>

    <h5><?php echo $stmt->rowCount();  ?> Resultados para <?php echo $Nomemodulo;  ?></h5>
    <ul class="list-group">
        <?php $n = 0; ?>
        <?php while ($row = $stmt->fetch(PDO::FETCH_ASSOC)): ?>
            <?php $id = $row['codigopublicacoescursos'];  ?>
            <?php $idPublic = $row['codigopublicacoes'];  ?>
            <?php $nm = $row['titulo'];  ?>
            <?php $ordem = $row['ordempc'];  ?>
            <?php $idmoduloCopia = $row['idmodulopc'] ?? '';  ?>
            <?php $encId = encrypt($idPublic, $action = 'e'); ?>
            <?php $encIdaula = encrypt($id, $action = 'e'); ?>
            <?php $encIdModulo = encrypt($idModulo, $action = 'e'); ?>
            <?php $status = $row['visivelpc'];  ?>
            <?php $n++; // Incrementa a cada item
            $duracao = 500 + ($n * 100); // Começa em 600ms e vai subindo 
            ?>
            <li class="list-group-item flex-column mb-2 shadow-sm rounded" data-aos="fade-left" data-aos-duration="<?= $duracao; ?>">
                <?php require 'contYoutube.php'; ?>
                <?php require 'contVideos.php'; ?>
                <div class="d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-file-text text-success fs-5"></i>
                        <a href="cursos_publicacaoEditar.php?id=<?= $_GET['id']; ?>&md=<?= $encIdModulo; ?>&pub=<?= $encIdaula; ?>" class="text-decoration-none fw-semibold text-dark me-3">
                            <?= $ordem; ?>.<?= $youtube; ?> <?= $video; ?> <?= $idPublic; ?> *<?= $nm; ?>*
                        </a>
                    </div>
                    <div class="d-flex align-items-center">
                        <span class="badge bg-secondary rounded-pill me-3"><?= $ordem; ?></span>
                        <span data-bs-toggle="tooltip" title="menu">
                            <i class="bi bi-globe-americas fs-5 <?= $status == 1 ? 'text-success' : 'text-danger'; ?>"></i>
                        </span>
                    </div>
                </div>

            </li>

        <?php endwhile; ?>

    </ul>
<?php else: ?>
    <p>Nenhuma publicação encontrada.</p>
<?php endif; ?>


<script>
    document.addEventListener('DOMContentLoaded', function() {
        const params = new URLSearchParams(window.location.search);
        const aulaURL = params.get('aula');
        const ultimaAula = localStorage.getItem('ultimaAulaSelecionada');

        // Sempre salvar aula atual
        localStorage.setItem('ultimaAulaSelecionada', aulaURL || '1');

        // Se não tiver aula na URL, mas tiver no localStorage, redireciona
        if (!aulaURL && ultimaAula && parseInt(ultimaAula) >= 1 && parseInt(ultimaAula) <= 9) {
            params.set('aula', ultimaAula);
            window.location.search = params.toString();
        }
    });
</script>
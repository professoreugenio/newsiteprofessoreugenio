<?php $id = "1"; ?>
<?php $ordem = "1"; ?>
<?php $status = "1"; ?>

<div class="d-flex flex-wrap gap-2 mb-3">
    <?php
    
    for ($i = 1; $i <= 9; $i++):
        $classe = $aulaAtual === $i ? 'btn-primary' : 'btn-outline-primary';
        $link = "";


    ?>
        <a href="<?= $link ?>" class="btn <?= $classe ?> btn-sm"><?= $i ?></a>
    <?php endfor; ?>
</div>
<?php require_once APP_ROOT . '/admin2/modelo/adm/idcursomodulo.php'; ?>
<?php
// Recebe o número da aula via GET ou define como 1 por padrão


$stmt = config::connect()->prepare("
    SELECT *
    FROM new_sistema_cadastro
    ORDER BY nome
");

$stmt->execute();
?>
<?php if ($stmt->rowCount() > 0): ?>

    <h5><?php echo $stmt->rowCount();  ?> Resultados para <?php echo $Nomemodulo;  ?></h5>
    <ul class="list-group">
        <?php $n = 0; ?>
        <?php while ($row = $stmt->fetch(PDO::FETCH_ASSOC)): ?>
            <?php $id = $row['codigocadastro'];  ?>
            <?php $encId = encrypt($id, $action = 'e'); ?>
            <?php $nm = $row['nome'];  ?>
            <?php $ordem = $row['ordem'];  ?>
            <?php $status = "1"; ?>
            <?php $n++; // Incrementa a cada item
            $duracao = 500 + ($n * 100); // Começa em 600ms e vai subindo 
            ?>
            <li class="list-group-item flex-column mb-2 shadow-sm rounded" data-aos="fade-up" data-aos-duration="<?= $duracao; ?>">

                <div class="d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-file-text text-success fs-5"></i>
                        <a href="#" class="text-decoration-none fw-semibold text-dark me-3">
                            <?= $ordem; ?>.<?= $nm; ?>
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
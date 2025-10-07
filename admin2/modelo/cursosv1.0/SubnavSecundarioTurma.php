<?php $paginaAtual = basename($_SERVER['PHP_SELF']); ?>
<?php if (empty($_GET['ano'])):
    $ano = date('Y');
else:
    $ano = $_GET['ano'];
endif; ?>
<div>
    <nav aria-label="Navegação de edição de curso">
        <ul class="pagination justify-content-center">
            <li class="page-item">
                <a class="page-link <?= $paginaAtual == 'cursos_turmasEditar.php' ? 'bglaranja' : '' ?>" href="cursos_turmasEditar.php?id=<?= $_GET['id'] ?>" title="Editar Curso">
                    <i class="bi bi-pencil-square"></i>
                </a>
            </li>
            <li class="page-item">
                <a class="page-link <?= $paginaAtual == 'cursos_turmas.php' ? 'bglaranja' : '' ?>" href="cursos_turmas.php?id=<?= $_GET['id'] ?>" title="Lista Turmas">
                    <i class="bi bi-mortarboard"></i>
                </a>
            </li>
            <li class="page-item">
                <a class="page-link <?= $paginaAtual == 'cursos_turmas_email.php' ? 'bglaranja' : '' ?>" href="cursos_turmas_email.php?id=<?= $_GET['id'] ?>&ano=<?= $ano ?>" title="e-mails">
                    <i class="bi bi-envelope"></i>
                </a>
            </li>
            <li class="page-item">
                <a class="page-link <?= $paginaAtual == 'cursos_modulos.php' ? 'bglaranja' : '' ?>" href="cursos_modulos.php?id=<?= $_GET['id'] ?>" title="Lista módulos">
                    <i class="bi bi-diagram-3 text-default"></i>
                </a>
            </li>
        </ul>
    </nav>
</div>
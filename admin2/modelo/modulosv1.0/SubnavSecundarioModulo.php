<?php $paginaAtual = basename($_SERVER['PHP_SELF']); ?>

<div>
    <nav aria-label="Navegação de edição de curso">
        <ul class="pagination justify-content-center">
            <?php if ($paginaAtual == 'cursos_modulosEditar.php' ): ?>
                <li class="page-item">
                    <a class="page-link <?= $paginaAtual == 'cursos_modulosEditar.php' ? 'bglaranja' : '' ?>" href="cursos_modulosEditar.php?id=<?= $_GET['id'] ?>" title="Editar Módulo">
                        <i class="bi bi-pencil-square"></i>*
                    </a>

                </li>
            <li class="page-item">
                <a class="page-link <?= $paginaAtual == 'cursos_publicacoes.php' ? 'bglaranja' : '' ?>" href="cursos_publicacoes.php?id=<?= $_GET['id'] ?>&md=<?= $_GET['md'] ?>" title="Publicacoes do Módulo">
                    <i class="bi bi-list"></i>*
                </a>

            </li>
            <?php endif; ?>



            <li class="page-item">
                <a class="page-link <?= $paginaAtual == 'cursos_modulos.php' ? 'bglaranja' : '' ?>" href="cursos_modulos.php?id=<?= $_GET['id'] ?>" title="Lista módulos">
                    <i class="bi bi-diagram-3 text-default"></i>
                </a>

            </li>

            <?php if ($paginaAtual == 'cursos_modulos.php' || $paginaAtual == 'cursos_moduloNovo.php'): ?>
                <li class="page-item">
                    <a class="page-link <?= $paginaAtual == 'cursos_moduloNovo.php' ? 'bglaranja' : 'bgverde' ?>" href="cursos_moduloNovo.php?id=<?= $_GET['id'] ?>" title="Novo Módulo">
                        + <i class="bi bi-diagram-3 text-default"></i>
                    </a>
                </li>
            <?php endif; ?>
            <li class="page-item">
                <a class="page-link <?= $paginaAtual == 'cursos_turmas.php' ? 'bglaranja' : '' ?>" href="cursos_turmas.php?id=<?= $_GET['id'] ?>" title="Lista Turmas">
                    <i class="bi bi-mortarboard"></i>
                </a>
            </li>


            <?php if ($paginaAtual == 'cursos_turmas.php'): ?>
                <li class="page-item">
                    <a class="page-link <?= $paginaAtual == 'cursos_turmaNovo.php' ? 'bglaranja' : 'bgverde' ?>" href="cursos_turmaNovo.php?id=<?= $_GET['id'] ?>" title="Lista Turmas">
                        + <i class="bi bi-people-fill"></i>
                    </a>
                </li>
            <?php endif; ?>



        </ul>
    </nav>
</div>
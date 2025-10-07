<?php $paginaAtual = basename($_SERVER['PHP_SELF']); ?>

<div>
    <nav aria-label="Navegação de edição de curso">
        <ul class="pagination justify-content-center">
            <li class="page-item">
                <a class="page-link <?= $paginaAtual == 'cursos.php' ? 'bglaranja' : '' ?>" href="usuarios.php" title="Lista de Usuários">
                    <i class="fas fa-users"></i>
                </a>
            </li>

        </ul>
    </nav>
</div>
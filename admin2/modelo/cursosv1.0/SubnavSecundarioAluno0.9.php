<?php $paginaAtual = basename($_SERVER['PHP_SELF']); ?>

<div id="NavegacaoSecundaria">
    <nav aria-label="Navegação de edição de curso">
        <ul class="pagination justify-content-center">
            <li class="page-item">
                <a class="page-link <?= $paginaAtual == 'cursos_turmasEditar.php' ? 'bglaranja' : '' ?>" href="cursos_turmasEditar.php?id=<?= $_GET['id']; ?>&tm=<?= $_GET['tm']; ?>" title="Editar Curso">
                    <i class="bi bi-pencil-square"></i>
                </a>

            </li>
            <li class="page-item">
                <a class="page-link <?= $paginaAtual == 'cursos_turmasEditarFinanceiro.php' ? 'bglaranja' : '' ?>" href="cursos_turmasEditarFinanceiro.php?id=<?= $_GET['id']; ?>&tm=<?= $_GET['tm']; ?>" title="Editar Financeiro">
                    <i class="bi bi-pencil-square"></i>
                </a>

            </li>
            <li class="page-item">
                <a class="page-link <?= $paginaAtual == 'cursos_TurmasAlunos.php' ? 'bglaranja' : '' ?>" href="cursos_TurmasAlunos.php?id=<?= $_GET['id'] ?>&tm=<?= $_GET['tm'] ?>" title="Alunos da Turma">
                    <i class="bi bi-people-fill"></i>
                </a>

            </li>
            <li class="page-item">
                <a class="page-link <?= $paginaAtual == 'cursos_TurmasAlunosEmail.php' ? 'bglaranja' : '' ?>" href="cursos_TurmasAlunosEmail.php?id=<?= $_GET['id'] ?>&tm=<?= $_GET['tm'] ?>" title="Alunos da Turma">
                    <i class="bi bi-envelope-fill"></i>
                </a>

            </li>
            <li class="page-item">
                <a class="page-link <?= $paginaAtual == 'cursos_TurmasAlunosFrequencia.php' ? 'bglaranja' : '' ?>" href="cursos_TurmasAlunosFrequencia.php?id=<?= $_GET['id'] ?>&tm=<?= $_GET['tm'] ?>" title="Frequência da Turma">
                    <i class="bi bi-calendar3"></i>
                </a>

            </li>
            <li class="page-item">
                <a class="page-link <?= $paginaAtual == 'cursos_turmas.php' ? 'bglaranja' : '' ?>" href="cursos_turmas.php?id=<?= $_GET['id'] ?>" title="Lista Turmas">
                    <i class="bi bi-mortarboard"></i>
                </a>
            </li>


        </ul>
    </nav>
</div>
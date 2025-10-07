<?php $paginaAtual = basename($_SERVER['PHP_SELF']); ?>

<style>
    #NavegacaoSecundaria {
        position: fixed;
        top: 50%;
        right: 0;
        transform: translateY(-50%);
        z-index: 1055;
        transition: right 0.3s ease;
    }

    #NavegacaoSecundaria.collapsed {
        right: -170px;
    }

    #NavegacaoSecundaria nav {
        background-color: #f8f9fa;
        border-radius: 10px 0 0 10px;
        padding: 10px;
        width: 170px;
        box-shadow: -2px 2px 6px rgba(0, 0, 0, 0.2);
    }

    #btnToggleNav {
        position: absolute;
        top: 50%;
        left: -30px;
        transform: translateY(-50%);
        border-radius: 50%;
        background-color: #ff9c00;
        color: white;
        width: 30px;
        height: 30px;
        display: flex;
        align-items: center;
        justify-content: center;
        border: none;
        cursor: pointer;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.2);
    }

    .page-link {
        display: flex;
        justify-content: center;
        align-items: center;
        width: 38px;
        height: 38px;
    }

    .bglaranja {
        background-color: #ff9c00 !important;
        color: white !important;
        border-color: #ff9c00 !important;
    }
</style>

<div id="NavegacaoSecundaria" class="collapsed">
    <button id="btnToggleNav" title="Abrir Menu">
        <i class="bi bi-chevron-left"></i>
    </button>

    <nav aria-label="Navegação de edição de curso">
        <ul class="pagination flex-column text-center mb-0">
            <li class="page-item mb-2">
                <a class="page-link <?= $paginaAtual == 'cursos_turmasEditar.php' ? 'bglaranja' : '' ?>" href="cursos_turmasEditar.php?id=<?= $_GET['id']; ?>&tm=<?= $_GET['tm']; ?>" title="Editar Curso">
                    <i class="bi bi-pencil-square"></i>
                </a>
            </li>
            <li class="page-item mb-2">
                <a class="page-link <?= $paginaAtual == 'cursos_turmasEditarFinanceiro.php' ? 'bglaranja' : '' ?>" href="cursos_turmasEditarFinanceiro.php?id=<?= $_GET['id']; ?>&tm=<?= $_GET['tm']; ?>" title="Editar Financeiro">
                    <i class="bi bi-cash-coin"></i>
                </a>
            </li>
            <li class="page-item mb-2">
                <a class="page-link <?= $paginaAtual == 'cursos_TurmasAlunos.php' ? 'bglaranja' : '' ?>" href="cursos_TurmasAlunos.php?id=<?= $_GET['id']; ?>&tm=<?= $_GET['tm']; ?>" title="Alunos">
                    <i class="bi bi-people-fill"></i>
                </a>
            </li>
            <li class="page-item mb-2">
                <a class="page-link <?= $paginaAtual == 'cursos_TurmasAlunosEmail.php' ? 'bglaranja' : '' ?>" href="cursos_TurmasAlunosEmail.php?id=<?= $_GET['id']; ?>&tm=<?= $_GET['tm']; ?>" title="E-mail Alunos">
                    <i class="bi bi-envelope-fill"></i>
                </a>
            </li>
            <li class="page-item mb-2">
                <a class="page-link <?= $paginaAtual == 'cursos_TurmasAlunosFrequencia.php' ? 'bglaranja' : '' ?>" href="cursos_TurmasAlunosFrequencia.php?id=<?= $_GET['id']; ?>&tm=<?= $_GET['tm']; ?>" title="Frequência">
                    <i class="bi bi-calendar-check-fill"></i>
                </a>
            </li>
            <li class="page-item mb-2">
                <a class="page-link <?= $paginaAtual == 'cursos_TurmasCalendarioAulas.php' ? 'bglaranja' : '' ?>" href="cursos_TurmasCalendarioAulas.php?id=<?= $_GET['id']; ?>&tm=<?= $_GET['tm']; ?>" title="Frequência">
                    <i class="bi bi-calendar-check-fill"></i>
                </a>
            </li>
            <li class="page-item">
                <a class="page-link <?= $paginaAtual == 'cursos_turmas.php' ? 'bglaranja' : '' ?>" href="cursos_turmas.php?id=<?= $_GET['id']; ?>" title="Voltar">
                    <i class="bi bi-mortarboard"></i>
                </a>
            </li>
        </ul>
    </nav>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const nav = document.getElementById('NavegacaoSecundaria');
        const btn = document.getElementById('btnToggleNav');
        const icon = btn.querySelector('i');

        btn.addEventListener('click', () => {
            nav.classList.toggle('collapsed');
            icon.classList.toggle('bi-chevron-left');
            icon.classList.toggle('bi-chevron-right');
        });
    });
</script>
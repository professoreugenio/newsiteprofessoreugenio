<?php $paginaAtual = basename($_SERVER['PHP_SELF']); ?>

<div>
    <nav aria-label="Navegação de edição de curso">
        <ul class="pagination justify-content-center">
            <li class="page-item">
                <a class="page-link <?= $paginaAtual == 'vendas.php' ? 'bglaranja' : '' ?>" href="vendas.php?status=1" title="Lista de Cursos">
                    <i class="bi bi-list"></i>
                </a>
            </li>
            <li class="page-item">
                <a class="page-link <?= $paginaAtual == 'cursos_editar.php' ? 'bglaranja' : '' ?>" href="cursos_editar.php?id=<?= $_GET['id'] ?>" title="Editar Curso">
                    <i class="bi bi-pencil-square"></i>
                </a>
            </li>
            <li class="page-item">
                <a class="page-link <?= $paginaAtual == 'cursos_novo.php' ? 'bglaranja' : '' ?>" href="cursos_novo.php?id=<?= $_GET['id'] ?>" title="Novo Curso">
                    [+]
                </a>
            </li>
            <li class="page-item">
                <a class="page-link <?= $paginaAtual == 'cursos_pagamentos.php' ? 'bglaranja' : '' ?>" href="cursos_pagamentos.php?id=<?= $_GET['id'] ?>" title="Financeiro">
                    <i class="bi bi-credit-card"></i>
                </a>
            </li>
            <li class="page-item">
                <a class="page-link <?= $paginaAtual == 'cursos_descricao.php' ? 'bglaranja' : '' ?>" href="cursos_descricao.php?id=<?= $_GET['id'] ?>" title="Descrição">
                    <i class="bi bi-file-text"></i>
                </a>
            </li>
            <li class="page-item dropdown">
                <a class="page-link dropdown-toggle <?= in_array($paginaAtual, [
                                                        'curso_edt_img_apres.php',
                                                        'curso_edt_img_capa.php',
                                                        'curso_edt_img_insc.php',
                                                    ]) ? 'bglaranja' : '' ?>" href="#" id="imagensDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false" title="Imagens">
                    <i class="fa fa-camera" aria-hidden="true"></i>
                </a>
                <ul class="dropdown-menu" aria-labelledby="imagensDropdown">
                    <li><a class="dropdown-item" href="curso_edt_img_apres.php?id=<?= $_GET['id']; ?>">Foto Apresentação*</a></li>
                    <li><a class="dropdown-item" href="curso_edt_img_capa.php?id=<?= $_GET['id']; ?>">Foto Capa Curso</a></li>
                    <li><a class="dropdown-item" href="curso_edt_img_insc.php?id=<?= $_GET['id']; ?>">Foto Inscrição</a></li>

                </ul>
            </li>
        </ul>
    </nav>
</div>
<?php $paginaAtual = basename($_SERVER['PHP_SELF']); ?>

<div>
    <nav aria-label="Navegação de edição de curso">

        <ul class="pagination justify-content-center">

            <li class="page-item">
                <a class="page-link <?= $paginaAtual == 'cursos_editar.php' ? 'bglaranja' : '' ?>" href="cursos_editar.php?id=<?= $_GET['id'] ?>" title="Editar Curso">
                    <i class="bi bi-pencil-square"></i>
                </a>

            </li>

            <?php
            $paginasLead = [
                "cursos_editar.php",
                "curso_edt_img_apres.php",
                "curso_edt_img_capa.php",
                "curso_edt_img_insc.php",

            ];

            if (in_array($paginaAtual, $paginasLead)):
            ?>
                <li class="page-item dropdown">
                    <a class="page-link dropdown-toggle <?= in_array($paginaAtual, array_slice($paginasLead, 1)) ? 'bglaranja' : '' ?>"
                        href="#" id="imagensDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false" title="Imagens">
                        <i class="fa fa-camera" aria-hidden="true"></i>
                    </a>
                    <ul class="dropdown-menu" aria-labelledby="imagensDropdown">
                        <li><a class="dropdown-item" href="curso_edt_img_apres.php?id=<?= $_GET['id']; ?>">Foto Apresentação*</a></li>
                        <li><a class="dropdown-item" href="curso_edt_img_capa.php?id=<?= $_GET['id']; ?>">Foto Capa Curso</a></li>
                        <li><a class="dropdown-item" href="curso_edt_img_insc.php?id=<?= $_GET['id']; ?>">Foto Inscrição</a></li>
                    </ul>
                </li>

            <?php endif; ?>

            <?php
            $paginasLead = [
                "cursos_editar.php",
                "cursos_LeadVendaHero.php",
                "cursos_LeadVendaBeneficios.php",
                "cursos_LeadVendaSobre.php",
                "cursos_LeadVendaCta.php"
            ];

            if (in_array($paginaAtual, $paginasLead)):
            ?>
                <li class="page-item dropdown">
                    <a class="page-link dropdown-toggle <?= in_array($paginaAtual, array_slice($paginasLead, 1)) ? 'bglaranja' : '' ?>"
                        href="#" id="imagensDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false" title="Imagens">
                        <i class="bi bi-card-text"></i>
                    </a>
                    <ul class="dropdown-menu" aria-labelledby="imagensDropdown">
                        <li><a class="dropdown-item" href="cursos_LeadVendaHero.php?id=<?= $_GET['id']; ?>">HERO</a></li>
                        <li><a class="dropdown-item" href="cursos_LeadVendaBeneficios.php?id=<?= $_GET['id']; ?>">BENEFÌCIOS</a></li>
                        <li><a class="dropdown-item" href="cursos_LeadVendaSobre.php?id=<?= $_GET['id']; ?>">SOBRE</a></li>
                        <li><a class="dropdown-item" href="cursos_LeadVendaCta.php?id=<?= $_GET['id']; ?>">CTA</a></li>
                    </ul>
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
<?php $paginaAtual = basename($_SERVER['PHP_SELF']); ?>

<?php

if ($paginaAtual != 'cursos_publicacoes.php' && $paginaAtual != 'cursos_publicacoesNovo.php'):
    if (!isset($_GET['pub']) || empty($_GET['pub'])) {
        $pub = "pub=" . $_GET['pub'];
    } else {
        $pub = "pub=" . $_GET['pub'];
    }
endif;
?>
<div>
    <nav aria-label="Navegação de edição de curso">
        <ul class="pagination justify-content-center">
            <?php if ($paginaAtual == 'cursos_publicacaoEditar.php' || $paginaAtual == 'cursos_publicacaoEditarTexto.php' || $paginaAtual == 'cursos_publicacaoFotos.php'): ?>
                <li class="page-item">
                    <a class="page-link <?= $paginaAtual == 'cursos_publicacaoEditar.php' ? 'bglaranja' : '' ?>" href="cursos_publicacaoEditar.php?id=<?= $_GET['id'] ?>&md=<?= $_GET['md'] ?>&<?= $pub ?>" title="Editar Publicacao">
                        <i class="bi bi-pencil-square"></i>*
                    </a>

                </li>
                <li class="page-item">
                    <a class="page-link <?= $paginaAtual == 'cursos_publicacaoEditarTexto.php' ? 'bglaranja' : '' ?>" href="cursos_publicacaoEditarTexto.php?id=<?= $_GET['id'] ?>&md=<?= $_GET['md'] ?>&<?= $pub ?>" title="Editar texto da Publicacao">
                        <i class="bi bi-file-text"></i>*
                    </a>

                </li>

                <li class="page-item">
                    <a class="page-link <?= $paginaAtual == 'cursos_publicacaoFotos.php' ? 'bglaranja' : '' ?>" href="cursos_publicacaoFotos.php?id=<?= $_GET['id'] ?>&md=<?= $_GET['md'] ?>&pub=<?= $_GET['pub'] ?>" title="Lista módulos">
                        <i class="bi bi-camera text-default"></i>
                    </a>

                </li>
                <li class="page-item">
                    <a class="page-link <?= $paginaAtual == 'cursos_publicacaoAnexos.php' ? 'bglaranja' : '' ?>" href="cursos_publicacaoAnexos.php?id=<?= $_GET['id'] ?>&md=<?= $_GET['md'] ?>&pub=<?= $_GET['pub'] ?>" title="Lista módulos">
                        <i class="bi bi-paperclip text-default"></i>
                    </a>

                </li>
            <?php endif; ?>
            <li class="page-item">
                <a class="page-link <?= $paginaAtual == 'cursos_publicacoes.php' ? 'bglaranja' : '' ?>" href="cursos_publicacoes.php?id=<?= $_GET['id'] ?>&md=<?= $_GET['md'] ?>" title="Publicacoes do Módulo">
                    <i class="bi bi-list"></i>*
                </a>

            </li>





            <li class="page-item">
                <a class="page-link <?= $paginaAtual == 'cursos_turmas.php' ? 'bglaranja' : '' ?>" href="cursos_turmas.php?id=<?= $_GET['id'] ?>" title="Lista Turmas">
                    <i class="bi bi-mortarboard"></i>
                </a>
            </li>

            <li class="page-item">
                <a class="page-link <?= $paginaAtual == 'cursos_publicacoesNovo.php' ? 'bglaranja' : 'bgverde' ?>" href="cursos_publicacoesNovo.php?id=<?= $_GET['id'] ?>&md=<?= $_GET['md'] ?>" title="Nova Publicação">

                    <i class="bi bi-file-text"></i>+
                </a>
            </li>




        </ul>
    </nav>
</div>
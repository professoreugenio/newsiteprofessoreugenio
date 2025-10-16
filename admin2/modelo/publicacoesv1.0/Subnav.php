<?php
$pagina = basename($_SERVER['PHP_SELF']);

?>
<div class="d-flex gap-2 flex-wrap mb-3">

    <?php if (temPermissao($niveladm, [1])): ?>
        <!-- conteudoCategorias.php?id=UFZVTFV4Wms5MTFXV0o2VFAwMGs1Zz09&md=RmYvUlB4dktNTDlkaGhDMHp0TUZFdz09 -->
        <a href="cursos.php?id=<?= $_GET['id'] ?>&md=<?= $_GET['md'] ?>"
            class="btn btn-flat btn-sm <?= $pagina == 'cursos.php' ? 'btn-laranja' : '' ?>"
            title="Ver publicações">
            CURSOS
        </a>
        <a href="conteudoCategorias.php?id=<?= $_GET['id'] ?>&md=<?= $_GET['md'] ?>"
            class="btn btn-flat btn-sm <?= $pagina == 'conteudoCategorias.php' ? 'btn-laranja' : '' ?>"
            title="Ver publicações">
            PUBLICAÇOES
        </a>
        <a href="cursos_publicacoes.php?id=<?= $_GET['id'] ?>&md=<?= $_GET['md'] ?>"
            class="btn btn-flat btn-sm <?= $pagina == 'cursos_publicacoes.php' ? 'btn-laranja' : '' ?>"
            title="Ver publicações">
            <i class="bi bi-list"></i>
        </a>
        <a href="cursos_publicacoesNovo.php?id=<?= $_GET['id'] ?>&md=<?= $_GET['md'] ?>"
            class="btn btn-flat btn-sm <?= $pagina == 'cursos_publicacoesNovo.php' ? 'bgverde' : 'bgverde' ?>"
            title="Nova publicação">
            <i class="bi bi-file-text"></i> +
        </a>
        <?php if ($pagina == 'cursos_publicacaoEditarTexto.php'): ?>
            <a href="#" id="abrirPainelFotos" class="btn btn-flat btn-sm " title="Ver imagens">
                <i class="bi bi-images"></i> Ver imagens (<?= $qtdFotos ?>)
            </a>
        <?php endif; ?>

        <a href="cursos_modulos.php?id=<?= $_GET['id'] ?>&md=<?= $_GET['md'] ?>"
            class="btn btn-flat btn-sm <?= $pagina == 'cursos_modulos.php' ? 'btn-success' : '' ?>"
            title="Ver módulos">
            <i class="bi bi-diagram-3 text-default"></i>
        </a>





    <?php endif; ?>



</div>
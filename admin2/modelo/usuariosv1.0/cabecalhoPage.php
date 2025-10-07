<!-- <h3><i class="bi bi-person-lines-fill me-1"></i>USUÁRIO</h3> -->
<!-- Miniatura da foto -->
<?php if (empty($nome)):


    echo ('<meta http-equiv="refresh" content="0; url=index.php">');
    exit();
endif;

?>
<div class="d-flex align-items-center gap-3 mb-4 p-3 border-bottom">
    <div>
        <a href="#" data-bs-toggle="modal" data-bs-target="#modalFotoUsuario">
            <img src="/fotos/usuarios/<?= htmlspecialchars($pastasc) ?>/<?= htmlspecialchars($imagem200) ?>"
                alt="Foto do usuário"
                class="rounded-circle border shadow-sm"
                style="width: 80px; height: 80px; object-fit: cover;"
                onerror="this.onerror=null; this.src='/fotos/usuarios/usuario.png';">
        </a>
    </div>
    <div>
        <h4 class="mb-0 fw-bold"><?= htmlspecialchars($nome ?? '') ?></h4>
        <small class="text-muted">Última turma: <?= htmlspecialchars($ultimaTurma ?? '') ?></small><br>
        <small class="text-muted">Código: <?= htmlspecialchars($codigoCadastro ?? '') ?></small>
    </div>
</div>


<!-- Modal de visualização da imagem -->
<!-- Modal de visualização da imagem -->
<div class="modal fade" id="modalFotoUsuario" tabindex="-1" aria-labelledby="fotoUsuarioLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-fullscreen-sm-down">
        <div class="modal-content bg-dark text-center p-3 border-0">
            <button type="button" class="btn-close btn-close-white ms-auto mb-2" data-bs-dismiss="modal" aria-label="Fechar"></button>

            <div class="d-flex justify-content-center align-items-center" style="min-height: 80vh;">
                <img src="/fotos/usuarios/<?= htmlspecialchars($pastasc) ?>/<?= htmlspecialchars($imagem200) ?>"
                    alt="Foto ampliada"
                    class="img-fluid rounded shadow"
                    style="max-height: 90vh; width: auto;"
                    onerror="this.onerror=null; this.src='/fotos/usuarios/usuario.png';">
            </div>
        </div>
    </div>
</div>
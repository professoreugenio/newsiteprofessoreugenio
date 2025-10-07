<?php
// ==============================================
// BodybancodeImagensLista.php  (MÓDULO LISTA)
// ==============================================

// Variáveis úteis do admin logado (vindas do cookie)
$decadm = encrypt($_COOKIE['adminuserstart'] ?? '', 'd');
$expadm = explode("&", $decadm);
$codadm = $expadm[0] ?? '';

// Helper para escapar
if (!function_exists('e')) {
    function e($s)
    {
        return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
    }
}

// Consulta inicial das galerias + admin
try {
    $sql = "
    SELECT 
      b.codigobancoimagens,
      b.tituloBI,
      b.descricaoBI,
      b.pastaBI,
      b.idadminBI,
      b.dataBI,
      b.horaBI,
      u.nome AS admin_nome,
      u.pastasu,
      u.imagem200
    FROM a_site_banco_imagens b
    LEFT JOIN new_sistema_usuario u 
      ON u.codigousuario = b.idadminBI
    ORDER BY b.dataBI DESC, b.horaBI DESC
  ";
    $stmt = $con->prepare($sql);
    $stmt->execute();
    $galerias = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (\Throwable $e) {
    $galerias = [];
}

// Helper foto admin
function montarFotoAdmin($pasta = '', $img = '')
{
    $pasta = trim((string)$pasta);
    $img   = trim((string)$img);
    // fallback simples (ajuste se seu caminho de admins for outro)
    if ($img === '' || $img === 'usuario.jpg') {
        return '/fotos/usuarios/usuario.jpg';
    }
    if ($pasta !== '') {
        return '/fotos/usuarios/' . $pasta . '/' . $img;
    }
    return '/fotos/usuarios/' . $img;
}

// Cor de fundo/estilo escuro para a lista
?>
<div class="container-fluid px-2 py-3">

    <div class="d-flex align-items-center justify-content-between mb-3">
        <h5 class="text-white m-0">
            <i class="bi bi-images me-2"></i> Banco de Imagens — Galerias
        </h5>

        <button class="btn btn-success btn-sm d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#modalNovaGaleria" id="btnNovaGaleria">
            <i class="bi bi-plus-circle"></i> Nova Galeria
        </button>
    </div>

    <!-- LISTA (render inicial em PHP) -->
    <div id="wrapListaGalerias">
        <?php if (!empty($galerias)): ?>
            <ul class="list-group list-group-flush rounded overflow-hidden">
                <?php foreach ($galerias as $g):
                    $idEnc = encrypt($g['codigobancoimagens'], 'e');
                    $fotoAdmin = montarFotoAdmin($g['pastasu'] ?? '', $g['imagem200'] ?? '');
                    $titulo = $g['tituloBI'] ?: 'Galeria Sem Título';
                    $descricao = $g['descricaoBI'] ?: '';
                    $dataBI = $g['dataBI'] ? (new DateTime($g['dataBI']))->format('d/m/Y') : '';
                    $horaBI = $g['horaBI'] ?: '';
                    $adminNome = $g['admin_nome'] ?: 'Admin';
                ?>
                    <li class="list-group-item bg-dark text-light py-3">
                        <div class="d-flex gap-3 align-items-center justify-content-between flex-wrap">

                            <!-- ESQUERDA: avatar + textos -->
                            <div class="d-flex align-items-center gap-3">
                                <img src="<?= e($fotoAdmin) ?>" alt="Admin" class="rounded-circle" style="width:44px;height:44px;object-fit:cover;border:2px solid rgba(255,255,255,.2);">
                                <div>
                                    <div class="fw-bold"><?= e($titulo) ?></div>
                                    <?php if ($descricao): ?>
                                        <div class="text-secondary small"><?= e($descricao) ?></div>
                                    <?php endif; ?>
                                    <div class="small mt-1">
                                        <span class="badge bg-secondary me-1"><i class="bi bi-calendar3"></i> <?= e($dataBI) ?></span>
                                        <span class="badge bg-secondary me-1"><i class="bi bi-clock"></i> <?= e($horaBI) ?></span>
                                        <span class="badge bg-info text-dark"><i class="bi bi-person-circle"></i> <?= e($adminNome) ?></span>
                                    </div>
                                </div>
                            </div>

                            <!-- DIREITA: botões -->
                            <div class="d-flex gap-2">
                                <button
                                    class="btn btn-warning btn-sm text-dark btnEditarGaleria"
                                    data-id="<?= e($idEnc) ?>"
                                    data-titulo='<?= e($titulo) ?>'
                                    data-descricao='<?= e($descricao) ?>'
                                    title="Editar">
                                    <i class="bi bi-pencil-square"></i>
                                </button>

                                <button
                                    class="btn btn-danger btn-sm btnExcluirGaleria"
                                    data-id="<?= e($idEnc) ?>"
                                    data-titulo='<?= e($titulo) ?>'
                                    title="Excluir">
                                    <i class="bi bi-trash"></i>
                                </button>

                                <a
                                    class="btn btn-primary btn-sm"
                                    href="bancoimagens_galeria.php?id=<?= e($idEnc) ?>"
                                    title="Acessar galeria">
                                    <i class="bi bi-arrow-right-circle"></i>
                                </a>
                            </div>

                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <div class="alert alert-secondary border-0">Nenhuma galeria cadastrada.</div>
        <?php endif; ?>
    </div>
</div>

<!-- MODAL: NOVA GALERIA -->
<div class="modal fade" id="modalNovaGaleria" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form id="formNovaGaleria" class="modal-content bg-dark text-light needs-validation" novalidate>
            <div class="modal-header border-secondary">
                <h6 class="modal-title"><i class="bi bi-plus-circle me-2"></i>Nova Galeria</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body">

                <div class="mb-3">
                    <label class="form-label">Título</label>
                    <input type="text" name="titulo" class="form-control" required>
                    <div class="invalid-feedback">Informe um título.</div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Descrição</label>
                    <textarea name="descricao" class="form-control" rows="3" placeholder="Opcional"></textarea>
                </div>

                <!-- id do admin (hidden) e exibição informativa -->
                <input type="hidden" name="idadmin" value="<?= e($codadm) ?>">
                <div class="small text-secondary">
                    <i class="bi bi-person-badge me-1"></i> ID do admin atual: <strong><?= e($codadm) ?></strong>
                </div>
            </div>
            <div class="modal-footer border-secondary">
                <button type="button" class="btn btn-outline-light btn-sm" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-success btn-sm">
                    <i class="bi bi-check2-circle me-1"></i>Salvar
                </button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL: EDITAR GALERIA -->
<div class="modal fade" id="modalEditarGaleria" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form id="formEditarGaleria" class="modal-content bg-dark text-light needs-validation" novalidate>
            <input type="hidden" name="idgaleria" id="editIdEnc">
            <div class="modal-header border-secondary">
                <h6 class="modal-title"><i class="bi bi-pencil-square me-2"></i>Editar Galeria</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body">

                <div class="mb-3">
                    <label class="form-label">Título</label>
                    <input type="text" name="titulo" id="editTitulo" class="form-control" required>
                    <div class="invalid-feedback">Informe um título.</div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Descrição</label>
                    <textarea name="descricao" id="editDescricao" class="form-control" rows="3" placeholder="Opcional"></textarea>
                </div>

            </div>
            <div class="modal-footer border-secondary">
                <button type="button" class="btn btn-outline-light btn-sm" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-warning btn-sm text-dark">
                    <i class="bi bi-save2 me-1"></i>Atualizar
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ESTILO FINO: escuro, botões compactos e coloridos -->
<style>
    #wrapListaGalerias .list-group-item {
        border-color: rgba(255, 255, 255, .06);
    }

    .btn {
        padding: .35rem .6rem;
    }
</style>

<!-- SCRIPTS (jQuery + Bootstrap) -->
<script>
    (function() {
        const $wrap = $("#wrapListaGalerias");

        // Validação simples BS5
        (function bsValidation() {
            const forms = document.querySelectorAll('.needs-validation');
            Array.prototype.slice.call(forms).forEach(function(form) {
                form.addEventListener('submit', function(event) {
                    if (!form.checkValidity()) {
                        event.preventDefault();
                        event.stopPropagation();
                    }
                    form.classList.add('was-validated');
                }, false);
            });
        })();

        // Recarregar lista via AJAX (após insert/update/delete)
        function carregarGalerias() {
            $.ajax({
                url: "bancodeImagens1.0/ajax_bancoGaleriasList.php",
                method: "GET",
                dataType: "html",
                success: function(html) {
                    $wrap.html(html);
                }
            });
        }

        // NOVA GALERIA - submit
        $("#formNovaGaleria").on("submit", function(e) {
            e.preventDefault();
            const $form = $(this);
            if (!this.checkValidity()) {
                return;
            }

            $.ajax({
                url: "bancodeImagens1.0/ajax_bancoNovaGaleria.php",
                method: "POST",
                data: $form.serialize(),
                dataType: "json",
                success: function(r) {
                    if (r && r.status === 'ok') {
                        $("#modalNovaGaleria").modal('hide');
                        $form[0].reset();
                        $form.removeClass('was-validated');
                        carregarGalerias();
                    } else {
                        alert(r && r.mensagem ? r.mensagem : "Erro ao salvar.");
                    }
                },
                error: function() {
                    alert("Falha na requisição.");
                }
            });
        });

        // Abrir modal EDITAR com dados
        $(document).on("click", ".btnEditarGaleria", function() {
            const idEnc = $(this).data("id");
            const titulo = $(this).data("titulo");
            const descricao = $(this).data("descricao");

            $("#editIdEnc").val(idEnc);
            $("#editTitulo").val(titulo);
            $("#editDescricao").val(descricao);

            $("#modalEditarGaleria").modal("show");
        });

        // EDITAR - submit
        $("#formEditarGaleria").on("submit", function(e) {
            e.preventDefault();
            const $form = $(this);
            if (!this.checkValidity()) {
                return;
            }

            $.ajax({
                url: "bancodeImagens1.0/ajax_bancoGaleriaUpdate.php",
                method: "POST",
                data: $form.serialize(),
                dataType: "json",
                success: function(r) {
                    if (r && r.status === 'ok') {
                        $("#modalEditarGaleria").modal('hide');
                        carregarGalerias();
                    } else {
                        alert(r && r.mensagem ? r.mensagem : "Erro ao atualizar.");
                    }
                },
                error: function() {
                    alert("Falha na requisição.");
                }
            });
        });

        // EXCLUIR
        $(document).on("click", ".btnExcluirGaleria", function() {
            const idEnc = $(this).data("id");
            const titulo = $(this).data("titulo");

            if (!confirm("Excluir a galeria \"" + titulo + "\"? Esta ação não poderá ser desfeita.")) {
                return;
            }

            $.ajax({
                url: "bancodeImagens1.0/ajax_bancoGaleriaDelete.php",
                method: "POST",
                data: {
                    idgaleria: idEnc
                },
                dataType: "json",
                success: function(r) {
                    if (r && r.status === 'ok') {
                        carregarGalerias();
                    } else {
                        alert(r && r.mensagem ? r.mensagem : "Erro ao excluir.");
                    }
                },
                error: function() {
                    alert("Falha na requisição.");
                }
            });
        });

    })();
</script>